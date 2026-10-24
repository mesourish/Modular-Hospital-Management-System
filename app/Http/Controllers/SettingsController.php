<?php

namespace App\Http\Controllers;

use App\Core\Audit\AuditLogger;
use App\Core\Tenancy\TenantManager;
use App\Models\Branch;
use App\Models\Hospital;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class SettingsController extends Controller
{
    /**
     * Unified Customizable Settings Workspace for Super Admin, Hospital Admin & Branch Head
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $activeHospital = TenantManager::getHospital();

        if (! $activeHospital && ! $user->isSuperAdmin()) {
            return redirect()->route('hospital.dashboard')->with('error', 'Select a hospital first.');
        }

        // Available Hospitals for Super Admin
        $hospitals = $user->isSuperAdmin() ? Hospital::where('is_active', true)->get() : collect([$activeHospital]);

        // Determine scope: Global, Hospital, or Branch
        $selectedHospitalId = $request->query('hospital_id', $activeHospital?->id);
        $selectedBranchId = $request->query('branch_id');

        // If user is a branch_admin, restrict to their assigned branch
        if ($user->role === 'branch_admin' && $user->branch_id) {
            $selectedBranchId = $user->branch_id;
            $selectedHospitalId = $user->hospital_id;
        }

        $branches = collect();
        if ($selectedHospitalId) {
            $branches = Branch::where('hospital_id', $selectedHospitalId)->get();
        }

        // Load all settings for this scope
        $settings = SystemSetting::getAllMapped(
            $selectedHospitalId ? (int) $selectedHospitalId : null,
            $selectedBranchId ? (int) $selectedBranchId : null
        );

        return view('settings.index', compact(
            'user',
            'hospitals',
            'branches',
            'selectedHospitalId',
            'selectedBranchId',
            'settings'
        ));
    }

    /**
     * Update Customizable Settings
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        $hospitalId = $request->input('scope_hospital_id');
        $branchId = $request->input('scope_branch_id');

        // Security check: restrict branch_admin role to their assigned branch
        if ($user->role === 'branch_admin' && $user->branch_id) {
            $branchId = $user->branch_id;
            $hospitalId = $user->hospital_id;
        }

        // Only super_admin can set global settings (null hospital and null branch)
        if (! $user->isSuperAdmin() && empty($hospitalId)) {
            $hospitalId = $user->hospital_id;
        }

        $data = $request->except(['_token', 'scope_hospital_id', 'scope_branch_id']);

        // Checkbox booleans that might not be in payload if unchecked
        $booleanKeys = [
            'announcement_enabled',
            'allow_online_booking',
            'show_doctor_fees',
            'show_token_tracker',
            'require_abha_id',
            'auto_confirm_appointment',
        ];

        // Process submitted settings
        foreach ($data as $key => $value) {
            $type = 'string';
            $group = 'general';

            if (in_array($key, $booleanKeys)) {
                $type = 'boolean';
                $value = $value ? '1' : '0';
                $group = 'frontend';
            } elseif (str_starts_with($key, 'frontend_')) {
                $group = 'frontend';
            } elseif (in_array($key, ['emergency_hotline', 'ambulance_phone', 'whatsapp_number', 'support_email', 'hospital_address', 'opd_hours', 'branch_notice'])) {
                $group = 'contact';
            } elseif (in_array($key, ['currency_symbol', 'gstin_number', 'token_prefix'])) {
                $group = 'clinical';
            } elseif (str_starts_with($key, 'sms_')) {
                $group = 'gateway';
            }

            SystemSetting::set(
                key: $key,
                value: $value,
                branchId: $branchId ? (int) $branchId : null,
                hospitalId: $hospitalId ? (int) $hospitalId : null,
                group: $group,
                type: $type
            );
        }

        // Ensure missing checkboxes are stored as false
        foreach ($booleanKeys as $bKey) {
            if (! isset($data[$bKey])) {
                SystemSetting::set(
                    key: $bKey,
                    value: '0',
                    branchId: $branchId ? (int) $branchId : null,
                    hospitalId: $hospitalId ? (int) $hospitalId : null,
                    group: 'frontend',
                    type: 'boolean'
                );
            }
        }

        AuditLogger::log(
            moduleKey: 'settings',
            action: 'settings_updated',
            entityType: 'SystemSetting',
            entityId: 0,
            details: [
                'updated_by' => $user->name,
                'role' => $user->role,
                'hospital_id' => $hospitalId,
                'branch_id' => $branchId,
                'keys_updated' => array_keys($data),
            ],
            hospitalId: $hospitalId ? (int) $hospitalId : null
        );

        return back()->with('success', 'Customizable settings saved successfully! Customer frontend and platform are now updated.');
    }

    /**
     * Clear All Data (Factory Reset)
     * Wipes all operational, patient, and transactional records while strictly preserving
     * minimal core baseline data required to run the software (tenants, branches, core staff users,
     * departments, sub-departments, designations, master constants, system settings, facilities catalog).
     */
    public function clearAllData(Request $request)
    {
        $user = Auth::user();

        // 1. Authorization: Only super_admin or hospital_admin can perform a system data reset
        if (! $user->isSuperAdmin() && $user->role !== 'hospital_admin') {
            abort(403, 'Unauthorized. Only Super Admin or Hospital Director can perform a system data reset.');
        }

        // 2. Strong Authentication & Confirmation verification
        $request->validate([
            'password' => 'required|string',
            'confirm_phrase' => 'required|string',
        ]);

        if (! Hash::check($request->input('password'), $user->password)) {
            return back()->with('error', 'Security Verification Failed: The administrator password entered is incorrect.');
        }

        if (trim($request->input('confirm_phrase')) !== 'CLEAR_ALL_DATA') {
            return back()->with('error', 'Confirmation Failed: You must type exactly "CLEAR_ALL_DATA" to confirm this irreversible operation.');
        }

        $hospitalId = $user->isSuperAdmin() && $request->filled('scope_hospital_id')
            ? (int) $request->input('scope_hospital_id')
            : $user->hospital_id;

        DB::beginTransaction();
        try {
            // Scoped table deleter
            $scopedDelete = function (string $table) use ($hospitalId) {
                if (Schema::hasTable($table)) {
                    $query = DB::table($table);
                    if ($hospitalId && Schema::hasColumn($table, 'hospital_id')) {
                        $query->where('hospital_id', $hospitalId);
                    }
                    $query->delete();
                }
            };

            // Disable foreign key constraints during mass clear
            $driver = DB::getDriverName();
            if ($driver === 'sqlite') {
                DB::statement('PRAGMA foreign_keys = OFF;');
            } elseif ($driver === 'mysql') {
                DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            }

            // A. Clinical Logs, Charts & Inpatient History
            $scopedDelete('code_blue_logs');
            $scopedDelete('icu_ventilator_logs');
            $scopedDelete('icu_admissions');
            $scopedDelete('ot_medication_logs');
            $scopedDelete('ot_surgeries');
            $scopedDelete('dietary_orders');
            $scopedDelete('package_bookings');
            $scopedDelete('ambulance_trips');
            $scopedDelete('radiology_reports');
            $scopedDelete('radiology_orders');
            $scopedDelete('blood_issues');
            $scopedDelete('cross_match_orders');
            $scopedDelete('blood_bags');
            $scopedDelete('blood_donors');
            $scopedDelete('vital_records_deaths');
            $scopedDelete('vital_records_births');
            $scopedDelete('vital_signs');
            $scopedDelete('patient_vitals');
            $scopedDelete('patient_longitudinal_histories');

            // B. Financial & Commercial Invoicing
            $scopedDelete('pharmacy_invoice_items');
            $scopedDelete('pharmacy_invoices');
            $scopedDelete('payments');
            $scopedDelete('invoice_items');
            $scopedDelete('invoices');

            // C. Consultations, Prescriptions & OPD
            $scopedDelete('prescriptions');
            $scopedDelete('consultations');
            $scopedDelete('appointments');

            // D. Inpatient Admissions & Shift Operations
            $scopedDelete('admissions');
            $scopedDelete('patient_assignments');
            $scopedDelete('shift_rosters');
            $scopedDelete('staff_attendances');
            $scopedDelete('payrolls');

            // E. Patient Records & Test Customer Accounts
            $scopedDelete('patients');
            // Remove users with role 'patient' (test customers), while 100% preserving administrative & clinical staff
            $patientUsersQuery = DB::table('users')->where('role', 'patient');
            if ($hospitalId) {
                $patientUsersQuery->where('hospital_id', $hospitalId);
            }
            $patientUsersQuery->delete();

            // F. Reset Equipment & Beds State to Available (Preserving Master Beds, Ambulances, and Rooms)
            if (Schema::hasTable('beds')) {
                $bedQuery = DB::table('beds');
                if ($hospitalId) {
                    $bedQuery->where('hospital_id', $hospitalId);
                }
                $bedQuery->update(['status' => 'available']);
            }

            if (Schema::hasTable('ambulances')) {
                $ambQuery = DB::table('ambulances');
                if ($hospitalId) {
                    $ambQuery->where('hospital_id', $hospitalId);
                }
                $ambQuery->update([
                    'status' => 'available',
                    'current_location' => 'Hospital Base ER Bay',
                ]);
            }

            if (Schema::hasTable('ot_rooms')) {
                $otQuery = DB::table('ot_rooms');
                if ($hospitalId) {
                    $otQuery->where('hospital_id', $hospitalId);
                }
                $otQuery->update(['status' => 'available']);
            }

            // Re-enable foreign key constraints
            if ($driver === 'sqlite') {
                DB::statement('PRAGMA foreign_keys = ON;');
            } elseif ($driver === 'mysql') {
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            }

            DB::commit();

            AuditLogger::log(
                moduleKey: 'settings',
                action: 'system_factory_reset_cleared',
                entityType: 'SystemDataReset',
                entityId: 0,
                details: [
                    'initiated_by' => $user->name,
                    'user_email' => $user->email,
                    'role' => $user->role,
                    'hospital_id' => $hospitalId,
                    'status' => 'minimal_baseline_retained',
                ],
                hospitalId: $hospitalId
            );

            return redirect()->route('settings.index')->with('success', 'Factory Data Reset Completed Successfully! All operational, patient, and billing records have been cleared. Minimal data (tenants, branches, staff users, departments, constants, settings, and facilities) has been strictly preserved.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('error', 'Error performing data reset: '.$e->getMessage());
        }
    }
}
