@extends('layouts.skote')

@section('title', 'Workforce & Branch Staff Attendance Center')

@section('content')
<!-- Page Title Box -->
<div class="row align-items-center mb-3">
    <div class="col-md-6">
        <h4 class="mb-1 font-size-18 text-dark fw-bold">
            <i class="bx bx-time-five text-primary me-2 align-middle"></i>
            Workforce Management, ER Duty & Branch Staff Attendance
        </h4>
        <p class="text-muted font-size-13 mb-0">Biometric Clock-In/Out Logs, Branch Attendance Monitoring, Shift Rosters & Inpatient Care Teams</p>
    </div>
    <div class="col-md-6 text-md-end mt-2 mt-md-0 d-flex gap-2 justify-content-md-end flex-wrap">
        <button type="button" class="btn btn-success btn-sm shadow-sm" data-bs-toggle="modal" data-bs-target="#newAttendanceModal">
            <i class="bx bx-user-check me-1"></i> Log Staff Attendance
        </button>
        <button type="button" class="btn btn-outline-success btn-sm" data-bs-toggle="modal" data-bs-target="#bulkAttendanceModal">
            <i class="bx bx-check-double me-1"></i> Bulk Clock-In All
        </button>
        <button type="button" class="btn btn-warning btn-sm text-dark shadow-sm" data-bs-toggle="modal" data-bs-target="#newRosterModal">
            <i class="bx bx-calendar-event me-1"></i> Assign Shift / ER Duty
        </button>
        <button type="button" class="btn btn-primary btn-sm shadow-sm" data-bs-toggle="modal" data-bs-target="#newAssignmentModal">
            <i class="bx bx-user-pin me-1"></i> Assign Care Team
        </button>
    </div>
</div>

<!-- Branch & Date Filter Bar -->
<div class="card shadow-sm border-0 mb-3 bg-light">
    <div class="card-body py-2 px-3">
        <form method="GET" action="{{ route('workforce.index') }}" class="row g-2 align-items-center">
            <div class="col-auto">
                <span class="font-size-13 fw-bold text-dark"><i class="bx bx-filter-alt text-primary me-1"></i> Filter By Branch:</span>
            </div>
            <div class="col-md-4 col-sm-6">
                <select name="branch_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    @if(!auth()->user()->isRestrictedToBranch())
                        <option value="all" {{ empty($selectedBranchId) || $selectedBranchId === 'all' ? 'selected' : '' }}>
                            🏥 All Hospital Branches (Consolidated Network)
                        </option>
                    @endif
                    @foreach($branches as $b)
                        <option value="{{ $b->id }}" {{ (string)$selectedBranchId === (string)$b->id ? 'selected' : '' }}>
                            {{ $b->name }} ({{ $b->code }}) {{ $b->is_standalone ? '[STANDALONE]' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white"><i class="bx bx-calendar"></i> Date</span>
                    <input type="date" name="date" class="form-control" value="{{ $selectedDate }}" onchange="this.form.submit()">
                </div>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-primary py-1 px-3">Apply</button>
                <a href="{{ route('workforce.index') }}" class="btn btn-sm btn-outline-secondary py-1 px-2">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Attendance KPI Summary Metrics Strip -->
<div class="row g-2 mb-3">
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card mini-stats-wid mb-0 border shadow-none">
            <div class="card-body p-3">
                <p class="text-muted font-size-11 mb-1 text-uppercase fw-semibold">Total Staff</p>
                <h5 class="mb-0 text-dark font-mono fw-bold">{{ $totalStaffCount }}</h5>
                <small class="text-secondary font-size-10">Active In Branch</small>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card mini-stats-wid mb-0 border border-success border-2 shadow-none">
            <div class="card-body p-3">
                <p class="text-muted font-size-11 mb-1 text-uppercase fw-semibold">Present Today</p>
                <h5 class="mb-0 text-success font-mono fw-bold">{{ $presentCount }}</h5>
                <small class="text-success font-size-10"><i class="bx bx-check"></i> On Clinical Duty</small>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card mini-stats-wid mb-0 border shadow-none">
            <div class="card-body p-3">
                <p class="text-muted font-size-11 mb-1 text-uppercase fw-semibold">Late Arrivals</p>
                <h5 class="mb-0 text-warning font-mono fw-bold">{{ $lateCount }}</h5>
                <small class="text-warning font-size-10">>15m Post-Shift</small>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card mini-stats-wid mb-0 border shadow-none">
            <div class="card-body p-3">
                <p class="text-muted font-size-11 mb-1 text-uppercase fw-semibold">On Leave</p>
                <h5 class="mb-0 text-secondary font-mono fw-bold">{{ $leaveCount }}</h5>
                <small class="text-muted font-size-10">Approved Sanctions</small>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card mini-stats-wid mb-0 border shadow-none">
            <div class="card-body p-3">
                <p class="text-muted font-size-11 mb-1 text-uppercase fw-semibold">Absent Unexcused</p>
                <h5 class="mb-0 text-danger font-mono fw-bold">{{ $absentCount }}</h5>
                <small class="text-danger font-size-10">Pending Triage</small>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card mini-stats-wid mb-0 border bg-primary text-white shadow-none">
            <div class="card-body p-3">
                <p class="text-white-50 font-size-11 mb-1 text-uppercase fw-semibold">Attendance Rate</p>
                <h5 class="mb-0 text-white font-mono fw-bold">{{ $attendanceRate }}%</h5>
                <small class="text-white-50 font-size-10">{{ $presentCount }}/{{ $totalStaffCount }} Verified</small>
            </div>
        </div>
    </div>
</div>

<!-- Tabbed Navigation Panels -->
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-bottom p-0">
                <ul class="nav nav-tabs nav-tabs-custom" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active font-size-14 py-3 px-4" data-bs-toggle="tab" href="#attendanceTab" role="tab">
                            <i class="bx bx-fingerprint me-1 text-success"></i> Branch Staff Attendance Records ({{ $attendances->count() }})
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link font-size-14 py-3 px-4" data-bs-toggle="tab" href="#rostersTab" role="tab">
                            <i class="bx bx-calendar-check me-1 text-warning"></i> ER & Department Shift Rosters ({{ $rosters->count() }})
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link font-size-14 py-3 px-4" data-bs-toggle="tab" href="#careTeamsTab" role="tab">
                            <i class="bx bx-user-pin me-1 text-primary"></i> Patient Care Team Assignments ({{ $assignments->count() }})
                        </a>
                    </li>
                </ul>
            </div>

            <div class="card-body p-3">
                <div class="tab-content">
                    <!-- Tab 1: Staff Attendance -->
                    <div class="tab-pane active" id="attendanceTab" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h5 class="font-size-14 mb-0 text-dark fw-bold">
                                    Daily Biometric & Duty Log • Date: <span class="text-primary font-mono">{{ \Carbon\Carbon::parse($selectedDate)->format('d F Y') }}</span>
                                </h5>
                                <small class="text-muted">Real-time attendance recorded across medical, nursing, and administrative personnel</small>
                            </div>
                            <button type="button" onclick="window.print()" class="btn btn-outline-secondary btn-sm py-1 px-3">
                                <i class="bx bx-printer me-1"></i> Print Attendance Sheet
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle table-nowrap mb-0 font-size-13">
                                <thead class="table-light">
                                    <tr>
                                        <th>Staff Member</th>
                                        <th>Branch Location</th>
                                        <th>Department & Role</th>
                                        <th>Shift</th>
                                        <th>Clock In</th>
                                        <th>Clock Out</th>
                                        <th>Hours</th>
                                        <th>Status</th>
                                        <th>Remarks</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($attendances as $att)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-xs me-2">
                                                        <span class="avatar-title rounded-circle bg-primary-subtle text-primary font-size-12 fw-bold">
                                                            {{ substr($att->employee->first_name ?? 'S', 0, 1) }}
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <strong class="text-dark">{{ $att->employee->fullName ?? 'Staff Member' }}</strong>
                                                        <div class="text-muted font-size-11 font-mono">{{ $att->employee->employee_code ?? '' }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-secondary border font-size-11">
                                                    {{ $att->branch->name ?? 'Central HQ' }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="text-dark fw-medium">{{ $att->employee->department->name ?? 'Clinical' }}</div>
                                                <small class="text-muted">{{ $att->employee->designation->title ?? 'Clinician' }}</small>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary-subtle text-secondary font-size-11 text-uppercase">
                                                    {{ $att->work_shift }}
                                                </span>
                                            </td>
                                            <td class="font-mono">
                                                @if($att->clock_in_time)
                                                    <span class="text-success fw-bold"><i class="bx bx-log-in me-1"></i>{{ \Carbon\Carbon::parse($att->clock_in_time)->format('h:i A') }}</span>
                                                @else
                                                    <span class="text-muted">--:--</span>
                                                @endif
                                            </td>
                                            <td class="font-mono">
                                                @if($att->clock_out_time)
                                                    <span class="text-danger"><i class="bx bx-log-out me-1"></i>{{ \Carbon\Carbon::parse($att->clock_out_time)->format('h:i A') }}</span>
                                                @else
                                                    <span class="badge bg-warning-subtle text-warning font-size-11">On-Duty</span>
                                                @endif
                                            </td>
                                            <td class="font-mono fw-semibold">{{ $att->total_hours }}h</td>
                                            <td>
                                                <span class="badge {{ $att->status_badge_class }} font-size-11">
                                                    {{ $att->status_label }}
                                                </span>
                                            </td>
                                            <td class="text-muted font-size-11">
                                                {{ Str::limit($att->remarks, 35) }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center py-5 text-muted">
                                                <i class="bx bx-calendar-x font-size-32 d-block mb-2 text-secondary"></i>
                                                No attendance logs found for this branch on {{ $selectedDate }}.<br>
                                                <button type="button" class="btn btn-sm btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#newAttendanceModal">
                                                    + Log Attendance Now
                                                </button>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Tab 2: ER & Shift Rosters -->
                    <div class="tab-pane" id="rostersTab" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table align-middle table-hover table-nowrap mb-0 font-size-13">
                                <thead class="table-light">
                                    <tr>
                                        <th>Staff Member</th>
                                        <th>Branch</th>
                                        <th>Department</th>
                                        <th>Shift Timing</th>
                                        <th>Role in Shift</th>
                                        <th>Duty Date</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($rosters as $ros)
                                        <tr>
                                            <td>
                                                <div class="fw-bold text-dark">{{ $ros->employee->fullName }}</div>
                                                <small class="text-muted">{{ $ros->employee->employee_code }}</small>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark border">{{ $ros->employee->branch->name ?? 'Main Branch' }}</span>
                                            </td>
                                            <td><span class="badge bg-info-subtle text-info">{{ $ros->department->name ?? 'Hospital' }}</span></td>
                                            <td>
                                                <div class="fw-medium text-dark">{{ $ros->shift->name }}</div>
                                                <small class="text-muted">{{ $ros->shift->start_time }} - {{ $ros->shift->end_time }}</small>
                                            </td>
                                            <td>
                                                <span class="badge {{ str_contains($ros->role_in_shift, 'er') ? 'bg-danger' : 'bg-warning text-dark' }}">
                                                    {{ strtoupper(str_replace('_', ' ', $ros->role_in_shift)) }}
                                                </span>
                                            </td>
                                            <td>{{ $ros->duty_date->format('d M Y') }}</td>
                                            <td>
                                                @if($ros->status === 'on_duty')
                                                    <span class="badge bg-success"><i class="bx bx-pulse"></i> On Duty</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ ucfirst($ros->status) }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-4 text-muted">No duty rosters scheduled.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Tab 3: Care Team Assignments -->
                    <div class="tab-pane" id="careTeamsTab" role="tabpanel">
                        <div class="list-group list-group-flush">
                            @forelse($assignments as $asg)
                                <div class="list-group-item px-0 py-3 border-bottom">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <h6 class="mb-0 text-dark font-size-14">{{ $asg->patient->fullName }}</h6>
                                        <span class="badge bg-primary-subtle text-primary">{{ strtoupper(str_replace('_', ' ', $asg->assignment_type)) }}</span>
                                    </div>
                                    <small class="text-muted d-block">
                                        Bed: <strong>{{ $asg->admission->bed->bed_number ?? 'Inpatient Bed' }}</strong> ({{ $asg->admission->ward->name ?? 'Ward' }}) •
                                        Branch: <strong>{{ $asg->employee->branch->name ?? 'Main Hospital' }}</strong>
                                    </small>
                                    <div class="font-size-13 text-dark mt-1">
                                        Assigned Clinician: <strong>{{ $asg->employee->fullName }}</strong> ({{ $asg->employee->designation->title ?? 'Staff' }})
                                    </div>
                                    @if($asg->clinical_handover_instructions)
                                        <div class="bg-light p-2 rounded font-size-12 text-muted mt-2">
                                            <i class="bx bx-info-circle text-primary me-1"></i> Handover: {{ $asg->clinical_handover_instructions }}
                                        </div>
                                    @endif
                                </div>
                            @empty
                                <div class="text-muted text-center py-4">No active patient care team assignments.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Log Staff Attendance -->
<div class="modal fade" id="newAttendanceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('workforce.attendance.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title text-white"><i class="bx bx-fingerprint me-2"></i> Log Staff Daily Attendance</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label font-size-12 fw-semibold">Employee <span class="text-danger">*</span></label>
                            <select name="employee_id" class="form-select" required>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}">
                                        {{ $emp->fullName }} ({{ $emp->designation->title ?? 'Staff' }}) - {{ $emp->branch->name ?? 'Main' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label font-size-12 fw-semibold">Branch Location</label>
                            <select name="branch_id" class="form-select">
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}" {{ (string)$selectedBranchId === (string)$b->id ? 'selected' : '' }}>
                                        {{ $b->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label font-size-12 fw-semibold">Date <span class="text-danger">*</span></label>
                            <input type="date" name="date" class="form-control" value="{{ $selectedDate }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label font-size-12 fw-semibold">Clock In Time</label>
                            <input type="time" name="clock_in_time" class="form-control" value="{{ now()->format('H:i') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label font-size-12 fw-semibold">Clock Out Time</label>
                            <input type="time" name="clock_out_time" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label font-size-12 fw-semibold">Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select" required>
                                <option value="present">Present On-Duty</option>
                                <option value="late">Late Arrival (>15m)</option>
                                <option value="half_day">Half Day Duty</option>
                                <option value="on_duty">Clinical On-Call</option>
                                <option value="on_leave">Approved Leave</option>
                                <option value="absent">Absent Unexcused</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label font-size-12 fw-semibold">Shift Timing <span class="text-danger">*</span></label>
                            <select name="work_shift" class="form-select" required>
                                <option value="morning">Morning Shift (07:00 - 15:30)</option>
                                <option value="evening">Evening Shift (15:00 - 23:00)</option>
                                <option value="night">Night Emergency (23:00 - 07:00)</option>
                                <option value="general" selected>General OPD (09:00 - 17:30)</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label font-size-12 fw-semibold">Remarks / Station Log</label>
                            <input type="text" name="remarks" class="form-control" placeholder="e.g. Biometric thumb scan / Emergency room swap">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success fw-bold">Save Attendance Log</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Bulk Clock-In Branch Staff -->
<div class="modal fade" id="bulkAttendanceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('workforce.attendance.bulk') }}" method="POST">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white"><i class="bx bx-check-double me-2"></i> Bulk Verify All Branch Staff</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted font-size-13 mb-3">
                        Quickly mark all active staff members in the selected branch as <strong>Present On-Duty</strong> for today's clinical shift briefing.
                    </p>
                    <div class="mb-3">
                        <label class="form-label font-size-12 fw-semibold">Branch Location <span class="text-danger">*</span></label>
                        <select name="branch_id" class="form-select" required>
                            @foreach($branches as $b)
                                <option value="{{ $b->id }}" {{ (string)$selectedBranchId === (string)$b->id ? 'selected' : '' }}>
                                    {{ $b->name }} ({{ $b->code }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label font-size-12 fw-semibold">Date <span class="text-danger">*</span></label>
                        <input type="date" name="date" class="form-control" value="{{ $selectedDate }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label font-size-12 fw-semibold">Shift <span class="text-danger">*</span></label>
                        <select name="work_shift" class="form-select" required>
                            <option value="general" selected>General OPD Shift (09:00 - 17:30)</option>
                            <option value="morning">Morning Shift (07:00 - 15:30)</option>
                            <option value="evening">Evening Shift (15:00 - 23:00)</option>
                            <option value="night">Night Emergency (23:00 - 07:00)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-bold">Confirm Bulk Clock-In</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Assign Shift / ER Duty -->
<div class="modal fade" id="newRosterModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('workforce.roster.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-warning">
                    <h5 class="modal-title text-dark"><i class="bx bx-calendar-event me-2"></i> Schedule Shift Duty & ER Assignment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label font-size-12 fw-semibold">Hospital Staff Member <span class="text-danger">*</span></label>
                            <select name="employee_id" class="form-select" required>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->fullName }} ({{ $emp->designation->title ?? 'Staff' }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label font-size-12 fw-semibold">Department <span class="text-danger">*</span></label>
                            <select name="department_id" class="form-select" required>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label font-size-12 fw-semibold">Shift Timing <span class="text-danger">*</span></label>
                            <select name="shift_id" class="form-select" required>
                                @foreach($shifts as $sh)
                                    <option value="{{ $sh->id }}">{{ $sh->name }} ({{ $sh->start_time }} - {{ $sh->end_time }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label font-size-12 fw-semibold">Duty Date <span class="text-danger">*</span></label>
                            <input type="date" name="duty_date" class="form-control" value="{{ now()->toDateString() }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label font-size-12 fw-semibold">Role in Shift <span class="text-danger">*</span></label>
                            <select name="role_in_shift" class="form-select" required>
                                <option value="er_incharge">ER Incharge</option>
                                <option value="triage_officer">Triage Officer</option>
                                <option value="attending_physician">Attending Physician</option>
                                <option value="staff_nurse">Staff Nurse</option>
                                <option value="on_call_consultant">On-Call Consultant</option>
                                <option value="code_blue_team">Code Blue Resuscitation</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label font-size-12 fw-semibold">Duty Handover Notes</label>
                            <textarea name="duty_notes" class="form-control" rows="2" placeholder="Specific ICU/ER instructions..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning text-dark fw-bold">Schedule Shift</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Assign Care Team to Patient -->
<div class="modal fade" id="newAssignmentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('workforce.assignment.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white"><i class="bx bx-user-pin me-2"></i> Assign Clinician to Patient Care Team</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label font-size-12 fw-semibold">Inpatient Admission <span class="text-danger">*</span></label>
                            <select name="patient_id" class="form-select" required>
                                @foreach($activeAdmissions as $adm)
                                    <option value="{{ $adm->patient_id }}">
                                        {{ $adm->patient->fullName }} (MRN: {{ $adm->patient->mrn }}) - Bed {{ $adm->bed->bed_number ?? 'Inpatient' }} ({{ $adm->ward->name ?? 'Ward' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label font-size-12 fw-semibold">Clinician Staff Member <span class="text-danger">*</span></label>
                            <select name="employee_id" class="form-select" required>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}">
                                        {{ $emp->fullName }} ({{ $emp->designation->title ?? 'Staff' }}) - {{ $emp->department->name ?? 'Dept' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label font-size-12 fw-semibold">Care Team Assignment Type <span class="text-danger">*</span></label>
                            <select name="assignment_type" class="form-select" required>
                                <option value="attending_doctor">Attending Consultant Doctor</option>
                                <option value="primary_nurse">Primary Bedside Nurse</option>
                                <option value="er_care_team">Emergency Trauma Care Team</option>
                                <option value="visiting_specialist">Visiting Specialist</option>
                                <option value="anesthetist">PACU / Anesthetist</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label font-size-12 fw-semibold">Clinical Handover Instructions</label>
                            <textarea name="clinical_handover_instructions" class="form-control" rows="2" placeholder="e.g. Monitor hourly vitals, continuous SpO2, strictly NPO post-midnight."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-bold">Confirm Care Assignment</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
