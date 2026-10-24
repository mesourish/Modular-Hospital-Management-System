<?php

namespace App\Http\Controllers;

use App\Core\Audit\AuditLogger;
use App\Core\Tenancy\TenantManager;
use App\Models\BirthRecord;
use App\Models\Branch;
use App\Models\DeathRecord;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\Request;

class VitalRecordsController extends Controller
{
    /**
     * Statutory Vital Records Registry: Births & Deaths
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

        $birthQuery = BirthRecord::with(['mother', 'obstetrician', 'pediatrician', 'branch']);
        $deathQuery = DeathRecord::with(['patient', 'certifyingDoctor', 'admission', 'branch']);

        if ($selectedBranchId && $selectedBranchId !== 'all') {
            $birthQuery->where('branch_id', (int) $selectedBranchId);
            $deathQuery->where('branch_id', (int) $selectedBranchId);
        }

        $birthRecords = $birthQuery->latest('birth_date')->latest('birth_time')->get();
        $deathRecords = $deathQuery->latest('date_of_death')->latest('time_of_death')->get();

        // Metrics
        $totalBirths = $birthRecords->count();
        $maleBirths = $birthRecords->where('gender', 'male')->count();
        $femaleBirths = $birthRecords->where('gender', 'female')->count();
        $lscsCount = $birthRecords->where('delivery_type', 'lscs_caesarean')->count();
        $totalDeaths = $deathRecords->count();

        $patients = Patient::orderBy('first_name')->get();
        $doctors = User::whereIn('role', ['doctor', 'hospital_admin'])->get();

        return view('modules.vital_records.index', compact(
            'birthRecords',
            'deathRecords',
            'totalBirths',
            'maleBirths',
            'femaleBirths',
            'lscsCount',
            'totalDeaths',
            'branches',
            'selectedBranchId',
            'activeBranch',
            'hospital',
            'patients',
            'doctors'
        ));
    }

    /**
     * Register Birth Record & Generate Certificate
     */
    public function storeBirth(Request $request, ?Branch $outlet = null)
    {
        if ($outlet && $outlet->exists) {
            TenantManager::setActiveBranch($outlet);
        }

        $validated = $request->validate([
            'mother_id' => 'nullable|exists:patients,id',
            'mother_name' => 'required|string|max:200',
            'father_name' => 'nullable|string|max:200',
            'baby_name' => 'required|string|max:200',
            'gender' => 'required|in:male,female,other',
            'birth_date' => 'required|date',
            'birth_time' => 'required',
            'birth_weight_kg' => 'required|numeric|min:0.5|max:8',
            'delivery_type' => 'required|in:normal_vaginal,lscs_caesarean,vacuum_instrumental',
            'gestational_age_weeks' => 'required|integer|min:24|max:44',
            'apgar_score_1min' => 'required|integer|min:0|max:10',
            'apgar_score_5min' => 'required|integer|min:0|max:10',
            'doctor_id' => 'required|exists:users,id',
            'pediatrician_id' => 'nullable|exists:users,id',
            'blood_group' => 'required|string|max:10',
            'branch_id' => 'nullable|exists:branches,id',
            'notes' => 'nullable|string|max:500',
        ]);

        $hospital = TenantManager::getHospital();
        $branchId = $validated['branch_id'] ?? $outlet?->id ?? TenantManager::getBranchId() ?? Branch::where('hospital_id', $hospital?->id)->first()?->id;

        $certNo = 'BC-MH-'.date('Y').'-'.str_pad((string) (BirthRecord::count() + 1), 4, '0', STR_PAD_LEFT);

        $record = BirthRecord::create([
            'hospital_id' => $hospital?->id ?? 1,
            'branch_id' => $branchId,
            'mother_id' => $validated['mother_id'] ?? null,
            'doctor_id' => $validated['doctor_id'],
            'pediatrician_id' => $validated['pediatrician_id'] ?? null,
            'certificate_number' => $certNo,
            'baby_name' => $validated['baby_name'],
            'gender' => $validated['gender'],
            'birth_date' => $validated['birth_date'],
            'birth_time' => $validated['birth_time'],
            'mother_name' => $validated['mother_name'],
            'father_name' => $validated['father_name'] ?? null,
            'birth_weight_kg' => $validated['birth_weight_kg'],
            'delivery_type' => $validated['delivery_type'],
            'gestational_age_weeks' => $validated['gestational_age_weeks'],
            'apgar_score_1min' => $validated['apgar_score_1min'],
            'apgar_score_5min' => $validated['apgar_score_5min'],
            'blood_group' => $validated['blood_group'],
            'status' => 'certificate_issued',
            'notes' => $validated['notes'] ?? null,
        ]);

        AuditLogger::log(
            moduleKey: 'vital_records',
            action: 'birth_record_registered',
            entityType: 'BirthRecord',
            entityId: $record->id,
            details: [
                'cert_no' => $certNo,
                'baby' => $record->baby_name,
                'mother' => $record->mother_name,
                'weight' => $record->birth_weight_kg,
            ]
        );

        return redirect()->route('vital_records.birth_certificate', $record->id)
            ->with('success', "Birth Record registered! Official Certificate #{$certNo} generated.");
    }

    /**
     * Register Death Record & Generate Certificate
     */
    public function storeDeath(Request $request, ?Branch $outlet = null)
    {
        if ($outlet && $outlet->exists) {
            TenantManager::setActiveBranch($outlet);
        }

        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'admission_id' => 'nullable|exists:admissions,id',
            'certifying_doctor_id' => 'required|exists:users,id',
            'date_of_death' => 'required|date',
            'time_of_death' => 'required',
            'place_of_death' => 'required|string|max:100',
            'primary_cause' => 'required|string|max:255',
            'secondary_cause' => 'nullable|string|max:255',
            'icd10_code' => 'required|string|max:50',
            'next_of_kin_name' => 'required|string|max:200',
            'next_of_kin_phone' => 'nullable|string|max:50',
            'next_of_kin_relation' => 'nullable|string|max:100',
            'body_released_to' => 'nullable|string|max:200',
            'cremation_pass_issued' => 'nullable|boolean',
            'autopsy_required' => 'nullable|boolean',
            'branch_id' => 'nullable|exists:branches,id',
            'notes' => 'nullable|string|max:500',
        ]);

        $hospital = TenantManager::getHospital();
        $branchId = $validated['branch_id'] ?? $outlet?->id ?? TenantManager::getBranchId() ?? Branch::where('hospital_id', $hospital?->id)->first()?->id;

        $certNo = 'DC-MH-'.date('Y').'-'.str_pad((string) (DeathRecord::count() + 1), 4, '0', STR_PAD_LEFT);

        $record = DeathRecord::create([
            'hospital_id' => $hospital?->id ?? 1,
            'branch_id' => $branchId,
            'patient_id' => $validated['patient_id'],
            'admission_id' => $validated['admission_id'] ?? null,
            'certifying_doctor_id' => $validated['certifying_doctor_id'],
            'certificate_number' => $certNo,
            'date_of_death' => $validated['date_of_death'],
            'time_of_death' => $validated['time_of_death'],
            'place_of_death' => $validated['place_of_death'],
            'primary_cause' => $validated['primary_cause'],
            'secondary_cause' => $validated['secondary_cause'] ?? null,
            'icd10_code' => $validated['icd10_code'],
            'next_of_kin_name' => $validated['next_of_kin_name'],
            'next_of_kin_phone' => $validated['next_of_kin_phone'] ?? null,
            'next_of_kin_relation' => $validated['next_of_kin_relation'] ?? null,
            'body_released_to' => $validated['body_released_to'] ?? $validated['next_of_kin_name'],
            'cremation_pass_issued' => $request->has('cremation_pass_issued'),
            'autopsy_required' => $request->has('autopsy_required'),
            'status' => 'certified',
            'notes' => $validated['notes'] ?? null,
        ]);

        AuditLogger::log(
            moduleKey: 'vital_records',
            action: 'death_record_certified',
            entityType: 'DeathRecord',
            entityId: $record->id,
            details: [
                'cert_no' => $certNo,
                'patient_id' => $record->patient_id,
                'cause' => $record->primary_cause,
                'icd10' => $record->icd10_code,
            ]
        );

        return redirect()->route('vital_records.death_certificate', $record->id)
            ->with('success', "Death Record registered & certified! Certificate #{$certNo} generated.");
    }

    /**
     * Printable Official Birth Certificate
     */
    public function printBirthCertificate(BirthRecord $birthRecord)
    {
        $birthRecord->load(['mother', 'obstetrician', 'pediatrician', 'branch', 'hospital']);
        $hospital = $birthRecord->hospital ?? TenantManager::getHospital();
        $branch = $birthRecord->branch ?? TenantManager::getBranch();

        return view('modules.vital_records.birth_certificate', compact('birthRecord', 'hospital', 'branch'));
    }

    /**
     * Printable Official Medical Certificate of Cause of Death (MCCD)
     */
    public function printDeathCertificate(DeathRecord $deathRecord)
    {
        $deathRecord->load(['patient', 'certifyingDoctor', 'admission.bed.ward', 'branch', 'hospital']);
        $hospital = $deathRecord->hospital ?? TenantManager::getHospital();
        $branch = $deathRecord->branch ?? TenantManager::getBranch();

        return view('modules.vital_records.death_certificate', compact('deathRecord', 'hospital', 'branch'));
    }
}
