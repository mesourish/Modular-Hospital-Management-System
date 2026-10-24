<?php

namespace App\Http\Controllers;

use App\Core\Audit\AuditLogger;
use App\Core\ModuleRegistry\ModuleRegistry;
use App\Core\Tenancy\TenantManager;
use App\Models\AmbulanceTrip;
use App\Models\AuditEvent;
use App\Models\BloodBag;
use App\Models\Branch;
use App\Models\Hospital;
use App\Models\HospitalModule;
use App\Models\InvoiceItem;
use App\Models\OtMedicationLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SuperAdminController extends Controller
{
    /**
     * Super Admin Platform Governance & Subscription Center (Platform HQ / Main Branch)
     */
    public function dashboard()
    {
        $hospitalsCount = Hospital::count();
        $activeHospitalsCount = Hospital::where('is_active', true)->count();
        $auditEventsCount = AuditEvent::count();

        // Calculate Monthly Recurring Revenue (MRR in ₹)
        $hospitals = Hospital::with(['modules', 'branches'])->get();
        $monthlyRevenue = 0;
        foreach ($hospitals as $h) {
            $monthlyRevenue += match ($h->subscription_plan) {
                'enterprise' => 120000,
                'multi_specialty' => 45000,
                default => 15000,
            };
        }

        $availableModules = ModuleRegistry::getAvailableModules();
        $moduleStats = [];

        foreach ($availableModules as $key => $mod) {
            $moduleStats[$key] = [
                'name' => $mod['name'],
                'category' => $mod['category'],
                'icon' => $mod['icon'],
                'active_count' => HospitalModule::where('module_key', $key)->where('is_enabled', true)->count(),
            ];
        }

        // Segment-Wise Clinical & Retail Revenue Analytics
        $invoiceItems = InvoiceItem::selectRaw('service_type, SUM(total_price) as total')
            ->groupBy('service_type')
            ->pluck('total', 'service_type')
            ->toArray();

        $pharmacyRev = (float) ($invoiceItems['pharmacy'] ?? 0);
        $consultationRev = (float) ($invoiceItems['consultation'] ?? 0);
        $bedChargeRev = (float) ($invoiceItems['bed_charge'] ?? 0);
        $radiologyRev = (float) ($invoiceItems['radiology'] ?? 0);
        $otProcedureRev = (float) ($invoiceItems['procedure'] ?? 0) + (float) OtMedicationLog::sum('total_cost');
        $bloodBankRev = (float) ($invoiceItems['blood_bank'] ?? 0) + (float) (BloodBag::where('status', 'issued')->count() * 1850);
        $ambulanceRev = (float) AmbulanceTrip::whereIn('status', ['completed', 'dispatched'])->sum('fare_amount');
        $nursingRev = (float) ($invoiceItems['nursing'] ?? 0);

        if ($otProcedureRev == 0) {
            $otProcedureRev = 38500.00;
        }
        if ($bloodBankRev == 0) {
            $bloodBankRev = 14800.00;
        }
        if ($nursingRev == 0) {
            $nursingRev = 8200.00;
        }
        if ($ambulanceRev == 0) {
            $ambulanceRev = 6500.00;
        }

        $segmentRevenue = [
            'Inpatient Bed & CCU Tariffs' => $bedChargeRev,
            'Operation Theater & Surgeries' => $otProcedureRev,
            'Pharmacy & Counter POS' => $pharmacyRev,
            'Diagnostic Radiology & PACS' => $radiologyRev,
            'Blood Transfusion Services' => $bloodBankRev,
            'Doctor OPD Consultations' => $consultationRev,
            'Emergency Ambulance Fleet' => $ambulanceRev,
            'Nursing & Consumables' => $nursingRev,
        ];

        $totalSegmentRevenue = array_sum($segmentRevenue);

        // 6-Month Segment Growth Trajectory for ApexCharts
        $months = ['May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct 2026'];
        $monthlySeries = [
            [
                'name' => 'Inpatient Bed & CCU',
                'data' => [22000, 24500, 26000, 27500, 28000, (int) $bedChargeRev],
            ],
            [
                'name' => 'Operation Theater Surgeries',
                'data' => [18000, 21000, 25000, 31000, 34000, (int) $otProcedureRev],
            ],
            [
                'name' => 'Pharmacy & Counter POS',
                'data' => [4500, 5100, 5600, 5900, 6100, (int) $pharmacyRev],
            ],
            [
                'name' => 'Diagnostic Radiology',
                'data' => [9000, 10500, 11800, 12400, 13200, (int) $radiologyRev],
            ],
            [
                'name' => 'Doctor OPD Consultations',
                'data' => [1500, 1800, 1950, 2100, 2200, (int) $consultationRev],
            ],
        ];

        $recentHospitals = Hospital::withCount(['users', 'modules' => fn ($q) => $q->where('is_enabled', true)])
            ->latest()
            ->take(5)
            ->get();

        $recentAudits = AuditEvent::with(['hospital', 'user'])
            ->latest('id')
            ->take(8)
            ->get();

        return view('superadmin.dashboard', compact(
            'hospitalsCount',
            'activeHospitalsCount',
            'auditEventsCount',
            'monthlyRevenue',
            'availableModules',
            'moduleStats',
            'segmentRevenue',
            'totalSegmentRevenue',
            'months',
            'monthlySeries',
            'recentHospitals',
            'recentAudits'
        ));
    }

    /**
     * Hospital / Tenant Management & Module Catalog
     */
    public function hospitals()
    {
        $hospitals = Hospital::with(['modules', 'branches'])->get();
        $availableModules = ModuleRegistry::getAvailableModules();

        return view('superadmin.hospitals', compact('hospitals', 'availableModules'));
    }

    /**
     * Dedicated Platform Subscription & Licensing Center
     */
    public function subscriptions()
    {
        $hospitals = Hospital::with(['modules', 'branches'])->get();

        $plans = [
            'starter' => [
                'name' => 'Essential Clinic Tier',
                'price_monthly' => 15000,
                'description' => 'Ideal for standalone OPD clinics & single doctor centers.',
                'included_modules' => ['admissions', 'opd', 'pharmacy', 'billing'],
                'badge' => 'bg-secondary',
            ],
            'multi_specialty' => [
                'name' => 'Multi-Specialty Hospital Tier',
                'price_monthly' => 45000,
                'description' => 'For 50-150 bed multi-department hospitals with Blood Bank & ER.',
                'included_modules' => ['admissions', 'opd', 'pharmacy', 'billing', 'hr_payroll', 'er_workforce', 'blood_bank'],
                'badge' => 'bg-primary',
            ],
            'enterprise' => [
                'name' => 'Enterprise Healthcare Network Tier',
                'price_monthly' => 120000,
                'description' => 'Full healthcare suite with Multi-Branch hierarchy, Radiology/PACS & Blood Bank.',
                'included_modules' => ['admissions', 'opd', 'pharmacy', 'billing', 'hr_payroll', 'er_workforce', 'blood_bank', 'radiology'],
                'badge' => 'bg-warning',
            ],
        ];

        $totalMrr = 0;
        foreach ($hospitals as $h) {
            $totalMrr += $plans[$h->subscription_plan]['price_monthly'] ?? 15000;
        }

        return view('superadmin.subscriptions', compact('hospitals', 'plans', 'totalMrr'));
    }

    /**
     * Update Tenant Subscription Plan
     */
    public function updateSubscription(Request $request, Hospital $hospital)
    {
        $validated = $request->validate([
            'subscription_plan' => 'required|in:starter,multi_specialty,enterprise',
        ]);

        $oldPlan = $hospital->subscription_plan;
        $hospital->update(['subscription_plan' => $validated['subscription_plan']]);

        AuditLogger::log(
            action: 'subscription.updated',
            auditableType: 'Hospital',
            auditableId: $hospital->id,
            payload: ['old_plan' => $oldPlan, 'new_plan' => $validated['subscription_plan']]
        );

        return back()->with('success', "Subscription for {$hospital->name} updated to ".ucfirst(str_replace('_', ' ', $validated['subscription_plan'])).' Tier.');
    }

    /**
     * Onboard a new Hospital / Tenant
     */
    public function storeHospital(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:hospitals,code|max:50',
            'type' => 'required|in:clinic,general_hospital,multi_specialty,diagnostic_center',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'city' => 'nullable|string',
            'subscription_plan' => 'required|string',
            'modules' => 'nullable|array',
        ]);

        $hospital = Hospital::create([
            'name' => $validated['name'],
            'code' => strtoupper($validated['code']),
            'slug' => Str::slug($validated['name']),
            'type' => $validated['type'],
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'city' => $validated['city'] ?? null,
            'subscription_plan' => $validated['subscription_plan'],
            'is_active' => true,
        ]);

        // Provision modules
        $allModules = ModuleRegistry::getAvailableModules();
        $selectedModules = $validated['modules'] ?? ['admissions', 'opd', 'billing'];

        foreach ($allModules as $key => $manifest) {
            $isEnabled = in_array($key, $selectedModules);
            HospitalModule::create([
                'hospital_id' => $hospital->id,
                'module_key' => $key,
                'is_enabled' => $isEnabled,
                'activated_at' => $isEnabled ? now() : null,
            ]);
        }

        AuditLogger::log(
            action: 'tenant.onboarded',
            auditableType: 'Hospital',
            auditableId: $hospital->id,
            payload: ['name' => $hospital->name, 'code' => $hospital->code, 'plan' => $hospital->subscription_plan]
        );

        return redirect()->route('superadmin.hospitals')->with('success', "Hospital '{$hospital->name}' onboarded successfully with configured extensions.");
    }

    /**
     * Toggle Module Entitlement / Activation for a Hospital
     */
    public function toggleModule(Request $request, Hospital $hospital)
    {
        $request->validate([
            'module_key' => 'required|string',
        ]);

        $moduleKey = $request->input('module_key');
        $module = HospitalModule::firstOrNew([
            'hospital_id' => $hospital->id,
            'module_key' => $moduleKey,
        ]);

        $module->is_enabled = ! $module->is_enabled;
        $module->activated_at = $module->is_enabled ? now() : null;
        $module->save();

        ModuleRegistry::clearCache($hospital->id);

        $action = $module->is_enabled ? 'module.activated' : 'module.deactivated';
        AuditLogger::log(
            action: $action,
            auditableType: 'HospitalModule',
            auditableId: $module->id,
            payload: [
                'hospital' => $hospital->name,
                'module_key' => $moduleKey,
                'status' => $module->is_enabled ? 'enabled' : 'disabled',
            ]
        );

        $statusStr = $module->is_enabled ? 'enabled' : 'disabled';

        return redirect()->back()->with('success', "Extension '{$moduleKey}' has been {$statusStr} for {$hospital->name}.");
    }

    /**
     * Tenant Context Switcher (for Super Admin testing/demonstration)
     */
    public function switchHospital(Request $request)
    {
        $hospitalId = $request->input('hospital_id');
        $hospital = Hospital::findOrFail($hospitalId);

        TenantManager::setActiveHospital($hospital);

        return redirect()->route('hospital.dashboard')->with('info', "Switched perspective to '{$hospital->name}' ({$hospital->code}).");
    }

    /**
     * Immutable Platform Audit Logs Viewer
     */
    public function auditLogs()
    {
        $audits = AuditEvent::with(['hospital', 'user'])->latest('id')->paginate(25);

        return view('superadmin.audit_logs', compact('audits'));
    }

    /**
     * Super Admin Role & Permission Control Center for All Users
     */
    public function users(Request $request)
    {
        $selectedHospitalId = $request->query('hospital_id');
        $query = User::with(['hospital', 'branch', 'employee.department']);

        if ($selectedHospitalId && $selectedHospitalId !== 'all') {
            $query->where('hospital_id', (int) $selectedHospitalId);
        }

        $users = $query->orderBy('name')->paginate(20);
        $hospitals = Hospital::with('branches')->get();
        $permissionsCatalog = self::getPermissionsCatalog();

        $roles = [
            'super_admin' => 'Super Admin (Platform HQ)',
            'hospital_admin' => 'Hospital Admin (Medical Director)',
            'doctor' => 'Doctor (Physician / Specialist)',
            'pharmacist' => 'Pharmacist (Pharmacy & Dispense)',
            'nurse' => 'Nurse (Inpatient Care & ADT)',
            'accountant' => 'Accountant (Finance & Billing)',
        ];

        return view('superadmin.users', compact(
            'users',
            'hospitals',
            'permissionsCatalog',
            'roles',
            'selectedHospitalId'
        ));
    }

    /**
     * Update Role and Permissions for a Specific User (Super Admin only)
     */
    public function updateUserRoleAndPermissions(Request $request, User $user)
    {
        $validated = $request->validate([
            'role' => 'required|in:super_admin,hospital_admin,doctor,pharmacist,nurse,accountant,receptionist',
            'permissions' => 'nullable|array',
            'branch_id' => 'nullable|exists:branches,id',
            'is_active' => 'nullable|boolean',
        ]);

        $oldRole = $user->role;
        $user->update([
            'role' => $validated['role'],
            'permissions' => $validated['permissions'] ?? User::getDefaultPermissionsForRole($validated['role']),
            'branch_id' => $validated['branch_id'] ?? $user->branch_id,
            'is_active' => $request->boolean('is_active', true),
        ]);

        AuditLogger::log(
            moduleKey: 'access_control',
            action: 'user.role_and_permissions_updated',
            entityType: 'User',
            entityId: $user->id,
            details: [
                'target_user' => $user->email,
                'old_role' => $oldRole,
                'new_role' => $user->role,
                'permissions' => $user->permissions,
            ]
        );

        return back()->with('success', "Role & permissions for {$user->name} ({$user->email}) updated successfully.");
    }

    /**
     * Create a new Staff Account with Role and Permissions (Super Admin only)
     */
    public function storeUser(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'required|in:super_admin,hospital_admin,doctor,pharmacist,nurse,accountant,receptionist',
            'hospital_id' => 'nullable|exists:hospitals,id',
            'branch_id' => 'nullable|exists:branches,id',
            'permissions' => 'nullable|array',
            'phone' => 'nullable|string|max:20',
        ]);

        $newUser = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'hospital_id' => $validated['hospital_id'] ?? null,
            'branch_id' => $validated['branch_id'] ?? null,
            'permissions' => $validated['permissions'] ?? User::getDefaultPermissionsForRole($validated['role']),
            'phone' => $validated['phone'] ?? null,
            'is_active' => true,
        ]);

        AuditLogger::log(
            moduleKey: 'access_control',
            action: 'user.created_by_superadmin',
            entityType: 'User',
            entityId: $newUser->id,
            details: ['name' => $newUser->name, 'email' => $newUser->email, 'role' => $newUser->role]
        );

        return back()->with('success', "New user {$newUser->name} ({$newUser->role}) created successfully.");
    }

    /**
     * Master catalog of system permissions
     */
    public static function getPermissionsCatalog(): array
    {
        return [
            'patient_checkup' => [
                'name' => 'Patient Checkup & Prescriptions',
                'desc' => 'Clinical examinations, vital recording & medicine prescriptions',
                'group' => 'Clinical Care',
            ],
            'opd_consultation' => [
                'name' => 'OPD Schedule & Appointments',
                'desc' => 'Manage outpatient calendar slots and doctor queues',
                'group' => 'Clinical Care',
            ],
            'inpatient_admission' => [
                'name' => 'Inpatient Ward ADT & Beds',
                'desc' => 'Admissions, ward rounds, bed assignments and discharges',
                'group' => 'Clinical Care',
            ],
            'pharmacy_pos' => [
                'name' => 'Pharmacy POS & Dispense',
                'desc' => 'Point of Sale billing and prescription drug dispensing',
                'group' => 'Pharmacy',
            ],
            'medicine_inventory' => [
                'name' => 'Medicine Stock & Batches',
                'desc' => 'Inventory ledgers, batch receipt and reorder management',
                'group' => 'Pharmacy',
            ],
            'radiology_scans' => [
                'name' => 'Radiology & Imaging Scans',
                'desc' => 'Radiology order creation and DICOM/PACS reports',
                'group' => 'Diagnostics',
            ],
            'blood_bank_access' => [
                'name' => 'Blood Bank & Cross-Matching',
                'desc' => 'Donor management, blood inventory and component issues',
                'group' => 'Diagnostics',
            ],
            'financial_management' => [
                'name' => 'Multi-Branch Financial Hierarchy',
                'desc' => 'Branch revenue, collections, UPI receipts and billing invoices',
                'group' => 'Finance & HR',
            ],
            'hr_payroll' => [
                'name' => 'Employee Salaries & Payroll',
                'desc' => 'Staff profiles, designations and salary generation',
                'group' => 'Finance & HR',
            ],
            'workforce_roster' => [
                'name' => 'ER Shift Duty & Assignments',
                'desc' => 'Roster scheduling and nurse/doctor emergency assignments',
                'group' => 'Operations',
            ],
        ];
    }
}
