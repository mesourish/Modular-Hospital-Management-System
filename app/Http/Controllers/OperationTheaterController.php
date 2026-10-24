<?php

namespace App\Http\Controllers;

use App\Core\Audit\AuditLogger;
use App\Core\Tenancy\TenantManager;
use App\Models\Branch;
use App\Models\Medicine;
use App\Models\MedicineBatch;
use App\Models\OperationTheater;
use App\Models\OtMedicationLog;
use App\Models\OtSurgery;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OperationTheaterController extends Controller
{
    /**
     * Operation Theater Management, Configurations, Patient Surgeries & Medication Ledger
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $hospital = TenantManager::getHospital();
        if (! $hospital) {
            return redirect()->route('hospital.dashboard')->with('error', 'Select a hospital first.');
        }

        // Branch Isolation: Super Admin can see all; Branch Admin is restricted to their branch
        $selectedBranchId = $request->query('branch_id');
        if ($user->isRestrictedToBranch()) {
            $selectedBranchId = (string) $user->branch_id;
        }

        // Available branches in dropdown (Super Admin sees all, Branch Admin sees their branch)
        $branchesQuery = Branch::where('hospital_id', $hospital->id)->where('is_active', true);
        if ($user->isRestrictedToBranch()) {
            $branchesQuery->where('id', $user->branch_id);
        }
        $branches = $branchesQuery->orderByDesc('is_main_branch')->get();

        // 1. Operation Theaters Query
        $otQuery = OperationTheater::where('hospital_id', $hospital->id)
            ->with(['branch', 'surgeries' => function ($sq) {
                $sq->whereDate('scheduled_start_time', now()->toDateString())->with('patient');
            }]);

        if ($selectedBranchId && $selectedBranchId !== 'all') {
            $otQuery->where('branch_id', (int) $selectedBranchId);
        }
        $theaters = $otQuery->orderBy('name')->get();

        // 2. Patient Surgeries Query
        $surgeriesQuery = OtSurgery::where('hospital_id', $hospital->id)
            ->with([
                'operationTheater',
                'patient',
                'leadSurgeon',
                'anesthesiologist',
                'scrubNurse',
                'branch',
                'medications',
            ]);

        if ($selectedBranchId && $selectedBranchId !== 'all') {
            $surgeriesQuery->where('branch_id', (int) $selectedBranchId);
        }

        $selectedStatus = $request->query('status');
        if ($selectedStatus && $selectedStatus !== 'all') {
            $surgeriesQuery->where('status', $selectedStatus);
        }

        $surgeries = $surgeriesQuery->orderByDesc('scheduled_start_time')->get();

        // 3. Itemized Medication Usage Ledger for Patient Surgeries
        $medLogsQuery = OtMedicationLog::where('hospital_id', $hospital->id)
            ->with(['otSurgery.patient', 'otSurgery.operationTheater', 'branch', 'patient']);

        if ($selectedBranchId && $selectedBranchId !== 'all') {
            $medLogsQuery->where('branch_id', (int) $selectedBranchId);
        }
        $medicationLogs = $medLogsQuery->orderByDesc('created_at')->limit(50)->get();

        // Doctors & Nurses list for assignments
        $surgeons = User::where('hospital_id', $hospital->id)
            ->where('role', 'doctor')
            ->get();

        $nurses = User::where('hospital_id', $hospital->id)
            ->where('role', 'nurse')
            ->get();

        // Patients list for surgery scheduling
        $patients = Patient::where('hospital_id', $hospital->id)
            ->orderBy('first_name')
            ->limit(100)
            ->get();

        // In-stock medicines for OT usage selector
        $medicines = Medicine::where('hospital_id', $hospital->id)
            ->where('is_active', true)
            ->get();

        // Operational Metrics
        $totalTheaters = $theaters->count();
        $occupiedTheaters = $theaters->where('status', 'occupied_in_surgery')->count();
        $availableTheaters = $theaters->where('status', 'available')->count();
        $activeSurgeriesCount = $surgeries->whereIn('status', ['scheduled', 'in_progress'])->count();
        $totalMedicationsCount = $medicationLogs->count();

        return view('modules.ot.index', compact(
            'user',
            'hospital',
            'branches',
            'selectedBranchId',
            'selectedStatus',
            'theaters',
            'surgeries',
            'medicationLogs',
            'surgeons',
            'nurses',
            'patients',
            'medicines',
            'totalTheaters',
            'occupiedTheaters',
            'availableTheaters',
            'activeSurgeriesCount',
            'totalMedicationsCount'
        ));
    }

    /**
     * Store / Configure New Operation Theater Suite
     */
    public function storeOt(Request $request)
    {
        $user = Auth::user();
        $hospital = TenantManager::getHospital();
        if (! $hospital) {
            return back()->with('error', 'Hospital context required.');
        }

        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50',
            'type' => 'required|string|in:major_modular,hybrid_cath_lab,minor_emergency,ophthalmic,day_care',
            'floor' => 'nullable|string|max:100',
            'status' => 'required|string|in:available,occupied_in_surgery,sterilization_fumigation,under_maintenance',
            'anesthesia_workstation' => 'nullable|string|max:255',
            'c_arm_model' => 'nullable|string|max:255',
            'heart_lung_machine' => 'nullable|string|max:255',
            'laparoscopic_tower' => 'nullable|string|max:255',
            'hepa_filter_status' => 'required|string|in:certified_active,cleaning_due,replacement_required',
            'air_pressure_type' => 'required|string|in:positive_laminar_flow,negative_pressure',
        ]);

        // Branch Isolation check
        if ($user->isRestrictedToBranch()) {
            $validated['branch_id'] = $user->branch_id;
        }

        $theater = OperationTheater::create([
            'hospital_id' => $hospital->id,
            'branch_id' => $validated['branch_id'],
            'name' => $validated['name'],
            'code' => strtoupper($validated['code']),
            'type' => $validated['type'],
            'floor' => $validated['floor'] ?? '3rd Floor - Surgical Pavilion',
            'status' => $validated['status'],
            'anesthesia_workstation' => $validated['anesthesia_workstation'],
            'c_arm_model' => $validated['c_arm_model'],
            'heart_lung_machine' => $validated['heart_lung_machine'],
            'laparoscopic_tower' => $validated['laparoscopic_tower'],
            'hepa_filter_status' => $validated['hepa_filter_status'],
            'air_pressure_type' => $validated['air_pressure_type'],
            'last_fumigation_at' => now(),
            'next_sterilization_due' => now()->addDays(7),
            'infection_control_cleared' => true,
            'is_active' => true,
        ]);

        AuditLogger::log(
            moduleKey: 'ot',
            action: 'ot_suite_configured',
            entityType: 'OperationTheater',
            entityId: $theater->id,
            details: ['name' => $theater->name, 'code' => $theater->code, 'branch_id' => $theater->branch_id],
            hospitalId: $hospital->id
        );

        return back()->with('success', "Operation Theater {$theater->name} configured successfully.");
    }

    /**
     * Schedule a Patient Surgery in an OT Suite
     */
    public function storeSurgery(Request $request)
    {
        $user = Auth::user();
        $hospital = TenantManager::getHospital();
        if (! $hospital) {
            return back()->with('error', 'Hospital context required.');
        }

        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'operation_theater_id' => 'required|exists:operation_theaters,id',
            'patient_id' => 'required|exists:patients,id',
            'lead_surgeon_id' => 'required|exists:users,id',
            'anesthesiologist_id' => 'nullable|exists:users,id',
            'scrub_nurse_id' => 'nullable|exists:users,id',
            'surgery_name' => 'required|string|max:255',
            'surgery_category' => 'required|string|in:major,super_major,minor,emergency',
            'pre_op_diagnosis' => 'nullable|string|max:500',
            'scheduled_start_time' => 'required|date',
            'scheduled_end_time' => 'required|date|after:scheduled_start_time',
            'anesthesia_type' => 'required|string|in:general,spinal_epidural,regional_block,local_sedation',
        ]);

        // Branch Isolation check
        if ($user->isRestrictedToBranch()) {
            $validated['branch_id'] = $user->branch_id;
        }

        $surgeryCode = 'SURG-'.date('Y').'-'.str_pad((string) rand(100, 9999), 4, '0', STR_PAD_LEFT);

        $surgery = OtSurgery::create([
            'hospital_id' => $hospital->id,
            'branch_id' => $validated['branch_id'],
            'operation_theater_id' => $validated['operation_theater_id'],
            'patient_id' => $validated['patient_id'],
            'lead_surgeon_id' => $validated['lead_surgeon_id'],
            'anesthesiologist_id' => $validated['anesthesiologist_id'] ?? null,
            'scrub_nurse_id' => $validated['scrub_nurse_id'] ?? null,
            'surgery_name' => $validated['surgery_name'],
            'surgery_code' => $surgeryCode,
            'surgery_category' => $validated['surgery_category'],
            'pre_op_diagnosis' => $validated['pre_op_diagnosis'],
            'scheduled_start_time' => $validated['scheduled_start_time'],
            'scheduled_end_time' => $validated['scheduled_end_time'],
            'status' => 'scheduled',
            'anesthesia_type' => $validated['anesthesia_type'],
        ]);

        AuditLogger::log(
            moduleKey: 'ot',
            action: 'surgery_scheduled',
            entityType: 'OtSurgery',
            entityId: $surgery->id,
            details: ['code' => $surgeryCode, 'surgery' => $surgery->surgery_name, 'patient_id' => $surgery->patient_id],
            hospitalId: $hospital->id
        );

        return back()->with('success', "Patient surgery scheduled! Surgery Code: {$surgeryCode}.");
    }

    /**
     * Log Medicines in specific quantity used during an OT patient surgery
     */
    public function storeMedication(Request $request)
    {
        $user = Auth::user();
        $hospital = TenantManager::getHospital();
        if (! $hospital) {
            return back()->with('error', 'Hospital context required.');
        }

        $validated = $request->validate([
            'ot_surgery_id' => 'required|exists:ot_surgeries,id',
            'medicine_name' => 'required|string|max:255',
            'quantity_used' => 'required|numeric|min:0.1',
            'unit' => 'required|string|max:50',
            'administration_stage' => 'required|string|in:pre_medication,induction,intra_op,maintenance,reversal,post_op_recovery',
            'batch_number' => 'nullable|string|max:100',
            'cost_per_unit' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        $surgery = OtSurgery::with(['patient', 'branch'])->findOrFail($validated['ot_surgery_id']);

        // Branch Isolation check
        if ($user->isRestrictedToBranch() && $surgery->branch_id != $user->branch_id) {
            return back()->with('error', 'Unauthorized to modify surgeries outside your branch.');
        }

        $unitCost = (float) ($validated['cost_per_unit'] ?? 150.00);
        $totalCost = $unitCost * (float) $validated['quantity_used'];

        $log = OtMedicationLog::create([
            'hospital_id' => $hospital->id,
            'branch_id' => $surgery->branch_id,
            'ot_surgery_id' => $surgery->id,
            'patient_id' => $surgery->patient_id,
            'medicine_name' => $validated['medicine_name'],
            'quantity_used' => $validated['quantity_used'],
            'unit' => $validated['unit'],
            'administration_stage' => $validated['administration_stage'],
            'batch_number' => $validated['batch_number'] ?? 'BAT-OT-'.rand(100, 999),
            'cost_per_unit' => $unitCost,
            'total_cost' => $totalCost,
            'administered_by' => $user->name,
            'notes' => $validated['notes'],
        ]);

        // Deduct from pharmacy stock if matching batch exists
        $deductedQty = (int) ceil((float) $validated['quantity_used']);
        $batch = null;
        if (! empty($validated['batch_number'])) {
            $batch = MedicineBatch::where('batch_number', $validated['batch_number'])->first();
        }
        if (! $batch) {
            $batch = MedicineBatch::whereHas('medicine', function ($q) use ($validated) {
                $q->where('brand_name', 'like', '%'.$validated['medicine_name'].'%');
            })->where('quantity_in_stock', '>=', $deductedQty)->first();
        }
        if ($batch && $batch->quantity_in_stock >= $deductedQty) {
            $batch->decrement('quantity_in_stock', $deductedQty);
        }

        AuditLogger::log(
            moduleKey: 'ot',
            action: 'ot_medication_logged',
            entityType: 'OtMedicationLog',
            entityId: $log->id,
            details: [
                'surgery' => $surgery->surgery_name,
                'patient' => $surgery->patient->name,
                'medicine' => $log->medicine_name,
                'quantity' => $log->formatted_quantity,
                'cost' => $totalCost,
                'batch_deducted' => $batch?->batch_number,
            ],
            hospitalId: $hospital->id
        );

        $stockMsg = $batch ? " Deducted {$deductedQty} units from Batch {$batch->batch_number} (Remaining: {$batch->quantity_in_stock})." : '';

        return back()->with('success', "Logged {$log->formatted_quantity} of {$log->medicine_name} for patient {$surgery->patient->name}.{$stockMsg}");
    }

    /**
     * Update Surgery Status (scheduled -> in_progress -> post_op_recovery -> completed)
     */
    public function updateStatus(Request $request, OtSurgery $surgery)
    {
        $user = Auth::user();
        $hospital = TenantManager::getHospital();

        if ($user->isRestrictedToBranch() && $surgery->branch_id != $user->branch_id) {
            return back()->with('error', 'Unauthorized to modify surgeries outside your branch.');
        }

        $validated = $request->validate([
            'status' => 'required|in:scheduled,in_progress,post_op_recovery,completed,cancelled',
            'post_op_findings' => 'nullable|string|max:1000',
            'surgical_notes' => 'nullable|string|max:1000',
            'blood_units_transfused' => 'nullable|integer|min:0',
        ]);

        $updates = ['status' => $validated['status']];
        if ($validated['status'] === 'in_progress' && ! $surgery->actual_start_time) {
            $updates['actual_start_time'] = now();
            // Mark OT as occupied
            $surgery->operationTheater()->update(['status' => 'occupied_in_surgery']);
        } elseif ($validated['status'] === 'completed' && ! $surgery->actual_end_time) {
            $updates['actual_end_time'] = now();
            // Mark OT as sterilization required
            $surgery->operationTheater()->update(['status' => 'sterilization_fumigation']);
        }

        if (isset($validated['post_op_findings'])) {
            $updates['post_op_findings'] = $validated['post_op_findings'];
        }
        if (isset($validated['surgical_notes'])) {
            $updates['surgical_notes'] = $validated['surgical_notes'];
        }
        if (isset($validated['blood_units_transfused'])) {
            $updates['blood_units_transfused'] = $validated['blood_units_transfused'];
        }

        $surgery->update($updates);

        return back()->with('success', "Surgery status updated to {$surgery->status_label}.");
    }

    /**
     * Detailed Patient Surgery & Medication Report Card
     */
    public function showSurgery(OtSurgery $surgery)
    {
        $user = Auth::user();
        if ($user->isRestrictedToBranch() && $surgery->branch_id != $user->branch_id) {
            return redirect()->route('ot.index')->with('error', 'Access restricted to your assigned branch.');
        }

        $surgery->load([
            'patient',
            'operationTheater',
            'leadSurgeon',
            'anesthesiologist',
            'scrubNurse',
            'branch',
            'medications',
        ]);

        return view('modules.ot.show', compact('surgery', 'user'));
    }
}
