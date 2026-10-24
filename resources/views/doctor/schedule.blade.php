@extends('layouts.skote')

@section('title', 'Doctor Schedule & Centralized Clinical Queue')

@section('content')
<!-- Slim Header & Quick Actions -->
<div class="row align-items-center mb-2">
    <div class="col-sm-6">
        <div class="d-flex align-items-center gap-2">
            <h5 class="mb-0 font-size-17 fw-bold text-dark text-truncate">
                <i class="bx bx-calendar-star text-primary me-1 font-size-20 align-middle"></i>
                Centralized Doctor Schedule & Clinical Queue
            </h5>
            <span class="badge bg-primary-subtle text-primary font-size-11 px-2 py-1">
                {{ $hospital->name }}
            </span>
        </div>
    </div>
    <div class="col-sm-6 text-sm-end mt-2 mt-sm-0 d-flex gap-1 justify-content-sm-end flex-wrap">
        <a href="{{ route('doctor_charts.index') }}" class="btn btn-outline-info btn-sm py-1 px-2 font-size-12">
            <i class="bx bx-list-check me-1"></i> OPD Duty Charts
        </a>
        <a href="{{ route('ot.index') }}" class="btn btn-outline-danger btn-sm py-1 px-2 font-size-12">
            <i class="fas fa-procedures me-1"></i> OT Suites
        </a>
        <button type="button" onclick="window.print()" class="btn btn-outline-secondary btn-sm py-1 px-2 font-size-12">
            <i class="bx bx-printer me-1"></i> Print Tokens
        </button>
        <button type="button" class="btn btn-primary btn-sm py-1 px-2 font-size-12 shadow-sm" data-bs-toggle="modal" data-bs-target="#newAppointmentModal">
            <i class="bx bx-calendar-plus me-1"></i> + Book Patient
        </button>
    </div>
</div>

<!-- Compact Active Doctor Chamber Strip (If Selected / Logged In) -->
@if($activeDoctorChart)
    <div class="card shadow-sm border-0 mb-2 bg-primary text-white">
        <div class="card-body py-2 px-3">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 font-size-12">
                <div class="d-flex align-items-center gap-2">
                    @php
                        $docAvatar = $activeDoctorChart->doctor?->avatar ?? $activeDoctorChart->user?->avatar ?? null;
                    @endphp
                    @if(!empty($docAvatar))
                        <img src="{{ asset($docAvatar) }}" alt="{{ $activeDoctorChart->doctor_name }}" class="rounded-circle border border-white" style="width: 28px; height: 28px; object-fit: cover;">
                    @endif
                    <span class="fw-bold font-size-13">{{ $activeDoctorChart->doctor_name }}</span>
                    <span class="badge bg-white text-primary font-size-11">{{ $activeDoctorChart->degrees }}</span>
                    <span class="text-white-50">&bull;</span>
                    <span><i class="bx bx-award me-1"></i>{{ $activeDoctorChart->specialty }}</span>
                    <span class="text-white-50">&bull;</span>
                    <span><i class="bx bx-git-branch me-1"></i>{{ $activeDoctorChart->branch?->name }}</span>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <span><strong class="text-white-50">OPD Chamber:</strong> {{ $activeDoctorChart->room_number }}</span>
                    <span><strong class="text-white-50">Timing:</strong> {{ $activeDoctorChart->formatted_timing }}</span>
                    <span><strong class="text-white-50">Consultation Fee:</strong> <span class="badge bg-light text-dark font-size-11">{{ $activeDoctorChart->formatted_fee }}</span></span>
                    <span class="badge {{ $activeDoctorChart->status === 'available' ? 'bg-success' : 'bg-warning text-dark' }} font-size-11">
                        {{ strtoupper($activeDoctorChart->status) }}
                    </span>
                </div>
            </div>
        </div>
    </div>
@endif

<!-- Space-Optimized Integrated Control Bar: 7-Day Slim Ribbon & Filter Bar -->
<div class="card shadow-sm border-0 mb-2">
    <div class="card-body p-2">
        <!-- Row 1: Slim 7-Day Date Ribbon -->
        @php
            $currentDateCarbon = \Carbon\Carbon::parse($selectedDate);
            $prevDate = $currentDateCarbon->copy()->subDay()->toDateString();
            $nextDate = $currentDateCarbon->copy()->addDay()->toDateString();
            $todayDate = now()->toDateString();
        @endphp
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2 pb-2 border-bottom">
            <div class="d-flex align-items-center gap-1">
                <a href="{{ route('doctor.schedule', ['date' => $prevDate, 'doctor_id' => $selectedDoctorId, 'branch_id' => $selectedBranchId]) }}" class="btn btn-xs btn-outline-secondary py-1 px-2" title="Previous Day">
                    <i class="bx bx-chevron-left font-size-14 align-middle"></i>
                </a>
                <a href="{{ route('doctor.schedule', ['date' => $todayDate, 'doctor_id' => $selectedDoctorId, 'branch_id' => $selectedBranchId]) }}" class="btn btn-xs {{ $selectedDate === $todayDate ? 'btn-primary' : 'btn-outline-primary' }} py-1 px-2 font-size-11">
                    Today
                </a>
                <a href="{{ route('doctor.schedule', ['date' => $nextDate, 'doctor_id' => $selectedDoctorId, 'branch_id' => $selectedBranchId]) }}" class="btn btn-xs btn-outline-secondary py-1 px-2" title="Next Day">
                    <i class="bx bx-chevron-right font-size-14 align-middle"></i>
                </a>
                <span class="fw-bold text-dark ms-1 font-size-13">
                    {{ $currentDateCarbon->format('D, d M Y') }}
                </span>
            </div>

            <!-- 7-Day Slim Pills Strip -->
            <div class="d-flex gap-1 overflow-auto flex-nowrap" style="max-width: 100%;">
                @foreach($weekDays as $wd)
                    <a href="{{ route('doctor.schedule', ['date' => $wd['date'], 'doctor_id' => $selectedDoctorId, 'branch_id' => $selectedBranchId]) }}"
                       class="px-2 py-1 text-center rounded border text-decoration-none transition-all font-size-11 {{ $wd['is_selected'] ? 'bg-primary text-white border-primary shadow-sm fw-bold' : 'bg-light text-dark' }}">
                        <span>{{ $wd['day_name'] }} {{ $wd['day_num'] }}</span>
                        @if($wd['count'] > 0)
                            <span class="badge {{ $wd['is_selected'] ? 'bg-white text-primary' : 'bg-primary-subtle text-primary' }} font-size-10 rounded-pill ms-1">
                                {{ $wd['count'] }}
                            </span>
                        @endif
                    </a>
                @endforeach
            </div>

            <!-- Date Picker Jump -->
            <form action="{{ route('doctor.schedule') }}" method="GET" class="d-flex align-items-center gap-1">
                <input type="hidden" name="doctor_id" value="{{ $selectedDoctorId }}">
                <input type="hidden" name="branch_id" value="{{ $selectedBranchId }}">
                <input type="date" name="date" class="form-control form-control-sm py-0 px-2 font-size-11" style="width: 135px;" value="{{ $selectedDate }}" onchange="this.form.submit()">
            </form>
        </div>

        <!-- Row 2: Inline Dropdowns + Instant Search -->
        <form action="{{ route('doctor.schedule') }}" method="GET" class="row g-2 align-items-center">
            <input type="hidden" name="date" value="{{ $selectedDate }}">

            <!-- Doctor Selector -->
            <div class="col-md-3 col-sm-6">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light text-muted py-0 font-size-11"><i class="bx bx-user me-1"></i>Doc:</span>
                    <select name="doctor_id" class="form-select form-select-sm font-size-12" onchange="this.form.submit()">
                        <option value="all" {{ !$selectedDoctorId || $selectedDoctorId === 'all' ? 'selected' : '' }}>All Doctors</option>
                        @foreach($doctors as $doc)
                            <option value="{{ $doc->id }}" {{ $selectedDoctorId == $doc->id ? 'selected' : '' }}>
                                {{ $doc->doctor_display_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Branch Selector (Strict Branch Scoping) -->
            <div class="col-md-3 col-sm-6">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light text-muted py-0 font-size-11"><i class="bx bx-buildings me-1"></i>Branch:</span>
                    @if(auth()->user()->isRestrictedToBranch())
                        <input type="text" class="form-control form-control-sm font-size-12 bg-light" value="{{ $branches->first()->name ?? 'My Branch' }}" readonly>
                        <input type="hidden" name="branch_id" value="{{ $selectedBranchId }}">
                    @else
                        <select name="branch_id" class="form-select form-select-sm font-size-12" onchange="this.form.submit()">
                            <option value="all" {{ !$selectedBranchId || $selectedBranchId === 'all' ? 'selected' : '' }}>All Branches (Consolidated)</option>
                            @foreach($branches as $br)
                                <option value="{{ $br->id }}" {{ $selectedBranchId == $br->id ? 'selected' : '' }}>
                                    {{ $br->is_main_branch ? '⭐ ' : '' }}{{ $br->name }}
                                </option>
                            @endforeach
                        </select>
                    @endif
                </div>
            </div>

            <!-- Real-Time Client Search Bar -->
            <div class="col-md-4 col-sm-8">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light text-muted py-0 font-size-11"><i class="bx bx-search"></i></span>
                    <input type="text" id="scheduleTableSearch" class="form-control form-control-sm font-size-12" placeholder="Instant Search Patient / MRN / Token / Symptoms..." onkeyup="filterScheduleTable()">
                </div>
            </div>

            <!-- Reset Filter Button -->
            <div class="col-md-2 col-sm-4 text-end">
                @if(($selectedDoctorId && $selectedDoctorId !== 'all') || (!auth()->user()->isRestrictedToBranch() && $selectedBranchId && $selectedBranchId !== 'all'))
                    <a href="{{ route('doctor.schedule', ['date' => $selectedDate]) }}" class="btn btn-outline-danger btn-sm py-1 px-2 font-size-11 w-100">
                        <i class="bx bx-reset me-1"></i> Clear Filter
                    </a>
                @else
                    <span class="badge bg-light text-dark border font-size-11 py-1 w-100">
                        <i class="bx bx-check text-success me-1"></i> Scoped Live
                    </span>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Compressed Micro KPI Status Bar -->
<div class="row g-2 mb-2">
    <div class="col-xl-3 col-6">
        <div class="card shadow-sm border-0 mb-0">
            <div class="card-body py-2 px-3 d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted font-size-11 text-uppercase d-block">Scheduled Patients</span>
                    <h5 class="mb-0 fw-bold text-primary">{{ $totalToday }}</h5>
                </div>
                <div class="avatar-xs">
                    <span class="avatar-title bg-primary-subtle text-primary rounded font-size-16">
                        <i class="bx bx-calendar-event"></i>
                    </span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-6">
        <div class="card shadow-sm border-0 mb-0">
            <div class="card-body py-2 px-3 d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted font-size-11 text-uppercase d-block">Completed OPD</span>
                    <h5 class="mb-0 fw-bold text-success">{{ $completedToday }}</h5>
                </div>
                <div class="avatar-xs">
                    <span class="avatar-title bg-success-subtle text-success rounded font-size-16">
                        <i class="bx bx-check-circle"></i>
                    </span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-6">
        <div class="card shadow-sm border-0 mb-0">
            <div class="card-body py-2 px-3 d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted font-size-11 text-uppercase d-block">Waiting Lounge</span>
                    <h5 class="mb-0 fw-bold text-warning">{{ $inProgressToday }}</h5>
                </div>
                <div class="avatar-xs">
                    <span class="avatar-title bg-warning-subtle text-warning rounded font-size-16">
                        <i class="bx bx-time-five"></i>
                    </span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-6">
        <div class="card shadow-sm border-0 mb-0">
            <div class="card-body py-2 px-3 d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted font-size-11 text-uppercase d-block">Prescriptions Issued</span>
                    <h5 class="mb-0 fw-bold text-info">{{ $totalPrescriptions }}</h5>
                </div>
                <div class="avatar-xs">
                    <span class="avatar-title bg-info-subtle text-info rounded font-size-16">
                        <i class="bx bx-capsule"></i>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Compressed Clinical Schedule (High Density Table View as Default) -->
<div class="card shadow-sm border-0 mb-3">
    <div class="card-header bg-white border-bottom py-2 px-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
            <h6 class="mb-0 font-size-14 text-dark fw-bold">
                <i class="bx bx-list-ol text-primary me-1"></i> Patient Queue & Clinical Prescriptions Table
            </h6>
            <span class="badge bg-secondary-subtle text-secondary font-size-11 font-monospace" id="visibleRowCount">
                {{ $appointments->count() }} Patients
            </span>
        </div>

        <ul class="nav nav-pills nav-pills-sm card-header-pills" id="pills-tab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active py-1 px-2 font-size-11" id="pills-table-tab" data-bs-toggle="pill" data-bs-target="#pills-table" type="button" role="tab" aria-selected="true">
                    <i class="bx bx-table me-1"></i> Fast Queue Table
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link py-1 px-2 font-size-11" id="pills-cards-tab" data-bs-toggle="pill" data-bs-target="#pills-cards" type="button" role="tab" aria-selected="false">
                    <i class="bx bx-grid-alt me-1"></i> Clinical Cards
                </button>
            </li>
        </ul>
    </div>

    <div class="card-body p-0">
        <div class="tab-content" id="pills-tabContent">
            <!-- TAB 1: High-Density Space-Managed Compressed Table (Default) -->
            <div class="tab-pane fade show active" id="pills-table" role="tabpanel" aria-labelledby="pills-table-tab">
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0 font-size-12" id="appointmentsScheduleTable">
                        <thead class="table-light text-muted font-size-11 text-uppercase border-bottom">
                            <tr>
                                <th style="width: 100px;">Token & Time</th>
                                <th style="width: 230px;">Patient Identity & Contact</th>
                                <th style="width: 180px;">Doctor & Specialty</th>
                                <th style="width: 140px;">Status</th>
                                <th>Clinical Vitals & Symptoms</th>
                                <th>Rx Prescriptions</th>
                                <th class="text-end" style="width: 110px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($appointments as $app)
                                @php
                                    $patient = $app->patient;
                                    $doctor = $app->doctor;
                                    $consultation = $app->consultation;
                                    $prescriptions = $consultation ? $consultation->prescriptions : collect();
                                @endphp
                                <tr class="schedule-row">
                                    <!-- Token & Time -->
                                    <td>
                                        <span class="badge bg-dark font-size-11 font-monospace px-2 py-1">{{ $app->token_number }}</span>
                                        <div class="font-size-11 text-muted mt-1">
                                            <i class="bx bx-time text-primary me-1"></i>{{ \Carbon\Carbon::parse($app->appointment_time)->format('h:i A') }}
                                        </div>
                                    </td>

                                    <!-- Patient Identity & Contact -->
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div>
                                                <div class="fw-bold text-dark font-size-13">{{ $patient->name ?? 'Walk-in Patient' }}</div>
                                                <div class="font-size-11 text-muted">
                                                    {{ $patient->gender ?? 'N/A' }}, {{ $patient->age ?? 'N/A' }}y &bull;
                                                    <span class="font-monospace">{{ $patient->uhid ?? $patient->mrn ?? 'UHID-N/A' }}</span>
                                                </div>
                                                <div class="d-flex align-items-center gap-1 mt-1">
                                                    @if($patient && $patient->phone)
                                                        <span class="text-muted font-size-11"><i class="bx bx-phone me-1"></i>{{ $patient->phone }}</span>
                                                    @endif
                                                    @if($patient && $patient->abha_id)
                                                        <span class="badge bg-info-subtle text-info font-size-10 font-monospace">ABHA</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Doctor & Specialty -->
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $doctor?->doctor_display_name ?? 'Unassigned' }}</div>
                                        <div class="font-size-11 text-muted">{{ $app->department->name ?? 'General OPD' }}</div>
                                        <span class="badge bg-light text-muted font-size-10 border">{{ $app->branch->name ?? 'Main Branch' }}</span>
                                    </td>

                                    <!-- Status Dropdown (1-Click Change) -->
                                    <td>
                                        <form action="{{ route('doctor.schedule.status', $app->id) }}" method="POST">
                                            @csrf
                                            <select name="status" class="form-select form-select-sm font-size-11 py-1 px-2 border fw-semibold rounded
                                                {{ $app->status === 'completed' ? 'bg-success-subtle text-success border-success' : ($app->status === 'in_consultation' ? 'bg-warning-subtle text-warning border-warning' : ($app->status === 'waiting' ? 'bg-info-subtle text-info border-info' : 'bg-light text-dark')) }}"
                                                onchange="this.form.submit()">
                                                <option value="scheduled" {{ $app->status === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                                                <option value="waiting" {{ $app->status === 'waiting' ? 'selected' : '' }}>Waiting</option>
                                                <option value="in_consultation" {{ $app->status === 'in_consultation' ? 'selected' : '' }}>In Consultation</option>
                                                <option value="completed" {{ $app->status === 'completed' ? 'selected' : '' }}>Completed</option>
                                                <option value="cancelled" {{ $app->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                            </select>
                                        </form>
                                    </td>

                                    <!-- Clinical Vitals & Complaints -->
                                    <td>
                                        @if($consultation && ($consultation->bp || $consultation->pulse || $consultation->spo2))
                                            <div class="d-flex flex-wrap gap-1 mb-1">
                                                @if($consultation->bp)<span class="badge bg-light text-dark border font-size-10">BP: {{ $consultation->bp }}</span>@endif
                                                @if($consultation->pulse)<span class="badge bg-light text-dark border font-size-10">Pulse: {{ $consultation->pulse }}</span>@endif
                                                @if($consultation->spo2)<span class="badge bg-light text-dark border font-size-10">SpO2: {{ $consultation->spo2 }}</span>@endif
                                            </div>
                                        @endif
                                        <div class="text-truncate text-muted font-size-11" style="max-width: 200px;" title="{{ $app->symptoms ?? 'Routine Consultation' }}">
                                            <i class="bx bx-info-circle me-1 text-primary"></i>{{ $app->symptoms ?? 'General OPD Checkup' }}
                                        </div>
                                    </td>

                                    <!-- Rx Prescriptions -->
                                    <td>
                                        @if($prescriptions->count() > 0)
                                            <div class="d-flex flex-wrap gap-1">
                                                @foreach($prescriptions->take(2) as $p)
                                                    <span class="badge bg-primary-subtle text-primary font-size-11">
                                                        <i class="bx bx-capsule me-1 text-danger"></i>{{ $p->medicine_name }}
                                                    </span>
                                                @endforeach
                                                @if($prescriptions->count() > 2)
                                                    <span class="badge bg-secondary-subtle text-secondary font-size-11" data-bs-toggle="modal" data-bs-target="#viewEncounterModal-{{ $app->id }}" style="cursor: pointer;">
                                                        +{{ $prescriptions->count() - 2 }} more
                                                    </span>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-muted font-size-11 font-italic">No Rx issued</span>
                                        @endif
                                    </td>

                                    <!-- Actions -->
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-outline-primary btn-sm py-1 px-2 font-size-11" data-bs-toggle="modal" data-bs-target="#rxModal-{{ $app->id }}" title="Prescribe Medicines & Record Vitals">
                                                <i class="bx bx-capsule me-1"></i> Prescribe Medicine
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary btn-sm py-1 px-2 font-size-11" data-bs-toggle="modal" data-bs-target="#viewEncounterModal-{{ $app->id }}" title="View Clinical Encounter Sheet">
                                                <i class="bx bx-file"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">
                                        <i class="bx bx-calendar-x font-size-24 d-block mb-1 text-secondary"></i>
                                        No patient appointments booked for {{ $currentDateCarbon->format('d M Y') }}.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TAB 2: Hourly Visual Timeline Cards View -->
            <div class="tab-pane fade p-3" id="pills-cards" role="tabpanel" aria-labelledby="pills-cards-tab">
                @php $hasCards = false; @endphp
                @foreach($groupedSlots as $slotLabel => $slotAppointments)
                    @if($slotAppointments->count() > 0)
                        @php $hasCards = true; @endphp
                        <div class="mb-3">
                            <div class="d-flex align-items-center mb-2">
                                <span class="badge bg-primary text-white font-size-11 px-2 py-1 rounded me-2">
                                    <i class="bx bx-time-five me-1"></i> {{ $slotLabel }}
                                </span>
                                <div class="border-top flex-grow-1"></div>
                                <span class="badge bg-light text-muted font-size-10 ms-2 border">
                                    {{ $slotAppointments->count() }} Patient{{ $slotAppointments->count() > 1 ? 's' : '' }}
                                </span>
                            </div>

                            <div class="row g-2">
                                @foreach($slotAppointments as $app)
                                    @php
                                        $patient = $app->patient;
                                        $doctor = $app->doctor;
                                        $consultation = $app->consultation;
                                        $prescriptions = $consultation ? $consultation->prescriptions : collect();
                                    @endphp
                                    <div class="col-xl-4 col-md-6">
                                        <div class="card border shadow-none rounded mb-0 p-2 bg-light-subtle">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <span class="badge bg-dark font-size-11">{{ $app->token_number }}</span>
                                                <span class="fw-bold text-primary font-size-11">{{ \Carbon\Carbon::parse($app->appointment_time)->format('h:i A') }}</span>
                                                <span class="badge {{ $app->status === 'completed' ? 'bg-success' : ($app->status === 'in_consultation' ? 'bg-warning text-dark' : 'bg-primary') }} font-size-10">
                                                    {{ strtoupper($app->status) }}
                                                </span>
                                            </div>
                                            <div class="fw-bold text-dark font-size-12">{{ $patient->name ?? 'Walk-in' }}</div>
                                            <div class="text-muted font-size-11 mb-2">{{ $patient->gender ?? '' }}, {{ $patient->age ?? '' }}y &bull; {{ $doctor?->doctor_display_name ?? 'Doctor' }}</div>
                                            <div class="d-flex gap-1 justify-content-end">
                                                <button type="button" class="btn btn-xs btn-primary font-size-11 py-1" data-bs-toggle="modal" data-bs-target="#rxModal-{{ $app->id }}">
                                                    <i class="bx bx-capsule me-1"></i> Prescribe
                                                </button>
                                                <button type="button" class="btn btn-xs btn-outline-info font-size-11 py-1" data-bs-toggle="modal" data-bs-target="#viewEncounterModal-{{ $app->id }}">
                                                    <i class="bx bx-file"></i> Sheet
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach

                @if(!$hasCards)
                    <div class="text-center py-4 text-muted font-size-12">
                        No appointments booked for this date in timeline view.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- All Appointment Modals (Prescribe Rx & Encounter Sheet) -->
@foreach($appointments as $app)
    @php
        $patient = $app->patient;
        $doctor = $app->doctor;
        $consultation = $app->consultation;
        $prescriptions = $consultation ? $consultation->prescriptions : collect();
    @endphp

    <!-- Modal: Prescribe Medicine & Record Clinical Vitals -->
    <div class="modal fade" id="rxModal-{{ $app->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white py-2 px-3">
                    <h5 class="modal-title text-white font-size-15">
                        <i class="bx bx-capsule me-1"></i> Clinical Consultation & Rx: {{ $patient->name ?? 'Patient' }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('doctor.schedule.prescription') }}" method="POST">
                    @csrf
                    <input type="hidden" name="appointment_id" value="{{ $app->id }}">
                    <div class="modal-body p-3">
                        <!-- Patient Identity Quick Header -->
                        <div class="p-2 bg-light rounded mb-2 d-flex justify-content-between align-items-center flex-wrap gap-2 font-size-12">
                            <div>
                                <strong class="text-dark">{{ $patient->name ?? 'N/A' }}</strong>
                                <span class="text-muted ms-1">({{ $patient->gender ?? '' }}, {{ $patient->age ?? '' }}y &bull; UHID: {{ $patient->uhid ?? $patient->mrn ?? 'N/A' }})</span>
                            </div>
                            <div>
                                <span class="badge bg-dark font-size-11">{{ $app->token_number }} &bull; {{ \Carbon\Carbon::parse($app->appointment_time)->format('h:i A') }}</span>
                            </div>
                        </div>

                        <!-- 1. Clinical Diagnosis & Complaints -->
                        <div class="row g-2 mb-2">
                            <div class="col-md-6">
                                <label class="form-label font-size-12 mb-1">Diagnosis / Clinical Condition <span class="text-danger">*</span></label>
                                <input type="text" name="diagnosis" class="form-control form-control-sm" value="{{ $consultation->diagnosis ?? 'Viral Pyrexia / Upper Respiratory Infection' }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label font-size-12 mb-1">Chief Complaints</label>
                                <input type="text" class="form-control form-control-sm bg-light" value="{{ $app->symptoms ?? 'General OPD Consultation' }}" readonly>
                            </div>
                        </div>

                        <!-- 2. Clinical Vitals Inputs -->
                        <div class="p-2 bg-light rounded mb-2 border">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="font-size-12 text-primary fw-bold"><i class="bx bx-pulse me-1"></i> Record Clinical Vitals</span>
                            </div>
                            <div class="row g-2">
                                <div class="col-md-3 col-6">
                                    <label class="form-label font-size-11 text-muted mb-0">Blood Pressure</label>
                                    <input type="text" name="bp" class="form-control form-control-sm" placeholder="e.g. 120/80 mmHg" value="{{ $consultation->bp ?? '120/80 mmHg' }}">
                                </div>
                                <div class="col-md-3 col-6">
                                    <label class="form-label font-size-11 text-muted mb-0">Pulse Rate</label>
                                    <input type="text" name="pulse" class="form-control form-control-sm" placeholder="e.g. 74 bpm" value="{{ $consultation->pulse ?? '74 bpm' }}">
                                </div>
                                <div class="col-md-3 col-6">
                                    <label class="form-label font-size-11 text-muted mb-0">Temperature</label>
                                    <input type="text" name="temperature" class="form-control form-control-sm" placeholder="e.g. 98.6 °F" value="{{ $consultation->temperature ?? '98.6 °F' }}">
                                </div>
                                <div class="col-md-3 col-6">
                                    <label class="form-label font-size-11 text-muted mb-0">SpO2 Oxygen</label>
                                    <input type="text" name="spo2" class="form-control form-control-sm" placeholder="e.g. 99%" value="{{ $consultation->spo2 ?? '99%' }}">
                                </div>
                            </div>
                        </div>

                        <!-- 3. Medicine Prescription -->
                        <div class="mb-2">
                            <label class="form-label font-size-12 mb-1">Medicine Name <span class="text-danger">*</span></label>
                            <input type="text" name="medicine_name" id="med-input-{{ $app->id }}" class="form-control form-control-sm" placeholder="e.g. Dolo 650 / Augmentin 625 Duo" required>

                            <!-- Quick Indian Medicine Selection Pills -->
                            <div class="mt-1">
                                <div class="d-flex flex-wrap gap-1">
                                    <button type="button" class="btn btn-xs btn-outline-secondary py-0 font-size-11" onclick="document.getElementById('med-input-{{ $app->id }}').value='Dolo 650 (Paracetamol 650mg)'">Dolo 650</button>
                                    <button type="button" class="btn btn-xs btn-outline-secondary py-0 font-size-11" onclick="document.getElementById('med-input-{{ $app->id }}').value='Augmentin 625 Duo (Amoxicillin + Clav)'">Augmentin 625</button>
                                    <button type="button" class="btn btn-xs btn-outline-secondary py-0 font-size-11" onclick="document.getElementById('med-input-{{ $app->id }}').value='Pan 40 (Pantoprazole 40mg)'">Pan 40</button>
                                    <button type="button" class="btn btn-xs btn-outline-secondary py-0 font-size-11" onclick="document.getElementById('med-input-{{ $app->id }}').value='Telma 40 (Telmisartan 40mg)'">Telma 40</button>
                                    <button type="button" class="btn btn-xs btn-outline-secondary py-0 font-size-11" onclick="document.getElementById('med-input-{{ $app->id }}').value='Glycomet GP2'">Glycomet GP2</button>
                                    <button type="button" class="btn btn-xs btn-outline-secondary py-0 font-size-11" onclick="document.getElementById('med-input-{{ $app->id }}').value='Azithral 500 (Azithromycin)'">Azithral 500</button>
                                    <button type="button" class="btn btn-xs btn-outline-secondary py-0 font-size-11" onclick="document.getElementById('med-input-{{ $app->id }}').value='Montair LC'">Montair LC</button>
                                </div>
                            </div>
                        </div>

                        <div class="row g-2 mb-2">
                            <div class="col-md-6">
                                <label class="form-label font-size-12 mb-1">Dosage <span class="text-danger">*</span></label>
                                <input type="text" name="dosage" class="form-control form-control-sm" placeholder="e.g. 1 Tablet / 650mg" value="1 Tablet" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label font-size-12 mb-1">Frequency <span class="text-danger">*</span></label>
                                <select name="frequency" class="form-select form-select-sm font-size-12" required>
                                    <option value="1-0-1 After food">1-0-1 (Morning & Night After Food)</option>
                                    <option value="1-1-1 After food">1-1-1 (Thrice Daily After Food)</option>
                                    <option value="1-0-0 Before breakfast">1-0-0 (Once Morning Empty Stomach)</option>
                                    <option value="0-0-1 Night">0-0-1 (Once at Bedtime)</option>
                                    <option value="SOS (As needed)">SOS (Only when fever/pain exceeds)</option>
                                </select>
                            </div>
                        </div>

                        <div class="row g-2 mb-2">
                            <div class="col-md-6">
                                <label class="form-label font-size-12 mb-1">Duration <span class="text-danger">*</span></label>
                                <input type="text" name="duration" class="form-control form-control-sm" placeholder="e.g. 5 days / 15 days" value="5 days" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label font-size-12 mb-1">Dietary Instructions</label>
                                <input type="text" name="instructions" class="form-control form-control-sm" placeholder="e.g. Take with warm water after meals" value="Take after meals with water">
                            </div>
                        </div>

                        <div class="mb-0">
                            <label class="form-label font-size-12 mb-1">Clinical Notes / Follow-up Advice</label>
                            <input type="text" name="clinical_notes" class="form-control form-control-sm" placeholder="e.g. Review after 5 days if symptoms persist. Maintain hydration.">
                        </div>
                    </div>
                    <div class="modal-footer py-2 px-3">
                        <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="bx bx-check-circle me-1"></i> Save & Send to Hospital Pharmacy
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal: View Full Clinical Encounter & Prescription Details -->
    <div class="modal fade" id="viewEncounterModal-{{ $app->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white py-2 px-3">
                    <h5 class="modal-title text-white font-size-15">
                        <i class="bx bx-file me-1"></i> Clinical Encounter Sheet: {{ $patient->name ?? 'Patient' }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-3 font-size-12">
                    <div class="row g-2 pb-2 mb-2 border-bottom">
                        <div class="col-md-4">
                            <small class="text-muted d-block">Patient Name</small>
                            <span class="fw-bold font-size-14 text-dark">{{ $patient->name ?? 'N/A' }}</span>
                            <div class="text-muted font-size-11">UHID: {{ $patient->uhid ?? $patient->mrn ?? 'N/A' }}</div>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted d-block">ABHA Health ID</small>
                            <span class="badge bg-info-subtle text-info font-size-11 font-monospace">{{ $patient->abha_id ?? 'Not Linked' }}</span>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted d-block">Attending Doctor</small>
                            <span class="fw-bold text-dark">{{ $doctor?->doctor_display_name ?? 'Doctor' }}</span>
                            <div class="text-muted font-size-11">{{ $app->branch->name ?? 'Main Branch' }}</div>
                        </div>
                    </div>

                    <div class="mb-2">
                        <strong class="text-dark d-block">Chief Complaints:</strong>
                        <div class="p-2 bg-light rounded text-dark">
                            {{ $app->symptoms ?? 'General Consultation & Routine Review' }}
                        </div>
                    </div>

                    @if($consultation)
                        <div class="row g-2 mb-2">
                            <div class="col-3 p-1 border rounded text-center bg-light">
                                <small class="text-muted d-block font-size-10">BP</small>
                                <span class="fw-bold text-primary">{{ $consultation->bp ?? '120/80' }}</span>
                            </div>
                            <div class="col-3 p-1 border rounded text-center bg-light">
                                <small class="text-muted d-block font-size-10">Pulse</small>
                                <span class="fw-bold text-success">{{ $consultation->pulse ?? '74' }}</span>
                            </div>
                            <div class="col-3 p-1 border rounded text-center bg-light">
                                <small class="text-muted d-block font-size-10">Temp</small>
                                <span class="fw-bold text-danger">{{ $consultation->temperature ?? '98.6°F' }}</span>
                            </div>
                            <div class="col-3 p-1 border rounded text-center bg-light">
                                <small class="text-muted d-block font-size-10">SpO2</small>
                                <span class="fw-bold text-info">{{ $consultation->spo2 ?? '99%' }}</span>
                            </div>
                        </div>

                        @if($consultation->diagnosis)
                            <div class="mb-2">
                                <strong class="text-dark d-block">Diagnosis:</strong>
                                <span class="badge bg-primary-subtle text-primary font-size-12 px-2 py-1">
                                    {{ $consultation->diagnosis }}
                                </span>
                            </div>
                        @endif
                    @endif

                    <strong class="text-dark d-block mb-1">Prescriptions Prescribed:</strong>
                    @if($prescriptions->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered align-middle mb-0 font-size-11">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Medicine Name</th>
                                        <th>Dosage</th>
                                        <th>Frequency</th>
                                        <th>Duration</th>
                                        <th>Instructions</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($prescriptions as $idx => $p)
                                        <tr>
                                            <td class="text-center fw-bold">{{ $idx + 1 }}</td>
                                            <td class="fw-bold text-primary">{{ $p->medicine_name }}</td>
                                            <td>{{ $p->dosage }}</td>
                                            <td><span class="badge bg-info-subtle text-info font-size-10">{{ $p->frequency }}</span></td>
                                            <td>{{ $p->duration }}</td>
                                            <td><small class="text-muted">{{ $p->instructions }}</small></td>
                                            <td>
                                                @if($p->is_dispensed)
                                                    <span class="badge bg-success font-size-10"><i class="bx bx-check"></i> Dispensed</span>
                                                @else
                                                    <span class="badge bg-warning font-size-10">Pending</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted font-size-11 mb-0">No prescriptions recorded yet for this encounter.</p>
                    @endif
                </div>
                <div class="modal-footer py-2 px-3">
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary btn-sm" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#rxModal-{{ $app->id }}">
                        <i class="bx bx-pencil me-1"></i> Add / Modify Prescription
                    </button>
                </div>
            </div>
        </div>
    </div>
@endforeach

<!-- Modal: Book & Assign Patient Appointment -->
<div class="modal fade" id="newAppointmentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white py-2 px-3">
                <h5 class="modal-title text-white font-size-15">
                    <i class="bx bx-calendar-plus me-1"></i> Book & Assign Patient to Doctor Schedule
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('doctor.schedule.appointment') }}" method="POST">
                @csrf
                <div class="modal-body p-3 font-size-12">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label font-size-12 mb-1">Select Patient <span class="text-danger">*</span></label>
                            <select name="patient_id" class="form-select form-select-sm" required>
                                <option value="">-- Choose Registered Patient --</option>
                                @foreach($patients as $pt)
                                    <option value="{{ $pt->id }}">
                                        {{ $pt->name }} (UHID: {{ $pt->uhid ?? $pt->mrn }} | ABHA: {{ $pt->abha_id ?? 'N/A' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label font-size-12 mb-1">Assign Attending Doctor <span class="text-danger">*</span></label>
                            <select name="doctor_id" class="form-select form-select-sm" required>
                                <option value="">-- Choose Doctor --</option>
                                @foreach($doctors as $d)
                                    <option value="{{ $d->id }}" {{ $selectedDoctorId == $d->id ? 'selected' : '' }}>
                                        {{ $d->doctor_display_name }} ({{ $d->employee?->department?->name ?? 'Physician' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label font-size-12 mb-1">Branch Location <span class="text-danger">*</span></label>
                            @if(auth()->user()->isRestrictedToBranch())
                                <input type="text" class="form-control form-control-sm bg-light" value="{{ $branches->first()->name ?? 'My Assigned Branch' }}" readonly>
                                <input type="hidden" name="branch_id" value="{{ auth()->user()->branch_id }}">
                            @else
                                <select name="branch_id" class="form-select form-select-sm" required>
                                    @foreach($branches as $b)
                                        <option value="{{ $b->id }}" {{ $selectedBranchId == $b->id ? 'selected' : '' }}>
                                            {{ $b->is_main_branch ? '⭐ ' : '' }}{{ $b->name }} ({{ $b->city }})
                                        </option>
                                    @endforeach
                                </select>
                            @endif
                        </div>

                        <div class="col-md-6">
                            <label class="form-label font-size-12 mb-1">Department</label>
                            <select name="department_id" class="form-select form-select-sm">
                                <option value="">-- Auto-Assign Department --</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label font-size-12 mb-1">Appointment Date <span class="text-danger">*</span></label>
                            <input type="date" name="appointment_date" class="form-control form-control-sm" value="{{ $selectedDate }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label font-size-12 mb-1">Time Slot <span class="text-danger">*</span></label>
                            <select name="appointment_time" class="form-select form-select-sm" required>
                                <option value="09:00:00">09:00 AM</option>
                                <option value="09:30:00">09:30 AM</option>
                                <option value="10:00:00">10:00 AM</option>
                                <option value="10:30:00">10:30 AM</option>
                                <option value="11:00:00">11:00 AM</option>
                                <option value="11:30:00">11:30 AM</option>
                                <option value="14:00:00">02:00 PM</option>
                                <option value="14:30:00">02:30 PM</option>
                                <option value="15:00:00">03:00 PM</option>
                                <option value="16:00:00">04:00 PM</option>
                                <option value="17:00:00">05:00 PM</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label font-size-12 mb-1">Chief Complaints / Clinical Symptoms</label>
                            <input type="text" name="symptoms" class="form-control form-control-sm" placeholder="e.g. Chronic chest tightness, shortness of breath, hypertension review">
                        </div>
                    </div>
                </div>
                <div class="modal-footer py-2 px-3">
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bx bx-check-circle me-1"></i> Book Appointment & Issue Token
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function filterScheduleTable() {
    const input = document.getElementById('scheduleTableSearch');
    const filter = input.value.toLowerCase();
    const rows = document.querySelectorAll('#appointmentsScheduleTable tbody tr.schedule-row');
    let visibleCount = 0;

    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        if (text.includes(filter)) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });

    const countBadge = document.getElementById('visibleRowCount');
    if (countBadge) {
        countBadge.textContent = visibleCount + ' Patients';
    }
}
</script>

<style>
    .transition-all {
        transition: all 0.15s ease-in-out;
    }
    .btn-xs {
        padding: 0.15rem 0.4rem;
        font-size: 0.75rem;
    }
    @media print {
        .navbar-brand-box, .vertical-menu, .page-title-right, .card-header-pills, form, .modal, .btn, .footer {
            display: none !important;
        }
        .main-content {
            margin-left: 0 !important;
            padding: 0 !important;
        }
        .page-content {
            padding: 0 !important;
        }
        body {
            background-color: #ffffff !important;
        }
    }
</style>
@endsection
