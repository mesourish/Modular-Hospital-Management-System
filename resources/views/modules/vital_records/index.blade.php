@extends('layouts.skote')

@section('title', 'Statutory Vital Records: Birth & Death Registry')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <div>
                <h4 class="mb-sm-0 font-size-18">Vital Records Registry (Births & Deaths)</h4>
                <div class="text-muted font-size-13 mt-1">
                    Hospital: <span class="badge bg-primary-subtle text-primary fw-bold">{{ $hospital->name ?? 'Apollo Care' }}</span>
                    • Statutory Registration of Births and Deaths Act (Form 1 & Form 4 MCCD)
                </div>
            </div>
            <div class="page-title-right d-flex gap-2">
                <button type="button" class="btn btn-sm btn-success waves-effect waves-light" data-bs-toggle="modal" data-bs-target="#newBirthModal">
                    <i class="bx bx-plus-circle me-1"></i> Register Birth Record
                </button>
                <button type="button" class="btn btn-sm btn-danger waves-effect waves-light" data-bs-toggle="modal" data-bs-target="#newDeathModal">
                    <i class="bx bx-plus-circle me-1"></i> Register Death Record
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

<!-- Vital Records Analytics Cards -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card mini-stats-wid h-100 shadow-sm border-0">
            <div class="card-body">
                <div class="d-flex">
                    <div class="flex-grow-1">
                        <p class="text-muted fw-medium font-size-12 mb-1 text-uppercase">Total Births Registered</p>
                        <h4 class="mb-0 font-mono font-size-22 text-success">{{ $totalBirths }}</h4>
                        <div class="text-muted font-size-11 mt-1">
                            👦 {{ $maleBirths }} Male • 👧 {{ $femaleBirths }} Female
                        </div>
                    </div>
                    <div class="avatar-sm rounded-circle bg-success-subtle text-success align-self-center mini-stat-icon">
                        <span class="avatar-title rounded-circle bg-success font-size-18">
                            <i class="bx bx-child"></i>
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
                        <p class="text-muted fw-medium font-size-12 mb-1 text-uppercase">LSCS / Caesarean Rate</p>
                        <h4 class="mb-0 font-mono font-size-22 text-primary">{{ $totalBirths > 0 ? round(($lscsCount / $totalBirths) * 100, 1) : 0 }}%</h4>
                        <div class="text-muted font-size-11 mt-1">{{ $lscsCount }} Caesarean • {{ $totalBirths - $lscsCount }} Normal</div>
                    </div>
                    <div class="avatar-sm rounded-circle bg-primary-subtle text-primary align-self-center mini-stat-icon">
                        <span class="avatar-title rounded-circle bg-primary font-size-18">
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
                        <p class="text-muted fw-medium font-size-12 mb-1 text-uppercase">Total Deaths Certified</p>
                        <h4 class="mb-0 font-mono font-size-22 text-danger">{{ $totalDeaths }}</h4>
                        <div class="text-muted font-size-11 mt-1">Form 4 MCCD Certified</div>
                    </div>
                    <div class="avatar-sm rounded-circle bg-danger-subtle text-danger align-self-center mini-stat-icon">
                        <span class="avatar-title rounded-circle bg-danger font-size-18">
                            <i class="bx bx-cross"></i>
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
                        <p class="text-muted fw-medium font-size-12 mb-1 text-uppercase">Statutory Compliance</p>
                        <h4 class="mb-0 font-size-18 text-dark">100% Digital</h4>
                        <div class="text-success font-size-11 mt-1"><i class="bx bx-check-shield me-1"></i> QR Verified Certificates</div>
                    </div>
                    <div class="avatar-sm rounded-circle bg-info-subtle text-info align-self-center mini-stat-icon">
                        <span class="avatar-title rounded-circle bg-info font-size-18">
                            <i class="bx bx-certification"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tabs: Birth Records vs Death Records -->
<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <ul class="nav nav-tabs nav-tabs-custom nav-justified" role="tablist">
            <li class="nav-item">
                <a class="nav-link active py-3" data-bs-toggle="tab" href="#birthsTab" role="tab">
                    <i class="bx bx-child me-1 font-size-16 align-middle text-success"></i>
                    <span class="fw-semibold">Birth Records & Maternity Certificates ({{ $totalBirths }})</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link py-3" data-bs-toggle="tab" href="#deathsTab" role="tab">
                    <i class="bx bx-cross me-1 font-size-16 align-middle text-danger"></i>
                    <span class="fw-semibold">Death Records & MCCD Certification ({{ $totalDeaths }})</span>
                </a>
            </li>
        </ul>

        <div class="tab-content p-4">
            <!-- TAB 1: BIRTH RECORDS -->
            <div class="tab-pane active" id="birthsTab" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 font-size-13">
                        <thead class="table-light">
                            <tr>
                                <th>Certificate No</th>
                                <th>Baby Name & Gender</th>
                                <th>Birth Date & Time</th>
                                <th>Mother & Father</th>
                                <th>Weight</th>
                                <th>Delivery Type</th>
                                <th>APGAR (1m/5m)</th>
                                <th>Attending Doctors</th>
                                <th class="text-end">Certificate</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($birthRecords as $birth)
                                <tr>
                                    <td class="font-mono fw-bold text-primary">{{ $birth->certificate_number }}</td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $birth->baby_name }}</div>
                                        <span class="badge {{ $birth->gender === 'male' ? 'bg-primary-subtle text-primary' : 'bg-danger-subtle text-danger' }} text-capitalize">
                                            {{ $birth->gender }}
                                        </span>
                                        <span class="badge bg-light text-secondary">{{ $birth->blood_group }}</span>
                                    </td>
                                    <td>
                                        <div>{{ \Carbon\Carbon::parse($birth->birth_date)->format('d M Y') }}</div>
                                        <small class="text-muted">{{ $birth->birth_time }}</small>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $birth->mother_name }}</div>
                                        @if($birth->mother)
                                            <small class="text-primary font-mono"><a href="{{ route('patients.history', $birth->mother->id) }}">{{ $birth->mother->mrn }} (History)</a></small><br>
                                        @endif
                                        <small class="text-muted">Father: {{ $birth->father_name ?? 'Not Listed' }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark font-mono font-size-12 border">
                                            {{ $birth->birth_weight_kg }} kg
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge {{ $birth->delivery_type === 'lscs_caesarean' ? 'bg-warning-subtle text-warning' : 'bg-success-subtle text-success' }} text-uppercase font-size-11">
                                            {{ str_replace('_', ' ', $birth->delivery_type) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="font-mono fw-bold {{ $birth->apgar_score_5min >= 9 ? 'text-success' : 'text-danger' }}">
                                            {{ $birth->apgar_score_1min }} / {{ $birth->apgar_score_5min }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="text-dark font-size-12">Obs: Dr. {{ $birth->obstetrician->name ?? 'Staff Obs' }}</div>
                                        @if($birth->pediatrician)
                                            <div class="text-muted font-size-11">Ped: Dr. {{ $birth->pediatrician->name }}</div>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('vital_records.birth_certificate', $birth->id) }}" target="_blank" class="btn btn-sm btn-outline-success">
                                            <i class="bx bx-printer me-1"></i> Print Certificate
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4 text-muted">
                                        No birth records registered yet. Click "Register Birth Record" to issue the first certificate.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TAB 2: DEATH RECORDS -->
            <div class="tab-pane" id="deathsTab" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 font-size-13">
                        <thead class="table-light">
                            <tr>
                                <th>Certificate No</th>
                                <th>Patient Name & MRN</th>
                                <th>Date & Time</th>
                                <th>Place of Death</th>
                                <th>Primary Cause & ICD-10</th>
                                <th>Certifying Clinician</th>
                                <th>Next of Kin</th>
                                <th>Cremation Pass</th>
                                <th class="text-end">Certificate</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($deathRecords as $death)
                                <tr>
                                    <td class="font-mono fw-bold text-danger">{{ $death->certificate_number }}</td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $death->patient->full_name ?? 'Patient' }}</div>
                                        <span class="badge bg-light text-secondary font-mono">{{ $death->patient->mrn ?? '' }}</span>
                                        <a href="{{ route('patients.history', $death->patient_id) }}" class="ms-1 font-size-11 text-primary">View EMR</a>
                                    </td>
                                    <td>
                                        <div>{{ \Carbon\Carbon::parse($death->date_of_death)->format('d M Y') }}</div>
                                        <small class="text-muted">{{ $death->time_of_death }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-info-subtle text-info">{{ $death->place_of_death }}</span>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $death->primary_cause }}</div>
                                        <span class="badge bg-light text-danger border font-mono">ICD-10: {{ $death->icd10_code }}</span>
                                    </td>
                                    <td>
                                        <div class="text-dark">Dr. {{ $death->certifyingDoctor->name ?? 'Duty Medical Officer' }}</div>
                                        <small class="text-muted">Reg No: MMC-2015-0918</small>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $death->next_of_kin_name }}</div>
                                        <small class="text-muted">{{ $death->next_of_kin_relation }} • {{ $death->next_of_kin_phone }}</small>
                                    </td>
                                    <td>
                                        @if($death->cremation_pass_issued)
                                            <span class="badge bg-success font-size-11"><i class="bx bx-check me-1"></i> Issued</span>
                                        @else
                                            <span class="badge bg-secondary font-size-11">Pending</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('vital_records.death_certificate', $death->id) }}" target="_blank" class="btn btn-sm btn-outline-danger">
                                            <i class="bx bx-printer me-1"></i> Print MCCD Form 4
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4 text-muted">
                                        No death records registered.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal 1: Register Birth Record -->
<div class="modal fade" id="newBirthModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('vital_records.births.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title text-white"><i class="bx bx-child me-2"></i> Register New Birth & Generate Certificate</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label font-size-12 fw-semibold">Mother (Patient Inpatient / EMR)</label>
                            <select name="mother_id" class="form-select font-size-13" onchange="document.getElementById('modalMotherName').value = this.options[this.selectedIndex].text.split(' - ')[1] || ''">
                                <option value="">-- Non-Registered / Walk-in Mother --</option>
                                @foreach($patients->where('gender', 'female') as $pat)
                                    <option value="{{ $pat->id }}">{{ $pat->mrn }} - {{ $pat->full_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label font-size-12 fw-semibold">Mother Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="mother_name" id="modalMotherName" class="form-control" required placeholder="Full Name of Mother">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label font-size-12 fw-semibold">Father Full Name</label>
                            <input type="text" name="father_name" class="form-control" placeholder="Full Name of Father">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label font-size-12 fw-semibold">Baby Name / Label <span class="text-danger">*</span></label>
                            <input type="text" name="baby_name" class="form-control" value="Baby of Patient" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label font-size-12 fw-semibold">Gender <span class="text-danger">*</span></label>
                            <select name="gender" class="form-select" required>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label font-size-12 fw-semibold">Birth Date <span class="text-danger">*</span></label>
                            <input type="date" name="birth_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label font-size-12 fw-semibold">Birth Time <span class="text-danger">*</span></label>
                            <input type="time" name="birth_time" class="form-control" value="{{ date('H:i') }}" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label font-size-12 fw-semibold">Birth Weight (kg) <span class="text-danger">*</span></label>
                            <input type="number" step="0.05" min="0.5" max="8" name="birth_weight_kg" class="form-control" value="3.15" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label font-size-12 fw-semibold">Delivery Type <span class="text-danger">*</span></label>
                            <select name="delivery_type" class="form-select" required>
                                <option value="normal_vaginal">Normal Vaginal Delivery</option>
                                <option value="lscs_caesarean">Lower Segment Caesarean (LSCS)</option>
                                <option value="vacuum_instrumental">Vacuum / Instrumental Delivery</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label font-size-12 fw-semibold">Gestational Weeks <span class="text-danger">*</span></label>
                            <input type="number" min="24" max="44" name="gestational_age_weeks" class="form-control" value="39" required>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label font-size-12 fw-semibold">APGAR (1 min)</label>
                            <input type="number" min="0" max="10" name="apgar_score_1min" class="form-control" value="9" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label font-size-12 fw-semibold">APGAR (5 min)</label>
                            <input type="number" min="0" max="10" name="apgar_score_5min" class="form-control" value="10" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label font-size-12 fw-semibold">Blood Group</label>
                            <select name="blood_group" class="form-select">
                                <option value="O+">O+</option>
                                <option value="A+">A+</option>
                                <option value="B+">B+</option>
                                <option value="AB+">AB+</option>
                                <option value="O-">O-</option>
                                <option value="A-">A-</option>
                                <option value="B-">B-</option>
                                <option value="AB-">AB-</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label font-size-12 fw-semibold">Branch Location</label>
                            <select name="branch_id" class="form-select">
                                @foreach($branches as $br)
                                    <option value="{{ $br->id }}">{{ $br->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label font-size-12 fw-semibold">Attending Obstetrician / Gynecologist <span class="text-danger">*</span></label>
                            <select name="doctor_id" class="form-select" required>
                                @foreach($doctors as $doc)
                                    <option value="{{ $doc->id }}">Dr. {{ $doc->name }} ({{ ucfirst($doc->role) }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label font-size-12 fw-semibold">Attending Pediatrician</label>
                            <select name="pediatrician_id" class="form-select">
                                <option value="">-- None / On-Call Pediatrician --</option>
                                @foreach($doctors as $doc)
                                    <option value="{{ $doc->id }}">Dr. {{ $doc->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success fw-bold"><i class="bx bx-check me-1"></i> Register & Generate Certificate</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal 2: Register Death Record -->
<div class="modal fade" id="newDeathModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('vital_records.deaths.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title text-white"><i class="bx bx-cross me-2"></i> Register Death & Issue MCCD Form 4</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label font-size-12 fw-semibold">Deceased Patient <span class="text-danger">*</span></label>
                            <select name="patient_id" class="form-select font-size-13" required>
                                <option value="">-- Select Patient --</option>
                                @foreach($patients as $pat)
                                    <option value="{{ $pat->id }}">{{ $pat->mrn }} - {{ $pat->full_name }} (Age: {{ $pat->age }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label font-size-12 fw-semibold">Certifying Physician <span class="text-danger">*</span></label>
                            <select name="certifying_doctor_id" class="form-select font-size-13" required>
                                @foreach($doctors as $doc)
                                    <option value="{{ $doc->id }}">Dr. {{ $doc->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label font-size-12 fw-semibold">Date of Death <span class="text-danger">*</span></label>
                            <input type="date" name="date_of_death" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label font-size-12 fw-semibold">Time of Death <span class="text-danger">*</span></label>
                            <input type="time" name="time_of_death" class="form-control" value="{{ date('H:i') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label font-size-12 fw-semibold">Place of Death <span class="text-danger">*</span></label>
                            <select name="place_of_death" class="form-select" required>
                                <option value="Intensive Care Unit (ICU)">Intensive Care Unit (ICU)</option>
                                <option value="Emergency Room (ER)">Emergency Room (ER)</option>
                                <option value="Operation Theater (OT)">Operation Theater (OT)</option>
                                <option value="Inpatient Ward">Inpatient Ward</option>
                                <option value="Brought Dead / Casualty">Brought Dead / Casualty</option>
                            </select>
                        </div>

                        <div class="col-md-8">
                            <label class="form-label font-size-12 fw-semibold">Primary Immediate Cause of Death <span class="text-danger">*</span></label>
                            <input type="text" name="primary_cause" class="form-control" required placeholder="e.g. Cardiorespiratory Arrest / Refractory Septic Shock">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label font-size-12 fw-semibold">ICD-10 Code <span class="text-danger">*</span></label>
                            <input type="text" name="icd10_code" class="form-control font-mono" value="I46.9" required placeholder="e.g. I46.9, R57.2">
                        </div>

                        <div class="col-12">
                            <label class="form-label font-size-12 fw-semibold">Antecedent / Secondary Underlying Cause</label>
                            <input type="text" name="secondary_cause" class="form-control" placeholder="e.g. Acute Myocardial Infarction / Severe Community Acquired Pneumonia">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label font-size-12 fw-semibold">Next of Kin Name <span class="text-danger">*</span></label>
                            <input type="text" name="next_of_kin_name" class="form-control" required placeholder="Family Contact Name">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label font-size-12 fw-semibold">Next of Kin Phone</label>
                            <input type="text" name="next_of_kin_phone" class="form-control" placeholder="+91 98200 XXXXX">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label font-size-12 fw-semibold">Relationship</label>
                            <select name="next_of_kin_relation" class="form-select">
                                <option value="Spouse">Spouse</option>
                                <option value="Son">Son</option>
                                <option value="Daughter">Daughter</option>
                                <option value="Father">Father</option>
                                <option value="Mother">Mother</option>
                                <option value="Brother">Brother</option>
                                <option value="Legal Guardian">Legal Guardian</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" name="cremation_pass_issued" id="cremationPass" checked>
                                <label class="form-check-label font-size-13" for="cremationPass">Issue Cremation / Burial Pass Permit</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" name="autopsy_required" id="autopsyReq">
                                <label class="form-check-label font-size-13 text-danger" for="autopsyReq">Medico-Legal Case (MLC / Autopsy Required)</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger fw-bold"><i class="bx bx-check me-1"></i> Certify Death & Issue Form 4</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
