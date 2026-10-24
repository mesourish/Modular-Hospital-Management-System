@extends('layouts.skote')

@section('title', 'Patient Lifetime Clinical History • ' . $patient->full_name)

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <div>
                <h4 class="mb-sm-0 font-size-18">Longitudinal Electronic Medical Record (EMR)</h4>
                <div class="text-muted font-size-13 mt-1">
                    Patient: <strong class="text-dark">{{ $patient->full_name }}</strong> • UHID: <span class="badge bg-primary-subtle text-primary font-mono font-size-12">{{ $patient->mrn }}</span>
                    @if($patient->abha_id)
                        • ABHA ID: <span class="badge bg-success-subtle text-success font-mono">{{ $patient->abha_id }}</span>
                    @endif
                </div>
            </div>
            <div class="page-title-right d-flex gap-2">
                <button type="button" class="btn btn-sm btn-primary waves-effect waves-light" data-bs-toggle="modal" data-bs-target="#editClinicalHistoryModal">
                    <i class="bx bx-edit me-1"></i> Update Clinical Alerts & History
                </button>
                <a href="{{ route('admissions.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bx bx-arrow-back me-1"></i> Back to Patient Master
                </a>
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

<!-- Patient Master Profile Card -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body p-4">
        <div class="row align-items-center">
            <div class="col-md-7">
                <div class="d-flex align-items-center">
                    <div class="avatar-md bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center me-3 font-size-24 fw-bold">
                        {{ strtoupper(substr($patient->first_name, 0, 1)) }}{{ strtoupper(substr($patient->last_name, 0, 1)) }}
                    </div>
                    <div>
                        <h4 class="text-dark mb-1">{{ $patient->full_name }}</h4>
                        <div class="text-muted font-size-13">
                            <strong>Age:</strong> {{ $patient->age }} Years • <strong>Gender:</strong> {{ ucfirst($patient->gender) }} • <strong>DOB:</strong> {{ $patient->date_of_birth ? $patient->date_of_birth->format('d M Y') : 'N/A' }} •
                            <strong>Blood Group:</strong> <span class="badge bg-danger">{{ $patient->blood_group }}</span>
                        </div>
                        <div class="text-muted font-size-12 mt-1">
                            <i class="bx bx-phone me-1"></i> {{ $patient->phone }} • <i class="bx bx-map-pin ms-2 me-1"></i> {{ $patient->address ?? 'Mumbai, MH' }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-5 text-md-end mt-3 mt-md-0">
                <div class="d-inline-block text-start p-2 bg-light rounded border">
                    <div class="font-size-11 text-muted text-uppercase fw-semibold">Emergency Contact</div>
                    <div class="fw-bold font-size-13 text-dark">{{ $patient->emergency_contact_name ?: 'Family Relative' }}</div>
                    <div class="text-secondary font-size-12"><i class="bx bx-phone-call me-1"></i> {{ $patient->emergency_contact_phone ?: '+91 98200 XXXXX' }}</div>
                </div>
            </div>
        </div>

        <!-- Clinical Alerts Banner -->
        <div class="row g-3 mt-3 pt-3 border-top">
            <div class="col-md-3">
                <div class="p-2 rounded bg-danger-subtle border border-danger-subtle">
                    <div class="text-danger fw-bold font-size-11 text-uppercase"><i class="bx bx-error me-1"></i> Drug & Food Allergies</div>
                    <div class="text-dark font-size-13 fw-semibold mt-1">
                        {{ $patient->allergies ?: 'No known drug allergies (NKDA)' }}
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="p-2 rounded bg-warning-subtle border border-warning-subtle">
                    <div class="text-warning-emphasis fw-bold font-size-11 text-uppercase"><i class="bx bx-pulse me-1"></i> Chronic Illnesses</div>
                    <div class="text-dark font-size-13 fw-semibold mt-1">
                        {{ $patient->chronic_conditions ?: 'None recorded' }}
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="p-2 rounded bg-info-subtle border border-info-subtle">
                    <div class="text-info-emphasis fw-bold font-size-11 text-uppercase"><i class="bx bx-cut me-1"></i> Past Surgeries</div>
                    <div class="text-dark font-size-13 fw-semibold mt-1">
                        {{ $patient->past_surgeries ?: 'No past surgical history' }}
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="p-2 rounded bg-secondary-subtle border border-secondary-subtle">
                    <div class="text-secondary fw-bold font-size-11 text-uppercase"><i class="bx bx-dna me-1"></i> Family Medical History</div>
                    <div class="text-dark font-size-13 fw-semibold mt-1">
                        {{ $patient->family_medical_history ?: 'Unremarkable' }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Longitudinal Lifetime Care Timeline -->
<div class="card shadow-sm border-0">
    <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
        <h5 class="card-title mb-0 font-size-16"><i class="bx bx-history text-primary me-2"></i> Chronological Lifetime Patient Journey</h5>
        <span class="badge bg-primary font-size-12 px-3 py-1">Comprehensive Health Record</span>
    </div>
    <div class="card-body p-4">
        <div class="timeline vstack gap-4">

            <!-- 1. OT Surgeries & Surgical Meds Usage -->
            @foreach($patient->otSurgeries as $surgery)
                <div class="border rounded p-3 bg-light-subtle border-start border-start-5 border-start-danger">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <span class="badge bg-danger text-uppercase font-size-11 mb-1"><i class="bx bx-cut me-1"></i> Operation Theater Surgery</span>
                            <h5 class="text-dark mb-1">{{ $surgery->surgery_name }} ({{ $surgery->surgery_code }})</h5>
                            <div class="text-muted font-size-12">
                                Theater: <strong>{{ $surgery->operationTheater->name ?? 'Main OT' }}</strong> •
                                Lead Surgeon: <strong>Dr. {{ $surgery->leadSurgeon->name ?? 'Surgeon' }}</strong> •
                                Anesthetist: <strong>Dr. {{ $surgery->anesthesiologist->name ?? 'Anesthetist' }}</strong> ({{ ucfirst($surgery->anesthesia_type) }})
                            </div>
                        </div>
                        <span class="font-mono text-muted font-size-12">{{ \Carbon\Carbon::parse($surgery->scheduled_start_time)->format('d M Y, h:i A') }}</span>
                    </div>

                    @if($surgery->medicationLogs->count() > 0)
                        <div class="mt-3 pt-2 border-top">
                            <div class="fw-bold font-size-12 text-secondary mb-1">Medications Administered in OT (Stock Deducted):</div>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered bg-white mb-0 font-size-11">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Medicine Administered</th>
                                            <th>Quantity Used</th>
                                            <th>Stage</th>
                                            <th>Batch No</th>
                                            <th>Administered By</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($surgery->medicationLogs as $med)
                                            <tr>
                                                <td class="fw-bold text-dark">{{ $med->medicine_name }}</td>
                                                <td class="font-mono text-danger fw-bold">{{ $med->formatted_quantity }}</td>
                                                <td><span class="badge bg-light text-secondary text-uppercase">{{ str_replace('_', ' ', $med->administration_stage) }}</span></td>
                                                <td class="font-mono">{{ $med->batch_number }}</td>
                                                <td>{{ $med->administered_by }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach

            <!-- 2. Inpatient ADT Admissions -->
            @foreach($patient->admissions as $adm)
                <div class="border rounded p-3 bg-light-subtle border-start border-start-5 border-start-primary">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <span class="badge bg-primary text-uppercase font-size-11 mb-1"><i class="bx bx-bed me-1"></i> Inpatient Admission (ADT)</span>
                            <h5 class="text-dark mb-1">Admitted to {{ $adm->bed->ward->name ?? 'Inpatient Ward' }} (Bed: {{ $adm->bed->bed_number ?? 'General' }})</h5>
                            <div class="text-muted font-size-12">
                                Attending Physician: <strong>Dr. {{ $adm->doctor->name ?? 'Duty Physician' }}</strong> •
                                Admission Reason: {{ $adm->reason_for_admission }}
                            </div>
                        </div>
                        <div class="text-end font-mono text-muted font-size-12">
                            <div>Adm: {{ \Carbon\Carbon::parse($adm->admission_date)->format('d M Y') }}</div>
                            @if($adm->discharge_date)
                                <div class="text-success">Disch: {{ \Carbon\Carbon::parse($adm->discharge_date)->format('d M Y') }}</div>
                            @else
                                <span class="badge bg-success">Currently Admitted</span>
                            @endif
                        </div>
                    </div>
                    @if($adm->discharge_summary)
                        <div class="mt-2 text-dark font-size-12 bg-white p-2 rounded border">
                            <strong>Discharge Summary:</strong> {{ $adm->discharge_summary }}
                        </div>
                    @endif
                </div>
            @endforeach

            <!-- 3. OPD Consultations -->
            @foreach($patient->consultations as $cons)
                <div class="border rounded p-3 bg-light-subtle border-start border-start-5 border-start-info">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <span class="badge bg-info text-uppercase font-size-11 mb-1"><i class="bx bx-clinic me-1"></i> OPD Clinical Consultation</span>
                            <h5 class="text-dark mb-1">Diagnosis: {{ $cons->diagnosis ?: 'General Consultation' }}</h5>
                            <div class="text-muted font-size-12">
                                Consultant: <strong>Dr. {{ $cons->doctor->name ?? 'Consultant' }}</strong> •
                                Chief Complaints: {{ $cons->chief_complaints ?: 'Routine Follow-up' }}
                            </div>
                        </div>
                        <span class="font-mono text-muted font-size-12">{{ \Carbon\Carbon::parse($cons->consultation_time)->format('d M Y, h:i A') }}</span>
                    </div>
                    @if($cons->clinical_notes)
                        <div class="mt-2 text-dark font-size-12 bg-white p-2 rounded border">
                            <strong>Clinical Notes & Advice:</strong> {{ $cons->clinical_notes }}
                        </div>
                    @endif
                </div>
            @endforeach

            <!-- 4. Electronic Prescriptions -->
            @foreach($patient->prescriptions as $rx)
                <div class="border rounded p-3 bg-light-subtle border-start border-start-5 border-start-success">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <span class="badge bg-success text-uppercase font-size-11 mb-1"><i class="bx bx-capsule me-1"></i> Electronic Prescription (Rx)</span>
                            <h5 class="text-dark mb-1">Prescription by Dr. {{ $rx->doctor->name ?? 'Physician' }}</h5>
                            <div class="text-muted font-size-12">
                                Medicine Orders: {{ $rx->medication_details ?? 'Hospital Formulary Dispensed' }}
                            </div>
                        </div>
                        <div class="text-end">
                            <span class="badge {{ $rx->is_dispensed ? 'bg-success' : 'bg-warning' }} font-size-11">
                                {{ $rx->is_dispensed ? 'Dispensed by Pharmacy' : 'Pending Dispense' }}
                            </span>
                            <div class="font-mono text-muted font-size-11 mt-1">{{ \Carbon\Carbon::parse($rx->created_at)->format('d M Y') }}</div>
                        </div>
                    </div>
                </div>
            @endforeach

            <!-- 5. Diagnostic Radiology Scans -->
            @foreach($patient->radiologyOrders as $rad)
                <div class="border rounded p-3 bg-light-subtle border-start border-start-5 border-start-warning">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <span class="badge bg-warning text-uppercase font-size-11 mb-1"><i class="bx bx-scan me-1"></i> Diagnostic Radiology (RIS/PACS)</span>
                            <h5 class="text-dark mb-1">{{ $rad->modality->name ?? 'Radiology Scan' }} - {{ $rad->body_part }}</h5>
                            <div class="text-muted font-size-12">
                                Indication: {{ $rad->clinical_indication }}
                            </div>
                        </div>
                        <span class="font-mono text-muted font-size-12">{{ \Carbon\Carbon::parse($rad->created_at)->format('d M Y') }}</span>
                    </div>
                </div>
            @endforeach

            <!-- 6. Invoices & Billing Receipts -->
            @foreach($patient->invoices as $inv)
                <div class="border rounded p-3 bg-light-subtle border-start border-start-5 border-start-secondary">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="badge bg-secondary text-uppercase font-size-11 mb-1"><i class="bx bx-receipt me-1"></i> GST Tax Invoice</span>
                            <h5 class="text-dark mb-1">Invoice #{{ $inv->invoice_number }} • ₹{{ number_format($inv->net_payable, 2) }}</h5>
                            <div class="text-muted font-size-12">
                                Tax Amount: ₹{{ number_format($inv->tax_amount, 2) }} • Paid: ₹{{ number_format($inv->paid_amount, 2) }} •
                                Status: <span class="badge {{ $inv->payment_status === 'paid' ? 'bg-success' : 'bg-warning' }}">{{ strtoupper($inv->payment_status) }}</span>
                            </div>
                        </div>
                        <div>
                            <a href="{{ route('billing.invoices.print', $inv->id) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="bx bx-printer me-1"></i> View GST Bill
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach

        </div>
    </div>
</div>

<!-- Modal: Edit Clinical Alerts & History -->
<div class="modal fade" id="editClinicalHistoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('patients.history.update', $patient->id) }}" method="POST">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white"><i class="bx bx-edit me-2"></i> Update Clinical Alerts & Longitudinal History</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label font-size-12 fw-semibold text-danger">Drug & Food Allergies</label>
                            <textarea name="allergies" rows="2" class="form-control" placeholder="e.g. Penicillin, NSAIDs (Aspirin, Ibuprofen), Sulfa Antibiotics">{{ $patient->allergies }}</textarea>
                            <small class="text-muted">High alert: Displayed prominently on POS and surgery verification desks.</small>
                        </div>

                        <div class="col-12">
                            <label class="form-label font-size-12 fw-semibold text-warning-emphasis">Chronic Conditions & Comorbidities</label>
                            <textarea name="chronic_conditions" rows="2" class="form-control" placeholder="e.g. Type-2 Diabetes Mellitus, Essential Hypertension, Chronic Kidney Disease Stage-2">{{ $patient->chronic_conditions }}</textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label font-size-12 fw-semibold text-info-emphasis">Past Surgical Procedures</label>
                            <textarea name="past_surgeries" rows="2" class="form-control" placeholder="e.g. Laparoscopic Appendectomy (2018), Coronary Stenting (2022)">{{ $patient->past_surgeries }}</textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label font-size-12 fw-semibold text-secondary">Family Medical History</label>
                            <textarea name="family_medical_history" rows="2" class="form-control" placeholder="e.g. Maternal history of breast carcinoma; Father had early myocardial infarction">{{ $patient->family_medical_history }}</textarea>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label font-size-12 fw-semibold">Emergency Contact Person</label>
                            <input type="text" name="emergency_contact_name" class="form-control" value="{{ $patient->emergency_contact_name }}" placeholder="Contact Name">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label font-size-12 fw-semibold">Emergency Contact Mobile</label>
                            <input type="text" name="emergency_contact_phone" class="form-control" value="{{ $patient->emergency_contact_phone }}" placeholder="+91 98200 XXXXX">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-bold"><i class="bx bx-check me-1"></i> Save Clinical Profile</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
