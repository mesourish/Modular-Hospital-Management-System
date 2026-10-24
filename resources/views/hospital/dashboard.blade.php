@extends('layouts.skote')

@section('title', 'Hospital Operations Dashboard')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <div>
                <h4 class="mb-sm-0 font-size-18">{{ $hospital->name ?? 'Hospital' }}</h4>
                <p class="text-muted font-size-13 mb-0">
                    Hospital Workspace • Code: <strong>{{ $hospital->code ?? 'N/A' }}</strong> • Plan: <span class="badge bg-soft-info text-info">{{ $hospital->subscription_plan ?? 'Standard' }}</span>
                </p>
            </div>
            <div class="page-title-right d-flex gap-2">
                <a href="{{ route('admissions.index') }}" class="btn btn-sm btn-primary">
                    <i class="bx bx-user-plus me-1"></i> New Admission
                </a>
                <a href="{{ route('opd.index') }}" class="btn btn-sm btn-success">
                    <i class="bx bx-calendar-plus me-1"></i> OPD Queue
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Hospital Enabled Extensions Ribbon -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="d-flex align-items-center">
                <span class="avatar-title rounded-circle bg-soft-primary text-primary font-size-18 p-2 me-2" style="width: 36px; height: 36px;">
                    <i class="bx bx-extension"></i>
                </span>
                <div>
                    <h6 class="mb-0 font-size-14 fw-bold">Active Modular Extensions For This Facility:</h6>
                    <small class="text-muted">Dynamic configuration enabled per hospital instance</small>
                </div>
            </div>

            <div class="d-flex flex-wrap gap-2">
                @foreach($availableModules as $modKey => $manifest)
                    @php
                        $isEnabled = isset($activeModules[$modKey]);
                    @endphp
                    @if($isEnabled)
                        <span class="badge bg-soft-success text-success border border-success font-size-12 px-2 py-1">
                            <i class="bx bx-check me-1"></i> {{ $manifest['name'] }}
                        </span>
                    @else
                        <span class="badge bg-soft-danger text-danger border border-danger font-size-12 px-2 py-1" title="Disabled by Super Admin entitlement">
                            <i class="bx bx-lock-alt me-1"></i> {{ $manifest['name'] }} (Inactive)
                        </span>
                    @endif
                @endforeach
            </div>
        </div>
    </div>
</div>

<!-- Operational Metric Cards -->
<div class="row">
    <div class="col-md-3">
        <div class="card mini-stats-wid">
            <div class="card-body">
                <div class="d-flex">
                    <div class="flex-grow-1">
                        <p class="text-muted fw-medium">Admitted Inpatients</p>
                        <h4 class="mb-0">{{ $admissionsCount }}</h4>
                    </div>
                    <div class="avatar-sm rounded-circle bg-primary align-self-center mini-stat-icon">
                        <span class="avatar-title rounded-circle bg-primary">
                            <i class="bx bx-hotel font-size-24"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card mini-stats-wid">
            <div class="card-body">
                <div class="d-flex">
                    <div class="flex-grow-1">
                        <p class="text-muted fw-medium">Bed Occupancy Rate</p>
                        <h4 class="mb-0">{{ $bedOccupancyRate }}% <small class="text-muted font-size-12">({{ $occupiedBeds }}/{{ $totalBeds }})</small></h4>
                    </div>
                    <div class="avatar-sm rounded-circle bg-success align-self-center mini-stat-icon">
                        <span class="avatar-title rounded-circle bg-success">
                            <i class="bx bx-pulse font-size-24"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card mini-stats-wid">
            <div class="card-body">
                <div class="d-flex">
                    <div class="flex-grow-1">
                        <p class="text-muted fw-medium">OPD Consultations Today</p>
                        <h4 class="mb-0">{{ $appointmentsToday }}</h4>
                    </div>
                    <div class="avatar-sm rounded-circle bg-info align-self-center mini-stat-icon">
                        <span class="avatar-title rounded-circle bg-info">
                            <i class="bx bx-calendar-check font-size-24"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card mini-stats-wid">
            <div class="card-body">
                <div class="d-flex">
                    <div class="flex-grow-1">
                        <p class="text-muted fw-medium">Active Staff Workforce</p>
                        <h4 class="mb-0">{{ $employeesCount }}</h4>
                    </div>
                    <div class="avatar-sm rounded-circle bg-warning align-self-center mini-stat-icon">
                        <span class="avatar-title rounded-circle bg-warning">
                            <i class="bx bx-user-voice font-size-24"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Active Inpatient Admissions -->
    <div class="col-xl-7">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="card-title mb-0">Current Inpatient Care & Bed Allocations</h4>
                    <a href="{{ route('admissions.index') }}" class="btn btn-sm btn-outline-primary">Manage ADT</a>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle table-nowrap mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Patient / MRN</th>
                                <th>Ward & Bed</th>
                                <th>Attending Doctor</th>
                                <th>Admission Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentAdmissions as $adm)
                                <tr>
                                    <td>
                                        <h6 class="mb-0 font-size-14">{{ $adm->patient->fullName }}</h6>
                                        <small class="text-muted">{{ $adm->patient->mrn }} • {{ $adm->patient->blood_group }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-soft-primary text-primary">{{ $adm->bed->bed_number }}</span>
                                        <small class="d-block text-muted">{{ $adm->ward->name }}</small>
                                    </td>
                                    <td>
                                        <div class="font-size-13">{{ $adm->attendingDoctor->name ?? 'Staff Team' }}</div>
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $adm->admission_date->format('d M, H:i') }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-success font-size-11">Admitted</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">No current active admissions.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ER & Care Team Duty Rosters -->
        @if(isset($activeModules['er_workforce']))
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h4 class="card-title mb-1">Today's Emergency Room (ER) & Staff Duty Roster</h4>
                            <p class="text-muted font-size-12 mb-0">Workforce scheduling & on-call assignments</p>
                        </div>
                        <a href="{{ route('workforce.index') }}" class="btn btn-sm btn-outline-warning">View Roster</a>
                    </div>

                    <div class="table-responsive">
                        <table class="table align-middle table-nowrap mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Staff Member</th>
                                    <th>Department</th>
                                    <th>Shift</th>
                                    <th>Role In Shift</th>
                                    <th>Duty Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($onDutyStaff as $r)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold text-dark">{{ $r->employee->fullName }}</div>
                                            <small class="text-muted">{{ $r->employee->employee_code }}</small>
                                        </td>
                                        <td>{{ $r->department->name ?? 'General' }}</td>
                                        <td><span class="badge bg-soft-info text-info">{{ $r->shift->name }}</span></td>
                                        <td>
                                            <span class="badge bg-warning text-dark font-size-11">
                                                {{ strtoupper(str_replace('_', ' ', $r->role_in_shift)) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-success">On Duty</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-3 text-muted">No staff scheduled for duty today.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Right Column: Diagnostic & Department Widgets -->
    <div class="col-xl-5">
        <!-- Blood Bank Widget (If enabled) -->
        @if(isset($activeModules['blood_bank']))
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="card-title mb-0">Blood Bank & Component Inventory</h4>
                        <a href="{{ route('blood_bank.index') }}" class="btn btn-sm btn-outline-danger">Transfusion Service</a>
                    </div>
                    <p class="text-muted font-size-12">Serology-tested screened bags ready for cross-matching</p>

                    <div class="row g-2 text-center">
                        @php
                            $groups = ['A+', 'B+', 'O+', 'AB+', 'A-', 'B-', 'O-', 'AB-'];
                        @endphp
                        @foreach($groups as $grp)
                            @php
                                $cnt = $bloodBags->where('blood_group', $grp)->count();
                            @endphp
                            <div class="col-3">
                                <div class="border rounded p-2 {{ $cnt > 0 ? 'bg-soft-danger border-danger' : 'bg-light' }}">
                                    <h5 class="mb-0 font-size-16 text-danger fw-bold">{{ $grp }}</h5>
                                    <small class="text-muted font-size-12">{{ $cnt }} Bags</small>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @else
            <!-- Blocked Blood Bank Notice -->
            <div class="card bg-light border">
                <div class="card-body text-center py-4">
                    <i class="bx bx-droplet font-size-36 text-muted mb-2"></i>
                    <h6 class="text-dark fw-bold">Blood Bank Extension Not Licensed</h6>
                    <p class="text-muted font-size-12 mb-3">This hospital operates without an on-premise blood transfusion center.</p>
                    <a href="{{ route('blood_bank.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bx bx-lock-alt me-1"></i> Attempt Access (Gate Test)
                    </a>
                </div>
            </div>
        @endif

        <!-- Radiology & PACS Widget (If enabled) -->
        @if(isset($activeModules['radiology']))
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="card-title mb-0">Radiology Diagnostic Orders</h4>
                        <a href="{{ route('radiology.index') }}" class="btn btn-sm btn-outline-info">PACS & RIS</a>
                    </div>

                    <div class="list-group list-group-flush">
                        @forelse($radiologyOrders as $ro)
                            <div class="list-group-item px-0 py-2">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="badge bg-soft-info text-info">{{ $ro->modality->modality_code ?? 'RAD' }}</span>
                                    <span class="badge {{ $ro->priority === 'urgent' ? 'bg-danger' : 'bg-secondary' }} font-size-10">
                                        {{ strtoupper($ro->priority) }}
                                    </span>
                                </div>
                                <div class="fw-semibold font-size-13 text-dark">{{ $ro->procedure_name }}</div>
                                <small class="text-muted d-block">
                                    Patient: {{ $ro->patient->fullName }} • Order #{{ $ro->order_number }}
                                </small>
                                <small class="text-success font-size-11">
                                    Status: {{ ucfirst($ro->status) }}
                                </small>
                            </div>
                        @empty
                            <div class="text-muted text-center py-3">No pending radiology scan orders.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        @else
            <!-- Blocked Radiology Notice -->
            <div class="card bg-light border">
                <div class="card-body text-center py-4">
                    <i class="bx bx-scan font-size-36 text-muted mb-2"></i>
                    <h6 class="text-dark fw-bold">Radiology & RIS Extension Not Licensed</h6>
                    <p class="text-muted font-size-12 mb-3">Diagnostic medical imaging is deactivated for this hospital tenant.</p>
                    <a href="{{ route('radiology.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bx bx-lock-alt me-1"></i> Attempt Access (Gate Test)
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
