<?php

namespace App\Http\Controllers;

use App\Core\Audit\AuditLogger;
use App\Models\Patient;
use Illuminate\Http\Request;

class PatientHistoryController extends Controller
{
    /**
     * Show Comprehensive Longitudinal Patient Medical Record & Journey
     */
    public function show(Patient $patient)
    {
        $patient->load([
            'admissions.bed.ward',
            'admissions.doctor',
            'consultations.doctor',
            'consultations.appointment',
            'appointments.doctor',
            'prescriptions.doctor',
            'otSurgeries.operationTheater',
            'otSurgeries.leadSurgeon',
            'otSurgeries.anesthesiologist',
            'otSurgeries.medicationLogs',
            'otMedicationLogs',
            'radiologyOrders.modality',
            'radiologyOrders.reports.verifiedBy',
            'dietaryOrders',
            'birthRecordsAsMother',
            'deathRecord.certifyingDoctor',
            'invoices.items',
            'invoices.payments',
            'ambulanceTrips.ambulance',
        ]);

        return view('modules.admissions.patient_history', compact('patient'));
    }

    /**
     * Update Patient Clinical History (Allergies, Chronic Illnesses, Past Surgeries)
     */
    public function update(Request $request, Patient $patient)
    {
        $validated = $request->validate([
            'allergies' => 'nullable|string|max:1000',
            'chronic_conditions' => 'nullable|string|max:1000',
            'past_surgeries' => 'nullable|string|max:1000',
            'family_medical_history' => 'nullable|string|max:1000',
            'emergency_contact_name' => 'nullable|string|max:150',
            'emergency_contact_phone' => 'nullable|string|max:50',
            'blood_group' => 'nullable|string|max:10',
        ]);

        $patient->update($validated);

        AuditLogger::log(
            moduleKey: 'admissions',
            action: 'clinical_history_updated',
            entityType: 'Patient',
            entityId: $patient->id,
            details: [
                'mrn' => $patient->mrn,
                'name' => $patient->full_name,
                'allergies' => $patient->allergies,
                'chronic_conditions' => $patient->chronic_conditions,
            ]
        );

        return back()->with('success', "Patient Clinical History updated for {$patient->full_name} (MRN: {$patient->mrn}).");
    }
}
