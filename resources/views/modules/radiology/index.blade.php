@extends('layouts.skote')

@section('title', 'Radiology & Medical Diagnostic Imaging (RIS)')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between pb-2 mb-3 border-bottom">
            <div>
                <h4 class="mb-sm-0 font-size-18 text-dark fw-bold">
                    <i class="bx bx-scan text-primary me-1"></i> Radiology & Medical Diagnostic Imaging (RIS)
                </h4>
                <p class="text-muted font-size-13 mb-0">Modality Timetable Schedule • Walk-in Diagnostic Reception • Worklist & PACS Reports</p>
            </div>
            <div class="page-title-right d-flex align-items-center flex-wrap gap-2 mt-2 mt-sm-0">
                <!-- Date Filter Selector -->
                <form action="{{ route('radiology.index') }}" method="GET" class="d-flex align-items-center me-2">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0"><i class="bx bx-calendar text-muted"></i></span>
                        <input type="date" name="date" class="form-control form-control-sm border-start-0" value="{{ $selectedDate }}" onchange="this.form.submit()">
                    </div>
                    @if($selectedDate !== now()->toDateString())
                        <a href="{{ route('radiology.index') }}" class="btn btn-sm btn-light ms-1" title="Reset to Today">
                            <i class="bx bx-reset"></i> Today
                        </a>
                    @endif
                </form>

                <button type="button" class="btn btn-success btn-sm rounded-pill px-3 shadow-sm d-inline-flex align-items-center" data-bs-toggle="modal" data-bs-target="#newWalkinModal">
                    <i class="bx bx-user-pin me-1 font-size-16"></i> Register Walk-in Customer
                </button>
                <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm d-inline-flex align-items-center" data-bs-toggle="modal" data-bs-target="#newOrderModal">
                    <i class="bx bx-calendar-plus me-1 font-size-16"></i> Order IPD/OPD Scan
                </button>
            </div>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show d-flex align-items-center py-2 px-3 shadow-sm" role="alert">
        <i class="bx bx-check-circle font-size-20 me-2"></i>
        <div>{{ session('success') }}</div>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
    </div>
@endif

<!-- Key Statistics Strip -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-sm-6">
        <div class="card mini-stats-wid mb-0 border shadow-sm h-100">
            <div class="card-body p-3">
                <div class="d-flex">
                    <div class="flex-grow-1">
                        <p class="text-muted fw-medium font-size-12 mb-1">TOTAL SCANS ({{ \Carbon\Carbon::parse($selectedDate)->format('d M') }})</p>
                        <h4 class="mb-0 fw-bold text-dark">{{ $totalScansToday }}</h4>
                        <small class="text-muted font-size-11">Scheduled & active imaging</small>
                    </div>
                    <div class="avatar-sm rounded-circle bg-primary-subtle text-primary align-self-center d-flex align-items-center justify-content-center font-size-22">
                        <i class="bx bx-radar"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="card mini-stats-wid mb-0 border shadow-sm h-100">
            <div class="card-body p-3">
                <div class="d-flex">
                    <div class="flex-grow-1">
                        <p class="text-muted fw-medium font-size-12 mb-1">WALK-IN CUSTOMERS</p>
                        <h4 class="mb-0 fw-bold text-success">{{ $walkinsToday }}</h4>
                        <small class="text-success font-size-11"><i class="bx bx-walk"></i> Fast-track reception queue</small>
                    </div>
                    <div class="avatar-sm rounded-circle bg-success-subtle text-success align-self-center d-flex align-items-center justify-content-center font-size-22">
                        <i class="bx bx-user-pin"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="card mini-stats-wid mb-0 border shadow-sm h-100">
            <div class="card-body p-3">
                <div class="d-flex">
                    <div class="flex-grow-1">
                        <p class="text-muted fw-medium font-size-12 mb-1">SCHEDULED BOOKINGS</p>
                        <h4 class="mb-0 fw-bold text-info">{{ $scheduledToday }}</h4>
                        <small class="text-muted font-size-11">Inpatient & OPD slots</small>
                    </div>
                    <div class="avatar-sm rounded-circle bg-info-subtle text-info align-self-center d-flex align-items-center justify-content-center font-size-22">
                        <i class="bx bx-calendar-event"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="card mini-stats-wid mb-0 border shadow-sm h-100">
            <div class="card-body p-3">
                <div class="d-flex">
                    <div class="flex-grow-1">
                        <p class="text-muted fw-medium font-size-12 mb-1">COMPLETED / REPORTED</p>
                        <h4 class="mb-0 fw-bold text-purple">{{ $completedScans }}</h4>
                        <small class="text-muted font-size-11">Verified DICOM PACS</small>
                    </div>
                    <div class="avatar-sm rounded-circle bg-warning-subtle text-warning align-self-center d-flex align-items-center justify-content-center font-size-22">
                        <i class="bx bx-check-double"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Active Modalities Machines Strip -->
<div class="card border mb-4 shadow-sm">
    <div class="card-body p-3">
        <div class="d-flex align-items-center justify-content-between mb-2">
            <h6 class="mb-0 font-size-13 text-uppercase fw-bold text-muted">
                <i class="bx bx-devices me-1 text-primary"></i> Diagnostic Modality Machines & Room Status
            </h6>
            <span class="badge bg-soft-success text-success font-size-11">
                <i class="bx bx-check-circle"></i> {{ $modalities->count() }} Units Operational
            </span>
        </div>
        <div class="row g-2">
            @foreach($modalities as $m)
                @php
                    $mCount = $scheduleOrders->where('modality_id', $m->id)->count();
                @endphp
                <div class="col-md-4 col-sm-6">
                    <div class="p-2 border rounded bg-light d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center overflow-hidden">
                            <span class="badge bg-primary me-2 font-size-11">{{ $m->modality_code }}</span>
                            <div class="text-truncate">
                                <div class="fw-semibold text-dark font-size-12 text-truncate">{{ $m->name }}</div>
                                <small class="text-muted font-size-11"><i class="bx bx-map-pin"></i> {{ $m->room_number ?? 'Suite 101' }}</small>
                            </div>
                        </div>
                        <div class="text-end ms-2 flex-shrink-0">
                            <span class="badge bg-soft-info text-info font-size-11">{{ $mCount }} Scans</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Main Workspace Tabs -->
<div class="card border shadow-sm">
    <div class="card-header bg-white border-bottom p-0">
        <ul class="nav nav-tabs nav-tabs-custom card-header-tabs m-0 border-0" id="radiologyTab" role="tablist">
            <li class="nav-item">
                <a class="nav-link active px-4 py-3 fw-semibold font-size-13 d-flex align-items-center" id="schedule-tab" data-bs-toggle="tab" href="#scheduleView" role="tab">
                    <i class="bx bx-time-five me-1 font-size-16 text-primary"></i>
                    <span>Modality Timetable Schedule</span>
                    <span class="badge bg-primary-subtle text-primary ms-2 rounded-pill">{{ $scheduleOrders->count() }}</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link px-4 py-3 fw-semibold font-size-13 d-flex align-items-center" id="walkin-tab" data-bs-toggle="tab" href="#walkinSection" role="tab">
                    <i class="bx bx-user-pin me-1 font-size-16 text-success"></i>
                    <span>Walk-in Customers & Reception Queue</span>
                    <span class="badge bg-success-subtle text-success ms-2 rounded-pill">{{ $walkinOrders->count() }}</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link px-4 py-3 fw-semibold font-size-13 d-flex align-items-center" id="worklist-tab" data-bs-toggle="tab" href="#worklistSection" role="tab">
                    <i class="bx bx-list-ul me-1 font-size-16 text-info"></i>
                    <span>Diagnostic Worklist & Inpatient Orders</span>
                    <span class="badge bg-info-subtle text-info ms-2 rounded-pill">{{ $orders->count() }}</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link px-4 py-3 fw-semibold font-size-13 d-flex align-items-center" id="reports-tab" data-bs-toggle="tab" href="#reportsSection" role="tab">
                    <i class="bx bx-file me-1 font-size-16 text-warning"></i>
                    <span>Verified PACS Reports</span>
                    <span class="badge bg-warning-subtle text-dark ms-2 rounded-pill">{{ $orders->filter(fn($o) => $o->report !== null)->count() }}</span>
                </a>
            </li>
        </ul>
    </div>

    <div class="card-body p-4">
        <div class="tab-content" id="radiologyTabContent">

            <!-- TAB 1: MODALITY TIMETABLE SCHEDULE VIEW -->
            <div class="tab-pane fade show active" id="scheduleView" role="tabpanel">
                <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                    <div>
                        <h5 class="card-title mb-1 text-dark fw-bold">
                            Modality Timetable Matrix for {{ \Carbon\Carbon::parse($selectedDate)->format('l, d F Y') }}
                        </h5>
                        <p class="text-muted font-size-12 mb-0">Hourly time-slot breakdown across scanner suites & examination rooms</p>
                    </div>

                    <!-- Modality Filter Buttons -->
                    <div class="btn-group btn-group-sm" role="group">
                        <a href="{{ route('radiology.index', ['date' => $selectedDate]) }}" class="btn {{ empty($selectedModalityId) ? 'btn-primary' : 'btn-outline-secondary' }}">
                            All Modalities
                        </a>
                        @foreach($modalities as $m)
                            <a href="{{ route('radiology.index', ['date' => $selectedDate, 'modality_id' => $m->id]) }}" class="btn {{ $selectedModalityId == $m->id ? 'btn-primary' : 'btn-outline-secondary' }}">
                                {{ $m->modality_code }}
                            </a>
                        @endforeach
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered align-middle table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 130px;">Time Slot</th>
                                <th style="width: 140px;">Modality</th>
                                <th>Patient / Customer</th>
                                <th>Procedure / Scan Details</th>
                                <th style="width: 110px;">Type</th>
                                <th style="width: 100px;">Priority</th>
                                <th style="width: 120px;">Status</th>
                                <th style="width: 150px;" class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $matchedAnySlot = false;
                            @endphp
                            @foreach($timeSlots as $slot)
                                @php
                                    $slotOrders = $scheduleOrders->filter(function($o) use ($slot) {
                                        return trim($o->slot_time) === $slot || 
                                               ($o->scheduled_at && $o->scheduled_at->format('h:00 A') === $slot);
                                    });
                                @endphp

                                @if($slotOrders->count() > 0)
                                    @php $matchedAnySlot = true; @endphp
                                    @foreach($slotOrders as $sOrd)
                                        <tr>
                                            <td class="bg-light fw-bold text-dark font-size-13">
                                                <i class="bx bx-time text-primary me-1"></i> {{ $slot }}
                                            </td>
                                            <td>
                                                <span class="badge bg-primary font-size-11">{{ $sOrd->modality->modality_code ?? 'RAD' }}</span>
                                                <small class="d-block text-muted font-size-11">{{ $sOrd->modality->room_number ?? 'Suite 1' }}</small>
                                            </td>
                                            <td>
                                                <div class="fw-bold text-dark">{{ $sOrd->patient->fullName }}</div>
                                                <small class="text-muted">MRN: {{ $sOrd->patient->mrn }} &bull; {{ $sOrd->patient->phone ?? 'Walkin' }}</small>
                                            </td>
                                            <td>
                                                <div class="font-size-13 fw-semibold text-dark">{{ $sOrd->procedure_name }}</div>
                                                @if($sOrd->clinical_indication)
                                                    <small class="text-muted">{{ Str::limit($sOrd->clinical_indication, 50) }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                @if($sOrd->is_walkin)
                                                    <span class="badge bg-soft-success text-success border border-success-subtle font-size-11">
                                                        <i class="bx bx-walk"></i> Walk-in ({{ $sOrd->token_number ?? 'Token' }})
                                                    </span>
                                                @else
                                                    <span class="badge bg-soft-info text-info font-size-11">
                                                        <i class="bx bx-buildings"></i> Inpatient / OPD
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge {{ $sOrd->priority === 'stat_critical' ? 'bg-danger' : ($sOrd->priority === 'urgent' ? 'bg-warning text-dark' : 'bg-secondary') }}">
                                                    {{ strtoupper(str_replace('_', ' ', $sOrd->priority)) }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($sOrd->status === 'reported')
                                                    <span class="badge bg-success"><i class="bx bx-check-double"></i> Reported</span>
                                                @elseif($sOrd->status === 'performed')
                                                    <span class="badge bg-warning text-dark"><i class="bx bx-check"></i> Scanned</span>
                                                @elseif($sOrd->status === 'in_progress')
                                                    <span class="badge bg-info text-white"><i class="bx bx-loader bx-spin"></i> Scanning</span>
                                                @else
                                                    <span class="badge bg-light text-dark border">{{ ucfirst($sOrd->status) }}</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if($sOrd->status === 'scheduled' || $sOrd->status === 'ordered')
                                                    <form action="{{ route('radiology.orders.status', $sOrd) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <input type="hidden" name="status" value="performed">
                                                        <button type="submit" class="btn btn-sm btn-outline-primary py-1 px-2 font-size-11" title="Mark Scan Performed">
                                                            <i class="bx bx-play-circle me-1"></i> Perform Scan
                                                        </button>
                                                    </form>
                                                @elseif($sOrd->status === 'performed' && !$sOrd->report)
                                                    <button type="button" class="btn btn-sm btn-success py-1 px-2 font-size-11" data-bs-toggle="modal" data-bs-target="#reportModal" onclick="document.getElementById('rep_order_id').value = '{{ $sOrd->id }}'; document.getElementById('rep_order_label').innerText = '{{ $sOrd->order_number }} - {{ $sOrd->procedure_name }} ({{ $sOrd->patient->fullName }})';">
                                                        <i class="bx bx-edit me-1"></i> Sign Report
                                                    </button>
                                                @elseif($sOrd->report)
                                                    <span class="badge bg-soft-success text-success font-size-11">Verified Report</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <!-- Available Free Slot Row -->
                                    <tr class="table-light">
                                        <td class="fw-bold text-muted font-size-12">
                                            <i class="bx bx-time me-1"></i> {{ $slot }}
                                        </td>
                                        <td colspan="6" class="text-muted font-size-12 py-2">
                                            <span class="badge bg-light text-muted border me-2"><i class="bx bx-check-circle text-success"></i> Slot Available</span>
                                            <span>No imaging procedures booked for {{ $slot }}.</span>
                                        </td>
                                        <td class="text-center py-2">
                                            <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2 font-size-11" data-bs-toggle="modal" data-bs-target="#newWalkinModal" onclick="document.getElementById('walkin_slot_time').value = '{{ $slot }}';">
                                                <i class="bx bx-plus"></i> Book
                                            </button>
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TAB 2: WALK-IN CUSTOMERS & RECEPTION QUEUE -->
            <div class="tab-pane fade" id="walkinSection" role="tabpanel">
                <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                    <div>
                        <h5 class="card-title mb-1 text-dark fw-bold">
                            <i class="bx bx-user-pin text-success me-1"></i> Walk-in Diagnostic Customers & Fast-Track Token Queue
                        </h5>
                        <p class="text-muted font-size-12 mb-0">External doctor prescriptions, direct out-of-pocket & corporate health checkup patients</p>
                    </div>
                    <div>
                        <button type="button" class="btn btn-success btn-sm rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#newWalkinModal">
                            <i class="bx bx-plus-circle me-1"></i> New Walk-in Registration
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle table-nowrap mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Token #</th>
                                <th>Order #</th>
                                <th>Customer Details</th>
                                <th>Modality & Procedure</th>
                                <th>Referring Clinician</th>
                                <th>Slot / Time</th>
                                <th>Priority</th>
                                <th>Scan Status</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($walkinOrders as $wOrd)
                                <tr>
                                    <td>
                                        <span class="badge bg-success font-size-12 px-2 py-1">
                                            <i class="bx bx-purchase-tag me-1"></i> {{ $wOrd->token_number ?? 'RAD-W' }}
                                        </span>
                                    </td>
                                    <td>
                                        <code class="text-primary fw-semibold">{{ $wOrd->order_number }}</code>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark font-size-13">{{ $wOrd->patient->fullName }}</div>
                                        <small class="text-muted">
                                            <i class="bx bx-phone"></i> {{ $wOrd->patient_contact ?? $wOrd->patient->phone ?? 'Contact N/A' }} 
                                            &bull; {{ ucfirst($wOrd->patient->gender ?? 'Patient') }}
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary-subtle text-primary font-size-11 mb-1">
                                            {{ $wOrd->modality->name ?? 'Scanner' }} ({{ $wOrd->modality->modality_code ?? 'RAD' }})
                                        </span>
                                        <div class="fw-semibold text-dark font-size-13">{{ $wOrd->procedure_name }}</div>
                                    </td>
                                    <td>
                                        <div class="font-size-12 text-dark fw-medium">
                                            <i class="bx bx-user-voice text-muted me-1"></i> {{ $wOrd->referring_doctor_name ?? 'Walk-in / External Prescription' }}
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            <i class="bx bx-time text-primary me-1"></i> {{ $wOrd->slot_time ?? $wOrd->created_at->format('h:i A') }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge {{ $wOrd->priority === 'stat_critical' ? 'bg-danger' : ($wOrd->priority === 'urgent' ? 'bg-warning text-dark' : 'bg-secondary') }}">
                                            {{ strtoupper(str_replace('_', ' ', $wOrd->priority)) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($wOrd->status === 'reported')
                                            <span class="badge bg-success"><i class="bx bx-check-double"></i> Verified</span>
                                        @elseif($wOrd->status === 'performed')
                                            <span class="badge bg-warning text-dark"><i class="bx bx-check"></i> Scanned</span>
                                        @elseif($wOrd->status === 'in_progress')
                                            <span class="badge bg-info"><i class="bx bx-loader bx-spin"></i> Scanning</span>
                                        @else
                                            <span class="badge bg-primary-subtle text-primary">{{ ucfirst($wOrd->status) }}</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            @if($wOrd->status === 'scheduled' || $wOrd->status === 'ordered')
                                                <form action="{{ route('radiology.orders.status', $wOrd) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <input type="hidden" name="status" value="performed">
                                                    <button type="submit" class="btn btn-sm btn-outline-primary" title="Fast-Track Perform Scan">
                                                        <i class="bx bx-play-circle me-1"></i> Start Scan
                                                    </button>
                                                </form>
                                            @elseif($wOrd->status === 'performed' && !$wOrd->report)
                                                <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#reportModal" onclick="document.getElementById('rep_order_id').value = '{{ $wOrd->id }}'; document.getElementById('rep_order_label').innerText = '{{ $wOrd->order_number }} - {{ $wOrd->procedure_name }} ({{ $wOrd->patient->fullName }})';">
                                                    <i class="bx bx-edit me-1"></i> Sign Report
                                                </button>
                                            @elseif($wOrd->report)
                                                <span class="badge bg-soft-success text-success"><i class="bx bx-check"></i> Finalized</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4 text-muted">
                                        <i class="bx bx-user-pin font-size-24 d-block mb-1 text-muted"></i>
                                        No walk-in diagnostic customers registered for this date yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TAB 3: DIAGNOSTIC WORKLIST (ALL ORDERS) -->
            <div class="tab-pane fade" id="worklistSection" role="tabpanel">
                <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                    <div>
                        <h5 class="card-title mb-1 text-dark fw-bold">Comprehensive Radiology Backlog & Order Worklist</h5>
                        <p class="text-muted font-size-12 mb-0">Combined view of Inpatient wards, Emergency, OPD and walk-in scans</p>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle table-hover table-nowrap mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Order #</th>
                                <th>Patient</th>
                                <th>Procedure & Modality</th>
                                <th>Clinical Indication</th>
                                <th>Ordering / Prescribing Doctor</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($orders as $ord)
                                <tr>
                                    <td>
                                        <span class="badge bg-soft-primary text-primary font-size-11">{{ $ord->order_number }}</span>
                                        @if($ord->is_walkin)
                                            <span class="badge bg-soft-success text-success font-size-10 d-block mt-1">Walk-in: {{ $ord->token_number ?? 'Fast' }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $ord->patient->fullName }}</div>
                                        <small class="text-muted">MRN: {{ $ord->patient->mrn }}</small>
                                    </td>
                                    <td>
                                        <div class="font-size-13 text-dark fw-medium">{{ $ord->procedure_name }}</div>
                                        <small class="text-muted">{{ $ord->modality->name ?? 'Scanner' }} ({{ $ord->modality->modality_code ?? 'RAD' }})</small>
                                    </td>
                                    <td>
                                        <span class="text-muted font-size-12">{{ Str::limit($ord->clinical_indication ?? 'Diagnostic Check', 45) }}</span>
                                    </td>
                                    <td>
                                        <div class="font-size-12 text-dark">{{ $ord->referring_doctor_name ?? $ord->orderingDoctor->doctor_display_name ?? $ord->orderingDoctor->name ?? 'Clinician' }}</div>
                                    </td>
                                    <td>
                                        <span class="badge {{ $ord->priority === 'stat_critical' ? 'bg-danger' : ($ord->priority === 'urgent' ? 'bg-warning text-dark' : 'bg-secondary') }}">
                                            {{ strtoupper(str_replace('_', ' ', $ord->priority)) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($ord->status === 'reported')
                                            <span class="badge bg-success"><i class="bx bx-check"></i> Reported</span>
                                        @elseif($ord->status === 'performed')
                                            <span class="badge bg-warning text-dark"><i class="bx bx-check"></i> Scanned</span>
                                        @else
                                            <span class="badge bg-info">{{ ucfirst($ord->status) }}</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if(!$ord->report)
                                            <button type="button" class="btn btn-sm btn-outline-primary py-1 px-2 font-size-11" data-bs-toggle="modal" data-bs-target="#reportModal" onclick="document.getElementById('rep_order_id').value = '{{ $ord->id }}'; document.getElementById('rep_order_label').innerText = '{{ $ord->order_number }} - {{ $ord->procedure_name }}';">
                                                <i class="bx bx-edit me-1"></i> Sign Report
                                            </button>
                                        @else
                                            <span class="badge bg-soft-success text-success font-size-11">Verified</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">No imaging orders placed.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TAB 4: VERIFIED PACS REPORTS -->
            <div class="tab-pane fade" id="reportsSection" role="tabpanel">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <h5 class="card-title mb-1 text-dark fw-bold">Verified Radiologist Findings & DICOM Reports</h5>
                        <p class="text-muted font-size-12 mb-0">Signed diagnostic impressions, PACS study UIDs and clinical recommendations</p>
                    </div>
                </div>

                <div class="accordion" id="reportsAccordion">
                    @forelse($orders->filter(fn($o) => $o->report !== null) as $idx => $reportedOrder)
                        @php $rep = $reportedOrder->report; @endphp
                        <div class="accordion-item mb-2 border rounded shadow-sm">
                            <h2 class="accordion-header" id="heading{{ $idx }}">
                                <button class="accordion-button {{ $idx > 0 ? 'collapsed' : '' }} p-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $idx }}">
                                    <div class="d-flex align-items-center justify-content-between w-100 me-3">
                                        <div>
                                            <span class="badge bg-primary me-2">{{ $reportedOrder->modality->modality_code ?? 'RAD' }}</span>
                                            <strong class="text-dark">{{ $reportedOrder->procedure_name }}</strong>
                                            <span class="text-muted font-size-12 ms-2">Patient: <strong>{{ $reportedOrder->patient->fullName }}</strong> (MRN: {{ $reportedOrder->patient->mrn }})</span>
                                        </div>
                                        <span class="badge bg-soft-success text-success font-size-11 flex-shrink-0">
                                            <i class="bx bx-badge-check"></i> {{ $rep->verified_at ? $rep->verified_at->format('d M Y, H:i') : 'Verified' }}
                                        </span>
                                    </div>
                                </button>
                            </h2>
                            <div id="collapse{{ $idx }}" class="accordion-collapse collapse {{ $idx === 0 ? 'show' : '' }}" data-bs-parent="#reportsAccordion">
                                <div class="accordion-body font-size-13 bg-light p-3">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <span class="fw-bold text-dark d-block mb-1"><i class="bx bx-search-alt text-primary me-1"></i> Detailed Radiological Findings:</span>
                                            <div class="p-2 bg-white rounded border text-muted">{{ $rep->findings }}</div>
                                        </div>
                                        <div class="col-md-6">
                                            <span class="fw-bold text-dark d-block mb-1"><i class="bx bx-check-circle text-success me-1"></i> Diagnostic Impression:</span>
                                            <div class="p-2 bg-white rounded border fw-medium text-dark">{{ $rep->impression }}</div>
                                        </div>
                                    </div>
                                    @if($rep->recommendations)
                                        <div class="mt-2 pt-2 border-top">
                                            <span class="fw-bold text-dark">Clinical Recommendations:</span>
                                            <span class="text-muted ms-1">{{ $rep->recommendations }}</span>
                                        </div>
                                    @endif
                                    <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top font-size-11 text-muted flex-wrap gap-2">
                                        <span>Consultant Radiologist: <strong class="text-dark">{{ $rep->radiologist->name ?? 'Dr. Radiologist' }}</strong></span>
                                        <code>DICOM Study UID: {{ $rep->dicom_study_uid }}</code>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4 text-muted">
                            <i class="bx bx-file-blank font-size-24 d-block mb-1 text-muted"></i>
                            No finalized radiology reports yet.
                        </div>
                    @endforelse
                </div>
            </div>

        </div>
    </div>
</div>

<!-- MODAL: REGISTER WALK-IN DIAGNOSTIC CUSTOMER -->
<div class="modal fade" id="newWalkinModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('radiology.walkin.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-success text-white py-2">
                    <h5 class="modal-title text-white font-size-16">
                        <i class="bx bx-user-pin me-1"></i> Register Walk-in Diagnostic Customer & Token Dispatch
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-info py-2 px-3 font-size-13 mb-3">
                        <i class="bx bx-info-circle me-1"></i> Fast-track registration for walk-in patients carrying external doctor prescriptions or self-referrals.
                    </div>

                    <div class="row g-3">
                        <!-- Patient Selection Mode -->
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Customer Type <span class="text-danger">*</span></label>
                            <div class="d-flex gap-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="patient_type" id="typeNew" value="new" checked onchange="toggleWalkinPatientType('new')">
                                    <label class="form-check-label fw-semibold" for="typeNew">New Walk-in Customer</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="patient_type" id="typeExisting" value="existing" onchange="toggleWalkinPatientType('existing')">
                                    <label class="form-check-label fw-semibold" for="typeExisting">Existing Registered Patient</label>
                                </div>
                            </div>
                        </div>

                        <!-- New Customer Fields -->
                        <div id="newPatientFields" class="row g-3 mt-0 col-12">
                            <div class="col-md-6">
                                <label class="form-label">Customer Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="walkin_name" class="form-control" placeholder="e.g. Rahul Sharma" required id="walkin_name_input">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Contact Phone <span class="text-danger">*</span></label>
                                <input type="text" name="walkin_phone" class="form-control" placeholder="10-digit mobile" required id="walkin_phone_input">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Gender</label>
                                <select name="walkin_gender" class="form-select">
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>

                        <!-- Existing Patient Dropdown (Hidden by Default) -->
                        <div id="existingPatientFields" class="col-12 d-none">
                            <label class="form-label">Select Patient from Database <span class="text-danger">*</span></label>
                            <select name="patient_id" class="form-select" id="existing_patient_select">
                                <option value="">-- Choose Patient --</option>
                                @foreach($patients as $p)
                                    <option value="{{ $p->id }}">{{ $p->fullName }} (MRN: {{ $p->mrn }} • {{ $p->phone }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Modality Scanner Unit <span class="text-danger">*</span></label>
                            <select name="modality_id" class="form-select" required>
                                @foreach($modalities as $m)
                                    <option value="{{ $m->id }}" {{ $selectedModalityId == $m->id ? 'selected' : '' }}>
                                        {{ $m->name }} ({{ $m->modality_code }} • {{ $m->room_number ?? 'Ground Floor' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Procedure / Examination Name <span class="text-danger">*</span></label>
                            <input type="text" name="procedure_name" class="form-control" list="commonProcedures" required placeholder="e.g. Chest X-Ray PA, Brain MRI, USG Abdomen">
                            <datalist id="commonProcedures">
                                <option value="Digital Chest X-Ray PA View">
                                <option value="CT Scan Whole Abdomen with IV Contrast">
                                <option value="High-Resolution Brain MRI with Contrast">
                                <option value="Color Doppler 4D Abdominal & Pelvic USG">
                                <option value="Digital Bilateral Screening Mammography">
                                <option value="X-Ray Lumbar Spine AP & Lateral">
                                <option value="CT Pulmonary Angiography (HRCT)">
                                <option value="MRI Right Knee Joint with 3D Reconstruction">
                            </datalist>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">External Referring Clinician / Clinic</label>
                            <input type="text" name="referring_doctor_name" class="form-control" placeholder="e.g. Dr. P. Mehta, City Medical Clinic">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Time Slot</label>
                            <select name="slot_time" class="form-select" id="walkin_slot_time">
                                @foreach($timeSlots as $ts)
                                    <option value="{{ $ts }}" {{ now()->format('h:00 A') === $ts ? 'selected' : '' }}>{{ $ts }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Priority <span class="text-danger">*</span></label>
                            <select name="priority" class="form-select" required>
                                <option value="routine" selected>Routine</option>
                                <option value="urgent">Urgent</option>
                                <option value="stat_critical">STAT Critical</option>
                            </select>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Clinical Indication / Prescription Notes</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="e.g. Acute abdominal pain for 3 days, rule out appendicitis."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bx bx-check-circle me-1"></i> Register Walk-in & Issue Token
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL: PLACE IPD/OPD SCAN ORDER -->
<div class="modal fade" id="newOrderModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('radiology.orders.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-primary text-white py-2">
                    <h5 class="modal-title text-white font-size-16">
                        <i class="bx bx-scan me-1"></i> Order Hospital Inpatient/OPD Radiology Scan
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Hospital Patient <span class="text-danger">*</span></label>
                            <select name="patient_id" class="form-select" required>
                                <option value="">-- Choose Patient --</option>
                                @foreach($patients as $p)
                                    <option value="{{ $p->id }}">{{ $p->fullName }} ({{ $p->mrn }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Modality Unit <span class="text-danger">*</span></label>
                            <select name="modality_id" class="form-select" required>
                                @foreach($modalities as $m)
                                    <option value="{{ $m->id }}">{{ $m->name }} ({{ $m->modality_code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Priority <span class="text-danger">*</span></label>
                            <select name="priority" class="form-select" required>
                                <option value="routine">Routine</option>
                                <option value="urgent">Urgent</option>
                                <option value="stat_critical">STAT Critical</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Procedure Name <span class="text-danger">*</span></label>
                            <input type="text" name="procedure_name" class="form-control" list="commonProcedures" required placeholder="e.g. Chest X-Ray PA View, Brain MRI with Contrast">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Schedule Date</label>
                            <input type="date" name="scheduled_date" class="form-control" value="{{ $selectedDate }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Time Slot</label>
                            <select name="slot_time" class="form-select">
                                @foreach($timeSlots as $ts)
                                    <option value="{{ $ts }}">{{ $ts }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Clinical Indication</label>
                            <textarea name="clinical_indication" class="form-control" rows="2" placeholder="e.g. Chronic cough, rule out consolidation/infiltrate."></textarea>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Ordering Clinician <span class="text-danger">*</span></label>
                            <select name="ordering_doctor_id" class="form-select" required>
                                @foreach($doctors as $d)
                                    <option value="{{ $d->id }}">{{ $d->doctor_display_name ?? $d->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Schedule Order</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL: RADIOLOGIST SIGN REPORT -->
<div class="modal fade" id="reportModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('radiology.reports.store') }}" method="POST">
                @csrf
                <input type="hidden" name="radiology_order_id" id="rep_order_id">
                <div class="modal-header bg-primary text-white py-2">
                    <h5 class="modal-title text-white font-size-16">
                        <i class="bx bx-check-double me-1"></i> Sign & Finalize Radiologist Diagnostic Report
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-info font-size-13">
                        <i class="bx bx-scan me-1"></i> Finalizing study for: <strong id="rep_order_label">Order</strong>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Consultant Radiologist <span class="text-danger">*</span></label>
                            <select name="radiologist_id" class="form-select" required>
                                @foreach($radiologists as $r)
                                    <option value="{{ $r->id }}">{{ $r->doctor_display_name ?? $r->name }} (Radiologist)</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Detailed Radiological Findings <span class="text-danger">*</span></label>
                            <textarea name="findings" class="form-control" rows="3" required placeholder="Describe anatomic structures, visual density, contrast opacification..."></textarea>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Diagnostic Impression (Summary) <span class="text-danger">*</span></label>
                            <textarea name="impression" class="form-control" rows="2" required placeholder="e.g. Normal cardiothoracic ratio. No focal parenchymal consolidation."></textarea>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Clinical Recommendations</label>
                            <input type="text" name="recommendations" class="form-control" placeholder="e.g. Follow up in 6 months if symptoms persist.">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success"><i class="bx bx-check-double me-1"></i> Sign & Verify Report</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function toggleWalkinPatientType(type) {
        const newFields = document.getElementById('newPatientFields');
        const existingFields = document.getElementById('existingPatientFields');
        const nameInput = document.getElementById('walkin_name_input');
        const phoneInput = document.getElementById('walkin_phone_input');
        const existingSelect = document.getElementById('existing_patient_select');

        if (type === 'new') {
            newFields.classList.remove('d-none');
            existingFields.classList.add('d-none');
            nameInput.setAttribute('required', 'required');
            phoneInput.setAttribute('required', 'required');
            existingSelect.removeAttribute('required');
        } else {
            newFields.classList.add('d-none');
            existingFields.classList.remove('d-none');
            nameInput.removeAttribute('required');
            phoneInput.removeAttribute('required');
            existingSelect.setAttribute('required', 'required');
        }
    }
</script>
@endsection
