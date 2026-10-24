<?php

namespace App\Http\Controllers;

use App\Core\Audit\AuditLogger;
use App\Models\Appointment;
use App\Models\Consultation;
use App\Models\Department;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\User;
use Illuminate\Http\Request;

class OpdController extends Controller
{
    public function index()
    {
        $appointments = Appointment::with(['patient', 'doctor', 'department', 'consultation'])->latest()->get();
        $patients = Patient::all();
        $doctors = User::whereIn('role', ['doctor', 'hospital_admin'])->get();
        $departments = Department::where('is_active', true)->get();
        $consultations = Consultation::with(['patient', 'doctor', 'prescriptions'])->latest()->take(10)->get();

        return view('modules.opd.index', compact(
            'appointments',
            'patients',
            'doctors',
            'departments',
            'consultations'
        ));
    }

    public function storeAppointment(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:users,id',
            'department_id' => 'nullable|exists:departments,id',
            'appointment_date' => 'required|date',
            'appointment_time' => 'nullable',
            'symptoms' => 'nullable|string',
        ]);

        $tokenNumber = Appointment::where('appointment_date', $validated['appointment_date'])
            ->where('doctor_id', $validated['doctor_id'])
            ->count() + 1;

        $appointment = Appointment::create(array_merge($validated, [
            'token_number' => $tokenNumber,
            'status' => 'scheduled',
        ]));

        AuditLogger::log(
            moduleKey: 'opd',
            action: 'appointment_scheduled',
            entityType: 'Appointment',
            entityId: $appointment->id,
            details: ['token' => $tokenNumber, 'date' => $validated['appointment_date']]
        );

        return redirect()->back()->with('success', "Appointment scheduled (Token #{$tokenNumber}).");
    }

    public function storeConsultation(Request $request)
    {
        $validated = $request->validate([
            'appointment_id' => 'nullable|exists:appointments,id',
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:users,id',
            'chief_complaints' => 'required|string',
            'examination_findings' => 'nullable|string',
            'diagnosis' => 'required|string',
            'bp' => 'nullable|string',
            'pulse' => 'nullable|string',
            'temperature' => 'nullable|string',
            'spo2' => 'nullable|string',
            'clinical_notes' => 'nullable|string',
            'medicine_name' => 'nullable|string',
            'dosage' => 'nullable|string',
            'frequency' => 'nullable|string',
            'duration' => 'nullable|string',
            'instructions' => 'nullable|string',
        ]);

        $consultation = Consultation::create([
            'appointment_id' => $validated['appointment_id'] ?? null,
            'patient_id' => $validated['patient_id'],
            'doctor_id' => $validated['doctor_id'],
            'encounter_date' => now(),
            'chief_complaints' => $validated['chief_complaints'],
            'examination_findings' => $validated['examination_findings'] ?? null,
            'diagnosis' => $validated['diagnosis'],
            'bp' => $validated['bp'] ?? null,
            'pulse' => $validated['pulse'] ?? null,
            'temperature' => $validated['temperature'] ?? null,
            'spo2' => $validated['spo2'] ?? null,
            'clinical_notes' => $validated['clinical_notes'] ?? null,
            'status' => 'finalized',
        ]);

        if (! empty($validated['appointment_id'])) {
            Appointment::where('id', $validated['appointment_id'])->update(['status' => 'completed']);
        }

        if (! empty($validated['medicine_name'])) {
            Prescription::create([
                'consultation_id' => $consultation->id,
                'patient_id' => $validated['patient_id'],
                'doctor_id' => $validated['doctor_id'],
                'medicine_name' => $validated['medicine_name'],
                'dosage' => $validated['dosage'] ?? '1 Tablet',
                'frequency' => $validated['frequency'] ?? 'Twice daily',
                'duration' => $validated['duration'] ?? '5 days',
                'instructions' => $validated['instructions'] ?? 'After food',
            ]);
        }

        AuditLogger::log(
            moduleKey: 'opd',
            action: 'consultation_finalized',
            entityType: 'Consultation',
            entityId: $consultation->id,
            details: ['diagnosis' => $consultation->diagnosis]
        );

        return redirect()->back()->with('success', 'Clinical consultation encounter & e-prescription saved.');
    }
}
