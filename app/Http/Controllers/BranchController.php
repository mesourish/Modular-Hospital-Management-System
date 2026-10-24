<?php

namespace App\Http\Controllers;

use App\Core\Audit\AuditLogger;
use App\Core\Tenancy\TenantManager;
use App\Models\Appointment;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PharmacyStore;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BranchController extends Controller
{
    /**
     * Hierarchical Multi-Branch Overview & Financial Center
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $hospital = TenantManager::getHospital();
        if (! $hospital) {
            return redirect()->route('hospital.dashboard')->with('error', 'Select a hospital first.');
        }

        // Branch Isolation: Super Admin can see all; Branch Admin can only see their assigned branch
        $branchesQuery = Branch::where('hospital_id', $hospital->id)
            ->withCount(['employees', 'appointments', 'admissions', 'invoices', 'payments'])
            ->orderByDesc('is_main_branch')
            ->orderBy('name');

        if ($user && $user->isRestrictedToBranch()) {
            $branchesQuery->where('id', $user->branch_id);
        }

        $branches = $branchesQuery->get();

        $selectedBranchId = $request->query('branch_id');
        $selectedBranch = null;

        if ($user && $user->isRestrictedToBranch()) {
            $selectedBranchId = (string) $user->branch_id;
            $selectedBranch = $branches->first();
        } elseif ($selectedBranchId && $selectedBranchId !== 'all') {
            $selectedBranch = $branches->firstWhere('id', (int) $selectedBranchId);
        }

        // Scoped financial calculations
        $invoicesQuery = Invoice::where('hospital_id', $hospital->id);
        $paymentsQuery = Payment::where('hospital_id', $hospital->id);

        if ($selectedBranch) {
            $invoicesQuery->where('branch_id', $selectedBranch->id);
            $paymentsQuery->where('branch_id', $selectedBranch->id);
        } elseif ($user && $user->isRestrictedToBranch()) {
            $invoicesQuery->where('branch_id', $user->branch_id);
            $paymentsQuery->where('branch_id', $user->branch_id);
        }

        $totalInvoiced = (float) (clone $invoicesQuery)->sum('net_payable');
        $totalCollected = (float) (clone $paymentsQuery)->sum('amount');
        $totalOutstanding = max(0, $totalInvoiced - $totalCollected);

        // Payment mode breakdown (UPI, Card, Cash, NEFT/Insurance)
        $paymentModes = (clone $paymentsQuery)
            ->selectRaw('payment_method, COUNT(*) as count, SUM(amount) as total')
            ->groupBy('payment_method')
            ->get();

        // Branch-by-branch financial matrix
        $branchFinancials = [];
        foreach ($branches as $branch) {
            $bInvoiced = (float) Invoice::where('hospital_id', $hospital->id)->where('branch_id', $branch->id)->sum('net_payable');
            $bCollected = (float) Payment::where('hospital_id', $hospital->id)->where('branch_id', $branch->id)->sum('amount');
            $bDue = max(0, $bInvoiced - $bCollected);

            $branchFinancials[$branch->id] = [
                'branch' => $branch,
                'invoiced' => $bInvoiced,
                'collected' => $bCollected,
                'due' => $bDue,
                'collection_rate' => $bInvoiced > 0 ? round(($bCollected / $bInvoiced) * 100, 1) : 0,
            ];
        }

        // Doctor Clinical Earnings & Revenue Statistics for the active branch (or network)
        $branchScopeId = $selectedBranch ? $selectedBranch->id : null;
        $doctors = User::where('role', 'doctor')->get();

        $doctorEarnings = [];
        $totalDoctorClinicalRevenue = 0;
        $totalDoctorPayout = 0;
        $totalHospitalRetainedShare = 0;

        foreach ($doctors as $doctor) {
            $employee = Employee::where('user_id', $doctor->id)->first();

            // Appointments for this doctor scoped to branch
            $appQuery = Appointment::where('doctor_id', $doctor->id);
            if ($branchScopeId) {
                $appQuery->where('branch_id', $branchScopeId);
            }
            $appointmentCount = $appQuery->count();
            $completedAppointments = (clone $appQuery)->where('status', 'completed')->count();

            // Consultations OPD Fee: ₹950 standard consultation
            $opdRevenue = $appointmentCount * 950.0;

            // Surgeries for this doctor scoped to branch
            $surgQuery = DB::table('ot_surgeries')->where('lead_surgeon_id', $doctor->id);
            if ($branchScopeId) {
                $surgQuery->where('branch_id', $branchScopeId);
            }
            $surgeries = $surgQuery->get();
            $surgeryCount = $surgeries->count();

            $surgicalRevenue = 0;
            foreach ($surgeries as $surg) {
                $surgicalRevenue += match ($surg->surgery_category ?? 'major') {
                    'super_major' => 125000.0,
                    'major' => 60000.0,
                    'minor' => 25000.0,
                    'day_care' => 15000.0,
                    default => 40000.0,
                };
            }

            $grossEarned = $opdRevenue + $surgicalRevenue;
            // Standard Clinical Revenue Sharing: Doctor retains 70% professional fee, Branch retains 30% facility fee
            $doctorShare = round($grossEarned * 0.70, 2);
            $hospitalShare = round($grossEarned * 0.30, 2);

            $totalDoctorClinicalRevenue += $grossEarned;
            $totalDoctorPayout += $doctorShare;
            $totalHospitalRetainedShare += $hospitalShare;

            $doctorEarnings[] = [
                'doctor' => $doctor,
                'employee' => $employee,
                'appointment_count' => $appointmentCount,
                'completed_appointments' => $completedAppointments,
                'opd_revenue' => $opdRevenue,
                'surgery_count' => $surgeryCount,
                'surgical_revenue' => $surgicalRevenue,
                'gross_earned' => $grossEarned,
                'doctor_share' => $doctorShare,
                'hospital_share' => $hospitalShare,
                'assigned_branch_id' => $doctor->branch_id,
            ];
        }

        // Sort doctor earnings by gross revenue descending
        usort($doctorEarnings, fn ($a, $b) => $b['gross_earned'] <=> $a['gross_earned']);

        // Parent branches list for standalone/connected modal
        $parentBranches = Branch::where('hospital_id', $hospital->id)
            ->where(function ($q) {
                $q->whereNull('facility_type')->orWhere('facility_type', 'hospital');
            })
            ->get();

        // Recent payment transactions
        $recentPayments = (clone $paymentsQuery)
            ->with(['invoice.patient', 'branch'])
            ->latest()
            ->take(8)
            ->get();

        // Recent branch invoices
        $recentInvoices = (clone $invoicesQuery)
            ->with(['patient', 'branch'])
            ->latest()
            ->take(8)
            ->get();

        return view('branches.index', compact(
            'hospital',
            'branches',
            'selectedBranch',
            'totalInvoiced',
            'totalCollected',
            'totalOutstanding',
            'paymentModes',
            'branchFinancials',
            'doctorEarnings',
            'totalDoctorClinicalRevenue',
            'totalDoctorPayout',
            'totalHospitalRetainedShare',
            'parentBranches',
            'recentPayments',
            'recentInvoices'
        ));
    }

    /**
     * Onboard a new branch or standalone facility into the hierarchical network
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        if ($user && $user->isRestrictedToBranch()) {
            return back()->with('error', 'Unauthorized: Branch Admin cannot create new hospital branches. Contact Super Admin.');
        }

        $hospital = TenantManager::getHospital();
        if (! $hospital) {
            return back()->with('error', 'Select a hospital first.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:branches,code',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'pincode' => 'nullable|string|max:10',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'manager_name' => 'nullable|string|max:255',
            'is_main_branch' => 'nullable|boolean',
            'facility_type' => 'nullable|in:hospital,pharmacy,blood_bank,diagnostic_lab,clinic',
            'connection_mode' => 'nullable|in:standalone,branch_connected',
            'parent_branch_id' => 'nullable|exists:branches,id',
            'license_number' => 'nullable|string|max:100',
        ]);

        $facilityType = $validated['facility_type'] ?? 'hospital';
        $isStandalone = ($request->input('connection_mode') === 'standalone') || $request->boolean('is_standalone');
        $parentBranchId = $isStandalone ? null : ($validated['parent_branch_id'] ?? null);

        $isMain = $request->boolean('is_main_branch') && $facilityType === 'hospital';

        // If newly designated as main branch, demote previous main branch
        if ($isMain) {
            Branch::where('hospital_id', $hospital->id)->update(['is_main_branch' => false]);
        }

        $branch = Branch::create([
            'hospital_id' => $hospital->id,
            'name' => $validated['name'],
            'code' => strtoupper($validated['code']),
            'city' => $validated['city'],
            'state' => $validated['state'],
            'pincode' => $validated['pincode'] ?? '400001',
            'address' => $validated['address'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'] ?? null,
            'manager_name' => $validated['manager_name'] ?? 'Facility Operations Head',
            'is_main_branch' => $isMain,
            'facility_type' => $facilityType,
            'is_standalone' => $isStandalone,
            'parent_branch_id' => $parentBranchId,
            'license_number' => $validated['license_number'] ?? null,
            'is_active' => true,
        ]);

        // If this is a pharmacy facility, synchronize/create a PharmacyStore entry
        if ($facilityType === 'pharmacy') {
            PharmacyStore::create([
                'hospital_id' => $hospital->id,
                'branch_id' => $isStandalone ? null : $branch->id,
                'name' => $branch->name,
                'code' => $branch->code,
                'slug' => Str::slug($branch->name).'-'.strtolower($branch->code),
                'store_type' => $isStandalone ? 'standalone_retail' : 'retail_counter',
                'is_standalone' => $isStandalone,
                'license_number' => $validated['license_number'] ?? 'DL-20B-'.rand(10000, 99999),
                'manager_name' => $branch->manager_name,
                'phone' => $branch->phone,
                'email' => $branch->email,
                'address' => $branch->address,
                'is_active' => true,
            ]);
        }

        AuditLogger::log(
            action: 'branch.created',
            auditableType: 'Branch',
            auditableId: $branch->id,
            payload: [
                'name' => $branch->name,
                'code' => $branch->code,
                'facility_type' => $facilityType,
                'is_standalone' => $isStandalone,
                'parent_branch_id' => $parentBranchId,
                'license_number' => $branch->license_number,
            ]
        );

        $facilityLabel = ucfirst(str_replace('_', ' ', $facilityType));
        $connectionLabel = $isStandalone ? 'Direct Super Admin / Standalone Unit' : 'Connected Branch Unit';

        return redirect()->route('branches.index')->with('success', "{$facilityLabel} '{$branch->name}' ({$branch->code}) registered successfully as {$connectionLabel}.");
    }
}
