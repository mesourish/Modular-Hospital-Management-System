<?php

namespace App\Http\Controllers;

use App\Core\Audit\AuditLogger;
use App\Core\Constants\HospitalConstants;
use App\Core\Tenancy\TenantManager;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Hospital;
use App\Models\HospitalConstant;
use App\Models\SubDepartment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConstantsController extends Controller
{
    /**
     * Master Constants Management Workspace
     * For Super Admin, Hospital Director, and Branch Administrators
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $activeHospital = TenantManager::getHospital();

        if (! $activeHospital && ! $user->isSuperAdmin()) {
            return redirect()->route('hospital.dashboard')->with('error', 'Select a hospital first.');
        }

        $hospitals = $user->isSuperAdmin() ? Hospital::where('is_active', true)->get() : collect([$activeHospital]);

        $selectedHospitalId = $request->query('hospital_id', $activeHospital?->id ?? ($hospitals->first()?->id));
        $selectedBranchId = $request->query('branch_id');

        if ($user->role === 'branch_admin' && $user->branch_id) {
            $selectedBranchId = $user->branch_id;
            $selectedHospitalId = $user->hospital_id;
        }

        $branches = collect();
        if ($selectedHospitalId) {
            $branches = Branch::where('hospital_id', $selectedHospitalId)->get();
        }

        // Query Departments
        $depQuery = Department::with(['subDepartments', 'branch']);
        if ($selectedHospitalId) {
            $depQuery->where('hospital_id', $selectedHospitalId);
        }
        if ($selectedBranchId) {
            $depQuery->where(function ($q) use ($selectedBranchId) {
                $q->where('branch_id', $selectedBranchId)->orWhereNull('branch_id');
            });
        }
        $departments = $depQuery->orderBy('name')->get();

        // Query Sub-Departments
        $subDepQuery = SubDepartment::with(['department', 'branch']);
        if ($selectedHospitalId) {
            $subDepQuery->where('hospital_id', $selectedHospitalId);
        }
        if ($selectedBranchId) {
            $subDepQuery->where(function ($q) use ($selectedBranchId) {
                $q->where('branch_id', $selectedBranchId)->orWhereNull('branch_id');
            });
        }
        $subDepartments = $subDepQuery->orderBy('name')->get();

        // Query Designations
        $desigQuery = Designation::with('department');
        if ($selectedHospitalId) {
            $desigQuery->where('hospital_id', $selectedHospitalId);
        }
        $designations = $desigQuery->orderBy('title')->get();

        // Query Master Hospital Constants
        $constQuery = HospitalConstant::with('branch');
        if ($selectedHospitalId) {
            $constQuery->where(function ($q) use ($selectedHospitalId) {
                $q->where('hospital_id', $selectedHospitalId)->orWhereNull('hospital_id');
            });
        }
        if ($selectedBranchId) {
            $constQuery->where(function ($q) use ($selectedBranchId) {
                $q->where('branch_id', $selectedBranchId)->orWhereNull('branch_id');
            });
        }
        $constants = $constQuery->orderBy('category')->orderBy('sort_order')->get()->groupBy('category');

        $activeTab = $request->query('tab', 'departments');

        return view('constants.index', compact(
            'user',
            'hospitals',
            'branches',
            'selectedHospitalId',
            'selectedBranchId',
            'departments',
            'subDepartments',
            'designations',
            'constants',
            'activeTab'
        ));
    }

    /**
     * Store Department
     */
    public function storeDepartment(Request $request)
    {
        $user = Auth::user();
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'code' => 'required|string|max:50',
            'type' => 'required|in:clinical,diagnostic,pharmacy,support,administrative',
            'description' => 'nullable|string|max:500',
            'branch_id' => 'nullable|exists:branches,id',
            'hospital_id' => 'nullable|exists:hospitals,id',
        ]);

        $hospitalId = $validated['hospital_id'] ?? $user->hospital_id ?? TenantManager::getHospitalId();
        if ($user->role === 'branch_admin' && $user->branch_id) {
            $validated['branch_id'] = $user->branch_id;
            $hospitalId = $user->hospital_id;
        }

        $department = Department::create([
            'hospital_id' => $hospitalId,
            'branch_id' => $validated['branch_id'] ?? null,
            'name' => $validated['name'],
            'code' => strtoupper($validated['code']),
            'type' => $validated['type'],
            'description' => $validated['description'] ?? null,
            'is_active' => true,
        ]);

        AuditLogger::log('constants', 'department_created', 'Department', $department->id, [
            'name' => $department->name,
            'code' => $department->code,
        ], $hospitalId);

        return back()->with('success', "Department '{$department->name}' created successfully.");
    }

    /**
     * Store Sub-Department
     */
    public function storeSubDepartment(Request $request)
    {
        $user = Auth::user();
        $validated = $request->validate([
            'department_id' => 'required|exists:departments,id',
            'name' => 'required|string|max:150',
            'code' => 'required|string|max:50',
            'description' => 'nullable|string|max:500',
            'branch_id' => 'nullable|exists:branches,id',
            'hospital_id' => 'nullable|exists:hospitals,id',
        ]);

        $hospitalId = $validated['hospital_id'] ?? $user->hospital_id ?? TenantManager::getHospitalId();
        if ($user->role === 'branch_admin' && $user->branch_id) {
            $validated['branch_id'] = $user->branch_id;
            $hospitalId = $user->hospital_id;
        }

        $subDep = SubDepartment::create([
            'hospital_id' => $hospitalId,
            'branch_id' => $validated['branch_id'] ?? null,
            'department_id' => $validated['department_id'],
            'name' => $validated['name'],
            'code' => strtoupper($validated['code']),
            'description' => $validated['description'] ?? null,
            'is_active' => true,
        ]);

        AuditLogger::log('constants', 'sub_department_created', 'SubDepartment', $subDep->id, [
            'name' => $subDep->name,
            'code' => $subDep->code,
        ], $hospitalId);

        return back()->with('success', "Sub-Department '{$subDep->name}' added successfully.");
    }

    /**
     * Store Staff Designation
     */
    public function storeDesignation(Request $request)
    {
        $user = Auth::user();
        $validated = $request->validate([
            'title' => 'required|string|max:150',
            'department_id' => 'nullable|exists:departments,id',
            'min_salary' => 'nullable|numeric|min:0',
            'max_salary' => 'nullable|numeric|min:0',
            'description' => 'nullable|string|max:500',
            'hospital_id' => 'nullable|exists:hospitals,id',
        ]);

        $hospitalId = $validated['hospital_id'] ?? $user->hospital_id ?? TenantManager::getHospitalId();

        $designation = Designation::create([
            'hospital_id' => $hospitalId,
            'department_id' => $validated['department_id'] ?? null,
            'title' => $validated['title'],
            'min_salary' => $validated['min_salary'] ?? 0,
            'max_salary' => $validated['max_salary'] ?? 0,
            'description' => $validated['description'] ?? null,
            'is_active' => true,
        ]);

        AuditLogger::log('constants', 'designation_created', 'Designation', $designation->id, [
            'title' => $designation->title,
        ], $hospitalId);

        return back()->with('success', "Designation '{$designation->title}' added successfully.");
    }

    /**
     * Store Custom Master Constant (Triage, Ambulance Type, Shift, Payment Mode, Ward Type)
     */
    public function storeConstant(Request $request)
    {
        $user = Auth::user();
        $validated = $request->validate([
            'category' => 'required|string|max:50',
            'key' => 'required|string|max:50',
            'name' => 'required|string|max:150',
            'code' => 'required|string|max:50',
            'badge_color' => 'nullable|string|max:30',
            'sort_order' => 'nullable|integer',
            'description' => 'nullable|string|max:500',
            'branch_id' => 'nullable|exists:branches,id',
            'hospital_id' => 'nullable|exists:hospitals,id',
        ]);

        $hospitalId = $validated['hospital_id'] ?? $user->hospital_id ?? TenantManager::getHospitalId();
        if ($user->role === 'branch_admin' && $user->branch_id) {
            $validated['branch_id'] = $user->branch_id;
            $hospitalId = $user->hospital_id;
        }

        $constant = HospitalConstant::updateOrCreate(
            [
                'hospital_id' => $hospitalId,
                'category' => $validated['category'],
                'key' => $validated['key'],
            ],
            [
                'branch_id' => $validated['branch_id'] ?? null,
                'name' => $validated['name'],
                'code' => strtoupper($validated['code']),
                'badge_color' => $validated['badge_color'] ?? 'primary',
                'sort_order' => $validated['sort_order'] ?? 0,
                'description' => $validated['description'] ?? null,
                'is_active' => true,
            ]
        );

        AuditLogger::log('constants', 'constant_saved', 'HospitalConstant', $constant->id, [
            'category' => $constant->category,
            'name' => $constant->name,
            'code' => $constant->code,
        ], $hospitalId);

        return back()->with('success', "Master Constant '{$constant->name}' saved successfully.");
    }

    /**
     * Toggle Active State
     */
    public function toggleConstant(HospitalConstant $constant)
    {
        $constant->update(['is_active' => ! $constant->is_active]);

        return back()->with('success', "Constant '{$constant->name}' status updated.");
    }

    /**
     * Pre-populate Realistic Clinical & Administrative Constants
     */
    public function seedDefaults(Request $request)
    {
        $user = Auth::user();
        $hospitalId = $request->input('hospital_id', $user->hospital_id ?? TenantManager::getHospitalId());

        if (! $hospitalId) {
            return back()->with('error', 'Select a target hospital to seed master constants.');
        }

        // 1. Seed Realistic Hospital Constants (Triage, Ambulance, Shifts, etc.)
        foreach (HospitalConstants::SYSTEM_CONSTANTS as $category => $items) {
            foreach ($items as $item) {
                HospitalConstant::firstOrCreate(
                    [
                        'hospital_id' => $hospitalId,
                        'category' => $category,
                        'key' => $item['key'],
                    ],
                    [
                        'name' => $item['name'],
                        'code' => $item['code'],
                        'badge_color' => $item['badge_color'] ?? 'primary',
                        'sort_order' => $item['sort_order'] ?? 0,
                        'description' => $item['description'] ?? null,
                        'is_active' => true,
                    ]
                );
            }
        }

        // 2. Seed Standard Departments
        foreach (HospitalConstants::DEPARTMENTS as $depData) {
            $dep = Department::firstOrCreate(
                [
                    'hospital_id' => $hospitalId,
                    'code' => $depData['code'],
                ],
                [
                    'name' => $depData['name'],
                    'type' => $depData['type'],
                    'description' => $depData['description'],
                    'is_active' => true,
                ]
            );

            // Sub-departments for this department code
            if (isset(HospitalConstants::SUB_DEPARTMENTS[$depData['code']])) {
                foreach (HospitalConstants::SUB_DEPARTMENTS[$depData['code']] as $subData) {
                    SubDepartment::firstOrCreate(
                        [
                            'hospital_id' => $hospitalId,
                            'department_id' => $dep->id,
                            'code' => $subData['code'],
                        ],
                        [
                            'name' => $subData['name'],
                            'description' => $subData['description'],
                            'is_active' => true,
                        ]
                    );
                }
            }
        }

        // 3. Seed Designations
        foreach (HospitalConstants::DESIGNATIONS as $desig) {
            $dept = Department::where('hospital_id', $hospitalId)
                ->where('code', $desig['department_code'])
                ->first();

            Designation::firstOrCreate(
                [
                    'hospital_id' => $hospitalId,
                    'title' => $desig['title'],
                ],
                [
                    'department_id' => $dept?->id,
                    'min_salary' => $desig['min_salary'],
                    'max_salary' => $desig['max_salary'],
                    'description' => $desig['description'],
                    'is_active' => true,
                ]
            );
        }

        AuditLogger::log('constants', 'default_constants_seeded', 'Hospital', $hospitalId, [
            'seeded_by' => $user->name,
            'hospital_id' => $hospitalId,
        ], $hospitalId);

        return back()->with('success', 'All realistic healthcare master constants, departments, sub-departments, and designations have been seeded successfully!');
    }
}
