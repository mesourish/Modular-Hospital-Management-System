<?php

namespace App\Http\Controllers;

use App\Core\Audit\AuditLogger;
use App\Core\Tenancy\TenantManager;
use App\Models\Branch;
use App\Models\HealthCheckupPackage;
use App\Models\Hospital;
use App\Models\PackageBooking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class HealthPackageController extends Controller
{
    /**
     * Display Health Checkup Packages master catalogue and bookings
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $hospital = TenantManager::getHospital();

        if (! $hospital) {
            $hospital = Hospital::where('is_active', true)->first();
            if ($hospital) {
                TenantManager::setActiveHospital($hospital);
            }
        }

        if (! $hospital && ! $user->isSuperAdmin()) {
            return redirect()->route('login');
        }

        $hospitalId = $hospital?->id;

        // Super Admin can view specific hospital or all
        $hospitals = $user->isSuperAdmin() ? Hospital::where('is_active', true)->get() : collect([$hospital]);

        // Branch options for assignment
        $branches = Branch::where('hospital_id', $hospitalId)->where('is_active', true)->get();

        $query = HealthCheckupPackage::with(['hospital', 'branch', 'bookings']);

        if (! $user->isSuperAdmin() || $request->filled('hospital_id')) {
            $effectiveHospitalId = $request->query('hospital_id', $hospitalId);
            $query->where('hospital_id', $effectiveHospitalId);
        }

        // Branch Admin scoping
        if ($user->isRestrictedToBranch()) {
            $query->where(function ($q) use ($user) {
                $q->whereNull('branch_id')->orWhere('branch_id', $user->branch_id);
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->query('category'));
        }

        if ($request->filled('branch_filter')) {
            $branchFilter = $request->query('branch_filter');
            if ($branchFilter === 'all') {
                $query->whereNull('branch_id');
            } elseif (is_numeric($branchFilter)) {
                $query->where('branch_id', (int) $branchFilter);
            }
        }

        $packages = $query->orderBy('is_popular', 'desc')->orderBy('name')->get();

        // Recent Bookings
        $bookingsQuery = PackageBooking::with(['package', 'branch', 'hospital'])
            ->orderByDesc('created_at');

        if (! $user->isSuperAdmin() || $request->filled('hospital_id')) {
            $bookingsQuery->where('hospital_id', $hospitalId);
        }

        if ($user->isRestrictedToBranch()) {
            $bookingsQuery->where('branch_id', $user->branch_id);
        }

        $recentBookings = $bookingsQuery->limit(10)->get();

        // Summary metrics
        $metrics = [
            'total_packages' => $packages->count(),
            'active_packages' => $packages->where('is_active', true)->count(),
            'popular_packages' => $packages->where('is_popular', true)->count(),
            'total_bookings' => PackageBooking::when($hospitalId && ! $user->isSuperAdmin(), fn ($q) => $q->where('hospital_id', $hospitalId))->count(),
        ];

        return view('modules.packages.index', compact(
            'packages',
            'recentBookings',
            'hospitals',
            'branches',
            'hospital',
            'user',
            'metrics'
        ));
    }

    /**
     * Store new Health Checkup Package
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $hospital = TenantManager::getHospital() ?? Hospital::where('is_active', true)->first();

        $validated = $request->validate([
            'hospital_id' => 'nullable|exists:hospitals,id',
            'branch_id' => 'nullable|exists:branches,id',
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:health_checkup_packages,code',
            'category' => 'required|string|max:100',
            'original_price' => 'required|numeric|min:0',
            'discounted_price' => 'required|numeric|min:0|lte:original_price',
            'tests_count' => 'required|integer|min:1',
            'turnaround_time' => 'required|string|max:100',
            'fasting_required' => 'nullable|boolean',
            'fasting_hours' => 'nullable|integer|min:0|max:24',
            'gender_target' => 'required|in:All,Male,Female',
            'age_group' => 'required|string|max:50',
            'tests_included' => 'nullable|string',
            'description' => 'nullable|string',
            'is_popular' => 'nullable|boolean',
        ]);

        $hospitalId = $validated['hospital_id'] ?? ($hospital?->id);
        $branchId = $validated['branch_id'] ?? null;

        // If user is restricted to a branch, force branch
        if ($user->isRestrictedToBranch()) {
            $branchId = $user->branch_id;
        }

        // Parse tests included (split by newline or comma)
        $testsArray = [];
        if (! empty($validated['tests_included'])) {
            $testsArray = array_values(array_filter(array_map('trim', preg_split('/[\r\n]+/', $validated['tests_included']))));
        }

        $package = HealthCheckupPackage::create([
            'hospital_id' => $hospitalId,
            'branch_id' => $branchId,
            'name' => $validated['name'],
            'code' => strtoupper($validated['code']),
            'slug' => Str::slug($validated['name']),
            'category' => $validated['category'],
            'original_price' => $validated['original_price'],
            'discounted_price' => $validated['discounted_price'],
            'tests_count' => $validated['tests_count'],
            'turnaround_time' => $validated['turnaround_time'],
            'fasting_required' => $request->boolean('fasting_required', true),
            'fasting_hours' => $validated['fasting_hours'] ?? 10,
            'gender_target' => $validated['gender_target'],
            'age_group' => $validated['age_group'],
            'tests_included' => $testsArray,
            'description' => $validated['description'] ?? null,
            'is_popular' => $request->boolean('is_popular', false),
            'is_active' => true,
        ]);

        AuditLogger::log(
            moduleKey: 'packages',
            action: 'package_created',
            entityType: 'HealthCheckupPackage',
            entityId: $package->id,
            details: [
                'name' => $package->name,
                'code' => $package->code,
                'price' => $package->discounted_price,
            ],
            hospitalId: $hospitalId
        );

        return back()->with('success', "Health Checkup Package '{$package->name}' created successfully.");
    }

    /**
     * Update existing Health Checkup Package
     */
    public function update(Request $request, HealthCheckupPackage $healthPackage)
    {
        $user = Auth::user();

        // Branch Admin protection
        if ($user->isRestrictedToBranch() && $healthPackage->branch_id !== null && $healthPackage->branch_id !== $user->branch_id) {
            abort(403, 'Unauthorized to edit packages from other branches.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'original_price' => 'required|numeric|min:0',
            'discounted_price' => 'required|numeric|min:0|lte:original_price',
            'tests_count' => 'required|integer|min:1',
            'turnaround_time' => 'required|string|max:100',
            'gender_target' => 'required|in:All,Male,Female',
            'age_group' => 'required|string|max:50',
            'fasting_hours' => 'nullable|integer|min:0|max:24',
            'tests_included' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        $testsArray = [];
        if (! empty($validated['tests_included'])) {
            $testsArray = array_values(array_filter(array_map('trim', preg_split('/[\r\n]+/', $validated['tests_included']))));
        }

        $healthPackage->update([
            'name' => $validated['name'],
            'category' => $validated['category'],
            'original_price' => $validated['original_price'],
            'discounted_price' => $validated['discounted_price'],
            'tests_count' => $validated['tests_count'],
            'turnaround_time' => $validated['turnaround_time'],
            'gender_target' => $validated['gender_target'],
            'age_group' => $validated['age_group'],
            'fasting_required' => $request->boolean('fasting_required', true),
            'fasting_hours' => $validated['fasting_hours'] ?? 10,
            'tests_included' => count($testsArray) > 0 ? $testsArray : $healthPackage->tests_included,
            'description' => $validated['description'] ?? $healthPackage->description,
            'is_popular' => $request->boolean('is_popular', false),
        ]);

        AuditLogger::log(
            moduleKey: 'packages',
            action: 'package_updated',
            entityType: 'HealthCheckupPackage',
            entityId: $healthPackage->id,
            details: ['name' => $healthPackage->name],
            hospitalId: $healthPackage->hospital_id
        );

        return back()->with('success', "Package '{$healthPackage->name}' updated successfully.");
    }

    /**
     * Toggle package active status
     */
    public function toggleStatus(HealthCheckupPackage $healthPackage)
    {
        $user = Auth::user();

        if ($user->isRestrictedToBranch() && $healthPackage->branch_id !== null && $healthPackage->branch_id !== $user->branch_id) {
            abort(403, 'Unauthorized.');
        }

        $healthPackage->is_active = ! $healthPackage->is_active;
        $healthPackage->save();

        return back()->with('success', 'Package status updated to '.($healthPackage->is_active ? 'Active' : 'Inactive').'.');
    }

    /**
     * Delete package
     */
    public function destroy(HealthCheckupPackage $healthPackage)
    {
        $user = Auth::user();

        if ($user->isRestrictedToBranch() && $healthPackage->branch_id !== null && $healthPackage->branch_id !== $user->branch_id) {
            abort(403, 'Unauthorized.');
        }

        $name = $healthPackage->name;
        $healthPackage->delete();

        return back()->with('success', "Package '{$name}' deleted successfully.");
    }
}
