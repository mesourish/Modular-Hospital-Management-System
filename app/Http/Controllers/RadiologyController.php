<?php

namespace App\Http\Controllers;

use App\Core\Audit\AuditLogger;
use App\Models\Patient;
use App\Models\RadiologyModality;
use App\Models\RadiologyOrder;
use App\Models\RadiologyReport;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RadiologyController extends Controller
{
    public function index(Request $request)
    {
        $selectedDate = $request->get('date', now()->toDateString());
        $selectedModalityId = $request->get('modality_id');

        $modalities = RadiologyModality::where('is_active', true)->get();
        $patients = Patient::orderBy('first_name')->get();
        $doctors = User::whereIn('role', ['doctor', 'hospital_admin', 'branch_admin'])->get();
        $radiologists = User::whereIn('role', ['radiologist', 'doctor', 'hospital_admin'])->get();

        // Standard radiology timetable time slots
        $timeSlots = [
            '08:00 AM',
            '09:00 AM',
            '10:00 AM',
            '11:00 AM',
            '12:00 PM',
            '01:00 PM',
            '02:00 PM',
            '03:00 PM',
            '04:00 PM',
            '05:00 PM',
            '06:00 PM',
            '07:00 PM',
        ];

        // Modality Timetable Schedule orders for selected date
        $scheduleQuery = RadiologyOrder::with(['patient', 'modality', 'orderingDoctor', 'report'])
            ->whereDate('scheduled_at', $selectedDate);

        if ($selectedModalityId) {
            $scheduleQuery->where('modality_id', $selectedModalityId);
        }

        $scheduleOrders = $scheduleQuery->orderBy('slot_time')->orderBy('scheduled_at')->get();

        // Walk-in Customers Section orders
        $walkinOrders = RadiologyOrder::with(['patient', 'modality', 'orderingDoctor', 'report'])
            ->where('is_walkin', true)
            ->where(function ($q) use ($selectedDate) {
                $q->whereDate('scheduled_at', $selectedDate)
                    ->orWhereDate('created_at', $selectedDate);
            })
            ->latest()
            ->get();

        // Complete Worklist orders
        $orders = RadiologyOrder::with(['patient', 'modality', 'orderingDoctor', 'report'])
            ->latest()
            ->take(100)
            ->get();

        // Summary KPI statistics
        $totalScansToday = RadiologyOrder::whereDate('scheduled_at', $selectedDate)->count();
        $walkinsToday = RadiologyOrder::where('is_walkin', true)->whereDate('created_at', $selectedDate)->count();
        $scheduledToday = RadiologyOrder::where('is_walkin', false)->whereDate('scheduled_at', $selectedDate)->count();
        $completedScans = RadiologyOrder::whereDate('scheduled_at', $selectedDate)->whereIn('status', ['performed', 'reported'])->count();

        return view('modules.radiology.index', compact(
            'modalities',
            'orders',
            'scheduleOrders',
            'walkinOrders',
            'patients',
            'doctors',
            'radiologists',
            'selectedDate',
            'selectedModalityId',
            'timeSlots',
            'totalScansToday',
            'walkinsToday',
            'scheduledToday',
            'completedScans'
        ));
    }

    public function storeOrder(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'modality_id' => 'required|exists:radiology_modalities,id',
            'procedure_name' => 'required|string|max:200',
            'clinical_indication' => 'nullable|string',
            'ordering_doctor_id' => 'required|exists:users,id',
            'priority' => 'required|in:routine,urgent,stat_critical',
            'slot_time' => 'nullable|string|max:50',
            'scheduled_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $orderNo = 'RAD-'.date('Y').'-'.str_pad((string) (RadiologyOrder::count() + 1), 5, '0', STR_PAD_LEFT);
        $scheduledDate = ! empty($validated['scheduled_date']) ? Carbon::parse($validated['scheduled_date']) : now();

        $order = RadiologyOrder::create([
            'hospital_id' => auth()->user()?->hospital_id,
            'patient_id' => $validated['patient_id'],
            'modality_id' => $validated['modality_id'],
            'order_number' => $orderNo,
            'procedure_name' => $validated['procedure_name'],
            'clinical_indication' => $validated['clinical_indication'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'ordering_doctor_id' => $validated['ordering_doctor_id'],
            'priority' => $validated['priority'],
            'status' => 'ordered',
            'is_walkin' => false,
            'slot_time' => $validated['slot_time'] ?? '10:00 AM',
            'scheduled_at' => $scheduledDate,
        ]);

        AuditLogger::log(
            moduleKey: 'radiology',
            action: 'radiology_order_placed',
            entityType: 'RadiologyOrder',
            entityId: $order->id,
            details: ['order_number' => $orderNo, 'procedure' => $order->procedure_name, 'priority' => $order->priority]
        );

        return redirect()->back()->with('success', "Radiology procedure order #{$orderNo} placed successfully.");
    }

    public function storeWalkin(Request $request)
    {
        $validated = $request->validate([
            'patient_type' => 'nullable|in:existing,new',
            'patient_id' => 'nullable|exists:patients,id',
            'walkin_name' => 'nullable|string|max:150',
            'walkin_phone' => 'nullable|string|max:30',
            'walkin_gender' => 'nullable|in:male,female,other',
            'modality_id' => 'required|exists:radiology_modalities,id',
            'procedure_name' => 'required|string|max:200',
            'referring_doctor_name' => 'nullable|string|max:150',
            'slot_time' => 'nullable|string|max:50',
            'priority' => 'required|in:routine,urgent,stat_critical',
            'notes' => 'nullable|string',
        ]);

        $patient = null;

        if ($request->input('patient_type') === 'existing' && ! empty($validated['patient_id'])) {
            $patient = Patient::findOrFail($validated['patient_id']);
        } else {
            // New or walk-in patient registration
            $fullName = trim($validated['walkin_name'] ?? 'Walk-in Patient');
            $parts = explode(' ', $fullName, 2);
            $firstName = $parts[0] ?? 'Walkin';
            $lastName = $parts[1] ?? 'Customer';

            $mrn = 'MRN-'.date('Ymd').'-'.str_pad((string) (Patient::count() + 1), 4, '0', STR_PAD_LEFT);

            $patient = Patient::create([
                'hospital_id' => auth()->user()?->hospital_id,
                'mrn' => $mrn,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'phone' => $validated['walkin_phone'] ?? '9876543210',
                'gender' => $validated['walkin_gender'] ?? 'other',
                'date_of_birth' => now()->subYears(30)->toDateString(),
            ]);
        }

        // Generate token number for today's walk-ins
        $todayWalkinCount = RadiologyOrder::where('is_walkin', true)
            ->whereDate('created_at', today())
            ->count();
        $tokenNumber = 'RAD-W'.str_pad((string) ($todayWalkinCount + 1), 2, '0', STR_PAD_LEFT);
        $orderNo = 'RAD-WALK-'.date('Ymd').'-'.str_pad((string) (RadiologyOrder::count() + 1), 4, '0', STR_PAD_LEFT);

        $orderingDoctorId = auth()->user()?->isDoctor() ? auth()->id() : User::where('role', 'doctor')->first()?->id ?? auth()->id();

        $order = RadiologyOrder::create([
            'hospital_id' => auth()->user()?->hospital_id,
            'branch_id' => auth()->user()?->branch_id,
            'patient_id' => $patient->id,
            'modality_id' => $validated['modality_id'],
            'order_number' => $orderNo,
            'token_number' => $tokenNumber,
            'procedure_name' => $validated['procedure_name'],
            'clinical_indication' => $validated['notes'] ?? 'Walk-in Diagnostic Scan',
            'notes' => $validated['notes'] ?? null,
            'ordering_doctor_id' => $orderingDoctorId,
            'referring_doctor_name' => $validated['referring_doctor_name'] ?? 'Walk-in / External Prescription',
            'patient_contact' => $validated['walkin_phone'] ?? $patient->phone,
            'priority' => $validated['priority'],
            'status' => 'scheduled',
            'is_walkin' => true,
            'scheduled_at' => now(),
            'slot_time' => $validated['slot_time'] ?? now()->format('h:i A'),
        ]);

        AuditLogger::log(
            moduleKey: 'radiology',
            action: 'radiology_walkin_registered',
            entityType: 'RadiologyOrder',
            entityId: $order->id,
            details: [
                'token_number' => $tokenNumber,
                'order_number' => $orderNo,
                'patient_name' => $patient->fullName,
                'procedure' => $order->procedure_name,
                'slot_time' => $order->slot_time,
            ]
        );

        return redirect()->back()->with('success', "Walk-in Diagnostic Customer registered successfully with Token #{$tokenNumber} ({$orderNo}).");
    }

    public function updateStatus(Request $request, RadiologyOrder $order)
    {
        $validated = $request->validate([
            'status' => 'required|in:scheduled,ordered,in_progress,performed,reported,cancelled',
        ]);

        $updateData = ['status' => $validated['status']];

        if ($validated['status'] === 'performed' && ! $order->performed_at) {
            $updateData['performed_at'] = now();
        }

        $order->update($updateData);

        AuditLogger::log(
            moduleKey: 'radiology',
            action: 'radiology_order_status_updated',
            entityType: 'RadiologyOrder',
            entityId: $order->id,
            details: ['order_number' => $order->order_number, 'new_status' => $validated['status']]
        );

        return redirect()->back()->with('success', "Order #{$order->order_number} status updated to ".ucfirst(str_replace('_', ' ', $validated['status'])).'.');
    }

    public function storeReport(Request $request)
    {
        $validated = $request->validate([
            'radiology_order_id' => 'required|exists:radiology_orders,id',
            'radiologist_id' => 'required|exists:users,id',
            'findings' => 'required|string',
            'impression' => 'required|string',
            'recommendations' => 'nullable|string',
        ]);

        $order = RadiologyOrder::findOrFail($validated['radiology_order_id']);

        $dicomUid = '1.2.840.113619.2.55.'.time();

        $report = RadiologyReport::updateOrCreate(
            ['radiology_order_id' => $order->id],
            [
                'hospital_id' => $order->hospital_id,
                'radiologist_id' => $validated['radiologist_id'],
                'findings' => $validated['findings'],
                'impression' => $validated['impression'],
                'recommendations' => $validated['recommendations'] ?? null,
                'dicom_study_uid' => $dicomUid,
                'status' => 'verified',
                'verified_at' => now(),
            ]
        );

        $order->update([
            'status' => 'reported',
            'performed_at' => $order->performed_at ?? now(),
        ]);

        AuditLogger::log(
            moduleKey: 'radiology',
            action: 'radiology_report_verified_and_signed',
            entityType: 'RadiologyReport',
            entityId: $report->id,
            details: ['order_number' => $order->order_number, 'impression' => substr($report->impression, 0, 100)]
        );

        return redirect()->back()->with('success', "Radiologist report signed and verified for Order #{$order->order_number}.");
    }
}
