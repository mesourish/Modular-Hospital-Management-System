<?php

namespace App\Http\Controllers;

use App\Core\Audit\AuditLogger;
use App\Core\Tenancy\TenantManager;
use App\Models\Branch;
use App\Models\Department;
use App\Models\DoctorChart;
use App\Models\Hospital;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DoctorChartController extends Controller
{
    /**
     * Hospital-Wise & Branch-Wise Doctor OPD Master Charts & Fee Schedule
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $hospital = TenantManager::getHospital();

        // If Super Admin viewing and specific hospital passed
        if ($user && $user->isSuperAdmin() && $request->filled('hospital_id')) {
            $hospital = Hospital::find((int) $request->hospital_id) ?? $hospital;
        }

        if (! $hospital) {
            return redirect()->route('hospital.dashboard')->with('error', 'Select an active hospital to manage doctor charts.');
        }

        $selectedBranchId = $request->query('branch_id');
        $selectedDeptId = $request->query('department_id');
        $selectedStatus = $request->query('status');
        $search = $request->query('search');

        // If user is a branch admin restricted to a specific branch
        if ($user && ! $user->isSuperAdmin() && ! empty($user->branch_id) && $user->role === 'branch_admin') {
            $selectedBranchId = (string) $user->branch_id;
        }

        $query = DoctorChart::with(['hospital', 'branch', 'department', 'user'])
            ->where('hospital_id', $hospital->id);

        if ($selectedBranchId && $selectedBranchId !== 'all') {
            $query->where('branch_id', (int) $selectedBranchId);
        }

        if ($selectedDeptId && $selectedDeptId !== 'all') {
            $query->where('department_id', (int) $selectedDeptId);
        }

        if ($selectedStatus && $selectedStatus !== 'all') {
            $query->where('status', $selectedStatus);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('doctor_name', 'like', "%{$search}%")
                    ->orWhere('degrees', 'like', "%{$search}%")
                    ->orWhere('specialty', 'like', "%{$search}%")
                    ->orWhere('room_number', 'like', "%{$search}%")
                    ->orWhere('opd_days', 'like', "%{$search}%");
            });
        }

        $doctorCharts = $query->orderBy('branch_id')
            ->orderBy('department_id')
            ->orderBy('start_time')
            ->paginate(15);

        // All charts query for stats
        $allHospitalCharts = DoctorChart::where('hospital_id', $hospital->id)->get();
        $totalCharts = $allHospitalCharts->count();
        $availableDoctors = $allHospitalCharts->where('status', 'available')->count();
        $onLeaveDoctors = $allHospitalCharts->where('status', 'on_leave')->count();
        $totalDailyCapacity = $allHospitalCharts->where('status', 'available')->sum('max_patients_per_session');
        $avgConsultationFee = $allHospitalCharts->count() > 0 ? (float) $allHospitalCharts->avg('consultation_fee') : 0;

        // Form lookups
        $branches = Branch::where('hospital_id', $hospital->id)->where('is_active', true)->get();
        $departments = Department::where('hospital_id', $hospital->id)->get();
        $registeredDoctors = User::where('hospital_id', $hospital->id)
            ->where('role', 'doctor')
            ->orderBy('name')
            ->get();
        $allHospitals = $user && $user->isSuperAdmin() ? Hospital::all() : collect([$hospital]);

        $canManage = $user && $user->canManageDoctorCharts();

        return view('modules.doctor_charts.index', compact(
            'doctorCharts',
            'hospital',
            'branches',
            'departments',
            'registeredDoctors',
            'allHospitals',
            'totalCharts',
            'availableDoctors',
            'onLeaveDoctors',
            'totalDailyCapacity',
            'avgConsultationFee',
            'selectedBranchId',
            'selectedDeptId',
            'selectedStatus',
            'search',
            'canManage'
        ));
    }

    /**
     * Store a New Doctor Chart / OPD Schedule Entry (Hospital & Branch Admin)
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        if (! $user || ! $user->canManageDoctorCharts()) {
            return back()->with('error', 'Unauthorized: Doctor charts can only be managed by Hospital Admin and Branch Admin.');
        }

        $hospital = TenantManager::getHospital();
        if (! $hospital) {
            return back()->with('error', 'Active hospital tenant not found.');
        }

        $validated = $request->validate([
            'doctor_name' => 'required|string|max:150',
            'degrees' => 'required|string|max:200',
            'specialty' => 'required|string|max:150',
            'branch_id' => 'required|exists:branches,id',
            'department_id' => 'nullable|exists:departments,id',
            'user_id' => 'nullable|exists:users,id',
            'opd_days' => 'required|string|max:150',
            'opd_date' => 'nullable|date',
            'start_time' => 'required',
            'end_time' => 'required',
            'time_slot_label' => 'nullable|string|max:150',
            'room_number' => 'required|string|max:100',
            'consultation_fee' => 'required|numeric|min:0',
            'follow_up_fee' => 'nullable|numeric|min:0',
            'emergency_fee' => 'nullable|numeric|min:0',
            'max_patients_per_session' => 'nullable|integer|min:1',
            'avg_consultation_time_mins' => 'nullable|integer|min:5|max:120',
            'status' => 'required|in:available,on_leave,busy,inactive',
            'instructions' => 'nullable|string|max:500',
        ]);

        // If user is a branch admin, lock to their assigned branch
        if ($user->role === 'branch_admin' && ! empty($user->branch_id)) {
            $validated['branch_id'] = $user->branch_id;
        }

        // Auto-generate time slot label if empty
        if (empty($validated['time_slot_label'])) {
            try {
                $startFormatted = Carbon::createFromTimeString($validated['start_time'])->format('h:i A');
                $endFormatted = Carbon::createFromTimeString($validated['end_time'])->format('h:i A');
                $validated['time_slot_label'] = "{$startFormatted} - {$endFormatted}";
            } catch (\Throwable $e) {
                $validated['time_slot_label'] = "{$validated['start_time']} - {$validated['end_time']}";
            }
        }

        $chart = DoctorChart::create([
            'hospital_id' => $hospital->id,
            'branch_id' => $validated['branch_id'],
            'department_id' => $validated['department_id'] ?? null,
            'user_id' => $validated['user_id'] ?? null,
            'doctor_name' => $validated['doctor_name'],
            'degrees' => $validated['degrees'],
            'specialty' => $validated['specialty'],
            'opd_days' => $validated['opd_days'],
            'opd_date' => $validated['opd_date'] ?? null,
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'time_slot_label' => $validated['time_slot_label'],
            'room_number' => $validated['room_number'],
            'consultation_fee' => (float) $validated['consultation_fee'],
            'follow_up_fee' => (float) ($validated['follow_up_fee'] ?? 0),
            'emergency_fee' => isset($validated['emergency_fee']) ? (float) $validated['emergency_fee'] : null,
            'max_patients_per_session' => (int) ($validated['max_patients_per_session'] ?? 25),
            'avg_consultation_time_mins' => (int) ($validated['avg_consultation_time_mins'] ?? 15),
            'status' => $validated['status'],
            'instructions' => $validated['instructions'] ?? null,
            'is_active' => true,
        ]);

        AuditLogger::log(
            action: 'doctor_chart.created',
            auditableType: 'DoctorChart',
            auditableId: $chart->id,
            payload: [
                'doctor' => $chart->doctor_name,
                'degrees' => $chart->degrees,
                'branch' => $chart->branch?->name,
                'opd_days' => $chart->opd_days,
                'timing' => $chart->formatted_timing,
                'fee' => $chart->consultation_fee,
                'created_by' => $user->email,
            ]
        );

        return back()->with('success', "Doctor chart for {$chart->doctor_name} ({$chart->degrees}) added successfully with fee ₹{$chart->consultation_fee}.");
    }

    /**
     * Update an Existing Doctor Chart Entry (Hospital & Branch Admin)
     */
    public function update(Request $request, DoctorChart $doctorChart)
    {
        $user = Auth::user();
        if (! $user || ! $user->canManageDoctorCharts()) {
            return back()->with('error', 'Unauthorized: Doctor charts can only be managed by Hospital Admin and Branch Admin.');
        }

        // Branch admin check
        if ($user->role === 'branch_admin' && ! empty($user->branch_id) && $doctorChart->branch_id != $user->branch_id) {
            return back()->with('error', 'Unauthorized: Branch Admin can only modify doctor charts of their assigned branch.');
        }

        $validated = $request->validate([
            'doctor_name' => 'required|string|max:150',
            'degrees' => 'required|string|max:200',
            'specialty' => 'required|string|max:150',
            'branch_id' => 'required|exists:branches,id',
            'department_id' => 'nullable|exists:departments,id',
            'user_id' => 'nullable|exists:users,id',
            'opd_days' => 'required|string|max:150',
            'opd_date' => 'nullable|date',
            'start_time' => 'required',
            'end_time' => 'required',
            'time_slot_label' => 'nullable|string|max:150',
            'room_number' => 'required|string|max:100',
            'consultation_fee' => 'required|numeric|min:0',
            'follow_up_fee' => 'nullable|numeric|min:0',
            'emergency_fee' => 'nullable|numeric|min:0',
            'max_patients_per_session' => 'nullable|integer|min:1',
            'avg_consultation_time_mins' => 'nullable|integer|min:5|max:120',
            'status' => 'required|in:available,on_leave,busy,inactive',
            'instructions' => 'nullable|string|max:500',
        ]);

        if (empty($validated['time_slot_label'])) {
            try {
                $startFormatted = Carbon::createFromTimeString($validated['start_time'])->format('h:i A');
                $endFormatted = Carbon::createFromTimeString($validated['end_time'])->format('h:i A');
                $validated['time_slot_label'] = "{$startFormatted} - {$endFormatted}";
            } catch (\Throwable $e) {
                $validated['time_slot_label'] = "{$validated['start_time']} - {$validated['end_time']}";
            }
        }

        $oldFee = $doctorChart->consultation_fee;
        $doctorChart->update($validated);

        AuditLogger::log(
            action: 'doctor_chart.updated',
            auditableType: 'DoctorChart',
            auditableId: $doctorChart->id,
            payload: [
                'doctor' => $doctorChart->doctor_name,
                'old_fee' => $oldFee,
                'new_fee' => $doctorChart->consultation_fee,
                'status' => $doctorChart->status,
                'updated_by' => $user->email,
            ]
        );

        return back()->with('success', "Doctor chart for {$doctorChart->doctor_name} updated successfully.");
    }

    /**
     * Delete / Deactivate Doctor Chart Entry
     */
    public function destroy(DoctorChart $doctorChart)
    {
        $user = Auth::user();
        if (! $user || ! $user->canManageDoctorCharts()) {
            return back()->with('error', 'Unauthorized: Doctor charts can only be managed by Hospital Admin and Branch Admin.');
        }

        if ($user->role === 'branch_admin' && ! empty($user->branch_id) && $doctorChart->branch_id != $user->branch_id) {
            return back()->with('error', 'Unauthorized: Branch Admin can only delete doctor charts of their assigned branch.');
        }

        $name = $doctorChart->doctor_name;
        $id = $doctorChart->id;
        $doctorChart->delete();

        AuditLogger::log(
            action: 'doctor_chart.deleted',
            auditableType: 'DoctorChart',
            auditableId: $id,
            payload: ['doctor' => $name, 'deleted_by' => $user->email]
        );

        return back()->with('success', "Doctor chart for {$name} removed successfully.");
    }

    /**
     * Quick 1-Click Status Toggle (e.g. Doctor is On-Leave or Available)
     */
    public function toggleStatus(DoctorChart $doctorChart)
    {
        $user = Auth::user();
        if (! $user || ! $user->canManageDoctorCharts()) {
            return back()->with('error', 'Unauthorized action.');
        }

        if ($doctorChart->status === 'available') {
            $doctorChart->status = 'on_leave';
        } else {
            $doctorChart->status = 'available';
        }

        $doctorChart->save();

        AuditLogger::log(
            action: 'doctor_chart.status_toggled',
            auditableType: 'DoctorChart',
            auditableId: $doctorChart->id,
            payload: [
                'doctor' => $doctorChart->doctor_name,
                'new_status' => $doctorChart->status,
                'toggled_by' => $user->email,
            ]
        );

        return back()->with('success', "Status for {$doctorChart->doctor_name} switched to ".strtoupper($doctorChart->status).'.');
    }

    /**
     * Clean Public / Reception Display Print View
     */
    public function print(Request $request)
    {
        $hospital = TenantManager::getHospital();
        $selectedBranchId = $request->query('branch_id');

        $query = DoctorChart::with(['hospital', 'branch', 'department'])
            ->where('hospital_id', $hospital->id)
            ->where('is_active', true);

        if ($selectedBranchId && $selectedBranchId !== 'all') {
            $query->where('branch_id', (int) $selectedBranchId);
        }

        $doctorCharts = $query->orderBy('branch_id')
            ->orderBy('department_id')
            ->orderBy('start_time')
            ->get();

        $branchName = $selectedBranchId && $selectedBranchId !== 'all'
            ? (Branch::find((int) $selectedBranchId)?->name ?? 'All Branches')
            : 'All Branches Roster';

        return view('modules.doctor_charts.print', compact('doctorCharts', 'hospital', 'branchName'));
    }
}
