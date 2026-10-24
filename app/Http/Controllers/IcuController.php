<?php

namespace App\Http\Controllers;

use App\Core\Audit\AuditLogger;
use App\Core\Tenancy\TenantManager;
use App\Models\Admission;
use App\Models\Bed;
use App\Models\Branch;
use App\Models\CodeBlueLog;
use App\Models\IcuMonitor;
use App\Models\Patient;
use App\Models\User;
use App\Models\Ward;
use Illuminate\Http\Request;

class IcuController extends Controller
{
    /**
     * Display ICU & Critical Care Command Suite
     */
    public function index(Request $request, ?Branch $outlet = null)
    {
        if ($outlet && $outlet->exists) {
            TenantManager::setActiveBranch($outlet);
        }

        $hospital = TenantManager::getHospital();
        $activeBranch = $outlet ?? TenantManager::getBranch() ?? Branch::where('hospital_id', $hospital?->id)->first();
        $branches = Branch::where('hospital_id', $hospital?->id)->get();

        $selectedBranchId = $request->input('branch_id', $activeBranch?->id);

        $monitorsQuery = IcuMonitor::with(['admission', 'patient', 'bed.ward', 'attendingDoctor', 'branch']);
        $codeBlueQuery = CodeBlueLog::with(['patient', 'bed', 'initiatedBy', 'branch']);

        if ($selectedBranchId && $selectedBranchId !== 'all') {
            $monitorsQuery->where('branch_id', (int) $selectedBranchId);
            $codeBlueQuery->where('branch_id', (int) $selectedBranchId);
        }

        $icuMonitors = $monitorsQuery->latest('updated_at')->get();
        $codeBlueLogs = $codeBlueQuery->latest('event_time')->take(20)->get();

        // ICU Wards and Beds
        $icuWards = Ward::where(function ($q) {
            $q->where('ward_type', 'icu')
                ->orWhere('name', 'like', '%ICU%')
                ->orWhere('name', 'like', '%CCU%')
                ->orWhere('name', 'like', '%Intensive%');
        })->pluck('id');

        $icuBeds = Bed::whereIn('ward_id', $icuWards)->with('ward')->get();
        $totalIcuBeds = $icuBeds->count();
        $occupiedIcuBeds = $icuMonitors->count();
        $ventilatedCount = $icuMonitors->filter(fn ($m) => $m->is_ventilated)->count();
        $criticalCount = $icuMonitors->whereIn('clinical_condition', ['critical_unstable', 'ventilator_dependent'])->count();
        $recentCodeBlues = $codeBlueLogs->where('event_time', '>=', now()->subDays(7))->count();

        // Doctors and active admitted patients for modal dropdowns
        $doctors = User::where('role', 'doctor')->get();
        $admittedPatients = Admission::where('status', 'admitted')
            ->with(['patient', 'bed.ward', 'attendingDoctor'])
            ->get();

        return view('modules.icu.index', compact(
            'icuMonitors',
            'codeBlueLogs',
            'icuBeds',
            'totalIcuBeds',
            'occupiedIcuBeds',
            'ventilatedCount',
            'criticalCount',
            'recentCodeBlues',
            'branches',
            'selectedBranchId',
            'activeBranch',
            'hospital',
            'doctors',
            'admittedPatients'
        ));
    }

    /**
     * Update telemetry, ventilator settings, and nursing notes
     */
    public function updateVitals(Request $request)
    {
        $validated = $request->validate([
            'monitor_id' => 'required|exists:icu_monitors,id',
            'vent_mode' => 'nullable|string|max:50',
            'fio2_percentage' => 'nullable|integer|min:21|max:100',
            'peep' => 'nullable|numeric|min:0|max:30',
            'tidal_volume' => 'nullable|integer|min:100|max:1200',
            'respiratory_rate' => 'nullable|integer|min:5|max:60',
            'spo2' => 'required|integer|min:50|max:100',
            'heart_rate' => 'required|integer|min:30|max:240',
            'bp_systolic' => 'required|integer|min:50|max:260',
            'bp_diastolic' => 'required|integer|min:30|max:160',
            'temperature_c' => 'nullable|numeric|min:30|max:45',
            'gcs_score' => 'nullable|integer|min:3|max:15',
            'arterial_line' => 'nullable|boolean',
            'central_line' => 'nullable|boolean',
            'inotrope_infusion' => 'nullable|string|max:255',
            'clinical_condition' => 'required|in:stable_weaned,ventilator_dependent,critical_unstable,post_cardiac_arrest',
            'nurse_shift_notes' => 'nullable|string|max:1000',
        ]);

        $monitor = IcuMonitor::findOrFail($validated['monitor_id']);

        $monitor->update([
            'vent_mode' => $validated['vent_mode'] ?? 'None (Room Air)',
            'fio2_percentage' => $validated['fio2_percentage'] ?? 21,
            'peep' => $validated['peep'] ?? 0,
            'tidal_volume' => $validated['tidal_volume'] ?? null,
            'respiratory_rate' => $validated['respiratory_rate'] ?? null,
            'spo2' => $validated['spo2'],
            'heart_rate' => $validated['heart_rate'],
            'bp_systolic' => $validated['bp_systolic'],
            'bp_diastolic' => $validated['bp_diastolic'],
            'temperature_c' => $validated['temperature_c'] ?? 37.0,
            'gcs_score' => $validated['gcs_score'] ?? 15,
            'arterial_line' => $request->has('arterial_line'),
            'central_line' => $request->has('central_line'),
            'inotrope_infusion' => $validated['inotrope_infusion'] ?? null,
            'clinical_condition' => $validated['clinical_condition'],
            'nurse_shift_notes' => $validated['nurse_shift_notes'] ?? $monitor->nurse_shift_notes,
            'last_assessment_at' => now(),
        ]);

        AuditLogger::log(
            moduleKey: 'icu',
            action: 'icu_vitals_updated',
            entityType: 'IcuMonitor',
            entityId: $monitor->id,
            details: [
                'patient_id' => $monitor->patient_id,
                'spo2' => $monitor->spo2,
                'bp' => $monitor->formatted_bp,
                'vent_mode' => $monitor->vent_mode,
            ]
        );

        return back()->with('success', "ICU Telemetry for Patient {$monitor->patient?->first_name} {$monitor->patient?->last_name} updated successfully.");
    }

    /**
     * Admit or transfer patient to an ICU bed
     */
    public function admitToIcu(Request $request)
    {
        $validated = $request->validate([
            'admission_id' => 'required|exists:admissions,id',
            'bed_id' => 'required|exists:beds,id',
            'attending_doctor_id' => 'required|exists:users,id',
            'vent_mode' => 'nullable|string|max:50',
            'fio2_percentage' => 'nullable|integer|min:21|max:100',
            'peep' => 'nullable|numeric|min:0|max:30',
            'spo2' => 'required|integer|min:50|max:100',
            'heart_rate' => 'required|integer|min:30|max:240',
            'bp_systolic' => 'required|integer|min:50|max:260',
            'bp_diastolic' => 'required|integer|min:30|max:160',
            'gcs_score' => 'nullable|integer|min:3|max:15',
            'clinical_condition' => 'required|in:stable_weaned,ventilator_dependent,critical_unstable,post_cardiac_arrest',
            'nurse_shift_notes' => 'nullable|string|max:1000',
        ]);

        $admission = Admission::with('patient')->findOrFail($validated['admission_id']);
        $hospital = TenantManager::getHospital();

        // Update bed status to occupied
        $bed = Bed::findOrFail($validated['bed_id']);
        $bed->update(['status' => 'occupied']);

        $monitor = IcuMonitor::updateOrCreate(
            ['admission_id' => $admission->id],
            [
                'hospital_id' => $hospital?->id ?? $admission->hospital_id ?? 1,
                'branch_id' => $admission->branch_id,
                'patient_id' => $admission->patient_id,
                'bed_id' => $bed->id,
                'attending_doctor_id' => $validated['attending_doctor_id'],
                'vent_mode' => $validated['vent_mode'] ?? 'None (Room Air)',
                'fio2_percentage' => $validated['fio2_percentage'] ?? 21,
                'peep' => $validated['peep'] ?? 0,
                'spo2' => $validated['spo2'],
                'heart_rate' => $validated['heart_rate'],
                'bp_systolic' => $validated['bp_systolic'],
                'bp_diastolic' => $validated['bp_diastolic'],
                'gcs_score' => $validated['gcs_score'] ?? 15,
                'clinical_condition' => $validated['clinical_condition'],
                'nurse_shift_notes' => $validated['nurse_shift_notes'] ?? 'Admitted to ICU Bed.',
                'last_assessment_at' => now(),
            ]
        );

        AuditLogger::log(
            moduleKey: 'icu',
            action: 'icu_admission_registered',
            entityType: 'IcuMonitor',
            entityId: $monitor->id,
            details: [
                'admission_id' => $admission->id,
                'patient' => $admission->patient?->mrn,
                'bed' => $bed->bed_number,
            ]
        );

        return back()->with('success', "Patient {$admission->patient?->full_name} enrolled in ICU Bed {$bed->bed_number}.");
    }

    /**
     * Step down patient from ICU to step-down / general ward
     */
    public function stepDown(Request $request, Admission $admission)
    {
        $monitor = IcuMonitor::where('admission_id', $admission->id)->first();

        if ($monitor) {
            if ($monitor->bed) {
                $monitor->bed->update(['status' => 'available']);
            }
            $monitor->delete();
        }

        AuditLogger::log(
            moduleKey: 'icu',
            action: 'icu_patient_stepped_down',
            entityType: 'Admission',
            entityId: $admission->id,
            details: ['admission_id' => $admission->id, 'mrn' => $admission->patient?->mrn]
        );

        return back()->with('success', "Patient {$admission->patient?->full_name} stepped down from ICU to general ward.");
    }

    /**
     * Log Code Blue emergency resuscitation event
     */
    public function logCodeBlue(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'bed_id' => 'nullable|exists:beds,id',
            'cpr_duration_mins' => 'required|integer|min:1|max:120',
            'shocks_delivered' => 'required|integer|min:0|max:20',
            'cardiac_rhythm' => 'required|string|max:100',
            'outcome' => 'required|in:resuscitated,deceased,ongoing',
            'resuscitation_notes' => 'required|string|max:1000',
        ]);

        $hospital = TenantManager::getHospital();
        $patient = Patient::findOrFail($validated['patient_id']);

        $codeBlue = CodeBlueLog::create([
            'hospital_id' => $hospital?->id ?? 1,
            'branch_id' => TenantManager::getBranchId() ?? $patient->branch_id ?? 1,
            'patient_id' => $patient->id,
            'bed_id' => $validated['bed_id'] ?? null,
            'initiated_by_id' => auth()->id() ?? 1,
            'event_time' => now(),
            'cpr_duration_mins' => $validated['cpr_duration_mins'],
            'shocks_delivered' => $validated['shocks_delivered'],
            'cardiac_rhythm' => $validated['cardiac_rhythm'],
            'outcome' => $validated['outcome'],
            'resuscitation_notes' => $validated['resuscitation_notes'],
        ]);

        AuditLogger::log(
            moduleKey: 'icu',
            action: 'code_blue_resuscitation_recorded',
            entityType: 'CodeBlueLog',
            entityId: $codeBlue->id,
            details: [
                'patient_id' => $patient->id,
                'outcome' => $codeBlue->outcome,
                'cpr_duration' => $codeBlue->cpr_duration_mins,
            ]
        );

        return back()->with('success', "Code Blue Emergency Event logged for {$patient->full_name} (Outcome: ".strtoupper($codeBlue->outcome).').');
    }
}
