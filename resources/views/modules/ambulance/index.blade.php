@extends('layouts.skote')

@section('title', 'Ambulance Fleet & Emergency Dispatch Control')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <div>
                <h4 class="mb-sm-0 font-size-18">Emergency Ambulance Fleet & GPS Dispatch Scheduler</h4>
                <div class="text-muted font-size-13 mt-1">
                    Outlet: <span class="badge bg-primary-subtle text-primary fw-bold">{{ $activeBranch->name ?? 'Main Hospital Campus' }}</span>
                    @if($activeBranch && $activeBranch->slug)
                        <span class="badge bg-light text-secondary border font-mono">/outlet/{{ $activeBranch->slug }}/ambulances</span>
                    @endif
                    • 24x7 Emergency Triage, Driver Scheduling & Mobile ICU Handover
                </div>
            </div>
            <div class="page-title-right d-flex gap-2">
                <button type="button" class="btn btn-sm btn-info text-white waves-effect waves-light fw-bold" data-bs-toggle="modal" data-bs-target="#scheduleTripModal">
                    <i class="bx bx-calendar-event me-1"></i> Schedule & Assign Driver
                </button>
                <button type="button" class="btn btn-sm btn-danger waves-effect waves-light" data-bs-toggle="modal" data-bs-target="#dispatchTripModal">
                    <i class="bx bx-alarm-exclamation me-1"></i> Emergency Dispatch
                </button>
                <button type="button" class="btn btn-sm btn-outline-primary waves-effect waves-light" data-bs-toggle="modal" data-bs-target="#newAmbulanceModal">
                    <i class="bx bx-plus-circle me-1"></i> Enroll Vehicle
                </button>
            </div>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bx bx-check-double me-2 font-size-16 align-middle"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bx bx-error-alt me-2 font-size-16 align-middle"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<!-- Fleet Analytics -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card mini-stats-wid h-100 shadow-sm border-0">
            <div class="card-body">
                <div class="d-flex">
                    <div class="flex-grow-1">
                        <p class="text-muted fw-medium font-size-12 mb-1 text-uppercase">Total Ambulance Fleet</p>
                        <h4 class="mb-0 font-mono font-size-22 text-dark">{{ $totalVehicles }}</h4>
                        <div class="text-muted font-size-11 mt-1">ALS, BLS & Neonatal Units</div>
                    </div>
                    <div class="avatar-sm rounded-circle bg-primary-subtle text-primary align-self-center mini-stat-icon">
                        <span class="avatar-title rounded-circle bg-primary font-size-18">
                            <i class="bx bx-car"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card mini-stats-wid h-100 shadow-sm border-0">
            <div class="card-body">
                <div class="d-flex">
                    <div class="flex-grow-1">
                        <p class="text-muted fw-medium font-size-12 mb-1 text-uppercase">Available for Call</p>
                        <h4 class="mb-0 font-mono font-size-22 text-success">{{ $availableCount }}</h4>
                        <div class="text-success font-size-11 mt-1">🟢 Standby in ER Bay</div>
                    </div>
                    <div class="avatar-sm rounded-circle bg-success-subtle text-success align-self-center mini-stat-icon">
                        <span class="avatar-title rounded-circle bg-success font-size-18">
                            <i class="bx bx-check-shield"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card mini-stats-wid h-100 shadow-sm border-0">
            <div class="card-body">
                <div class="d-flex">
                    <div class="flex-grow-1">
                        <p class="text-muted fw-medium font-size-12 mb-1 text-uppercase">Live Dispatches En-Route</p>
                        <h4 class="mb-0 font-mono font-size-22 text-danger">{{ $onCallCount }}</h4>
                        <div class="text-danger font-size-11 mt-1">🚨 Sirens Active / En-Route</div>
                    </div>
                    <div class="avatar-sm rounded-circle bg-danger-subtle text-danger align-self-center mini-stat-icon">
                        <span class="avatar-title rounded-circle bg-danger font-size-18">
                            <i class="bx bx-navigation"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card mini-stats-wid h-100 shadow-sm border-0">
            <div class="card-body">
                <div class="d-flex">
                    <div class="flex-grow-1">
                        <p class="text-muted fw-medium font-size-12 mb-1 text-uppercase">Scheduled Calendar Trips</p>
                        <h4 class="mb-0 font-mono font-size-22 text-info">{{ $scheduledCount ?? $scheduledTrips->count() }}</h4>
                        <div class="text-info font-size-11 mt-1">📅 Assigned Driver & Customer Bookings</div>
                    </div>
                    <div class="avatar-sm rounded-circle bg-info-subtle text-info align-self-center mini-stat-icon">
                        <span class="avatar-title rounded-circle bg-info font-size-18">
                            <i class="bx bx-calendar-event"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Navigation Tabs -->
<ul class="nav nav-tabs nav-tabs-custom mb-3" role="tablist">
    <li class="nav-item">
        <a class="nav-link active" data-bs-toggle="tab" href="#tab-scheduler" role="tab">
            <i class="bx bx-calendar me-1"></i> Fleet Dispatch Calendar & Scheduler ({{ $scheduledTrips->count() }})
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-bs-toggle="tab" href="#tab-emergency-trips" role="tab">
            <i class="bx bx-navigation text-danger me-1"></i> Emergency Dispatches & Handover ({{ $trips->count() }})
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-bs-toggle="tab" href="#tab-vehicles" role="tab">
            <i class="bx bx-car me-1"></i> Registered Vehicles & Standby ({{ $ambulances->count() }})
        </a>
    </li>
</ul>

<div class="tab-content">
    <!-- Tab 1: Fleet Dispatch Calendar & Driver Scheduler -->
    <div class="tab-pane active" id="tab-scheduler" role="tabpanel">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="card-title mb-0 fw-bold"><i class="bx bx-calendar-check text-info me-1"></i> Ambulance Scheduler & Driver Assignment Matrix</h5>
                    <small class="text-muted">Allocate any ambulance to any certified driver based on specific customer & patient medical requirements.</small>
                </div>
                <button type="button" class="btn btn-info text-white btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#scheduleTripModal">
                    <i class="bx bx-plus me-1"></i> Book & Schedule Ambulance
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 font-size-13">
                        <thead class="table-light">
                            <tr>
                                <th>Booking # / Date</th>
                                <th>Customer & Requirement</th>
                                <th>Assigned Vehicle</th>
                                <th>Assigned Driver</th>
                                <th>Route / Destination</th>
                                <th>Fare Quote</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($scheduledTrips as $sTrip)
                            <tr>
                                <td>
                                    <span class="font-mono fw-bold text-primary">{{ $sTrip->trip_number }}</span>
                                    <div class="font-size-12 text-dark mt-1">
                                        <i class="bx bx-time-five text-info me-1"></i>
                                        <strong>{{ $sTrip->scheduled_for ? \Carbon\Carbon::parse($sTrip->scheduled_for)->format('d M Y, h:i A') : ($sTrip->dispatched_at ? \Carbon\Carbon::parse($sTrip->dispatched_at)->format('d M Y, h:i A') : 'Immediate') }}</strong>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark font-size-14">{{ $sTrip->patient_name }}</div>
                                    <div class="text-muted font-size-12"><i class="bx bx-phone me-1"></i>{{ $sTrip->patient_phone }}</div>
                                    @if($sTrip->customer_notes)
                                        <div class="p-1 mt-1 rounded bg-info-subtle text-info border border-info-subtle font-size-11">
                                            <i class="bx bx-user-voice me-1"></i><strong>Requirement:</strong> {{ $sTrip->customer_notes }}
                                        </div>
                                    @else
                                        <span class="badge bg-light text-muted">{{ $sTrip->emergency_category }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($sTrip->ambulance)
                                        <span class="badge bg-light text-dark font-mono border font-size-12">
                                            <i class="bx bx-car text-primary me-1"></i>{{ $sTrip->ambulance->vehicle_number }}
                                        </span>
                                        <div class="font-size-11 text-muted mt-1">{{ $sTrip->ambulance->vehicle_model }}</div>
                                        <span class="badge bg-primary-subtle text-primary font-size-10 text-uppercase">
                                            {{ str_replace('_', ' ', $sTrip->ambulance->type) }}
                                        </span>
                                    @else
                                        <span class="text-muted">Vehicle Unassigned</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-bold text-dark">
                                        <i class="bx bx-user-pin text-success me-1"></i>{{ $sTrip->assigned_driver_name ?? ($sTrip->ambulance->driver_name ?? 'Fleet Driver') }}
                                    </div>
                                    <div class="text-muted font-size-12">
                                        <i class="bx bx-phone me-1"></i>{{ $sTrip->assigned_driver_phone ?? ($sTrip->ambulance->driver_phone ?? '—') }}
                                    </div>
                                </td>
                                <td>
                                    <div class="text-truncate" style="max-width: 220px;" title="{{ $sTrip->pickup_address }}">
                                        <i class="bx bx-map-pin text-danger me-1"></i><strong>From:</strong> {{ $sTrip->pickup_address }}
                                    </div>
                                    <div class="text-truncate text-muted mt-1" style="max-width: 220px;" title="{{ $sTrip->destination_hospital }}">
                                        <i class="bx bx-buildings text-primary me-1"></i><strong>To:</strong> {{ $sTrip->destination_hospital }}
                                    </div>
                                </td>
                                <td>
                                    <span class="font-mono fw-bold text-dark font-size-13">₹{{ number_format($sTrip->fare_amount, 2) }}</span>
                                </td>
                                <td>
                                    @php
                                        $statusBadges = [
                                            'scheduled' => 'bg-info-subtle text-info',
                                            'dispatched' => 'bg-danger text-white',
                                            'patient_picked_up' => 'bg-warning-subtle text-warning',
                                            'completed' => 'bg-success-subtle text-success',
                                            'cancelled' => 'bg-secondary-subtle text-secondary'
                                        ];
                                    @endphp
                                    <span class="badge {{ $statusBadges[$sTrip->status] ?? 'bg-light text-dark' }} text-uppercase font-size-11 px-2 py-1">
                                        {{ str_replace('_', ' ', $sTrip->status) }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    @if($sTrip->status === 'scheduled')
                                        <form action="{{ route('ambulances.trips.status', $sTrip->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="status" value="dispatched">
                                            <button type="submit" class="btn btn-sm btn-danger py-1 px-2 font-size-12 fw-bold" title="Dispatch Driver & Vehicle Now">
                                                <i class="bx bx-play me-1"></i> Dispatch
                                            </button>
                                        </form>
                                        <form action="{{ route('ambulances.trips.status', $sTrip->id) }}" method="POST" class="d-inline ms-1">
                                            @csrf
                                            <input type="hidden" name="status" value="cancelled">
                                            <button type="submit" class="btn btn-sm btn-outline-secondary py-1 px-2 font-size-12" title="Cancel Booking" onclick="return confirm('Cancel this scheduled trip?');">
                                                <i class="bx bx-x"></i>
                                            </button>
                                        </form>
                                    @elseif($sTrip->status === 'dispatched')
                                        <form action="{{ route('ambulances.trips.status', $sTrip->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="status" value="patient_picked_up">
                                            <button type="submit" class="btn btn-sm btn-warning py-1 px-2 font-size-12 text-dark fw-bold">
                                                Picked Up
                                            </button>
                                        </form>
                                    @elseif($sTrip->status === 'patient_picked_up')
                                        <form action="{{ route('ambulances.trips.status', $sTrip->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="status" value="completed">
                                            <button type="submit" class="btn btn-sm btn-success py-1 px-2 font-size-12 fw-bold">
                                                Complete Handover
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-muted font-size-12"><i class="bx bx-check-double text-success me-1"></i> Finished</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="bx bx-calendar-event font-size-36 text-info mb-2"></i>
                                    <h5>No Scheduled Trips Yet</h5>
                                    <p class="mb-3 font-size-13">Schedule customer pick-ups in advance and assign any driver to any ambulance vehicle based on patient clinical needs.</p>
                                    <button type="button" class="btn btn-info text-white btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#scheduleTripModal">
                                        <i class="bx bx-plus me-1"></i> Schedule First Ambulance Booking
                                    </button>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab 2: Live Emergency Dispatches -->
    <div class="tab-pane" id="tab-emergency-trips" role="tabpanel">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                <div>
                    <h5 class="card-title mb-0 font-size-15"><i class="bx bx-navigation text-danger me-1"></i> Emergency Dispatches & Hospital Handover</h5>
                    <small class="text-muted">Real-time log of acute emergency dispatches, transit times, and patient handovers.</small>
                </div>
                <button type="button" class="btn btn-sm btn-danger waves-effect waves-light" data-bs-toggle="modal" data-bs-target="#dispatchTripModal">
                    <i class="bx bx-alarm-exclamation me-1"></i> New Emergency Dispatch
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 font-size-13">
                        <thead class="table-light">
                            <tr>
                                <th>Trip #</th>
                                <th>Patient & Condition</th>
                                <th>Assigned Vehicle & Driver</th>
                                <th>Pickup Location</th>
                                <th>Status</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($trips as $trip)
                                <tr>
                                    <td class="font-mono fw-bold text-primary">{{ $trip->trip_number }}</td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $trip->patient_name }}</div>
                                        <div class="text-danger font-size-11 fw-semibold">{{ $trip->emergency_category }}</div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark font-mono border">{{ $trip->ambulance->vehicle_number ?? 'Ambulance' }}</span>
                                        <div class="font-size-11 text-muted mt-1">
                                            <i class="bx bx-user text-secondary me-1"></i>{{ $trip->assigned_driver_name ?? ($trip->ambulance->driver_name ?? 'Driver') }}
                                        </div>
                                    </td>
                                    <td class="font-size-12 text-muted">
                                        {{ $trip->pickup_address }}
                                    </td>
                                    <td>
                                        <span class="badge {{ $trip->status === 'completed' ? 'bg-success' : ($trip->status === 'dispatched' ? 'bg-danger' : 'bg-warning') }} text-uppercase font-size-11">
                                            {{ str_replace('_', ' ', $trip->status) }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        @if($trip->status === 'dispatched')
                                            <form action="{{ route('ambulances.trips.status', $trip->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="status" value="patient_picked_up">
                                                <button type="submit" class="btn btn-xs btn-outline-warning py-1 px-2 font-size-11">Picked Up</button>
                                            </form>
                                        @elseif($trip->status === 'patient_picked_up')
                                            <form action="{{ route('ambulances.trips.status', $trip->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="status" value="completed">
                                                <button type="submit" class="btn btn-xs btn-outline-success py-1 px-2 font-size-11">Arrived ER</button>
                                            </form>
                                        @else
                                            <span class="text-muted font-size-11">Handed Over</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">No emergency trips recorded.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab 3: Vehicles Roster -->
    <div class="tab-pane" id="tab-vehicles" role="tabpanel">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                <div>
                    <h5 class="card-title mb-0 font-size-15"><i class="bx bx-car text-primary me-1"></i> Registered Fleet Vehicles & Drivers</h5>
                    <small class="text-muted">Onboarded ambulance units, current location, and designated drivers.</small>
                </div>
                <button type="button" class="btn btn-sm btn-outline-primary waves-effect waves-light" data-bs-toggle="modal" data-bs-target="#newAmbulanceModal">
                    <i class="bx bx-plus-circle me-1"></i> Enroll Vehicle
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 font-size-13">
                        <thead class="table-light">
                            <tr>
                                <th>Vehicle Reg #</th>
                                <th>Vehicle Model</th>
                                <th>Fleet Capability</th>
                                <th>Default Assigned Driver</th>
                                <th>Current Location</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($ambulances as $amb)
                            <tr>
                                <td>
                                    <span class="font-mono fw-bold text-dark font-size-14">{{ $amb->vehicle_number }}</span>
                                </td>
                                <td>{{ $amb->vehicle_model }}</td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        {{ strtoupper(str_replace('_', ' ', $amb->type)) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark"><i class="bx bx-user me-1 text-primary"></i>{{ $amb->driver_name }}</div>
                                    <div class="text-muted font-size-11">{{ $amb->driver_phone }}</div>
                                </td>
                                <td>
                                    <i class="bx bx-map-pin me-1 text-danger"></i> {{ $amb->current_location }}
                                </td>
                                <td>
                                    <span class="badge {{ $amb->status === 'available' ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }} text-uppercase font-size-11 px-2 py-1">
                                        {{ str_replace('_', ' ', $amb->status) }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">No ambulances in fleet. Click "Enroll Vehicle" to add one.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Schedule Ambulance & Assign Driver -->
<div class="modal fade" id="scheduleTripModal" tabindex="-1" aria-labelledby="scheduleTripModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <form action="{{ route('ambulances.schedule.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title text-white fw-bold" id="scheduleTripModalLabel">
                        <i class="bx bx-calendar-check me-2"></i> Schedule Ambulance & Assign Driver
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-info-subtle border border-info-subtle text-info font-size-12 mb-3">
                        <i class="bx bx-info-circle me-1"></i> Assign any ambulance in your fleet to any certified driver based on specific customer requirements (e.g. oxygen support, wheelchair, cardiac monitor, or inter-facility transfer).
                    </div>

                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">1. Vehicle & Driver Assignment</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label font-size-12 fw-semibold">Select Ambulance Vehicle <span class="text-danger">*</span></label>
                            <select name="ambulance_id" id="sched_ambulance_select" class="form-select" required onchange="handleAmbulanceSelect(this)">
                                <option value="">-- Choose Ambulance from Fleet --</option>
                                @foreach($ambulances as $veh)
                                    <option value="{{ $veh->id }}" data-driver="{{ $veh->driver_name }}" data-phone="{{ $veh->driver_phone }}">
                                        {{ $veh->vehicle_number }} — {{ $veh->vehicle_model }} ({{ strtoupper(str_replace('_', ' ', $veh->type)) }}) • {{ strtoupper($veh->status) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label font-size-12 fw-semibold">Quick Pick Known Driver (Optional)</label>
                            <select id="quick_driver_select" class="form-select" onchange="handleDriverPick(this)">
                                <option value="">-- Select from Known Drivers --</option>
                                @foreach($knownDrivers ?? [] as $kd)
                                    <option value="{{ $kd->driver_name }}" data-phone="{{ $kd->driver_phone }}">{{ $kd->driver_name }} ({{ $kd->driver_phone }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label font-size-12 fw-semibold">Assigned Driver Name <span class="text-danger">*</span></label>
                            <input type="text" name="driver_name" id="sched_driver_name" class="form-control" placeholder="Driver Full Name" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label font-size-12 fw-semibold">Assigned Driver Phone <span class="text-danger">*</span></label>
                            <input type="text" name="driver_phone" id="sched_driver_phone" class="form-control" placeholder="+91 98200 XXXXX" required>
                        </div>
                    </div>

                    <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">2. Customer Requirements & Schedule</h6>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label font-size-12 fw-semibold">Customer / Patient Name <span class="text-danger">*</span></label>
                            <input type="text" name="patient_name" class="form-control" placeholder="Customer / Patient Name" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label font-size-12 fw-semibold">Contact Phone <span class="text-danger">*</span></label>
                            <input type="text" name="patient_phone" class="form-control" placeholder="+91 98200 99887" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label font-size-12 fw-semibold">Scheduled Date & Time <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="scheduled_for" class="form-control" value="{{ now()->addHours(2)->format('Y-m-d\TH:i') }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label font-size-12 fw-semibold">Service / Emergency Category <span class="text-danger">*</span></label>
                            <select name="emergency_category" class="form-select" required>
                                <option value="Scheduled Inter-Facility ALS Transfer">Scheduled Inter-Facility ALS Transfer</option>
                                <option value="Routine Dialysis Pickup & Drop">Routine Dialysis Pickup & Drop</option>
                                <option value="Discharge Patient Home Transport (Wheelchair)">Discharge Patient Home Transport (Wheelchair)</option>
                                <option value="Neonatal Incubator Mobile Transport">Neonatal Incubator Mobile Transport</option>
                                <option value="Emergency Cardiac Critical Transfer">Emergency Cardiac Critical Transfer</option>
                                <option value="Spine & Orthopedic Stretcher Transport">Spine & Orthopedic Stretcher Transport</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label font-size-12 fw-semibold">Customer Specific Medical Requirements & Notes</label>
                            <textarea name="customer_notes" class="form-control" rows="2" placeholder="e.g. Patient requires portable oxygen cylinder @ 4L/min, ramp for wheelchair, paramedic attendant on board, patient on 3rd floor with lift..."></textarea>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label font-size-12 fw-semibold">Pickup Address <span class="text-danger">*</span></label>
                            <input type="text" name="pickup_address" class="form-control" placeholder="Pickup Residence / Facility Address" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label font-size-12 fw-semibold">Destination Address / Hospital <span class="text-danger">*</span></label>
                            <input type="text" name="destination_hospital" class="form-control" value="{{ $hospital->name ?? 'Apollo Care Emergency Pavilion' }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label font-size-12 fw-semibold">Fare / Service Quote (₹)</label>
                            <input type="number" step="0.01" name="fare_amount" class="form-control" value="1800.00">
                        </div>

                        <div class="col-md-6 d-flex align-items-center pt-3">
                            <div class="form-check form-switch font-size-13">
                                <input class="form-check-input" type="checkbox" name="dispatch_immediately" id="dispatchImmediately">
                                <label class="form-check-label fw-bold text-danger" for="dispatchImmediately">
                                    Dispatch Immediately (Skip Wait, Start Sirens)
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info text-white fw-bold">
                        <i class="bx bx-calendar-check me-1"></i> Confirm & Schedule Booking
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Enroll Ambulance -->
<div class="modal fade" id="newAmbulanceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('ambulances.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white"><i class="bx bx-car me-2"></i> Enroll Ambulance into Fleet</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label font-size-12 fw-semibold">Registration Number (RTO) <span class="text-danger">*</span></label>
                        <input type="text" name="vehicle_number" class="form-control font-mono text-uppercase" placeholder="e.g. MH-01-CR-1088" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label font-size-12 fw-semibold">Vehicle Model <span class="text-danger">*</span></label>
                        <input type="text" name="vehicle_model" class="form-control" value="Force Traveller Cardiac Care Ambulance" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label font-size-12 fw-semibold">Type of Unit <span class="text-danger">*</span></label>
                        <select name="type" class="form-select" required>
                            <option value="advanced_life_support">Advanced Life Support (ALS Cardiac ICU)</option>
                            <option value="basic_life_support">Basic Life Support (BLS Transit)</option>
                            <option value="neonatal_mobile_icu">Neonatal Mobile ICU (NICU on Wheels)</option>
                        </select>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label font-size-12 fw-semibold">Default Driver Name <span class="text-danger">*</span></label>
                            <input type="text" name="driver_name" class="form-control" placeholder="Ramesh Patil" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label font-size-12 fw-semibold">Driver Phone <span class="text-danger">*</span></label>
                            <input type="text" name="driver_phone" class="form-control" placeholder="+91 98200 44556" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label font-size-12 fw-semibold">Paramedic Escort Name</label>
                        <input type="text" name="paramedic_name" class="form-control" value="Senior Paramedic On-Duty">
                    </div>
                    <div class="mb-3">
                        <label class="form-label font-size-12 fw-semibold">Current Standby Location</label>
                        <input type="text" name="current_location" class="form-control" value="Hospital Base ER Bay">
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bx bx-check me-1"></i> Register Ambulance</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Dispatch Emergency Trip -->
<div class="modal fade" id="dispatchTripModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('ambulances.trips.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title text-white"><i class="bx bx-alarm-exclamation me-2"></i> Immediate Emergency Ambulance Dispatch</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-danger font-size-12 mb-3">
                        <i class="bx bx-radio-circle-marked me-1"></i> High priority emergency: Dispatching immediately turns vehicle sirens on and alerts base hospital triage desk.
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label font-size-12 fw-semibold">Select Standby Ambulance <span class="text-danger">*</span></label>
                            <select name="ambulance_id" class="form-select" required>
                                <option value="">-- Choose Ambulance --</option>
                                @foreach($ambulances as $amb)
                                    <option value="{{ $amb->id }}" {{ $amb->status !== 'available' ? 'disabled' : '' }}>
                                        {{ $amb->vehicle_number }} ({{ strtoupper(str_replace('_', ' ', $amb->type)) }}) - {{ strtoupper($amb->status) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label font-size-12 fw-semibold">Emergency Category <span class="text-danger">*</span></label>
                            <select name="emergency_category" class="form-select" required>
                                <option value="Acute Coronary Syndrome / STEMI">Acute Coronary Syndrome / STEMI</option>
                                <option value="Acute Ischemic Stroke (Golden Hour)">Acute Ischemic Stroke (Golden Hour)</option>
                                <option value="Severe Polytrauma / Road Accident">Severe Polytrauma / Road Accident</option>
                                <option value="Respiratory Distress / Acute Hypoxia">Respiratory Distress / Acute Hypoxia</option>
                                <option value="Obstetric Emergency / Active Labor">Obstetric Emergency / Active Labor</option>
                                <option value="Cardiac Arrest / Resuscitation Transit">Cardiac Arrest / Resuscitation Transit</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label font-size-12 fw-semibold">Patient Name <span class="text-danger">*</span></label>
                            <input type="text" name="patient_name" class="form-control" placeholder="Full name of patient" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label font-size-12 fw-semibold">Contact Phone <span class="text-danger">*</span></label>
                            <input type="text" name="patient_phone" class="form-control" placeholder="+91 98200 XXXXX" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label font-size-12 fw-semibold">Incident / Pickup Address <span class="text-danger">*</span></label>
                            <input type="text" name="pickup_address" class="form-control" placeholder="House/Flat No, Landmark, Street, GPS Coordinates" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label font-size-12 fw-semibold">Destination Facility</label>
                            <input type="text" name="destination_hospital" class="form-control" value="{{ $hospital->name ?? 'Apollo Care Emergency Pavilion' }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label font-size-12 fw-semibold">Base Fare (₹)</label>
                            <input type="number" step="0.01" name="fare_amount" class="form-control" value="1500.00">
                        </div>

                        <div class="col-12">
                            <label class="form-label font-size-12 fw-semibold">Paramedic Initial Triage Instructions</label>
                            <textarea name="paramedic_triage_notes" class="form-control" rows="2" placeholder="Start high flow O2, 12-lead ECG transmission, notify Cath Lab..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger fw-bold"><i class="bx bx-alarm-exclamation me-1"></i> Authorize Instant Dispatch</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function handleAmbulanceSelect(select) {
    const selectedOption = select.options[select.selectedIndex];
    const driver = selectedOption.getAttribute('data-driver');
    const phone = selectedOption.getAttribute('data-phone');
    if (driver && !document.getElementById('sched_driver_name').value) {
        document.getElementById('sched_driver_name').value = driver;
    }
    if (phone && !document.getElementById('sched_driver_phone').value) {
        document.getElementById('sched_driver_phone').value = phone;
    }
}

function handleDriverPick(select) {
    const selectedOption = select.options[select.selectedIndex];
    if (selectedOption.value) {
        document.getElementById('sched_driver_name').value = selectedOption.value;
        document.getElementById('sched_driver_phone').value = selectedOption.getAttribute('data-phone') || '';
    }
}
</script>
@endsection
