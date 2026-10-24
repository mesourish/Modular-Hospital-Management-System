<?php

namespace App\Http\Controllers;

use App\Core\Audit\AuditLogger;
use App\Core\Tenancy\TenantManager;
use App\Models\Admission;
use App\Models\Branch;
use App\Models\Department;
use App\Models\DutyRoster;
use App\Models\Employee;
use App\Models\PatientAssignment;
use App\Models\Shift;
use App\Models\StaffAttendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WorkforceController extends Controller
{
    /**
     * Workforce Management, Duty Rosters, Patient Care Teams & Branch Staff Attendance
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $hospital = TenantManager::getHospital();

        $branches = Branch::where('hospital_id', $hospital->id)->orderBy('name')->get();

        $selectedBranchId = $request->query('branch_id');
        if ($user && $user->isRestrictedToBranch()) {
            $selectedBranchId = (string) $user->branch_id;
        }

        $selectedDate = $request->query('date', now()->toDateString());

        $shifts = Shift::all();
        $departments = Department::where('hospital_id', $hospital->id)->where('is_active', true)->get();

        // Employees query filtered by branch if specified
        $employeesQuery = Employee::where('hospital_id', $hospital->id)
            ->with(['branch', 'department', 'designation'])
            ->where('status', 'active');

        if ($selectedBranchId && $selectedBranchId !== 'all') {
            $employeesQuery->where('branch_id', $selectedBranchId);
        }
        $employees = $employeesQuery->get();

        // Rosters
        $rostersQuery = DutyRoster::where('hospital_id', $hospital->id)
            ->with(['employee.branch', 'shift', 'department'])
            ->latest('duty_date');

        if ($selectedBranchId && $selectedBranchId !== 'all') {
            $rostersQuery->whereHas('employee', fn ($q) => $q->where('branch_id', $selectedBranchId));
        }
        $rosters = $rostersQuery->take(30)->get();

        $activeAdmissions = Admission::where('hospital_id', $hospital->id)
            ->with(['patient', 'ward', 'bed'])
            ->where('status', 'admitted')
            ->get();

        $assignments = PatientAssignment::where('hospital_id', $hospital->id)
            ->with(['patient', 'admission', 'employee.branch'])
            ->where('is_active', true)
            ->latest()
            ->get();

        // Branch-Wise Staff Attendance Query
        $attendancesQuery = StaffAttendance::where('hospital_id', $hospital->id)
            ->with(['employee.department', 'employee.designation', 'branch'])
            ->where('date', $selectedDate);

        if ($selectedBranchId && $selectedBranchId !== 'all') {
            $attendancesQuery->where('branch_id', $selectedBranchId);
        }
        $attendances = $attendancesQuery->latest('clock_in_time')->get();

        // Attendance KPI Metrics
        $totalStaffCount = $employees->count();
        $presentCount = $attendances->whereIn('status', ['present', 'on_duty', 'late'])->count();
        $lateCount = $attendances->where('status', 'late')->count();
        $leaveCount = $attendances->where('status', 'on_leave')->count();
        $absentCount = max(0, $totalStaffCount - $presentCount - $leaveCount);
        $attendanceRate = $totalStaffCount > 0 ? round(($presentCount / $totalStaffCount) * 100, 1) : 0;

        return view('modules.workforce.index', compact(
            'shifts',
            'departments',
            'branches',
            'selectedBranchId',
            'selectedDate',
            'employees',
            'rosters',
            'activeAdmissions',
            'assignments',
            'attendances',
            'totalStaffCount',
            'presentCount',
            'lateCount',
            'leaveCount',
            'absentCount',
            'attendanceRate'
        ));
    }

    public function storeRoster(Request $request)
    {
        $hospital = TenantManager::getHospital();
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'shift_id' => 'required|exists:shifts,id',
            'department_id' => 'required|exists:departments,id',
            'duty_date' => 'required|date',
            'role_in_shift' => 'required|in:er_incharge,triage_officer,attending_physician,staff_nurse,on_call_consultant,code_blue_team',
            'duty_notes' => 'nullable|string',
        ]);

        $roster = DutyRoster::create(array_merge($validated, [
            'hospital_id' => $hospital->id,
            'status' => 'scheduled',
        ]));

        AuditLogger::log(
            moduleKey: 'er_workforce',
            action: 'staff_duty_scheduled',
            entityType: 'DutyRoster',
            entityId: $roster->id,
            details: ['employee_id' => $validated['employee_id'], 'role' => $validated['role_in_shift'], 'date' => $validated['duty_date']]
        );

        return redirect()->back()->with('success', 'Staff duty shift assigned successfully.');
    }

    public function storePatientAssignment(Request $request)
    {
        $hospital = TenantManager::getHospital();
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'admission_id' => 'nullable|exists:admissions,id',
            'employee_id' => 'required|exists:employees,id',
            'assignment_type' => 'required|in:attending_doctor,primary_nurse,er_care_team,visiting_specialist,anesthetist',
            'clinical_handover_instructions' => 'nullable|string',
        ]);

        $assignment = PatientAssignment::create(array_merge($validated, [
            'hospital_id' => $hospital->id,
            'assigned_at' => now(),
            'is_active' => true,
        ]));

        AuditLogger::log(
            moduleKey: 'er_workforce',
            action: 'patient_care_team_assigned',
            entityType: 'PatientAssignment',
            entityId: $assignment->id,
            details: ['patient_id' => $validated['patient_id'], 'staff_id' => $validated['employee_id'], 'type' => $validated['assignment_type']]
        );

        return redirect()->back()->with('success', 'Staff assigned to patient care team successfully.');
    }

    /**
     * Record or Update Staff Daily Attendance
     */
    public function storeAttendance(Request $request)
    {
        $hospital = TenantManager::getHospital();
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'branch_id' => 'nullable|exists:branches,id',
            'date' => 'required|date',
            'clock_in_time' => 'nullable',
            'clock_out_time' => 'nullable',
            'status' => 'required|in:present,late,half_day,absent,on_leave,on_duty',
            'work_shift' => 'required|in:morning,evening,night,general',
            'remarks' => 'nullable|string|max:255',
        ]);

        $employee = Employee::findOrFail($validated['employee_id']);
        $branchId = $validated['branch_id'] ?? $employee->branch_id;

        $attendance = StaffAttendance::updateOrCreate(
            [
                'hospital_id' => $hospital->id,
                'employee_id' => $employee->id,
                'date' => $validated['date'],
            ],
            [
                'branch_id' => $branchId,
                'clock_in_time' => $validated['clock_in_time'] ?: now()->format('H:i:s'),
                'clock_out_time' => $validated['clock_out_time'] ?: null,
                'status' => $validated['status'],
                'work_shift' => $validated['work_shift'],
                'total_hours' => $validated['status'] === 'half_day' ? 4.00 : 8.50,
                'remarks' => $validated['remarks'] ?? 'Manually logged by Department/Branch Admin',
                'is_verified' => true,
            ]
        );

        AuditLogger::log(
            moduleKey: 'er_workforce',
            action: 'staff_attendance_marked',
            entityType: 'StaffAttendance',
            entityId: $attendance->id,
            details: [
                'employee' => $employee->full_name,
                'status' => $validated['status'],
                'date' => $validated['date'],
                'branch_id' => $branchId,
            ]
        );

        return redirect()->back()->with('success', "Attendance recorded for {$employee->full_name} ({$attendance->status_label}).");
    }

    /**
     * 1-Click Bulk Attendance for All Branch Staff
     */
    public function bulkClockIn(Request $request)
    {
        $hospital = TenantManager::getHospital();
        $validated = $request->validate([
            'branch_id' => 'nullable|exists:branches,id',
            'date' => 'required|date',
            'work_shift' => 'required|in:morning,evening,night,general',
        ]);

        $employeesQuery = Employee::where('hospital_id', $hospital->id)->where('status', 'active');
        if (! empty($validated['branch_id'])) {
            $employeesQuery->where('branch_id', $validated['branch_id']);
        }
        $employees = $employeesQuery->get();

        $count = 0;
        foreach ($employees as $emp) {
            StaffAttendance::updateOrCreate(
                [
                    'hospital_id' => $hospital->id,
                    'employee_id' => $emp->id,
                    'date' => $validated['date'],
                ],
                [
                    'branch_id' => $emp->branch_id,
                    'clock_in_time' => now()->format('H:i:s'),
                    'clock_out_time' => null,
                    'status' => 'present',
                    'work_shift' => $validated['work_shift'],
                    'total_hours' => 8.50,
                    'remarks' => 'Bulk verified at morning clinical shift briefing',
                    'is_verified' => true,
                ]
            );
            $count++;
        }

        return redirect()->back()->with('success', "Bulk Clock-In successfully logged for {$count} staff members on {$validated['date']}.");
    }
}
