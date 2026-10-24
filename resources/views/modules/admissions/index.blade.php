@extends('layouts.skote')

@section('title', 'Patient Master & Admissions (ADT)')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <div>
                <h4 class="mb-sm-0 font-size-18">Patient Master Registry & Inpatient Admissions (ADT)</h4>
                <p class="text-muted font-size-13 mb-0">Demographics, MRN Generation, Bed Occupancy & Discharge History</p>
            </div>
            <div class="page-title-right d-flex gap-2">
                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#newPatientModal">
                    <i class="bx bx-user-plus me-1"></i> Register New Patient
                </button>
                <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#newAdmissionModal">
                    <i class="bx bx-bed me-1"></i> Admit to Bed
                </button>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Active Inpatient Admissions -->
    <div class="col-xl-7">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title mb-3">Active Inpatient Admissions (ADT)</h4>
                <div class="table-responsive">
                    <table class="table align-middle table-hover table-nowrap mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Admission #</th>
                                <th>Patient Name</th>
                                <th>Bed / Ward</th>
                                <th>Admission Type</th>
                                <th>Attending Doctor</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($admissions as $adm)
                                <tr>
                                    <td>
                                        <span class="badge bg-soft-primary text-primary">{{ $adm->admission_number }}</span>
                                        <small class="d-block text-muted">{{ $adm->admission_date->format('d M, H:i') }}</small>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $adm->patient->fullName }}</div>
                                        <small class="text-muted">MRN: {{ $adm->patient->mrn }} • Group: {{ $adm->patient->blood_group }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary">{{ $adm->bed->bed_number }}</span>
                                        <small class="d-block text-muted">{{ $adm->ward->name }}</small>
                                    </td>
                                    <td>
                                        <span class="badge {{ $adm->admission_type === 'emergency' ? 'bg-danger' : 'bg-info' }}">
                                            {{ strtoupper($adm->admission_type) }}
                                        </span>
                                    </td>
                                    <td>{{ $adm->attendingDoctor->name ?? 'Staff Team' }}</td>
                                    <td>
                                        @if($adm->status === 'admitted')
                                            <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#dischargeModal" onclick="document.getElementById('dis_adm_id').value = '{{ $adm->id }}'; document.getElementById('discharge_form').action = '/admissions/' + '{{ $adm->id }}' + '/discharge';">
                                                <i class="bx bx-log-out-circle me-1"></i> Discharge
                                            </button>
                                        @else
                                            <span class="badge bg-secondary font-size-11">Discharged</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">No patient admissions recorded.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Ward Occupancy Overview -->
        <div class="card">
            <div class="card-body">
                <h4 class="card-title mb-3">Hospital Wards & Bed Occupancy Map</h4>
                <div class="row g-3">
                    @foreach($wards as $w)
                        <div class="col-md-6">
                            <div class="border rounded p-3 bg-light">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="mb-0 text-dark font-size-14">{{ $w->name }}</h6>
                                    <span class="badge bg-soft-info text-info">{{ strtoupper($w->ward_type) }}</span>
                                </div>
                                <small class="text-muted d-block mb-2">{{ $w->floor }} • Capacity: {{ $w->capacity }} Beds</small>

                                <div class="d-flex flex-wrap gap-1">
                                    @foreach($w->beds as $b)
                                        <span class="badge {{ $b->status === 'available' ? 'bg-success' : 'bg-danger' }} p-1" title="Bed {{ $b->bed_number }} ({{ $b->status }})">
                                            {{ $b->bed_number }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Patient Master Registry -->
    <div class="col-xl-5">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="card-title mb-0">Patient Master Index</h4>
                    <span class="badge bg-soft-primary text-primary">{{ $patients->total() }} Total Patients</span>
                </div>

                <div class="list-group list-group-flush">
                    @forelse($patients as $pt)
                        <div class="list-group-item px-0 py-2 border-bottom">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <h6 class="mb-0 text-dark font-size-14">{{ $pt->fullName }}</h6>
                                <span class="badge bg-primary font-size-11">{{ $pt->mrn }}</span>
                            </div>
                            <small class="text-muted d-block">
                                {{ ucfirst($pt->gender) }} • Blood Group: <strong class="text-danger">{{ $pt->blood_group ?? 'Unknown' }}</strong> • Phone: {{ $pt->phone ?? 'N/A' }}
                            </small>
                            @if($pt->allergies)
                                <small class="text-danger d-block mt-1">
                                    <i class="bx bx-error-circle me-1"></i> Allergies: {{ $pt->allergies }}
                                </small>
                            @endif
                            <div class="mt-2">
                                <a href="{{ route('patients.history', $pt->id) }}" class="btn btn-xs btn-outline-primary py-0 px-2 font-size-11 rounded-pill">
                                    <i class="bx bx-history me-1"></i> Full Clinical History (EMR)
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="text-muted text-center py-4">No patients registered.</div>
                    @endforelse
                </div>

                <div class="mt-3">
                    {{ $patients->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Register Patient -->
<div class="modal fade" id="newPatientModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('admissions.patients.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Register Patient Master Record</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">First Name <span class="text-danger">*</span></label>
                            <input type="text" name="first_name" class="form-control" required placeholder="e.g. Ramesh">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Last Name <span class="text-danger">*</span></label>
                            <input type="text" name="last_name" class="form-control" required placeholder="e.g. Patel">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Gender <span class="text-danger">*</span></label>
                            <select name="gender" class="form-select" required>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Blood Group</label>
                            <select name="blood_group" class="form-select">
                                <option value="">Select</option>
                                <option value="A+">A+</option>
                                <option value="B+">B+</option>
                                <option value="O+">O+</option>
                                <option value="AB+">AB+</option>
                                <option value="A-">A-</option>
                                <option value="B-">B-</option>
                                <option value="O-">O-</option>
                                <option value="AB-">AB-</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Date of Birth</label>
                            <input type="date" name="date_of_birth" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Contact Phone</label>
                            <input type="text" name="phone" class="form-control" placeholder="+91 98200 00000">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Emergency Contact Name & Phone</label>
                            <input type="text" name="emergency_contact_name" class="form-control" placeholder="Kin / Guardian name">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label text-danger fw-semibold">Known Drug Allergies</label>
                            <input type="text" name="allergies" class="form-control" placeholder="e.g. Penicillin, NSAIDs, Sulfa">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Pre-existing Chronic Conditions</label>
                            <input type="text" name="chronic_conditions" class="form-control" placeholder="e.g. Hypertension, Diabetes, COPD">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Generate MRN & Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Admit to Bed -->
<div class="modal fade" id="newAdmissionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admissions.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Inpatient Admission & Bed Assignment (ADT)</h5>
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
                            <label class="form-label">Ward <span class="text-danger">*</span></label>
                            <select name="ward_id" class="form-select" required>
                                @foreach($wards as $w)
                                    <option value="{{ $w->id }}">{{ $w->name }} ({{ strtoupper($w->ward_type) }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Select Available Bed <span class="text-danger">*</span></label>
                            <select name="bed_id" class="form-select" required>
                                @foreach($availableBeds as $b)
                                    <option value="{{ $b->id }}">{{ $b->bed_number }} ({{ $b->ward->name ?? 'Ward' }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Admission Type <span class="text-danger">*</span></label>
                            <select name="admission_type" class="form-select" required>
                                <option value="emergency">Emergency Trauma</option>
                                <option value="planned">Planned Clinical</option>
                                <option value="transfer">Transfer from other facility</option>
                                <option value="day_care">Day Care</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Attending Doctor</label>
                            <select name="attending_doctor_id" class="form-select">
                                @foreach($doctors as $d)
                                    <option value="{{ $d->id }}">{{ $d->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Admission Clinical Reason <span class="text-danger">*</span></label>
                            <textarea name="admission_reason" class="form-control" rows="2" required placeholder="Provisional clinical condition justifying inpatient admission..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Confirm Bed Admission</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Discharge -->
<div class="modal fade" id="dischargeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="discharge_form" action="" method="POST">
                @csrf
                <input type="hidden" name="admission_id" id="dis_adm_id">
                <div class="modal-header">
                    <h5 class="modal-title">Patient Clinical Discharge</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted">This will record the discharge time, release the assigned hospital bed to available status, and log the clinical discharge summary.</p>
                    <div class="mb-3">
                        <label class="form-label">Discharge Summary & Instructions <span class="text-danger">*</span></label>
                        <textarea name="discharge_summary" class="form-control" rows="3" required placeholder="Discharge vitals stable. Advised home medications. Review in OPD after 7 days."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger"><i class="bx bx-check me-1"></i> Process Discharge</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
