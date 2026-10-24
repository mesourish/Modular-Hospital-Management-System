@extends('layouts.skote')

@section('title', 'Hospital Doctor Charts & OPD Fees Directory')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <div>
                <h4 class="mb-sm-0 font-size-18">Hospital-Wise Doctor Charts & OPD Duty Directory</h4>
                <p class="text-muted font-size-13 mb-0">
                    {{ $hospital->name }} • Multi-Branch OPD Timings, Physician Degrees, Cabin Allocations & Consultation Fee Tariffs
                </p>
            </div>
            <div class="page-title-right d-flex gap-2">
                <a href="{{ route('doctor_charts.print', request()->query()) }}" target="_blank" class="btn btn-outline-secondary waves-effect">
                    <i class="bx bx-printer me-1"></i> Print OPD Duty Chart
                </a>
                @if($canManage)
                    <button type="button" class="btn btn-primary waves-effect waves-light shadow-sm" data-bs-toggle="modal" data-bs-target="#addDoctorChartModal">
                        <i class="bx bx-plus-circle me-1"></i> Add Doctor Chart
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Authority & Scope Notice Banner -->
@if($canManage)
    <div class="alert alert-info alert-top-border p-3 mb-4 shadow-sm border-0 bg-soft-info">
        <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <div class="avatar-xs me-3">
                    <span class="avatar-title bg-info text-white rounded-circle font-size-16">
                        <i class="bx bx-user-check"></i>
                    </span>
                </div>
                <div>
                    <h6 class="text-info font-size-14 mb-1 fw-bold">Admin Management Mode Active</h6>
                    <p class="text-muted font-size-12 mb-0">
                        As <strong>{{ ucfirst(str_replace('_', ' ', Auth::user()->role)) }}</strong>, you have full authorization to add new doctor rosters, edit degrees/fees, update OPD timing slots, and toggle on-leave availability for your hospital branch.
                    </p>
                </div>
            </div>
            <span class="badge bg-info font-size-11 px-2 py-1"><i class="bx bx-lock-open me-1"></i> Add / Edit / Manage Enabled</span>
        </div>
    </div>
@endif

<!-- Summary Metric Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card card-h-100 shadow-sm border-0">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <span class="text-muted mb-2 lh-1 d-block text-truncate font-size-13">Total Doctor Charts</span>
                        <h4 class="mb-0 text-primary">
                            <span class="counter-value">{{ $totalCharts }}</span>
                            <small class="text-muted font-size-12 fw-normal">Roster Entries</small>
                        </h4>
                    </div>
                    <div class="flex-shrink-0 text-end">
                        <div class="avatar-sm">
                            <span class="avatar-title bg-primary-subtle text-primary rounded-3 font-size-22">
                                <i class="bx bx-user-voice"></i>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="text-nowrap mt-3">
                    <span class="badge bg-success-subtle text-success font-size-11">{{ $availableDoctors }} Available</span>
                    <span class="badge bg-warning-subtle text-warning font-size-11">{{ $onLeaveDoctors }} On Leave</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card card-h-100 shadow-sm border-0">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <span class="text-muted mb-2 lh-1 d-block text-truncate font-size-13">Branches Covered</span>
                        <h4 class="mb-0 text-success">
                            <span class="counter-value">{{ $branches->count() }}</span>
                            <small class="text-muted font-size-12 fw-normal">Active Locations</small>
                        </h4>
                    </div>
                    <div class="flex-shrink-0 text-end">
                        <div class="avatar-sm">
                            <span class="avatar-title bg-success-subtle text-success rounded-3 font-size-22">
                                <i class="bx bx-git-branch"></i>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="text-nowrap mt-3">
                    <span class="text-muted font-size-12"><i class="bx bx-check-circle text-success me-1"></i> Multi-Branch Hierarchy</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card card-h-100 shadow-sm border-0">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <span class="text-muted mb-2 lh-1 d-block text-truncate font-size-13">Daily OPD Token Capacity</span>
                        <h4 class="mb-0 text-info">
                            <span class="counter-value">{{ number_format($totalDailyCapacity) }}</span>
                            <small class="text-muted font-size-12 fw-normal">Patients / Day</small>
                        </h4>
                    </div>
                    <div class="flex-shrink-0 text-end">
                        <div class="avatar-sm">
                            <span class="avatar-title bg-info-subtle text-info rounded-3 font-size-22">
                                <i class="bx bx-calendar-star"></i>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="text-nowrap mt-3">
                    <span class="text-muted font-size-12"><i class="bx bx-time-five text-info me-1"></i> Morning & Evening Slots</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card card-h-100 shadow-sm border-0">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <span class="text-muted mb-2 lh-1 d-block text-truncate font-size-13">Avg. Consultation Fee</span>
                        <h4 class="mb-0 text-warning">
                            ₹{{ number_format($avgConsultationFee, 2) }}
                            <small class="text-muted font-size-12 fw-normal">INR</small>
                        </h4>
                    </div>
                    <div class="flex-shrink-0 text-end">
                        <div class="avatar-sm">
                            <span class="avatar-title bg-warning-subtle text-warning rounded-3 font-size-22">
                                <i class="bx bx-rupee"></i>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="text-nowrap mt-3">
                    <span class="text-muted font-size-12"><i class="bx bx-wallet text-warning me-1"></i> Standard Indian Tariffs</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters Bar -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('doctor_charts.index') }}" class="row g-2 align-items-center">
            @if(Auth::user()->isSuperAdmin() && $allHospitals->count() > 1)
                <div class="col-md-2">
                    <label class="form-label font-size-11 text-muted mb-0">Hospital Tenant</label>
                    <select name="hospital_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        @foreach($allHospitals as $h)
                            <option value="{{ $h->id }}" {{ $hospital->id == $h->id ? 'selected' : '' }}>
                                {{ $h->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="col-md-3">
                <label class="form-label font-size-11 text-muted mb-0">Filter by Hospital Branch</label>
                <select name="branch_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="all">All Hospital Branches</option>
                    @foreach($branches as $b)
                        <option value="{{ $b->id }}" {{ $selectedBranchId == $b->id ? 'selected' : '' }}>
                            {{ $b->name }} {{ $b->is_main_branch ? '(Main Branch)' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label font-size-11 text-muted mb-0">Department / Specialty</label>
                <select name="department_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="all">All Departments</option>
                    @foreach($departments as $d)
                        <option value="{{ $d->id }}" {{ $selectedDeptId == $d->id ? 'selected' : '' }}>
                            {{ $d->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label font-size-11 text-muted mb-0">Availability Status</label>
                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="all">All Statuses</option>
                    <option value="available" {{ $selectedStatus === 'available' ? 'selected' : '' }}>Available</option>
                    <option value="on_leave" {{ $selectedStatus === 'on_leave' ? 'selected' : '' }}>On Leave</option>
                    <option value="busy" {{ $selectedStatus === 'busy' ? 'selected' : '' }}>Session Full</option>
                    <option value="inactive" {{ $selectedStatus === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label font-size-11 text-muted mb-0">Search Doctor, Degree or Room</label>
                <div class="input-group input-group-sm">
                    <input type="text" name="search" class="form-control" placeholder="Search by name, MBBS, cabin..." value="{{ $search }}">
                    <button class="btn btn-primary" type="submit">
                        <i class="bx bx-search"></i>
                    </button>
                    @if($search || $selectedBranchId || $selectedDeptId || $selectedStatus)
                        <a href="{{ route('doctor_charts.index') }}" class="btn btn-outline-secondary">
                            <i class="bx bx-reset"></i>
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Doctor Charts Directory Table -->
<div class="card shadow-sm border-0">
    <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
        <div>
            <h5 class="card-title mb-0 font-size-16 text-dark">
                <i class="bx bx-calendar-star me-2 text-primary font-size-20 align-middle"></i>
                Doctor OPD Roster & Fee Tariff Schedule
            </h5>
            <small class="text-muted">Showing {{ $doctorCharts->count() }} of {{ $doctorCharts->total() }} physician duty charts</small>
        </div>
        <div class="d-flex gap-2">
            <span class="badge bg-soft-success text-success d-flex align-items-center font-size-12 px-3 py-2">
                <i class="bx bx-check-double me-1 font-size-14"></i> Official Hospital Schedule
            </span>
        </div>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-muted font-size-12 text-uppercase">
                    <tr>
                        <th style="min-width: 220px;">Doctor & Degrees</th>
                        <th style="min-width: 170px;">Department & Specialty</th>
                        <th style="min-width: 180px;">Branch & Cabin</th>
                        <th style="min-width: 160px;">OPD Days & Timing</th>
                        <th style="min-width: 140px;">Consultation Fees</th>
                        <th style="min-width: 110px;">Token Limit</th>
                        <th style="min-width: 120px;">Status</th>
                        @if($canManage)
                            <th class="text-end" style="min-width: 150px;">Admin Actions</th>
                        @else
                            <th class="text-end" style="min-width: 100px;">Action</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="font-size-13">
                    @forelse($doctorCharts as $chart)
                        <tr>
                            <!-- Doctor Name & Degrees -->
                            <td>
                                <div class="d-flex align-items-center">
                                    @php
                                        $docImg = $chart->user?->avatar ?? $chart->doctor?->avatar ?? null;
                                    @endphp
                                    @if(!empty($docImg))
                                        <div class="avatar-sm me-3 flex-shrink-0">
                                            <img src="{{ asset($docImg) }}" alt="{{ $chart->doctor_name }}" class="rounded-circle" style="width: 38px; height: 38px; object-fit: cover;">
                                        </div>
                                    @endif
                                    <div>
                                        <h6 class="font-size-14 mb-1 fw-bold text-dark">
                                            {{ $chart->doctor_name }}
                                        </h6>
                                        <span class="badge bg-primary-subtle text-primary font-size-11 fw-medium">
                                            <i class="bx bx-award me-1"></i> {{ $chart->degrees }}
                                        </span>
                                        @if($chart->user)
                                            <small class="d-block text-muted font-size-11 mt-1">
                                                <i class="bx bx-envelope me-1"></i> {{ $chart->user->email }}
                                            </small>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <!-- Department & Specialty -->
                            <td>
                                <div class="fw-medium text-dark">{{ $chart->specialty }}</div>
                                <span class="text-muted font-size-12">
                                    <i class="bx bx-folder me-1"></i> {{ $chart->department?->name ?? 'Clinical OPD' }}
                                </span>
                            </td>

                            <!-- Branch & Cabin -->
                            <td>
                                <div class="fw-medium text-dark">
                                    <i class="bx bx-git-branch text-warning me-1"></i>
                                    {{ $chart->branch?->name ?? 'Hospital Main Center' }}
                                </div>
                                <span class="badge bg-light text-secondary font-size-11 border mt-1">
                                    <i class="bx bx-door-open me-1 text-primary"></i> {{ $chart->room_number }}
                                </span>
                            </td>

                            <!-- OPD Days & Timing -->
                            <td>
                                <div class="mb-1">
                                    <span class="badge bg-info-subtle text-info font-size-11 px-2 py-1">
                                        <i class="bx bx-calendar me-1"></i> {{ $chart->opd_days }}
                                    </span>
                                </div>
                                <div class="text-dark font-size-12 fw-medium">
                                    <i class="bx bx-time-five text-muted me-1"></i> {{ $chart->formatted_timing }}
                                </div>
                                @if($chart->opd_date)
                                    <small class="text-muted font-size-11">
                                        Special Date: {{ $chart->opd_date->format('d M, Y') }}
                                    </small>
                                @endif
                            </td>

                            <!-- Consultation Fees -->
                            <td>
                                <div class="font-size-14 fw-bold text-success">
                                    {{ $chart->formatted_fee }}
                                    <small class="text-muted font-size-10 fw-normal">Regular</small>
                                </div>
                                <div class="text-muted font-size-11">
                                    Follow-up: <span class="fw-medium text-dark">{{ $chart->formatted_follow_up_fee }}</span>
                                </div>
                                @if($chart->emergency_fee)
                                    <div class="text-danger font-size-11">
                                        Emergency: <span class="fw-bold">{{ $chart->formatted_emergency_fee }}</span>
                                    </div>
                                @endif
                            </td>

                            <!-- Token Limit / Capacity -->
                            <td>
                                <span class="badge bg-secondary-subtle text-secondary font-size-12 px-2 py-1">
                                    <i class="bx bx-group me-1"></i> Max {{ $chart->max_patients_per_session }}
                                </span>
                                <small class="d-block text-muted font-size-11 mt-1">
                                    ~{{ $chart->avg_consultation_time_mins }}m / patient
                                </small>
                            </td>

                            <!-- Status -->
                            <td>
                                {!! $chart->status_badge !!}
                                @if($chart->instructions)
                                    <small class="d-block text-muted font-size-11 mt-1 text-truncate" style="max-width: 140px;" title="{{ $chart->instructions }}">
                                        <i class="bx bx-info-circle me-1"></i> {{ $chart->instructions }}
                                    </small>
                                @endif
                            </td>

                            <!-- Admin Actions -->
                            <td class="text-end">
                                <div class="d-flex gap-1 justify-content-end align-items-center">
                                    @if($canManage)
                                        <!-- Quick Status Toggle -->
                                        <form action="{{ route('doctor_charts.toggle_status', $chart->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm {{ $chart->status === 'available' ? 'btn-outline-warning' : 'btn-outline-success' }}" title="Toggle On-Leave / Available">
                                                <i class="bx {{ $chart->status === 'available' ? 'bx-calendar-x' : 'bx-check' }}"></i>
                                            </button>
                                        </form>

                                        <!-- Edit Modal Trigger -->
                                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editDoctorChartModal_{{ $chart->id }}" title="Edit Doctor Chart & Fees">
                                            <i class="bx bx-pencil"></i>
                                        </button>

                                        <!-- Delete Trigger -->
                                        <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteDoctorChartModal_{{ $chart->id }}" title="Delete Roster">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    @endif

                                    <!-- Book OPD Appointment Shortcut -->
                                    <a href="{{ route('doctor.schedule') }}?doctor_id={{ $chart->user_id ?? '' }}&branch_id={{ $chart->branch_id }}" class="btn btn-sm btn-soft-primary" title="Book Patient Appointment">
                                        <i class="bx bx-calendar-plus me-1"></i> Book
                                    </a>
                                </div>
                            </td>
                        </tr>

                        <!-- Modal: Edit Doctor Chart -->
                        @if($canManage)
                            <div class="modal fade" id="editDoctorChartModal_{{ $chart->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header bg-primary text-white">
                                            <h5 class="modal-title text-white">
                                                <i class="bx bx-edit me-1"></i> Edit Doctor Chart & Fees: {{ $chart->doctor_name }}
                                            </h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form action="{{ route('doctor_charts.update', $chart->id) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-body p-4 text-start">
                                                <div class="row g-3">
                                                    <!-- Doctor Name & Degrees -->
                                                    <div class="col-md-6">
                                                        <label class="form-label fw-bold font-size-13">Doctor Name <span class="text-danger">*</span></label>
                                                        <input type="text" name="doctor_name" class="form-control" value="{{ $chart->doctor_name }}" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label fw-bold font-size-13">Degrees & Qualifications <span class="text-danger">*</span></label>
                                                        <input type="text" name="degrees" class="form-control" value="{{ $chart->degrees }}" placeholder="e.g. MBBS, MD, DM (Cardiology)" required>
                                                    </div>

                                                    <!-- Specialty & Department -->
                                                    <div class="col-md-6">
                                                        <label class="form-label fw-bold font-size-13">Specialty / Sub-specialty <span class="text-danger">*</span></label>
                                                        <input type="text" name="specialty" class="form-control" value="{{ $chart->specialty }}" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label fw-bold font-size-13">Department</label>
                                                        <select name="department_id" class="form-select">
                                                            <option value="">-- General OPD --</option>
                                                            @foreach($departments as $d)
                                                                <option value="{{ $d->id }}" {{ $chart->department_id == $d->id ? 'selected' : '' }}>
                                                                    {{ $d->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>

                                                    <!-- Branch & Chamber/Cabin -->
                                                    <div class="col-md-6">
                                                        <label class="form-label fw-bold font-size-13">Hospital Branch <span class="text-danger">*</span></label>
                                                        <select name="branch_id" class="form-select" required>
                                                            @foreach($branches as $b)
                                                                <option value="{{ $b->id }}" {{ $chart->branch_id == $b->id ? 'selected' : '' }}>
                                                                    {{ $b->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label fw-bold font-size-13">Room / Cabin Number <span class="text-danger">*</span></label>
                                                        <input type="text" name="room_number" class="form-control" value="{{ $chart->room_number }}" required>
                                                    </div>

                                                    <!-- OPD Days & Special Date -->
                                                    <div class="col-md-6">
                                                        <label class="form-label fw-bold font-size-13">OPD Consultation Days <span class="text-danger">*</span></label>
                                                        <input type="text" name="opd_days" class="form-control" value="{{ $chart->opd_days }}" placeholder="e.g. Mon, Wed, Fri or Daily" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label fw-bold font-size-13">Specific OPD Date (Optional)</label>
                                                        <input type="date" name="opd_date" class="form-control" value="{{ $chart->opd_date ? $chart->opd_date->format('Y-m-d') : '' }}">
                                                    </div>

                                                    <!-- Timings -->
                                                    <div class="col-md-4">
                                                        <label class="form-label fw-bold font-size-13">Start Time <span class="text-danger">*</span></label>
                                                        <input type="time" name="start_time" class="form-control" value="{{ $chart->start_time }}" required>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label fw-bold font-size-13">End Time <span class="text-danger">*</span></label>
                                                        <input type="time" name="end_time" class="form-control" value="{{ $chart->end_time }}" required>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label fw-bold font-size-13">Time Slot Label</label>
                                                        <input type="text" name="time_slot_label" class="form-control" value="{{ $chart->time_slot_label }}" placeholder="e.g. Morning OPD Slot">
                                                    </div>

                                                    <!-- Fees Section (Indian Rupees) -->
                                                    <div class="col-12">
                                                        <div class="p-3 bg-light rounded-3">
                                                            <h6 class="font-size-13 text-primary mb-2 fw-bold">
                                                                <i class="bx bx-rupee me-1"></i> Consultation Fee Tariffs (INR ₹)
                                                            </h6>
                                                            <div class="row g-2">
                                                                <div class="col-md-4">
                                                                    <label class="form-label font-size-12 fw-medium">Standard Fee (₹) <span class="text-danger">*</span></label>
                                                                    <div class="input-group input-group-sm">
                                                                        <span class="input-group-text">₹</span>
                                                                        <input type="number" step="0.01" name="consultation_fee" class="form-control" value="{{ $chart->consultation_fee }}" required>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <label class="form-label font-size-12 fw-medium">Follow-Up Fee (₹)</label>
                                                                    <div class="input-group input-group-sm">
                                                                        <span class="input-group-text">₹</span>
                                                                        <input type="number" step="0.01" name="follow_up_fee" class="form-control" value="{{ $chart->follow_up_fee }}">
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <label class="form-label font-size-12 fw-medium">Emergency / Priority Fee (₹)</label>
                                                                    <div class="input-group input-group-sm">
                                                                        <span class="input-group-text">₹</span>
                                                                        <input type="number" step="0.01" name="emergency_fee" class="form-control" value="{{ $chart->emergency_fee }}">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Capacity & Status -->
                                                    <div class="col-md-4">
                                                        <label class="form-label fw-bold font-size-13">Max Tokens / Session</label>
                                                        <input type="number" name="max_patients_per_session" class="form-control" value="{{ $chart->max_patients_per_session }}" min="1">
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label fw-bold font-size-13">Avg Mins / Patient</label>
                                                        <input type="number" name="avg_consultation_time_mins" class="form-control" value="{{ $chart->avg_consultation_time_mins }}" min="5">
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label fw-bold font-size-13">Availability Status <span class="text-danger">*</span></label>
                                                        <select name="status" class="form-select" required>
                                                            <option value="available" {{ $chart->status === 'available' ? 'selected' : '' }}>Available</option>
                                                            <option value="on_leave" {{ $chart->status === 'on_leave' ? 'selected' : '' }}>On Leave</option>
                                                            <option value="busy" {{ $chart->status === 'busy' ? 'selected' : '' }}>Session Full</option>
                                                            <option value="inactive" {{ $chart->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                                        </select>
                                                    </div>

                                                    <!-- Notes / Instructions -->
                                                    <div class="col-12">
                                                        <label class="form-label fw-bold font-size-13">Special Instructions / Patient Notice</label>
                                                        <input type="text" name="instructions" class="form-control" value="{{ $chart->instructions }}" placeholder="e.g. Prior appointment mandatory. Fasting blood sugar needed for checkup.">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="bx bx-save me-1"></i> Update Doctor Chart
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Modal: Delete Confirmation -->
                            <div class="modal fade" id="deleteDoctorChartModal_{{ $chart->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header bg-danger text-white">
                                            <h5 class="modal-title text-white">
                                                <i class="bx bx-trash me-1"></i> Remove Doctor Chart
                                            </h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body text-start p-4">
                                            <p class="font-size-14 text-dark mb-1">
                                                Are you sure you want to remove the doctor chart for:
                                            </p>
                                            <div class="p-3 bg-light rounded-3 my-2">
                                                <strong>{{ $chart->doctor_name }}</strong> ({{ $chart->degrees }})<br>
                                                <span class="text-muted font-size-12">{{ $chart->branch?->name }} • {{ $chart->room_number }} • {{ $chart->opd_days }}</span>
                                            </div>
                                            <p class="text-muted font-size-12 mb-0">
                                                This action will remove the schedule from the active OPD directory and reception booking list.
                                            </p>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                            <form action="{{ route('doctor_charts.destroy', $chart->id) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger">
                                                    <i class="bx bx-trash me-1"></i> Confirm Delete
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="avatar-md mx-auto mb-3">
                                    <span class="avatar-title bg-light text-muted rounded-circle font-size-24">
                                        <i class="bx bx-calendar-x"></i>
                                    </span>
                                </div>
                                <h5 class="font-size-15 text-dark">No Doctor Charts Found</h5>
                                <p class="text-muted font-size-13 mb-3">
                                    No physician duty charts match your current hospital branch or filter criteria.
                                </p>
                                @if($canManage)
                                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addDoctorChartModal">
                                        <i class="bx bx-plus me-1"></i> Add First Doctor Chart
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($doctorCharts->hasPages())
            <div class="card-footer bg-white border-top p-3">
                {{ $doctorCharts->withQueryString()->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Modal: Add New Doctor Chart (Hospital & Branch Admin) -->
@if($canManage)
<div class="modal fade" id="addDoctorChartModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white">
                    <i class="bx bx-plus-circle me-1"></i> Add Doctor Chart & OPD Schedule Entry
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('doctor_charts.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <!-- Quick Pick from Registered Doctors -->
                        <div class="col-12">
                            <label class="form-label font-size-12 fw-medium text-muted">
                                Link Registered Staff Physician (Optional)
                            </label>
                            <select id="select_registered_doctor" class="form-select form-select-sm" onchange="autofillDoctor(this)">
                                <option value="">-- Or enter custom / visiting consultant manually below --</option>
                                @foreach($registeredDoctors as $doc)
                                    <option value="{{ $doc->id }}"
                                            data-name="{{ $doc->name }}"
                                            data-branch="{{ $doc->branch_id }}"
                                            data-department="{{ $doc->employee?->department_id }}"
                                            data-degrees="{{ $doc->employee?->qualification ?? 'MBBS, MD' }}">
                                        {{ $doc->name }} ({{ $doc->email }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Doctor Name & Degrees -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold font-size-13">Doctor Name <span class="text-danger">*</span></label>
                            <input type="text" id="add_doctor_name" name="doctor_name" class="form-control" placeholder="e.g. Dr. Ramesh Gupta" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold font-size-13">Degrees & Qualifications <span class="text-danger">*</span></label>
                            <input type="text" id="add_degrees" name="degrees" class="form-control" placeholder="e.g. MBBS, MD, DM (Cardiology), AIIMS" required>
                        </div>

                        <!-- Specialty & Department -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold font-size-13">Specialty / Designation <span class="text-danger">*</span></label>
                            <input type="text" id="add_specialty" name="specialty" class="form-control" placeholder="e.g. Senior Interventional Cardiologist" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold font-size-13">Department</label>
                            <select id="add_department_id" name="department_id" class="form-select">
                                <option value="">-- General OPD --</option>
                                @foreach($departments as $d)
                                    <option value="{{ $d->id }}">{{ $d->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Branch & Room/Cabin -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold font-size-13">Hospital Branch <span class="text-danger">*</span></label>
                            <select id="add_branch_id" name="branch_id" class="form-select" required>
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}" {{ $selectedBranchId == $b->id ? 'selected' : '' }}>
                                        {{ $b->name }} {{ $b->is_main_branch ? '(Main Branch)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold font-size-13">OPD Room / Cabin Number <span class="text-danger">*</span></label>
                            <input type="text" name="room_number" class="form-control" placeholder="e.g. Room 104, Block B, 1st Floor" required>
                        </div>

                        <!-- OPD Days & Specific Date -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold font-size-13">OPD Consultation Days <span class="text-danger">*</span></label>
                            <input type="text" name="opd_days" class="form-control" placeholder="e.g. Mon, Wed, Fri or Daily (Mon - Sat)" value="Mon, Wed, Fri" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold font-size-13">Specific OPD Date (If single-session)</label>
                            <input type="date" name="opd_date" class="form-control">
                        </div>

                        <!-- Timings -->
                        <div class="col-md-4">
                            <label class="form-label fw-bold font-size-13">Start Time <span class="text-danger">*</span></label>
                            <input type="time" name="start_time" class="form-control" value="09:00" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold font-size-13">End Time <span class="text-danger">*</span></label>
                            <input type="time" name="end_time" class="form-control" value="13:00" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold font-size-13">Slot Label (Optional)</label>
                            <input type="text" name="time_slot_label" class="form-control" placeholder="e.g. Morning OPD Slot">
                        </div>

                        <!-- Fees (Indian Rupees) -->
                        <div class="col-12">
                            <div class="p-3 bg-light rounded-3 border">
                                <h6 class="font-size-13 text-primary mb-2 fw-bold">
                                    <i class="bx bx-rupee me-1"></i> Consultation Fee Tariffs (Indian Rupees ₹)
                                </h6>
                                <div class="row g-2">
                                    <div class="col-md-4">
                                        <label class="form-label font-size-12 fw-medium">Standard Consultation Fee (₹) <span class="text-danger">*</span></label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">₹</span>
                                            <input type="number" step="0.01" name="consultation_fee" class="form-control" value="800.00" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label font-size-12 fw-medium">Follow-Up Fee (₹)</label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">₹</span>
                                            <input type="number" step="0.01" name="follow_up_fee" class="form-control" value="400.00">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label font-size-12 fw-medium">Emergency / Priority Fee (₹)</label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">₹</span>
                                            <input type="number" step="0.01" name="emergency_fee" class="form-control" value="1500.00">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Capacity & Status -->
                        <div class="col-md-4">
                            <label class="form-label fw-bold font-size-13">Max Tokens / Session</label>
                            <input type="number" name="max_patients_per_session" class="form-control" value="25" min="1">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold font-size-13">Avg Mins / Patient</label>
                            <input type="number" name="avg_consultation_time_mins" class="form-control" value="15" min="5">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold font-size-13">Availability Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select" required>
                                <option value="available" selected>Available</option>
                                <option value="on_leave">On Leave</option>
                                <option value="busy">Session Full</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>

                        <!-- Instructions -->
                        <div class="col-12">
                            <label class="form-label fw-bold font-size-13">Special Instructions / Patient Notice</label>
                            <input type="text" name="instructions" class="form-control" placeholder="e.g. Senior citizens priority queue. Bring prior ECG / Echo reports.">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bx bx-check-circle me-1"></i> Save Doctor Chart
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function autofillDoctor(select) {
        var option = select.options[select.selectedIndex];
        if (!option.value) return;

        var name = option.getAttribute('data-name');
        var degrees = option.getAttribute('data-degrees');
        var branch = option.getAttribute('data-branch');
        var dept = option.getAttribute('data-department');

        if (name) document.getElementById('add_doctor_name').value = name;
        if (degrees) document.getElementById('add_degrees').value = degrees;
        if (branch) document.getElementById('add_branch_id').value = branch;
        if (dept) document.getElementById('add_department_id').value = dept;
    }
</script>
@endif

@endsection
