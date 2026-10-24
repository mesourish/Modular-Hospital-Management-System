@extends('layouts.skote')

@section('title', 'Biomedical Equipment & Engineering Maintenance')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <div>
                <h4 class="mb-sm-0 font-size-18">Biomedical Engineering & Medical Device Assets</h4>
                <div class="text-muted font-size-13 mt-1">
                    Outlet: <span class="badge bg-primary-subtle text-primary fw-bold">{{ $activeBranch->name ?? 'Main Hospital Campus' }}</span>
                    • Preventive Maintenance, Calibration Schedules & Breakdown Service Tickets
                </div>
            </div>
            <div class="page-title-right d-flex gap-2">
                <button type="button" class="btn btn-sm btn-danger waves-effect waves-light" data-bs-toggle="modal" data-bs-target="#newTicketModal">
                    <i class="bx bx-wrench me-1"></i> File Breakdown Ticket
                </button>
                <button type="button" class="btn btn-sm btn-outline-primary waves-effect waves-light" data-bs-toggle="modal" data-bs-target="#newAssetModal">
                    <i class="bx bx-plus-circle me-1"></i> Register Medical Device
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

<!-- Biomedical Metrics -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card mini-stats-wid h-100 shadow-sm border-0">
            <div class="card-body">
                <div class="d-flex">
                    <div class="flex-grow-1">
                        <p class="text-muted fw-medium font-size-12 mb-1 text-uppercase">Total Hospital Assets</p>
                        <h4 class="mb-0 font-mono font-size-22 text-dark">{{ $totalAssets }}</h4>
                        <div class="text-muted font-size-11 mt-1">Life Support, Imaging & Surgical</div>
                    </div>
                    <div class="avatar-sm rounded-circle bg-primary-subtle text-primary align-self-center mini-stat-icon">
                        <span class="avatar-title rounded-circle bg-primary font-size-18">
                            <i class="bx bx-devices"></i>
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
                        <p class="text-muted fw-medium font-size-12 mb-1 text-uppercase">Operational Uptime</p>
                        <h4 class="mb-0 font-mono font-size-22 text-success">{{ $operationalCount }}</h4>
                        <div class="text-success font-size-11 mt-1">🟢 100% Calibrated & Safe</div>
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
                        <p class="text-muted fw-medium font-size-12 mb-1 text-uppercase">Calibration Due</p>
                        <h4 class="mb-0 font-mono font-size-22 text-warning">{{ $calibrationDueCount }}</h4>
                        <div class="text-warning font-size-11 mt-1">Pending Quality Audit</div>
                    </div>
                    <div class="avatar-sm rounded-circle bg-warning-subtle text-warning align-self-center mini-stat-icon">
                        <span class="avatar-title rounded-circle bg-warning font-size-18">
                            <i class="bx bx-time-five"></i>
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
                        <p class="text-muted fw-medium font-size-12 mb-1 text-uppercase">Open Service Tickets</p>
                        <h4 class="mb-0 font-mono font-size-22 text-danger">{{ $openTicketsCount }}</h4>
                        <div class="text-danger font-size-11 mt-1">Bioengineer Active</div>
                    </div>
                    <div class="avatar-sm rounded-circle bg-danger-subtle text-danger align-self-center mini-stat-icon">
                        <span class="avatar-title rounded-circle bg-danger font-size-18">
                            <i class="bx bx-wrench"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <!-- Left: Assets Inventory -->
    <div class="col-xl-7">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                <h5 class="card-title mb-0 font-size-15"><i class="bx bx-microchip text-primary me-1"></i> Medical Device Asset Registry</h5>
                <span class="badge bg-light text-secondary border font-mono">{{ $equipmentList->count() }} Assets</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 font-size-13">
                        <thead class="table-light">
                            <tr>
                                <th>Asset Code & Equipment</th>
                                <th>Category</th>
                                <th>Location Room</th>
                                <th>Calibration Due</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($equipmentList as $eq)
                                <tr>
                                    <td>
                                        <div class="font-mono fw-bold text-primary">{{ $eq->asset_code }}</div>
                                        <div class="fw-semibold text-dark">{{ $eq->name }}</div>
                                        <small class="text-muted">{{ $eq->manufacturer }} {{ $eq->model_number ? '• '.$eq->model_number : '' }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-secondary text-capitalize">{{ $eq->category }}</span>
                                    </td>
                                    <td>
                                        <div class="text-dark">{{ $eq->location_room ?: 'General OT / ICU' }}</div>
                                        <small class="text-muted">{{ $eq->department->name ?? 'Clinical Services' }}</small>
                                    </td>
                                    <td>
                                        <div class="font-mono text-dark font-size-12">
                                            {{ $eq->next_calibration_due ? \Carbon\Carbon::parse($eq->next_calibration_due)->format('d M Y') : 'Annual' }}
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge {{ $eq->status === 'operational' ? 'bg-success' : ($eq->status === 'under_repair' ? 'bg-danger' : 'bg-warning') }} text-uppercase font-size-11">
                                            {{ str_replace('_', ' ', $eq->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">No medical equipment assets enrolled.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Right: Service Breakdown Tickets -->
    <div class="col-xl-5">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                <h5 class="card-title mb-0 font-size-15"><i class="bx bx-wrench text-danger me-1"></i> Maintenance & Repair Tickets</h5>
                <span class="badge bg-danger-subtle text-danger font-size-11">Live Fault Log</span>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @forelse($tickets as $tkt)
                        <div class="list-group-item p-3 border-bottom">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <div>
                                    <span class="font-mono text-primary fw-bold font-size-12">{{ $tkt->ticket_number }}</span>
                                    <h6 class="mb-0 text-dark font-size-13">{{ $tkt->equipment->name ?? 'Equipment' }}</h6>
                                </div>
                                <span class="badge {{ $tkt->status === 'resolved' ? 'bg-success' : ($tkt->severity === 'critical_life_support' ? 'bg-danger' : 'bg-warning') }} text-uppercase font-size-10">
                                    {{ $tkt->status }}
                                </span>
                            </div>
                            <div class="text-muted font-size-12 mb-2">
                                {{ $tkt->fault_description }}
                            </div>
                            <div class="d-flex justify-content-between align-items-center font-size-11">
                                <span class="text-secondary"><i class="bx bx-user me-1"></i> {{ $tkt->technician_assigned ?: 'Duty Engineer' }}</span>
                                @if($tkt->status === 'open')
                                    <form action="{{ route('biomedical.tickets.resolve', $tkt->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="corrective_action" value="Sensor recalibrated, flow valve replaced, self-test passed.">
                                        <button type="submit" class="btn btn-xs btn-outline-success py-0 px-2 font-size-11">Resolve Ticket</button>
                                    </form>
                                @else
                                    <span class="text-success"><i class="bx bx-check-circle me-1"></i> Repaired</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-muted text-center py-4">No open maintenance tickets. All hospital devices operational.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Enroll Equipment Asset -->
<div class="modal fade" id="newAssetModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('biomedical.equipment.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white"><i class="bx bx-plus-circle me-2"></i> Enroll Biomedical Equipment Asset</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label font-size-12 fw-semibold">Equipment Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Dräger Fabius Plus Anesthesia Workstation" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label font-size-12 fw-semibold">Asset Code (Tag) <span class="text-danger">*</span></label>
                            <input type="text" name="asset_code" class="form-control font-mono text-uppercase" placeholder="e.g. BME-MUM-ANES-01" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label font-size-12 fw-semibold">Clinical Category <span class="text-danger">*</span></label>
                            <select name="category" class="form-select" required>
                                <option value="Life Support & Critical Care">Life Support & Critical Care (Ventilators, Anesthesia)</option>
                                <option value="Diagnostic & Imaging">Diagnostic & Imaging (X-Ray, CT, Ultrasound)</option>
                                <option value="Surgical & OT">Surgical & OT (Electrocautery, Heart-Lung)</option>
                                <option value="Laboratory Analyzers">Laboratory Analyzers</option>
                                <option value="Dialysis & Nephrology">Dialysis & Nephrology Units</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label font-size-12 fw-semibold">Manufacturer & Model</label>
                            <input type="text" name="manufacturer" class="form-control" placeholder="e.g. Dräger Medical / Fabius Plus">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label font-size-12 fw-semibold">Location / Room</label>
                            <input type="text" name="location_room" class="form-control" value="Operation Theater Suite 01">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label font-size-12 fw-semibold">Next Calibration Due Date</label>
                            <input type="date" name="next_calibration_due" class="form-control" value="{{ date('Y-m-d', strtotime('+6 months')) }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label font-size-12 fw-semibold">Operational Status</label>
                            <select name="status" class="form-select">
                                <option value="operational">Operational</option>
                                <option value="calibration_due">Calibration Due</option>
                                <option value="under_repair">Under Repair</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-bold">Enroll Asset</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: File Breakdown Ticket -->
<div class="modal fade" id="newTicketModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('biomedical.tickets.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title text-white"><i class="bx bx-wrench me-2"></i> File Breakdown Fault Ticket</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label font-size-12 fw-semibold">Faulty Device <span class="text-danger">*</span></label>
                        <select name="equipment_id" class="form-select" required>
                            @foreach($equipmentList as $item)
                                <option value="{{ $item->id }}">{{ $item->asset_code }} - {{ $item->name }} ({{ $item->location_room }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label font-size-12 fw-semibold">Severity Breakdown <span class="text-danger">*</span></label>
                        <select name="severity" class="form-select" required>
                            <option value="critical_life_support">Critical Life Support (Immediate Action Required)</option>
                            <option value="major_downtime">Major Operational Downtime</option>
                            <option value="routine_check">Routine Check / Minor Issue</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label font-size-12 fw-semibold">Fault Observation Description <span class="text-danger">*</span></label>
                        <textarea name="fault_description" class="form-control" rows="3" required placeholder="Describe error code, physical issue, or sensor malfunction..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label font-size-12 fw-semibold">Assign Bioengineer</label>
                        <input type="text" name="technician_assigned" class="form-control" value="Senior Bioengineer On-Duty">
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger fw-bold">Submit Breakdown Ticket</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
