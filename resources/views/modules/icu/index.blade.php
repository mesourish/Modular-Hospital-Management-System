@extends('layouts.skote')

@section('title', 'ICU & Critical Care Management Suite')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <div>
                <h4 class="mb-sm-0 font-size-18">
                    <i class="bx bx-pulse text-danger me-2"></i> Intensive Care Unit (ICU) & Critical Care Telemetry
                </h4>
                <div class="text-muted font-size-13 mt-1">
                    Outlet: <span class="badge bg-primary-subtle text-primary fw-bold">{{ $activeBranch->name ?? 'Main Hospital Campus' }}</span>
                    @if($activeBranch && $activeBranch->slug)
                        <span class="badge bg-light text-secondary border font-mono">/outlet/{{ $activeBranch->slug }}/icu</span>
                    @endif
                    • 24x7 Continuous Hemodynamic Telemetry, Ventilator Modes & Code Blue Log
                </div>
            </div>
            <div class="page-title-right d-flex gap-2">
                <button type="button" class="btn btn-sm btn-danger waves-effect waves-light shadow-sm" data-bs-toggle="modal" data-bs-target="#codeBlueModal">
                    <i class="bx bx-alarm-exclamation me-1"></i> Log Code Blue Event
                </button>
                <button type="button" class="btn btn-sm btn-primary waves-effect waves-light shadow-sm" data-bs-toggle="modal" data-bs-target="#admitIcuModal">
                    <i class="bx bx-plus-circle me-1"></i> Admit to ICU Bed
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

<!-- ICU KPI Metric Cards -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card mini-stats-wid h-100 shadow-sm border-0">
            <div class="card-body">
                <div class="d-flex">
                    <div class="flex-grow-1">
                        <p class="text-muted fw-medium font-size-12 mb-1 text-uppercase">Total ICU Beds</p>
                        <h4 class="mb-0 font-mono font-size-22 text-dark">{{ $totalIcuBeds }}</h4>
                        <div class="text-muted font-size-11 mt-1">
                            <span class="text-primary fw-bold">{{ $occupiedIcuBeds }} Occupied</span> • {{ max(0, $totalIcuBeds - $occupiedIcuBeds) }} Standby
                        </div>
                    </div>
                    <div class="avatar-sm rounded-circle bg-primary-subtle text-primary align-self-center mini-stat-icon">
                        <span class="avatar-title rounded-circle bg-primary font-size-18">
                            <i class="bx bx-bed"></i>
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
                        <p class="text-muted fw-medium font-size-12 mb-1 text-uppercase">Mechanical Ventilator</p>
                        <h4 class="mb-0 font-mono font-size-22 text-info">{{ $ventilatedCount }}</h4>
                        <div class="text-info font-size-11 mt-1">SIMV, CPAP & BiPAP Active</div>
                    </div>
                    <div class="avatar-sm rounded-circle bg-info-subtle text-info align-self-center mini-stat-icon">
                        <span class="avatar-title rounded-circle bg-info font-size-18">
                            <i class="bx bx-wind"></i>
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
                        <p class="text-muted fw-medium font-size-12 mb-1 text-uppercase">Critical / Unstable</p>
                        <h4 class="mb-0 font-mono font-size-22 text-danger">{{ $criticalCount }}</h4>
                        <div class="text-danger font-size-11 mt-1">Continuous Monitoring Required</div>
                    </div>
                    <div class="avatar-sm rounded-circle bg-danger-subtle text-danger align-self-center mini-stat-icon">
                        <span class="avatar-title rounded-circle bg-danger font-size-18">
                            <i class="bx bx-pulse"></i>
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
                        <p class="text-muted fw-medium font-size-12 mb-1 text-uppercase">Code Blue Resuscitations</p>
                        <h4 class="mb-0 font-mono font-size-22 text-dark">{{ $recentCodeBlues }}</h4>
                        <div class="text-muted font-size-11 mt-1">Past 7 Days Emergency Logs</div>
                    </div>
                    <div class="avatar-sm rounded-circle bg-warning-subtle text-warning align-self-center mini-stat-icon">
                        <span class="avatar-title rounded-circle bg-warning font-size-18">
                            <i class="bx bx-alarm-exclamation"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Active Telemetry & ICU Monitor Grid -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h5 class="card-title mb-0 font-size-16 text-dark">
                <i class="bx bx-tv text-primary me-2 align-middle font-size-18"></i> Live ICU Bedside Telemetry Monitors
            </h5>
            <small class="text-muted">Real-time SpO2, Invasive Hemodynamics, Ventilator Modes & Central Lines</small>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-success-subtle text-success px-3 py-2 font-size-12">
                <i class="bx bx-check-circle me-1"></i> Telemetry Stream Online
            </span>
        </div>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 font-size-13">
                <thead class="table-light text-muted font-size-12 text-uppercase">
                    <tr>
                        <th style="min-width: 180px;">Bed & Patient</th>
                        <th style="min-width: 170px;">Hemodynamics (Vitals)</th>
                        <th style="min-width: 170px;">Ventilator Telemetry</th>
                        <th style="min-width: 150px;">Invasive Lines & Infusion</th>
                        <th style="min-width: 140px;">Condition & Doctor</th>
                        <th style="min-width: 180px;">Shift Nursing Notes</th>
                        <th class="text-end" style="min-width: 130px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($icuMonitors as $mon)
                        <tr>
                            <!-- Bed & Patient -->
                            <td>
                                <div class="d-flex align-items-center">
                                    @if(!empty($mon->patient?->photo))
                                        <img src="{{ asset($mon->patient->photo) }}" alt="" class="rounded-circle me-2" style="width: 32px; height: 32px; object-fit: cover;">
                                    @endif
                                    <div>
                                        <div class="fw-bold font-size-14 text-dark">
                                            {{ $mon->patient?->full_name ?? 'ICU Patient' }}
                                        </div>
                                        <div class="text-muted font-size-11">
                                            <span class="badge bg-primary font-mono">{{ $mon->bed?->bed_number ?? 'ICU-BED' }}</span>
                                            MRN: {{ $mon->patient?->mrn }}
                                        </div>
                                        <div class="text-muted font-size-11 mt-1">
                                            {{ $mon->patient?->gender ? ucfirst($mon->patient->gender) : '' }} • {{ $mon->patient?->age ?? 'Adult' }} yrs
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Hemodynamics -->
                            <td>
                                <div class="d-flex flex-column gap-1">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <span class="text-muted font-size-11">SpO2:</span>
                                        <span class="badge {{ $mon->spo2 >= 95 ? 'bg-success' : ($mon->spo2 >= 90 ? 'bg-warning text-dark' : 'bg-danger') }} font-mono fw-bold">
                                            {{ $mon->spo2 }}%
                                        </span>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between">
                                        <span class="text-muted font-size-11">Heart Rate:</span>
                                        <span class="font-mono fw-bold text-dark">{{ $mon->heart_rate }} bpm</span>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between">
                                        <span class="text-muted font-size-11">BP:</span>
                                        <span class="font-mono text-dark">{{ $mon->formatted_bp }}</span>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between">
                                        <span class="text-muted font-size-11">GCS:</span>
                                        <span class="badge bg-light text-dark border font-mono">E4V5M6 ({{ $mon->gcs_score }}/15)</span>
                                    </div>
                                </div>
                            </td>

                            <!-- Ventilator Settings -->
                            <td>
                                @if($mon->is_ventilated)
                                    <span class="badge bg-danger-subtle text-danger font-size-11 fw-bold mb-1">
                                        <i class="bx bx-wind me-1"></i> {{ $mon->vent_mode }}
                                    </span>
                                    <div class="text-muted font-size-11">
                                        <strong>FiO2:</strong> {{ $mon->fio2_percentage }}% • <strong>PEEP:</strong> {{ $mon->peep }} cmH2O
                                    </div>
                                    @if($mon->tidal_volume)
                                        <div class="text-muted font-size-11">
                                            <strong>Vt:</strong> {{ $mon->tidal_volume }} mL • <strong>RR:</strong> {{ $mon->respiratory_rate }}/min
                                        </div>
                                    @endif
                                @else
                                    <span class="badge bg-light text-secondary border font-size-11 mb-1">
                                        {{ $mon->vent_mode ?? 'Room Air (Non-Ventilated)' }}
                                    </span>
                                    <div class="text-muted font-size-11">Room Air / Nasal Cannula</div>
                                @endif
                            </td>

                            <!-- Invasive Lines & Infusions -->
                            <td>
                                <div class="d-flex flex-column gap-1 font-size-11">
                                    <div>
                                        <i class="bx {{ $mon->arterial_line ? 'bxs-check-circle text-danger' : 'bx-x-circle text-muted' }}"></i>
                                        <span>A-Line (Radial/Femoral)</span>
                                    </div>
                                    <div>
                                        <i class="bx {{ $mon->central_line ? 'bxs-check-circle text-primary' : 'bx-x-circle text-muted' }}"></i>
                                        <span>CVC (Subclavian/IJV)</span>
                                    </div>
                                    @if($mon->inotrope_infusion)
                                        <div class="text-danger fw-semibold mt-1">
                                            <i class="bx bx-injection me-1"></i> {{ $mon->inotrope_infusion }}
                                        </div>
                                    @endif
                                </div>
                            </td>

                            <!-- Clinical Condition & Attending Doctor -->
                            <td>
                                @php
                                    $condClass = match($mon->clinical_condition) {
                                        'critical_unstable' => 'bg-danger text-white',
                                        'ventilator_dependent' => 'bg-warning text-dark',
                                        'post_cardiac_arrest' => 'bg-dark text-white',
                                        default => 'bg-success text-white',
                                    };
                                @endphp
                                <span class="badge {{ $condClass }} text-uppercase font-size-11 mb-1">
                                    {{ str_replace('_', ' ', $mon->clinical_condition) }}
                                </span>
                                <div class="text-muted font-size-11">
                                    <i class="bx bx-user-pin text-primary me-1"></i>
                                    {{ $mon->attendingDoctor?->name ?? 'Attending Intensivist' }}
                                </div>
                                <div class="text-muted font-size-10 mt-1">
                                    Assessed: {{ $mon->last_assessment_at ? $mon->last_assessment_at->diffForHumans() : 'Just now' }}
                                </div>
                            </td>

                            <!-- Notes -->
                            <td class="font-size-11 text-muted">
                                {{ \Illuminate\Support\Str::limit($mon->nurse_shift_notes ?? 'Hemodynamics monitored continuously.', 75) }}
                            </td>

                            <!-- Actions -->
                            <td class="text-end">
                                <button type="button" class="btn btn-sm btn-outline-primary py-1 px-2 font-size-11 mb-1" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#updateVitalsModal{{ $mon->id }}">
                                    <i class="bx bx-edit me-1"></i> Update
                                </button>
                                @if($mon->admission)
                                    <form action="{{ route('icu.step_down', $mon->admission->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Confirm step-down of patient from ICU to general ward?')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-success py-1 px-2 font-size-11">
                                            <i class="bx bx-log-out me-1"></i> Step-Down
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>

                        <!-- Modal: Update Vitals for this patient -->
                        <div class="modal fade" id="updateVitalsModal{{ $mon->id }}" tabindex="-1">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <form action="{{ route('icu.vitals.update') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="monitor_id" value="{{ $mon->id }}">
                                        <div class="modal-header bg-primary text-white">
                                            <h5 class="modal-title text-white">
                                                <i class="bx bx-edit me-1"></i> Update ICU Telemetry: {{ $mon->patient?->full_name }} ({{ $mon->bed?->bed_number }})
                                            </h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body p-4">
                                            <div class="row g-3">
                                                <div class="col-md-3">
                                                    <label class="form-label font-size-12 fw-semibold">SpO2 (%) <span class="text-danger">*</span></label>
                                                    <input type="number" name="spo2" class="form-control" value="{{ $mon->spo2 }}" min="50" max="100" required>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label font-size-12 fw-semibold">Heart Rate (BPM) <span class="text-danger">*</span></label>
                                                    <input type="number" name="heart_rate" class="form-control" value="{{ $mon->heart_rate }}" min="30" max="240" required>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label font-size-12 fw-semibold">BP Systolic <span class="text-danger">*</span></label>
                                                    <input type="number" name="bp_systolic" class="form-control" value="{{ $mon->bp_systolic }}" min="50" max="260" required>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label font-size-12 fw-semibold">BP Diastolic <span class="text-danger">*</span></label>
                                                    <input type="number" name="bp_diastolic" class="form-control" value="{{ $mon->bp_diastolic }}" min="30" max="160" required>
                                                </div>

                                                <div class="col-md-4">
                                                    <label class="form-label font-size-12 fw-semibold">Ventilator Mode</label>
                                                    <select name="vent_mode" class="form-select font-size-12">
                                                        <option value="None (Room Air)" {{ $mon->vent_mode === 'None (Room Air)' ? 'selected' : '' }}>None (Room Air)</option>
                                                        <option value="SIMV + PSV" {{ $mon->vent_mode === 'SIMV + PSV' ? 'selected' : '' }}>SIMV + PSV (Synchronized Mandatory)</option>
                                                        <option value="PRVC (Volume Control)" {{ $mon->vent_mode === 'PRVC (Volume Control)' ? 'selected' : '' }}>PRVC (Pressure Regulated Volume Control)</option>
                                                        <option value="CPAP / BiPAP" {{ $mon->vent_mode === 'CPAP / BiPAP' ? 'selected' : '' }}>CPAP / BiPAP (Non-Invasive)</option>
                                                        <option value="High Flow Nasal Cannula" {{ $mon->vent_mode === 'High Flow Nasal Cannula' ? 'selected' : '' }}>High Flow Nasal Cannula (HFNC)</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label font-size-12 fw-semibold">FiO2 (%)</label>
                                                    <input type="number" name="fio2_percentage" class="form-control" value="{{ $mon->fio2_percentage }}" min="21" max="100">
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label font-size-12 fw-semibold">PEEP (cmH2O)</label>
                                                    <input type="number" step="0.5" name="peep" class="form-control" value="{{ $mon->peep }}" min="0" max="30">
                                                </div>

                                                <div class="col-md-3">
                                                    <label class="form-label font-size-12 fw-semibold">Tidal Volume (mL)</label>
                                                    <input type="number" name="tidal_volume" class="form-control" value="{{ $mon->tidal_volume }}">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label font-size-12 fw-semibold">Resp Rate (/min)</label>
                                                    <input type="number" name="respiratory_rate" class="form-control" value="{{ $mon->respiratory_rate }}">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label font-size-12 fw-semibold">Temp (°C)</label>
                                                    <input type="number" step="0.1" name="temperature_c" class="form-control" value="{{ $mon->temperature_c }}">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label font-size-12 fw-semibold">GCS Score (3-15)</label>
                                                    <input type="number" name="gcs_score" class="form-control" value="{{ $mon->gcs_score }}" min="3" max="15">
                                                </div>

                                                <div class="col-md-6">
                                                    <label class="form-label font-size-12 fw-semibold">Clinical Acuity Condition <span class="text-danger">*</span></label>
                                                    <select name="clinical_condition" class="form-select font-size-12" required>
                                                        <option value="stable_weaned" {{ $mon->clinical_condition === 'stable_weaned' ? 'selected' : '' }}>Stable Weaned / Ready for Step-Down</option>
                                                        <option value="ventilator_dependent" {{ $mon->clinical_condition === 'ventilator_dependent' ? 'selected' : '' }}>Ventilator Dependent</option>
                                                        <option value="critical_unstable" {{ $mon->clinical_condition === 'critical_unstable' ? 'selected' : '' }}>Critical / Hemodynamically Unstable</option>
                                                        <option value="post_cardiac_arrest" {{ $mon->clinical_condition === 'post_cardiac_arrest' ? 'selected' : '' }}>Post-Cardiac Arrest / Hypothermia Protocol</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label font-size-12 fw-semibold">Inotrope / Vasopressor Infusions</label>
                                                    <input type="text" name="inotrope_infusion" class="form-control font-size-12" value="{{ $mon->inotrope_infusion }}" placeholder="e.g. Noradrenaline 0.04 mcg/kg/min, Vasopressin">
                                                </div>

                                                <div class="col-12 d-flex gap-4">
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" name="arterial_line" id="artLine{{ $mon->id }}" value="1" {{ $mon->arterial_line ? 'checked' : '' }}>
                                                        <label class="form-check-label font-size-12" for="artLine{{ $mon->id }}">Arterial Line Active</label>
                                                    </div>
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" name="central_line" id="cvcLine{{ $mon->id }}" value="1" {{ $mon->central_line ? 'checked' : '' }}>
                                                        <label class="form-check-label font-size-12" for="cvcLine{{ $mon->id }}">Central Venous Line Active</label>
                                                    </div>
                                                </div>

                                                <div class="col-12">
                                                    <label class="form-label font-size-12 fw-semibold">Shift Nursing & Intensivist Notes</label>
                                                    <textarea name="nurse_shift_notes" class="form-control font-size-12" rows="3">{{ $mon->nurse_shift_notes }}</textarea>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer bg-light">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-primary fw-bold">Update Telemetry</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                <i class="bx bx-check-circle text-success font-size-22 d-block mb-1"></i>
                                No patients currently in ICU telemetry. Click "Admit to ICU Bed" to register a patient.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Code Blue Emergency Resuscitation Logs -->
<div class="card shadow-sm border-0">
    <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
        <div>
            <h5 class="card-title mb-0 font-size-16 text-dark">
                <i class="bx bx-alarm-exclamation text-danger me-2 align-middle font-size-18"></i>
                Emergency Code Blue Resuscitation Logs
            </h5>
            <small class="text-muted">Cardiac arrest resuscitation records, rhythm analysis, CPR duration & defibrillation shocks</small>
        </div>
        <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#codeBlueModal">
            <i class="bx bx-plus me-1"></i> Record New Code Blue
        </button>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 font-size-13">
                <thead class="table-light text-muted font-size-12 text-uppercase">
                    <tr>
                        <th>Time of Arrest</th>
                        <th>Patient Identity</th>
                        <th>Bed / Location</th>
                        <th>CPR Duration</th>
                        <th>Shocks</th>
                        <th>Initial Rhythm</th>
                        <th>Outcome</th>
                        <th>Team & Resuscitation Notes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($codeBlueLogs as $log)
                        <tr>
                            <td class="font-mono text-muted">
                                {{ $log->event_time ? $log->event_time->format('d M Y, H:i') : 'N/A' }}
                            </td>
                            <td>
                                <div class="fw-bold text-dark">{{ $log->patient?->full_name ?? 'Emergency Patient' }}</div>
                                <div class="text-muted font-size-11 font-mono">MRN: {{ $log->patient?->mrn }}</div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border font-mono">
                                    {{ $log->bed?->bed_number ?? 'Emergency Bay' }}
                                </span>
                            </td>
                            <td class="font-mono fw-bold">
                                {{ $log->cpr_duration_mins }} Mins
                            </td>
                            <td>
                                <span class="badge {{ $log->shocks_delivered > 0 ? 'bg-danger' : 'bg-light text-muted border' }}">
                                    {{ $log->shocks_delivered }} Delivered
                                </span>
                            </td>
                            <td class="font-size-12 fw-semibold text-danger">
                                {{ $log->cardiac_rhythm }}
                            </td>
                            <td>
                                <span class="badge {{ $log->outcome === 'resuscitated' ? 'bg-success' : ($log->outcome === 'ongoing' ? 'bg-warning text-dark' : 'bg-danger') }} text-uppercase font-size-11">
                                    {{ $log->outcome }}
                                </span>
                            </td>
                            <td class="font-size-11 text-muted">
                                <div><strong>Lead:</strong> {{ $log->initiatedBy?->name ?? 'Dr. Intensivist' }}</div>
                                <div>{{ $log->resuscitation_notes }}</div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">No Code Blue emergencies recorded.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Admit to ICU -->
<div class="modal fade" id="admitIcuModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('icu.admit') }}" method="POST">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white"><i class="bx bx-bed me-2"></i> Enroll Patient in ICU Bed</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label font-size-12 fw-semibold">Select Admitted Inpatient <span class="text-danger">*</span></label>
                            <select name="admission_id" class="form-select font-size-12" required>
                                <option value="">-- Choose Admitted Patient --</option>
                                @foreach($admittedPatients as $adm)
                                    <option value="{{ $adm->id }}">
                                        {{ $adm->patient?->mrn }} - {{ $adm->patient?->full_name }} (Current Bed: {{ $adm->bed?->bed_number }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label font-size-12 fw-semibold">Target ICU Bed <span class="text-danger">*</span></label>
                            <select name="bed_id" class="form-select font-mono font-size-12" required>
                                @foreach($icuBeds as $bed)
                                    <option value="{{ $bed->id }}">
                                        {{ $bed->bed_number }} - {{ $bed->ward?->name }} ({{ strtoupper($bed->status) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label font-size-12 fw-semibold">Attending Intensivist / Doctor <span class="text-danger">*</span></label>
                            <select name="attending_doctor_id" class="form-select font-size-12" required>
                                @foreach($doctors as $doc)
                                    <option value="{{ $doc->id }}">{{ $doc->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label font-size-12 fw-semibold">Clinical Acuity <span class="text-danger">*</span></label>
                            <select name="clinical_condition" class="form-select font-size-12" required>
                                <option value="ventilator_dependent">Ventilator Dependent</option>
                                <option value="critical_unstable">Critical / Hemodynamically Unstable</option>
                                <option value="post_cardiac_arrest">Post-Cardiac Arrest</option>
                                <option value="stable_weaned">Stable / Post-Operative ICU Monitoring</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label font-size-12 fw-semibold">SpO2 (%) <span class="text-danger">*</span></label>
                            <input type="number" name="spo2" class="form-control" value="98" min="50" max="100" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label font-size-12 fw-semibold">Heart Rate (BPM) <span class="text-danger">*</span></label>
                            <input type="number" name="heart_rate" class="form-control" value="82" min="30" max="240" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label font-size-12 fw-semibold">BP Systolic <span class="text-danger">*</span></label>
                            <input type="number" name="bp_systolic" class="form-control" value="120" min="50" max="260" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label font-size-12 fw-semibold">BP Diastolic <span class="text-danger">*</span></label>
                            <input type="number" name="bp_diastolic" class="form-control" value="80" min="30" max="160" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label font-size-12 fw-semibold">Ventilator Settings (Optional)</label>
                            <input type="text" name="vent_mode" class="form-control font-size-12" placeholder="e.g. SIMV + PSV, FiO2 45%, PEEP 6">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label font-size-12 fw-semibold">GCS Score (3-15)</label>
                            <input type="number" name="gcs_score" class="form-control" value="15" min="3" max="15">
                        </div>

                        <div class="col-12">
                            <label class="form-label font-size-12 fw-semibold">Admission Reason & Nursing Instructions</label>
                            <textarea name="nurse_shift_notes" class="form-control font-size-12" rows="2" placeholder="Transfer from OT / ER. Start ICU protocol..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-bold">Confirm ICU Bed Allocation</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Record Code Blue Emergency -->
<div class="modal fade" id="codeBlueModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('icu.code_blue') }}" method="POST">
                @csrf
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title text-white"><i class="bx bx-alarm-exclamation me-2"></i> Record Emergency Code Blue Resuscitation</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label font-size-12 fw-semibold">Patient <span class="text-danger">*</span></label>
                            <select name="patient_id" class="form-select font-size-12" required>
                                @foreach($admittedPatients as $adm)
                                    <option value="{{ $adm->patient_id }}">{{ $adm->patient?->mrn }} - {{ $adm->patient?->full_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label font-size-12 fw-semibold">Bed Location</label>
                            <select name="bed_id" class="form-select font-size-12">
                                <option value="">-- Select Bed --</option>
                                @foreach($icuBeds as $bed)
                                    <option value="{{ $bed->id }}">{{ $bed->bed_number }} ({{ $bed->ward?->name }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label font-size-12 fw-semibold">Total CPR Duration (Minutes) <span class="text-danger">*</span></label>
                            <input type="number" name="cpr_duration_mins" class="form-control" value="6" min="1" max="120" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label font-size-12 fw-semibold">Defibrillation Shocks Delivered <span class="text-danger">*</span></label>
                            <input type="number" name="shocks_delivered" class="form-control" value="1" min="0" max="20" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label font-size-12 fw-semibold">Cardiac Rhythm Identified <span class="text-danger">*</span></label>
                            <select name="cardiac_rhythm" class="form-select font-size-12" required>
                                <option value="Ventricular Fibrillation">Ventricular Fibrillation (Shockable)</option>
                                <option value="Pulseless VT">Pulseless Ventricular Tachycardia (Shockable)</option>
                                <option value="Asystole">Asystole (Non-Shockable)</option>
                                <option value="PEA (Pulseless Electrical Activity)">PEA - Pulseless Electrical Activity</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label font-size-12 fw-semibold">Resuscitation Outcome <span class="text-danger">*</span></label>
                            <select name="outcome" class="form-select font-size-12" required>
                                <option value="resuscitated">Resuscitated (ROSC - Return of Spontaneous Circulation)</option>
                                <option value="ongoing">Resuscitation Ongoing in ICU</option>
                                <option value="deceased">Deceased / CPR Discontinued</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label font-size-12 fw-semibold">Resuscitation Team Notes & Medications Administered <span class="text-danger">*</span></label>
                            <textarea name="resuscitation_notes" class="form-control font-size-12" rows="3" required placeholder="IV Adrenaline 1mg given at 2 min intervals, Amiodarone 300mg, chest compressions 100-120/min, ET tube secured..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger fw-bold"><i class="bx bx-alarm-exclamation me-1"></i> Log Event</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
