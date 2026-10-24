@extends('layouts.skote')

@section('title', 'OPD & Clinical Consultation')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <div>
                <h4 class="mb-sm-0 font-size-18">OPD Queue & Doctor Consultation</h4>
                <p class="text-muted font-size-13 mb-0">Outpatient Triage, Encounter Records & Electronic Prescriptions</p>
            </div>
            <div class="page-title-right d-flex gap-2">
                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#newApptModal">
                    <i class="bx bx-calendar-plus me-1"></i> Book Appointment
                </button>
                <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#consultationModal">
                    <i class="bx bx-notepad me-1"></i> Start Consultation Encounter
                </button>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Appointments Queue -->
    <div class="col-xl-6">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title mb-3">Today's OPD Queue & Scheduled Appointments</h4>
                <div class="table-responsive">
                    <table class="table align-middle table-hover table-nowrap mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Token</th>
                                <th>Patient Name</th>
                                <th>Doctor</th>
                                <th>Department</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($appointments as $appt)
                                <tr>
                                    <td><span class="badge bg-primary font-size-13 px-2">#{{ $appt->token_number }}</span></td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $appt->patient->fullName }}</div>
                                        <small class="text-muted">MRN: {{ $appt->patient->mrn }}</small>
                                    </td>
                                    <td>{{ $appt->doctor->name ?? 'Doctor' }}</td>
                                    <td><span class="badge bg-soft-info text-info">{{ $appt->department->name ?? 'OPD' }}</span></td>
                                    <td>
                                        @if($appt->status === 'completed')
                                            <span class="badge bg-success">Completed</span>
                                        @else
                                            <span class="badge bg-warning text-dark">{{ ucfirst(str_replace('_', ' ', $appt->status)) }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($appt->status !== 'completed')
                                            <button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#consultationModal" onclick="document.getElementById('c_appt_id').value = '{{ $appt->id }}'; document.getElementById('c_patient_id').value = '{{ $appt->patient_id }}'; document.getElementById('c_doctor_id').value = '{{ $appt->doctor_id }}';">
                                                <i class="bx bx-stethoscope me-1"></i> Consult
                                            </button>
                                        @else
                                            <span class="text-muted font-size-12">Closed</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">No appointments booked.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Consultation Records -->
    <div class="col-xl-6">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title mb-3">Recent Clinical Encounters & Prescriptions</h4>
                <div class="list-group list-group-flush">
                    @forelse($consultations as $c)
                        <div class="list-group-item px-0 py-3 border-bottom">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <h6 class="mb-0 text-dark font-size-14">{{ $c->patient->fullName }}</h6>
                                <span class="badge bg-soft-primary text-primary">{{ $c->encounter_date->format('d M Y, H:i') }}</span>
                            </div>
                            <small class="text-muted d-block mb-1">
                                Attending: <strong>{{ $c->doctor->name ?? 'Clinician' }}</strong> •
                                Vitals: BP {{ $c->bp ?? '—' }} | Pulse {{ $c->pulse ?? '—' }} | Temp {{ $c->temperature ?? '—' }}
                            </small>
                            <div class="p-2 bg-light rounded font-size-13 mb-2">
                                <span class="fw-bold text-dark">Diagnosis:</span> {{ $c->diagnosis }}
                                @if($c->chief_complaints)
                                    <div class="text-muted font-size-12 mt-1">Complaints: {{ $c->chief_complaints }}</div>
                                @endif
                            </div>
                            @if($c->prescriptions->count() > 0)
                                <div class="font-size-12">
                                    <span class="text-primary fw-semibold"><i class="bx bx-receipt me-1"></i> Rx:</span>
                                    @foreach($c->prescriptions as $rx)
                                        <span class="badge bg-soft-secondary text-secondary me-1">
                                            {{ $rx->medicine_name }} ({{ $rx->dosage }}, {{ $rx->frequency }})
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="text-center py-4 text-muted">No completed consultation encounters.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Book Appointment -->
<div class="modal fade" id="newApptModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('opd.appointments.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Book Outpatient Appointment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Patient <span class="text-danger">*</span></label>
                            <select name="patient_id" class="form-select" required>
                                @foreach($patients as $p)
                                    <option value="{{ $p->id }}">{{ $p->fullName }} ({{ $p->mrn }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Consulting Doctor <span class="text-danger">*</span></label>
                            <select name="doctor_id" class="form-select" required>
                                @foreach($doctors as $doc)
                                    <option value="{{ $doc->id }}">{{ $doc->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Department</label>
                            <select name="department_id" class="form-select">
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Appointment Date <span class="text-danger">*</span></label>
                            <input type="date" name="appointment_date" class="form-control" value="{{ now()->toDateString() }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Appointment Time</label>
                            <input type="time" name="appointment_time" class="form-control" value="10:00">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Presenting Symptoms</label>
                            <input type="text" name="symptoms" class="form-control" placeholder="e.g. Headache, high fever, throat pain">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Generate Token & Book</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Start Consultation Encounter -->
<div class="modal fade" id="consultationModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('opd.consultations.store') }}" method="POST">
                @csrf
                <input type="hidden" name="appointment_id" id="c_appt_id">
                <div class="modal-header">
                    <h5 class="modal-title">Clinical Consultation Encounter & Rx</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Patient <span class="text-danger">*</span></label>
                            <select name="patient_id" id="c_patient_id" class="form-select" required>
                                @foreach($patients as $p)
                                    <option value="{{ $p->id }}">{{ $p->fullName }} ({{ $p->mrn }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Attending Doctor <span class="text-danger">*</span></label>
                            <select name="doctor_id" id="c_doctor_id" class="form-select" required>
                                @foreach($doctors as $doc)
                                    <option value="{{ $doc->id }}">{{ $doc->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Vitals -->
                        <div class="col-12">
                            <h6 class="font-size-13 text-muted text-uppercase fw-semibold border-bottom pb-1 mb-2">Triage Vitals</h6>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Blood Pressure</label>
                            <input type="text" name="bp" class="form-control" placeholder="120/80">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Pulse (bpm)</label>
                            <input type="text" name="pulse" class="form-control" placeholder="72">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Temperature</label>
                            <input type="text" name="temperature" class="form-control" placeholder="98.6 F">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">SpO2 (%)</label>
                            <input type="text" name="spo2" class="form-control" placeholder="99%">
                        </div>

                        <!-- Clinical Findings -->
                        <div class="col-md-6">
                            <label class="form-label">Chief Complaints <span class="text-danger">*</span></label>
                            <textarea name="chief_complaints" class="form-control" rows="2" required placeholder="Patient reported history..."></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Clinical Diagnosis <span class="text-danger">*</span></label>
                            <textarea name="diagnosis" class="form-control" rows="2" required placeholder="Definitive or provisional diagnosis..."></textarea>
                        </div>

                        <!-- Electronic Prescription -->
                        <div class="col-12 mt-3">
                            <h6 class="font-size-13 text-primary text-uppercase fw-semibold border-bottom pb-1 mb-2">Electronic Prescription (Rx)</h6>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Medicine</label>
                            <input type="text" name="medicine_name" class="form-control" placeholder="e.g. Paracetamol 650mg">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Dosage</label>
                            <input type="text" name="dosage" class="form-control" placeholder="650mg">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Frequency</label>
                            <input type="text" name="frequency" class="form-control" placeholder="1-0-1 after food">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Duration</label>
                            <input type="text" name="duration" class="form-control" placeholder="5 days">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success"><i class="bx bx-check me-1"></i> Finalize Encounter</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
