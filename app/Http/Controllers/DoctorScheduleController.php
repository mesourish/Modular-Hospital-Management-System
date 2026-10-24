<?php

namespace App\Http\Controllers;

use App\Core\Audit\AuditLogger;
use App\Core\Tenancy\TenantManager;
use App\Models\Appointment;
use App\Models\Branch;
use App\Models\Consultation;
use App\Models\Department;
use App\Models\DoctorChart;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DoctorScheduleController extends Controller
{
    /**
     * Centralized Calendar-Based Doctor Schedule & Appointment Workspace
     */
    public function index(Request $request)
    {
        $hospital = TenantManager::getHospital();
        if (! $hospital) {
            return redirect()->route('hospital.dashboard')->with('error', 'Select a hospital first.');
        }

        $user = Auth::user();
        $selectedDate = $request->query('date', now()->toDateString());
        $selectedDoctorId = $request->query('doctor_id');
        $selectedBranchId = $request->query('branch_id');

        // Branch Isolation: Super Admin can see all; Branch Admin is restricted to their branch
        if ($user && $user->isRestrictedToBranch()) {
            $selectedBranchId = (string) $user->branch_id;
        }

        // Query appointments for the chosen calendar date
        $query = Appointment::where('hospital_id', $hospital->id)
            ->whereDate('appointment_date', $selectedDate)
            ->with([
                'patient',
                'doctor',
                'branch',
                'department',
                'consultation.prescriptions',
            ]);

        if ($selectedDoctorId && $selectedDoctorId !== 'all') {
            $query->where('doctor_id', (int) $selectedDoctorId);
        }

        if ($selectedBranchId && $selectedBranchId !== 'all') {
            $query->where('branch_id', (int) $selectedBranchId);
        }

        $appointments = $query->orderBy('appointment_time')->get();

        // Doctors list for filtering
        $doctors = User::where('hospital_id', $hospital->id)
            ->where('role', 'doctor')
            ->get();

        // Branches list for filtering (Scoped if restricted)
        $branchesQuery = Branch::where('hospital_id', $hospital->id);
        if ($user && $user->isRestrictedToBranch()) {
            $branchesQuery->where('id', $user->branch_id);
        }
        $branches = $branchesQuery->get();

        // Departments
        $departments = Department::where('hospital_id', $hospital->id)->get();

        // Patients for scheduling modal
        $patients = Patient::where('hospital_id', $hospital->id)->get();

        // Summary counters
        $totalToday = $appointments->count();
        $completedToday = $appointments->where('status', 'completed')->count();
        $inProgressToday = $appointments->whereIn('status', ['waiting', 'in_consultation'])->count();
        $totalPrescriptions = $appointments->sum(function ($app) {
            return $app->consultation ? $app->consultation->prescriptions->count() : 0;
        });

        // Time slots structure for calendar grid (08:00 AM to 07:00 PM)
        $timeSlots = [
            '08:00:00' => '08:00 AM',
            '09:00:00' => '09:00 AM',
            '10:00:00' => '10:00 AM',
            '11:00:00' => '11:00 AM',
            '12:00:00' => '12:00 PM',
            '13:00:00' => '01:00 PM (Lunch)',
            '14:00:00' => '02:00 PM',
            '15:00:00' => '03:00 PM',
            '16:00:00' => '04:00 PM',
            '17:00:00' => '05:00 PM',
            '18:00:00' => '06:00 PM',
            '19:00:00' => '07:00 PM',
        ];

        // Group appointments by approximate hour slot
        $groupedSlots = [];
        foreach ($timeSlots as $rawTime => $slotLabel) {
            $slotHour = substr($rawTime, 0, 2);
            $groupedSlots[$slotLabel] = $appointments->filter(function ($app) use ($slotHour) {
                return substr($app->appointment_time, 0, 2) === $slotHour;
            });
        }

        // 7-Day Interactive Week Ribbon
        $selectedDateCarbon = Carbon::parse($selectedDate);
        $startOfWeek = $selectedDateCarbon->copy()->startOfWeek();
        $weekDays = [];
        for ($i = 0; $i < 7; $i++) {
            $day = $startOfWeek->copy()->addDays($i);
            $dayDateStr = $day->toDateString();
            $count = Appointment::where('hospital_id', $hospital->id)
                ->whereDate('appointment_date', $dayDateStr)
                ->when($selectedDoctorId && $selectedDoctorId !== 'all', fn ($q) => $q->where('doctor_id', (int) $selectedDoctorId))
                ->when($selectedBranchId && $selectedBranchId !== 'all', fn ($q) => $q->where('branch_id', (int) $selectedBranchId))
                ->count();

            $weekDays[] = [
                'date' => $dayDateStr,
                'day_name' => $day->format('D'),
                'day_full' => $day->format('l'),
                'day_num' => $day->format('d'),
                'month' => $day->format('M'),
                'is_selected' => $dayDateStr === $selectedDate,
                'is_today' => $dayDateStr === now()->toDateString(),
                'count' => $count,
            ];
        }

        // Active Doctor Profile & Chamber details from DoctorChart
        $activeDoctorChart = null;
        if ($selectedDoctorId && $selectedDoctorId !== 'all') {
            $activeDoctorChart = DoctorChart::with(['department', 'branch'])->where('user_id', (int) $selectedDoctorId)->first();
        } elseif (Auth::user() && Auth::user()->isDoctor()) {
            $activeDoctorChart = DoctorChart::with(['department', 'branch'])->where('user_id', Auth::id())->first();
        }

        return view('doctor.schedule', compact(
            'hospital',
            'selectedDate',
            'selectedDoctorId',
            'selectedBranchId',
            'appointments',
            'doctors',
            'branches',
            'departments',
            'patients',
            'totalToday',
            'completedToday',
            'inProgressToday',
            'totalPrescriptions',
            'groupedSlots',
            'timeSlots',
            'weekDays',
            'activeDoctorChart'
        ));
    }

    /**
     * Book & Assign a Patient to a Doctor Schedule
     */
    public function storeAppointment(Request $request)
    {
        $hospital = TenantManager::getHospital();
        if (! $hospital) {
            return back()->with('error', 'Select a hospital first.');
        }

        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:users,id',
            'branch_id' => 'required|exists:branches,id',
            'department_id' => 'nullable|exists:departments,id',
            'appointment_date' => 'required|date',
            'appointment_time' => 'required',
            'symptoms' => 'nullable|string|max:500',
        ]);

        $user = Auth::user();
        if ($user && $user->isRestrictedToBranch()) {
            $validated['branch_id'] = $user->branch_id;
        }

        $tokenCount = Appointment::where('hospital_id', $hospital->id)
            ->whereDate('appointment_date', $validated['appointment_date'])
            ->where('doctor_id', $validated['doctor_id'])
            ->count();

        $tokenNumber = 'T-'.str_pad($tokenCount + 1, 3, '0', STR_PAD_LEFT);

        $appointment = Appointment::create([
            'hospital_id' => $hospital->id,
            'branch_id' => $validated['branch_id'],
            'patient_id' => $validated['patient_id'],
            'doctor_id' => $validated['doctor_id'],
            'department_id' => $validated['department_id'] ?? null,
            'appointment_date' => $validated['appointment_date'],
            'appointment_time' => $validated['appointment_time'],
            'token_number' => $tokenNumber,
            'status' => 'scheduled',
            'symptoms' => $validated['symptoms'] ?? 'General Consultation & Health Check',
        ]);

        AuditLogger::log(
            action: 'appointment.assigned',
            auditableType: 'Appointment',
            auditableId: $appointment->id,
            payload: [
                'token' => $tokenNumber,
                'patient_id' => $validated['patient_id'],
                'doctor_id' => $validated['doctor_id'],
                'date' => $validated['appointment_date'],
                'time' => $validated['appointment_time'],
            ]
        );

        return redirect()->route('doctor.schedule', [
            'date' => $validated['appointment_date'],
            'doctor_id' => $validated['doctor_id'],
            'branch_id' => $validated['branch_id'],
        ])->with('success', "Appointment scheduled! Patient assigned with Token {$tokenNumber}.");
    }

    /**
     * Add Itemized Medicine Prescription to Consultation from Calendar
     */
    public function storePrescription(Request $request)
    {
        $hospital = TenantManager::getHospital();
        if (! $hospital) {
            return back()->with('error', 'Select a hospital first.');
        }

        $validated = $request->validate([
            'appointment_id' => 'required|exists:appointments,id',
            'medicine_name' => 'required|string|max:255',
            'dosage' => 'required|string|max:100',
            'frequency' => 'required|string|max:100',
            'duration' => 'required|string|max:100',
            'instructions' => 'nullable|string|max:255',
            'diagnosis' => 'nullable|string|max:255',
            'bp' => 'nullable|string|max:20',
            'pulse' => 'nullable|string|max:20',
            'temperature' => 'nullable|string|max:20',
            'spo2' => 'nullable|string|max:20',
            'weight_kg' => 'nullable|numeric',
            'clinical_notes' => 'nullable|string|max:500',
        ]);

        $appointment = Appointment::with(['patient', 'doctor'])->findOrFail($validated['appointment_id']);

        // Find or create consultation record
        $consultation = Consultation::firstOrCreate(
            ['appointment_id' => $appointment->id],
            [
                'hospital_id' => $hospital->id,
                'patient_id' => $appointment->patient_id,
                'doctor_id' => $appointment->doctor_id,
                'encounter_date' => now(),
                'chief_complaints' => $appointment->symptoms,
                'diagnosis' => $validated['diagnosis'] ?? 'Clinical Evaluation',
                'status' => 'completed',
            ]
        );

        $vitals = array_filter([
            'bp' => $request->input('bp'),
            'pulse' => $request->input('pulse'),
            'temperature' => $request->input('temperature'),
            'spo2' => $request->input('spo2'),
            'weight_kg' => $request->input('weight_kg'),
            'clinical_notes' => $request->input('clinical_notes'),
            'diagnosis' => $validated['diagnosis'] ?? null,
        ]);

        if (! empty($vitals)) {
            $consultation->update($vitals);
        }

        // Add itemized medicine prescription
        $prescription = Prescription::create([
            'hospital_id' => $hospital->id,
            'consultation_id' => $consultation->id,
            'patient_id' => $appointment->patient_id,
            'doctor_id' => $appointment->doctor_id,
            'medicine_name' => $validated['medicine_name'],
            'dosage' => $validated['dosage'],
            'frequency' => $validated['frequency'],
            'duration' => $validated['duration'],
            'instructions' => $validated['instructions'] ?? 'Take as directed by doctor',
            'is_dispensed' => false,
        ]);

        // Mark appointment as completed
        $appointment->update(['status' => 'completed']);

        AuditLogger::log(
            action: 'prescription.issued',
            auditableType: 'Prescription',
            auditableId: $prescription->id,
            payload: [
                'patient_id' => $appointment->patient_id,
                'doctor_id' => $appointment->doctor_id,
                'medicine' => $prescription->medicine_name,
                'dosage' => $prescription->dosage,
            ]
        );

        return redirect()->route('doctor.schedule', [
            'date' => Carbon::parse($appointment->appointment_date)->toDateString(),
            'doctor_id' => $appointment->doctor_id,
            'branch_id' => $appointment->branch_id,
        ])->with('success', "Prescription recorded for {$appointment->patient->name}: {$prescription->medicine_name} ({$prescription->dosage}, {$prescription->frequency}). Sent to Central Pharmacy.");
    }

    /**
     * Update appointment status (e.g. In Consultation, Completed)
     */
    public function updateStatus(Request $request, Appointment $appointment)
    {
        $validated = $request->validate([
            'status' => 'required|in:scheduled,waiting,in_consultation,completed,cancelled',
        ]);

        $appointment->update(['status' => $validated['status']]);

        return back()->with('success', 'Appointment status updated to '.ucfirst(str_replace('_', ' ', $validated['status'])));
    }
}
