@extends('layouts.skote')

@section('title', 'Operation Theater (OT) Management & Surgical Logs')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0 font-size-18">
                <i class="bx bx-plus-medical text-danger me-1"></i> Operation Theater (OT) Suites, Patient Surgeries & Meds Ledger
            </h4>
            <div class="page-title-right d-flex gap-2">
                <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#newOtModal">
                    <i class="bx bx-cog me-1"></i> Configure OT Suite
                </button>
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#scheduleSurgeryModal">
                    <i class="bx bx-calendar-plus me-1"></i> Schedule Surgery
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Branch Scope & Filter Bar -->
<div class="card shadow-sm border-0 mb-3">
    <div class="card-body p-2">
        <form action="{{ route('ot.index') }}" method="GET" class="row align-items-center g-2">
            <div class="col-md-4">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light"><i class="bx bx-git-branch text-primary"></i></span>
                    @if($user->isRestrictedToBranch())
                        <input type="text" class="form-control form-control-sm bg-light" value="{{ $branches->first()->name ?? 'My Assigned Branch' }}" readonly>
                        <input type="hidden" name="branch_id" value="{{ $selectedBranchId }}">
                    @else
                        <select name="branch_id" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="all">🌐 All Hospital Branches (Consolidated View)</option>
                            @foreach($branches as $b)
                                <option value="{{ $b->id }}" {{ $selectedBranchId == $b->id ? 'selected' : '' }}>
                                    {{ $b->name }} ({{ $b->city }})
                                </option>
                            @endforeach
                        </select>
                    @endif
                </div>
            </div>

            <div class="col-md-3">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light"><i class="bx bx-filter"></i></span>
                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="all" {{ empty($selectedStatus) || $selectedStatus === 'all' ? 'selected' : '' }}>All Surgery Statuses</option>
                        <option value="scheduled" {{ $selectedStatus === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                        <option value="in_progress" {{ $selectedStatus === 'in_progress' ? 'selected' : '' }}>Under Surgery (Active)</option>
                        <option value="post_op_recovery" {{ $selectedStatus === 'post_op_recovery' ? 'selected' : '' }}>PACU Recovery</option>
                        <option value="completed" {{ $selectedStatus === 'completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                </div>
            </div>

            <div class="col-md-5 text-md-end">
                @if($user->isRestrictedToBranch())
                    <span class="badge bg-warning-subtle text-warning font-size-11 px-2 py-1">
                        <i class="bx bx-lock-alt me-1"></i> Locked to Assigned Branch: <strong>{{ $branches->first()->name ?? '' }}</strong>
                    </span>
                @else
                    <span class="badge bg-success-subtle text-success font-size-11 px-2 py-1">
                        <i class="bx bx-check-shield me-1"></i> Super Admin Cross-Branch Visibility
                    </span>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Compact Operational Metrics Strip -->
<div class="row g-2 mb-3">
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card shadow-sm border-0 mb-0">
            <div class="card-body p-2 text-center">
                <span class="text-muted font-size-11 d-block text-uppercase">Total OT Suites</span>
                <h4 class="mb-0 fw-bold text-dark">{{ $totalTheaters }}</h4>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card shadow-sm border-0 mb-0">
            <div class="card-body p-2 text-center">
                <span class="text-muted font-size-11 d-block text-uppercase">Active in Surgery</span>
                <h4 class="mb-0 fw-bold text-danger">{{ $occupiedTheaters }}</h4>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 col-6">
        <div class="card shadow-sm border-0 mb-0">
            <div class="card-body p-2 text-center">
                <span class="text-muted font-size-11 d-block text-uppercase">Ready / Cleared</span>
                <h4 class="mb-0 fw-bold text-success">{{ $availableTheaters }}</h4>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 col-6">
        <div class="card shadow-sm border-0 mb-0">
            <div class="card-body p-2 text-center">
                <span class="text-muted font-size-11 d-block text-uppercase">Surgeries Scheduled / In-Op</span>
                <h4 class="mb-0 fw-bold text-primary">{{ $activeSurgeriesCount }}</h4>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 col-12">
        <div class="card shadow-sm border-0 mb-0">
            <div class="card-body p-2 text-center">
                <span class="text-muted font-size-11 d-block text-uppercase">Meds Usage Records</span>
                <h4 class="mb-0 fw-bold text-info">{{ $totalMedicationsCount }} Logs</h4>
            </div>
        </div>
    </div>
</div>

<!-- Main OT Tabs: Suites, Surgeries & Medications Ledger -->
<div class="card shadow-sm border-0">
    <div class="card-header bg-white border-bottom p-2">
        <ul class="nav nav-tabs card-header-tabs" id="otTab" role="tablist">
            <li class="nav-item">
                <button class="nav-link active font-size-13 py-2 px-3 fw-semibold" id="surgeries-tab-btn" data-bs-toggle="tab" data-bs-target="#tab-surgeries" type="button" role="tab">
                    <i class="bx bx-calendar-check text-primary me-1"></i> Patient Surgeries Schedule ({{ $surgeries->count() }})
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link font-size-13 py-2 px-3 fw-semibold" id="medications-tab-btn" data-bs-toggle="tab" data-bs-target="#tab-medications" type="button" role="tab">
                    <i class="bx bx-capsule text-danger me-1"></i> Surgery Medications Ledger & Quantities
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link font-size-13 py-2 px-3 fw-semibold" id="theaters-tab-btn" data-bs-toggle="tab" data-bs-target="#tab-theaters" type="button" role="tab">
                    <i class="bx bx-door-open text-info me-1"></i> OT Suites & Equipment Configurations ({{ $theaters->count() }})
                </button>
            </li>
        </ul>
    </div>

    <div class="card-body p-3">
        <div class="tab-content" id="otTabContent">

            <!-- TAB 1: Patient Surgeries Schedule (Compressed, High-Density Table) -->
            <div class="tab-pane fade show active" id="tab-surgeries" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0 font-size-12 border">
                        <thead class="table-light">
                            <tr>
                                <th>Surgery Code</th>
                                <th>Patient & MRN</th>
                                <th>Surgical Procedure</th>
                                <th>OT Suite & Branch</th>
                                <th>Surgical Team</th>
                                <th>Scheduled Slot</th>
                                <th>Status</th>
                                <th>Meds Used</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($surgeries as $surg)
                                <tr>
                                    <td>
                                        <strong class="text-primary font-monospace">{{ $surg->surgery_code }}</strong>
                                        <small class="d-block text-muted text-uppercase">{{ $surg->surgery_category }}</small>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $surg->patient->name ?? 'Patient' }}</div>
                                        <small class="text-muted">{{ $surg->patient->mrn ?? '' }} • {{ $surg->patient->gender }}, {{ $surg->patient->age }}y</small>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $surg->surgery_name }}</div>
                                        <small class="text-muted d-inline-block text-truncate" style="max-width: 200px;" title="{{ $surg->pre_op_diagnosis }}">
                                            Dx: {{ $surg->pre_op_diagnosis ?? 'Pre-op workup complete' }}
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            {{ $surg->operationTheater->name ?? 'OT Room' }}
                                        </span>
                                        <small class="d-block text-muted">{{ $surg->branch->city ?? '' }}</small>
                                    </td>
                                    <td>
                                        <div><i class="bx bx-user-pin text-primary"></i> <strong>Surgeon:</strong> {{ $surg->leadSurgeon->name ?? 'Lead Surgeon' }}</div>
                                        @if($surg->anesthesiologist)
                                            <small class="text-muted"><i class="bx bx-pulse text-info"></i> Anesth: {{ $surg->anesthesiologist->name }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <div><i class="bx bx-calendar me-1"></i>{{ $surg->scheduled_start_time->format('d M, Y') }}</div>
                                        <small class="text-muted">{{ $surg->scheduled_start_time->format('h:i A') }} - {{ $surg->scheduled_end_time->format('h:i A') }}</small>
                                    </td>
                                    <td>
                                        <span class="badge {{ $surg->status_badge_class }} px-2 py-1">
                                            {{ $surg->status_label }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info-subtle text-info">
                                            {{ $surg->medications->count() }} Meds
                                        </span>
                                        <small class="d-block text-muted">₹{{ number_format($surg->total_medication_cost, 0) }}</small>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#logMedModal-{{ $surg->id }}" title="Log Surgery Medications">
                                                <i class="bx bx-plus-medical"></i> Meds
                                            </button>
                                            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#statusModal-{{ $surg->id }}" title="Update Status">
                                                <i class="bx bx-edit"></i>
                                            </button>
                                            <a href="{{ route('ot.surgeries.show', $surg->id) }}" class="btn btn-outline-secondary" title="View Surgical Encounter Sheet">
                                                <i class="bx bx-file"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4 text-muted">
                                        No patient surgeries found for the selected branch filter.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TAB 2: Itemized Surgery Medication & Consumables Ledger -->
            <div class="tab-pane fade" id="tab-medications" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div>
                        <h6 class="mb-0 fw-bold text-dark">
                            <i class="bx bx-capsule text-danger me-1"></i> Patient Surgical Medication & Quantity Ledger
                        </h6>
                        <small class="text-muted">Itemized audit of all anesthetics, antibiotics, hemostats, and IV infusions used per patient surgery encounter.</small>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm table-striped align-middle mb-0 font-size-12 border">
                        <thead class="table-light">
                            <tr>
                                <th>Patient & Surgery</th>
                                <th>Medicine / Drug Name</th>
                                <th>Quantity Used</th>
                                <th>Stage of Administration</th>
                                <th>Batch #</th>
                                <th>Unit Cost</th>
                                <th>Total Cost</th>
                                <th>Administered By</th>
                                <th>Recorded At</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($medicationLogs as $log)
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $log->patient->name ?? 'Patient' }}</div>
                                        <small class="text-muted">{{ $log->otSurgery->surgery_name ?? 'Surgery' }} ({{ $log->otSurgery->surgery_code ?? '' }})</small>
                                    </td>
                                    <td>
                                        <strong class="text-primary">{{ $log->medicine_name }}</strong>
                                        @if($log->notes)
                                            <small class="d-block text-muted">{{ $log->notes }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-danger-subtle text-danger font-size-12 px-2 py-1 fw-bold">
                                            {{ $log->formatted_quantity }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            {{ $log->stage_label }}
                                        </span>
                                    </td>
                                    <td><code>{{ $log->batch_number ?? 'N/A' }}</code></td>
                                    <td>₹{{ number_format($log->cost_per_unit, 2) }}</td>
                                    <td><strong>₹{{ number_format($log->total_cost, 2) }}</strong></td>
                                    <td><small class="text-muted">{{ $log->administered_by ?? 'OT Anesthetist' }}</small></td>
                                    <td><small class="text-muted">{{ $log->created_at->format('d M, h:i A') }}</small></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4 text-muted">
                                        No surgical medication usage logs found. Click "Meds" on any surgery to record medications used.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TAB 3: OT Suites & Equipment Configurations -->
            <div class="tab-pane fade" id="tab-theaters" role="tabpanel">
                <div class="row g-3">
                    @forelse($theaters as $ot)
                        <div class="col-lg-6 col-md-12">
                            <div class="card border shadow-sm h-100 mb-0">
                                <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
                                    <div>
                                        <strong class="text-dark font-size-14">{{ $ot->name }}</strong>
                                        <span class="badge bg-secondary ms-1">{{ $ot->code }}</span>
                                    </div>
                                    <span class="badge {{ $ot->status_badge_class }} px-2 py-1">
                                        {{ $ot->status_label }}
                                    </span>
                                </div>
                                <div class="card-body p-3 font-size-12">
                                    <div class="row g-2 mb-2">
                                        <div class="col-6">
                                            <span class="text-muted d-block font-size-11">Branch Location</span>
                                            <strong>{{ $ot->branch->name ?? '' }} ({{ $ot->floor }})</strong>
                                        </div>
                                        <div class="col-6">
                                            <span class="text-muted d-block font-size-11">Suite Classification</span>
                                            <span class="badge bg-info-subtle text-info">{{ ucfirst(str_replace('_', ' ', $ot->type)) }}</span>
                                        </div>
                                    </div>

                                    <div class="bg-light p-2 rounded-2 mb-2">
                                        <span class="fw-bold text-dark d-block mb-1 font-size-11 text-uppercase">Installed Biomedical Equipment:</span>
                                        <div class="row g-1">
                                            <div class="col-6"><i class="bx bx-check-circle text-success me-1"></i> Anesthesia: <small class="text-muted">{{ $ot->anesthesia_workstation ?? 'Standard Station' }}</small></div>
                                            <div class="col-6"><i class="bx bx-check-circle text-success me-1"></i> Imaging: <small class="text-muted">{{ $ot->c_arm_model ?? 'Mobile C-Arm' }}</small></div>
                                            <div class="col-6"><i class="bx bx-check-circle text-success me-1"></i> Perfusion: <small class="text-muted">{{ $ot->heart_lung_machine ?? 'On Standby' }}</small></div>
                                            <div class="col-6"><i class="bx bx-check-circle text-success me-1"></i> Endoscopy: <small class="text-muted">{{ $ot->laparoscopic_tower ?? 'HD Video Tower' }}</small></div>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center font-size-11 text-muted border-top pt-2">
                                        <div>
                                            <i class="bx bx-wind text-info me-1"></i> HEPA: 
                                            <span class="badge bg-success-subtle text-success">{{ ucfirst(str_replace('_', ' ', $ot->hepa_filter_status)) }}</span>
                                            ({{ ucfirst(str_replace('_', ' ', $ot->air_pressure_type)) }})
                                        </div>
                                        <div>
                                            <i class="bx bx-shield-check text-success me-1"></i> Sterilization Due: 
                                            <strong>{{ $ot->next_sterilization_due ? $ot->next_sterilization_due->format('d M') : 'Clear' }}</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12 text-center py-4 text-muted">
                            No operation theaters configured for this branch. Click "Configure OT Suite" above.
                        </div>
                    @endforelse
                </div>
            </div>

        </div>
    </div>
</div>

<!-- MODAL: Configure New OT Suite -->
<div class="modal fade" id="newOtModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white"><i class="bx bx-cog me-1"></i> Configure Operation Theater (OT) Suite</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('ot.theaters.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold font-size-13">Branch Location <span class="text-danger">*</span></label>
                            @if($user->isRestrictedToBranch())
                                <input type="text" class="form-control bg-light" value="{{ $branches->first()->name ?? '' }}" readonly>
                                <input type="hidden" name="branch_id" value="{{ $branches->first()->id ?? '' }}">
                            @else
                                <select name="branch_id" class="form-select" required>
                                    @foreach($branches as $b)
                                        <option value="{{ $b->id }}">{{ $b->name }} ({{ $b->city }})</option>
                                    @endforeach
                                </select>
                            @endif
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold font-size-13">OT Suite Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Modular OT 1 - Cardiac & Cath Hybrid" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold font-size-13">OT Code <span class="text-danger">*</span></label>
                            <input type="text" name="code" class="form-control text-uppercase" placeholder="e.g. OT-SUITE-01" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold font-size-13">OT Type <span class="text-danger">*</span></label>
                            <select name="type" class="form-select" required>
                                <option value="major_modular">Major Modular OT</option>
                                <option value="hybrid_cath_lab">Hybrid Cath Lab OT</option>
                                <option value="minor_emergency">Minor Emergency OT</option>
                                <option value="ophthalmic">Ophthalmic OT</option>
                                <option value="day_care">Day Care OT</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold font-size-13">Initial Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select" required>
                                <option value="available">Ready for Surgery</option>
                                <option value="sterilization_fumigation">Under Sterilization</option>
                                <option value="under_maintenance">Under Maintenance</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold font-size-13">Floor / Wing</label>
                            <input type="text" name="floor" class="form-control" value="3rd Floor - Surgical Pavilion">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold font-size-13">Anesthesia Workstation Model</label>
                            <input type="text" name="anesthesia_workstation" class="form-control" placeholder="e.g. Dräger Fabius Plus Workstation">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold font-size-13">Mobile C-Arm Fluoroscopy</label>
                            <input type="text" name="c_arm_model" class="form-control" placeholder="e.g. Siemens Cios Select C-Arm">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold font-size-13">Perfusion / Heart-Lung Machine</label>
                            <input type="text" name="heart_lung_machine" class="form-control" placeholder="e.g. Terumo Advanced Perfusion System">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold font-size-13">Laparoscopic Endoscopy Tower</label>
                            <input type="text" name="laparoscopic_tower" class="form-control" placeholder="e.g. Karl Storz 4K 3D Rubina Tower">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-bold font-size-13">HEPA Filter Status</label>
                            <select name="hepa_filter_status" class="form-select">
                                <option value="certified_active">Certified Active</option>
                                <option value="cleaning_due">Cleaning Due</option>
                                <option value="replacement_required">Replacement Required</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-bold font-size-13">Air Pressure Type</label>
                            <select name="air_pressure_type" class="form-select">
                                <option value="positive_laminar_flow">Positive Laminar Flow</option>
                                <option value="negative_pressure">Negative Pressure</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bx bx-check me-1"></i> Save OT Suite Configuration</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL: Schedule Patient Surgery -->
<div class="modal fade" id="scheduleSurgeryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title text-white"><i class="bx bx-calendar-plus me-1"></i> Schedule Patient Surgery</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('ot.surgeries.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold font-size-13">Branch Location <span class="text-danger">*</span></label>
                            @if($user->isRestrictedToBranch())
                                <input type="text" class="form-control bg-light" value="{{ $branches->first()->name ?? '' }}" readonly>
                                <input type="hidden" name="branch_id" value="{{ $branches->first()->id ?? '' }}">
                            @else
                                <select name="branch_id" class="form-select" required>
                                    @foreach($branches as $b)
                                        <option value="{{ $b->id }}">{{ $b->name }} ({{ $b->city }})</option>
                                    @endforeach
                                </select>
                            @endif
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold font-size-13">Operation Theater Suite <span class="text-danger">*</span></label>
                            <select name="operation_theater_id" class="form-select" required>
                                @foreach($theaters as $th)
                                    <option value="{{ $th->id }}">{{ $th->name }} ({{ $th->branch->city ?? '' }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold font-size-13">Patient <span class="text-danger">*</span></label>
                            <select name="patient_id" class="form-select" required>
                                @foreach($patients as $pt)
                                    <option value="{{ $pt->id }}">{{ $pt->name }} (MRN: {{ $pt->mrn ?? 'N/A' }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold font-size-13">Lead Chief Surgeon <span class="text-danger">*</span></label>
                            <select name="lead_surgeon_id" class="form-select" required>
                                @foreach($surgeons as $surg)
                                    <option value="{{ $surg->id }}">{{ $surg->name }} ({{ ucfirst($surg->role) }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold font-size-13">Surgery / Procedure Name <span class="text-danger">*</span></label>
                            <input type="text" name="surgery_name" class="form-control" placeholder="e.g. Off-Pump Coronary Artery Bypass Grafting (CABG)" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold font-size-13">Surgery Category <span class="text-danger">*</span></label>
                            <select name="surgery_category" class="form-select" required>
                                <option value="major">Major Surgery</option>
                                <option value="super_major">Super Major (Complex/Cardiothoracic)</option>
                                <option value="minor">Minor Surgery</option>
                                <option value="emergency">Emergency Trauma Laparotomy</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold font-size-13">Scheduled Start Time <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="scheduled_start_time" class="form-control" value="{{ now()->addHours(2)->format('Y-m-d\TH:i') }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold font-size-13">Scheduled End Time <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="scheduled_end_time" class="form-control" value="{{ now()->addHours(5)->format('Y-m-d\TH:i') }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold font-size-13">Anesthesia Type <span class="text-danger">*</span></label>
                            <select name="anesthesia_type" class="form-select" required>
                                <option value="general">General Anesthesia (GA)</option>
                                <option value="spinal_epidural">Spinal / Epidural</option>
                                <option value="regional_block">Regional Nerve Block</option>
                                <option value="local_sedation">Local Sedation (MAC)</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold font-size-13">Consultant Anesthesiologist</label>
                            <select name="anesthesiologist_id" class="form-select">
                                <option value="">-- Assign Anesthesiologist --</option>
                                @foreach($surgeons as $surg)
                                    <option value="{{ $surg->id }}">{{ $surg->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold font-size-13">Pre-Operative Diagnosis & Indication</label>
                            <textarea name="pre_op_diagnosis" class="form-control" rows="2" placeholder="e.g. Severe triple vessel CAD with unstable angina. EF 45%."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success"><i class="bx bx-check me-1"></i> Confirm & Schedule Surgery</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- SURGERY ACTION MODALS (Log Meds & Update Status) -->
@foreach($surgeries as $surg)
    <!-- Modal: Log OT Medications Used for Patient Surgery -->
    <div class="modal fade" id="logMedModal-{{ $surg->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title text-white">
                        <i class="bx bx-capsule me-1"></i> Log OT Medications: {{ $surg->patient->name ?? 'Patient' }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('ot.medications.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="ot_surgery_id" value="{{ $surg->id }}">
                    <div class="modal-body p-4">
                        <div class="p-2 bg-light rounded-2 mb-3 font-size-12">
                            <strong>Procedure:</strong> {{ $surg->surgery_name }} • 
                            <strong>Surgery Code:</strong> <code>{{ $surg->surgery_code }}</code> • 
                            <strong>Patient:</strong> {{ $surg->patient->name ?? '' }} ({{ $surg->patient->mrn ?? '' }})
                        </div>

                        <!-- 1-Click Indian OT Med Presets -->
                        <div class="mb-3">
                            <span class="fw-bold font-size-11 text-muted text-uppercase d-block mb-1">Quick Select Common Surgical Drugs:</span>
                            <div class="d-flex flex-wrap gap-1">
                                <button type="button" class="btn btn-sm btn-outline-secondary font-size-11 py-1" onclick="setOtMed('Propofol 1% (200mg/20ml)', 2, 'vials', 'induction', 250)">Propofol 1% (2 Vials)</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary font-size-11 py-1" onclick="setOtMed('Fentanyl Citrate 50mcg/ml (2ml)', 3, 'ampoules', 'intra_op', 120)">Fentanyl 50mcg (3 Amp)</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary font-size-11 py-1" onclick="setOtMed('Vecuronium Bromide 4mg', 2, 'vials', 'induction', 180)">Vecuronium 4mg (2 Vials)</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary font-size-11 py-1" onclick="setOtMed('Heparin Sodium 25,000 IU/5ml', 1, 'vials', 'intra_op', 450)">Heparin 25,000 IU (1 Vial)</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary font-size-11 py-1" onclick="setOtMed('Sevoflurane Inhalation Agent (250ml)', 50, 'ml', 'maintenance', 15)">Sevoflurane (50 ml)</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary font-size-11 py-1" onclick="setOtMed('Cefuroxime 1.5g IV Infusion', 1, 'vials', 'pre_medication', 210)">Cefuroxime 1.5g (1 Vial)</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary font-size-11 py-1" onclick="setOtMed('Surgicel Absorbable Hemostat Mesh', 1, 'packs', 'intra_op', 850)">Surgicel Hemostat (1 Pack)</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary font-size-11 py-1" onclick="setOtMed('Normal Saline 0.9% (500ml)', 2, 'bags', 'intra_op', 65)">NS 500ml (2 Bags)</button>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold font-size-13">Medicine / Consumable Name <span class="text-danger">*</span></label>
                                <input type="text" name="medicine_name" id="ot_med_name_{{ $surg->id }}" class="form-control" placeholder="e.g. Propofol 1% 20ml" required>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-bold font-size-13">Quantity Used <span class="text-danger">*</span></label>
                                <input type="number" step="0.1" name="quantity_used" id="ot_med_qty_{{ $surg->id }}" class="form-control" placeholder="e.g. 2" required>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-bold font-size-13">Unit <span class="text-danger">*</span></label>
                                <select name="unit" id="ot_med_unit_{{ $surg->id }}" class="form-select" required>
                                    <option value="vials">Vials</option>
                                    <option value="ampoules">Ampoules</option>
                                    <option value="ml">ml (Milliliters)</option>
                                    <option value="bags">Bags (IV Infusion)</option>
                                    <option value="packs">Packs (Mesh/Gauze)</option>
                                    <option value="units">Units</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold font-size-13">Administration Stage <span class="text-danger">*</span></label>
                                <select name="administration_stage" id="ot_med_stage_{{ $surg->id }}" class="form-select" required>
                                    <option value="intra_op">Intra-Operative</option>
                                    <option value="induction">Anesthesia Induction</option>
                                    <option value="maintenance">Maintenance</option>
                                    <option value="reversal">Anesthesia Reversal</option>
                                    <option value="pre_medication">Pre-Medication</option>
                                    <option value="post_op_recovery">PACU Recovery</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold font-size-13">Batch Number</label>
                                <input type="text" name="batch_number" class="form-control" placeholder="e.g. BAT-OT-994">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold font-size-13">Unit Cost (₹)</label>
                                <input type="number" step="0.01" name="cost_per_unit" id="ot_med_cost_{{ $surg->id }}" class="form-control" value="150.00">
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold font-size-13">Clinical Administration Notes</label>
                                <input type="text" name="notes" class="form-control" placeholder="e.g. Bolus given prior to sternotomy. BP maintained at 110/70.">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger"><i class="bx bx-check me-1"></i> Record Medicine Usage</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal: Update Surgery Status -->
    <div class="modal fade" id="statusModal-{{ $surg->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white"><i class="bx bx-edit me-1"></i> Update Status: {{ $surg->surgery_code }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('ot.surgeries.status', $surg->id) }}" method="POST">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold font-size-13">Surgical Encounter Status</label>
                            <select name="status" class="form-select" required>
                                <option value="scheduled" {{ $surg->status === 'scheduled' ? 'selected' : '' }}>Scheduled (Patient In Pre-Op)</option>
                                <option value="in_progress" {{ $surg->status === 'in_progress' ? 'selected' : '' }}>Under Surgery (Active in OT)</option>
                                <option value="post_op_recovery" {{ $surg->status === 'post_op_recovery' ? 'selected' : '' }}>Post-Op Recovery (PACU)</option>
                                <option value="completed" {{ $surg->status === 'completed' ? 'selected' : '' }}>Completed & Transferred to Ward/ICU</option>
                                <option value="cancelled" {{ $surg->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold font-size-13">Blood Units Transfused (PRBC/FFP)</label>
                            <input type="number" name="blood_units_transfused" class="form-control" value="{{ $surg->blood_units_transfused }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold font-size-13">Post-Operative Findings</label>
                            <textarea name="post_op_findings" class="form-control" rows="2" placeholder="e.g. Grafts to LAD, OM, RCA patent. Hemostasis complete.">{{ $surg->post_op_findings }}</textarea>
                        </div>

                        <div class="mb-0">
                            <label class="form-label fw-bold font-size-13">Surgical Notes & Closure Details</label>
                            <textarea name="surgical_notes" class="form-control" rows="2" placeholder="e.g. Sternal closure with stainless steel wires. Skin closed with Monocryl.">{{ $surg->surgical_notes }}</textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary"><i class="bx bx-check me-1"></i> Update Surgery Status</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach

<script>
    function setOtMed(name, qty, unit, stage, cost) {
        var activeModal = document.querySelector('.modal.show');
        if (activeModal) {
            var nameInput = activeModal.querySelector('input[name="medicine_name"]');
            var qtyInput = activeModal.querySelector('input[name="quantity_used"]');
            var unitInput = activeModal.querySelector('select[name="unit"]');
            var stageInput = activeModal.querySelector('select[name="administration_stage"]');
            var costInput = activeModal.querySelector('input[name="cost_per_unit"]');

            if (nameInput) nameInput.value = name;
            if (qtyInput) qtyInput.value = qty;
            if (unitInput) unitInput.value = unit;
            if (stageInput) stageInput.value = stage;
            if (costInput) costInput.value = cost;
        }
    }
</script>
@endsection
