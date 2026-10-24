<?php

namespace App\Http\Controllers;

use App\Core\Audit\AuditLogger;
use App\Models\Admission;
use App\Models\Bed;
use App\Models\Patient;
use App\Models\User;
use App\Models\Ward;
use Illuminate\Http\Request;

class AdmissionsController extends Controller
{
    public function index()
    {
        $patients = Patient::latest()->paginate(15);
        $admissions = Admission::with(['patient', 'ward', 'bed', 'attendingDoctor'])->latest()->get();
        $wards = Ward::with('beds')->where('is_active', true)->get();
        $availableBeds = Bed::where('status', 'available')->get();
        $doctors = User::whereIn('role', ['doctor', 'hospital_admin'])->get();

        return view('modules.admissions.index', compact(
            'patients',
            'admissions',
            'wards',
            'availableBeds',
            'doctors'
        ));
    }

    public function storePatient(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'date_of_birth' => 'nullable|date',
            'gender' => 'required|in:male,female,other',
            'blood_group' => 'nullable|string',
            'phone' => 'nullable|string',
            'emergency_contact_name' => 'nullable|string',
            'emergency_contact_phone' => 'nullable|string',
            'allergies' => 'nullable|string',
            'chronic_conditions' => 'nullable|string',
        ]);

        $mrn = 'MRN-'.date('Y').'-'.str_pad((string) (Patient::count() + 1), 5, '0', STR_PAD_LEFT);

        $patient = Patient::create(array_merge($validated, ['mrn' => $mrn]));

        AuditLogger::log(
            moduleKey: 'admissions',
            action: 'patient_registered',
            entityType: 'Patient',
            entityId: $patient->id,
            details: ['mrn' => $mrn, 'name' => $patient->fullName]
        );

        return redirect()->back()->with('success', "Patient {$patient->fullName} registered with {$mrn}.");
    }

    public function storeAdmission(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'ward_id' => 'required|exists:wards,id',
            'bed_id' => 'required|exists:beds,id',
            'attending_doctor_id' => 'nullable|exists:users,id',
            'admission_type' => 'required|in:emergency,planned,transfer,day_care',
            'admission_reason' => 'required|string',
        ]);

        $bed = Bed::findOrFail($validated['bed_id']);
        if ($bed->status !== 'available') {
            return redirect()->back()->with('error', 'Selected bed is already occupied or under maintenance.');
        }

        $admNo = 'ADM-'.date('Y').'-'.str_pad((string) (Admission::count() + 1), 4, '0', STR_PAD_LEFT);

        $admission = Admission::create([
            'patient_id' => $validated['patient_id'],
            'admission_number' => $admNo,
            'admission_date' => now(),
            'ward_id' => $validated['ward_id'],
            'bed_id' => $validated['bed_id'],
            'attending_doctor_id' => $validated['attending_doctor_id'],
            'admission_type' => $validated['admission_type'],
            'admission_reason' => $validated['admission_reason'],
            'status' => 'admitted',
        ]);

        $bed->update(['status' => 'occupied']);

        AuditLogger::log(
            moduleKey: 'admissions',
            action: 'patient_admitted',
            entityType: 'Admission',
            entityId: $admission->id,
            details: ['admission_no' => $admNo, 'bed' => $bed->bed_number]
        );

        return redirect()->back()->with('success', "Patient admitted to {$bed->bed_number} (Admission #{$admNo}).");
    }

    public function discharge(Request $request, Admission $admission)
    {
        $admission->update([
            'status' => 'discharged',
            'discharge_date' => now(),
            'discharge_summary' => $request->input('discharge_summary', 'Discharged in stable clinical condition. Follow up in OPD after 1 week.'),
        ]);

        $admission->bed->update(['status' => 'available']);

        AuditLogger::log(
            moduleKey: 'admissions',
            action: 'patient_discharged',
            entityType: 'Admission',
            entityId: $admission->id,
            details: ['admission_no' => $admission->admission_number]
        );

        return redirect()->back()->with('success', 'Patient discharged successfully.');
    }
}
