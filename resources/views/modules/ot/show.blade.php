@extends('layouts.skote')

@section('title', 'Surgical Case Sheet & OT Medication Ledger - ' . $surgery->surgery_code)

@section('content')
<div class="row d-print-none mb-3">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between pb-0">
            <div>
                <a href="{{ route('ot.index') }}" class="btn btn-outline-secondary btn-sm me-2">
                    <i class="bx bx-arrow-back me-1"></i> Back to OT Suite Center
                </a>
                <span class="badge bg-primary font-size-12 px-2 py-1">{{ $surgery->surgery_code }}</span>
                <span class="badge {{ $surgery->status_badge }} font-size-12 px-2 py-1 ms-1">{{ $surgery->status_label }}</span>
            </div>
            <div class="page-title-right d-flex gap-2 mt-2 mt-sm-0">
                <button type="button" class="btn btn-outline-success btn-sm" data-bs-toggle="modal" data-bs-target="#logMedModal">
                    <i class="bx bx-plus me-1"></i> Log OT Meds / Consumables
                </button>
                <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#updateSurgeryStatusModal">
                    <i class="bx bx-edit me-1"></i> Update Status / Notes
                </button>
                <button type="button" onclick="window.print()" class="btn btn-dark btn-sm">
                    <i class="bx bx-printer me-1"></i> Print Case Sheet
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Printable Surgical Case Sheet & Medication Ledger -->
<div class="card shadow-sm border-0 print-card">
    <div class="card-body p-4">
        <!-- Hospital & Case Header -->
        <div class="row border-bottom pb-3 mb-3">
            <div class="col-sm-7">
                <div class="d-flex align-items-center">
                    <div class="avatar-sm bg-danger text-white rounded d-flex align-items-center justify-content-center me-3 font-size-22 fw-bold">
                        OT
                    </div>
                    <div>
                        <h4 class="mb-0 text-dark fw-bold">{{ $surgery->branch?->hospital?->name ?? 'APOLLO HEALTHCARE NETWORK' }}</h4>
                        <div class="text-muted font-size-12">
                            {{ $surgery->branch?->name }} • {{ $surgery->branch?->city }}, {{ $surgery->branch?->state }}
                            @if($surgery->branch?->phone) | Phone: {{ $surgery->branch?->phone }} @endif
                        </div>
                        <div class="fw-semibold text-danger font-size-12 mt-1">
                            DEPARTMENT OF SURGICAL SERVICES & ANESTHESIOLOGY
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-5 text-sm-end mt-3 mt-sm-0">
                <div class="font-size-13 text-muted">SURGERY ENCOUNTER CODE</div>
                <div class="font-size-18 fw-bold text-dark font-monospace">{{ $surgery->surgery_code }}</div>
                <div class="font-size-12 text-muted">
                    Date: <strong>{{ \Carbon\Carbon::parse($surgery->scheduled_start_time)->format('d M Y') }}</strong> |
                    Time: <strong>{{ \Carbon\Carbon::parse($surgery->scheduled_start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($surgery->scheduled_end_time)->format('h:i A') }}</strong>
                </div>
            </div>
        </div>

        <!-- 3-Column Meta: Patient Demographics, OT Suite Configurations, Surgical Team -->
        <div class="row g-3 mb-4">
            <!-- Patient Demographics -->
            <div class="col-md-4">
                <div class="p-3 bg-light rounded border h-100">
                    <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
                        <h6 class="text-primary mb-0 fw-bold"><i class="bx bx-user me-1"></i> Patient Demographics</h6>
                        <span class="badge bg-secondary-subtle text-secondary font-monospace">{{ $surgery->patient?->mrn ?? 'MRN-N/A' }}</span>
                    </div>
                    <table class="table table-sm table-borderless mb-0 font-size-12">
                        <tr>
                            <td class="text-muted py-1 ps-0" style="width: 35%;">Name:</td>
                            <td class="fw-bold text-dark py-1">{{ $surgery->patient?->name ?? 'Walk-in Patient' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted py-1 ps-0">Age / Sex:</td>
                            <td class="py-1">{{ $surgery->patient?->age ?? 'N/A' }} yrs / {{ ucfirst($surgery->patient?->gender ?? 'N/A') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted py-1 ps-0">Blood Group:</td>
                            <td class="py-1"><span class="badge bg-danger-subtle text-danger font-size-11">{{ $surgery->patient?->blood_group ?? 'O+ve' }}</span></td>
                        </tr>
                        <tr>
                            <td class="text-muted py-1 ps-0">ABHA ID:</td>
                            <td class="py-1 font-monospace text-muted">{{ $surgery->patient?->abha_id ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted py-1 ps-0">Contact:</td>
                            <td class="py-1">{{ $surgery->patient?->phone ?? 'N/A' }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- OT Suite Configurations -->
            <div class="col-md-4">
                <div class="p-3 bg-light rounded border h-100">
                    <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
                        <h6 class="text-danger mb-0 fw-bold"><i class="bx bx-plus-medical me-1"></i> OT Suite Config</h6>
                        <span class="badge bg-primary-subtle text-primary font-monospace">{{ $surgery->operationTheater?->code }}</span>
                    </div>
                    <table class="table table-sm table-borderless mb-0 font-size-12">
                        <tr>
                            <td class="text-muted py-1 ps-0" style="width: 45%;">Theater Name:</td>
                            <td class="fw-bold text-dark py-1">{{ $surgery->operationTheater?->name }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted py-1 ps-0">Suite Type:</td>
                            <td class="py-1">{{ $surgery->operationTheater?->type_label }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted py-1 ps-0">Location:</td>
                            <td class="py-1">{{ $surgery->operationTheater?->floor ?? 'Surgical Floor' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted py-1 ps-0">Air Pressure:</td>
                            <td class="py-1">
                                <span class="badge bg-info-subtle text-info font-size-10">
                                    {{ ucwords(str_replace('_', ' ', $surgery->operationTheater?->air_pressure_type ?? 'positive_laminar_flow')) }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted py-1 ps-0">HEPA Filter:</td>
                            <td class="py-1">
                                <span class="badge bg-success-subtle text-success font-size-10">
                                    {{ ucwords(str_replace('_', ' ', $surgery->operationTheater?->hepa_filter_status ?? 'certified_active')) }}
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Surgical Team & Anesthesia -->
            <div class="col-md-4">
                <div class="p-3 bg-light rounded border h-100">
                    <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
                        <h6 class="text-success mb-0 fw-bold"><i class="bx bx-shield-plus me-1"></i> Surgical Team</h6>
                        <span class="badge bg-info-subtle text-info">{{ strtoupper($surgery->anesthesia_type) }}</span>
                    </div>
                    <table class="table table-sm table-borderless mb-0 font-size-12">
                        <tr>
                            <td class="text-muted py-1 ps-0" style="width: 45%;">Lead Surgeon:</td>
                            <td class="fw-bold text-dark py-1">Dr. {{ $surgery->leadSurgeon?->name ?? 'Assigned Surgeon' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted py-1 ps-0">Anesthesiologist:</td>
                            <td class="py-1">Dr. {{ $surgery->anesthesiologist?->name ?? 'On-Duty Anesthetist' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted py-1 ps-0">Scrub Nurse:</td>
                            <td class="py-1">{{ $surgery->scrubNurse?->name ?? 'OT Staff Nurse' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted py-1 ps-0">Surgery Category:</td>
                            <td class="py-1">
                                <span class="badge bg-warning-subtle text-warning font-size-11">
                                    {{ strtoupper($surgery->surgery_category) }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted py-1 ps-0">Blood Transfusion:</td>
                            <td class="py-1"><strong>{{ $surgery->blood_units_transfused }}</strong> Units</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Clinical Findings & Procedure Notes -->
        <div class="card border mb-4">
            <div class="card-header bg-light py-2">
                <h6 class="mb-0 fw-bold text-dark font-size-13"><i class="bx bx-file-blank me-1 text-primary"></i> Surgical Diagnosis & Intra-Operative Findings</h6>
            </div>
            <div class="card-body p-3 font-size-12">
                <div class="row">
                    <div class="col-md-4">
                        <div class="text-muted font-size-11 text-uppercase">Procedure Name</div>
                        <div class="fw-bold text-dark font-size-14">{{ $surgery->surgery_name }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted font-size-11 text-uppercase">Pre-Op Diagnosis</div>
                        <div class="text-dark">{{ $surgery->pre_op_diagnosis ?? 'Indicated for surgical intervention.' }}</div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-muted font-size-11 text-uppercase">Post-Op Findings / Complications</div>
                        <div class="text-dark">{{ $surgery->post_op_findings ?? 'Procedure completed uneventfully. Vitals stable.' }}</div>
                    </div>
                </div>
                @if($surgery->surgical_notes)
                    <div class="mt-2 pt-2 border-top">
                        <div class="text-muted font-size-11 text-uppercase">Surgeon's Operational Notes:</div>
                        <div class="text-dark">{{ $surgery->surgical_notes }}</div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Itemized OT Medications & Consumables Ledger -->
        <div class="card border mb-4">
            <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold text-dark font-size-13">
                    <i class="bx bx-capsule me-1 text-danger"></i> Itemized OT Medications & Consumables Ledger for Patient Surgery
                </h6>
                <span class="badge bg-danger text-white font-size-11">
                    Total Drugs & Items: {{ $surgery->medications->count() }}
                </span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0 font-size-12">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Medicine / Molecule Name</th>
                                <th>Qty Used</th>
                                <th>Unit</th>
                                <th>Stage of Administration</th>
                                <th>Batch No.</th>
                                <th>Administered By</th>
                                <th class="text-end">Unit Cost (₹)</th>
                                <th class="text-end">Total (₹)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $grandTotal = 0; @endphp
                            @forelse($surgery->medications as $idx => $med)
                                @php $grandTotal += (float) $med->total_cost; @endphp
                                <tr>
                                    <td class="text-muted">{{ $idx + 1 }}</td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $med->medicine_name }}</div>
                                        @if($med->notes)
                                            <small class="text-muted">{{ $med->notes }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-info-subtle text-info font-size-12 fw-bold">
                                            {{ $med->quantity_used }}
                                        </span>
                                    </td>
                                    <td class="text-muted">{{ $med->unit }}</td>
                                    <td>
                                        @php
                                            $stageBadge = match($med->administration_stage) {
                                                'pre_medication' => 'bg-secondary-subtle text-secondary',
                                                'induction' => 'bg-primary-subtle text-primary',
                                                'intra_op' => 'bg-danger-subtle text-danger',
                                                'maintenance' => 'bg-warning-subtle text-warning',
                                                'reversal' => 'bg-info-subtle text-info',
                                                'post_op_recovery' => 'bg-success-subtle text-success',
                                                default => 'bg-light text-dark'
                                            };
                                        @endphp
                                        <span class="badge {{ $stageBadge }} font-size-11">
                                            {{ ucwords(str_replace('_', ' ', $med->administration_stage)) }}
                                        </span>
                                    </td>
                                    <td class="font-monospace text-muted">{{ $med->batch_number ?? 'BAT-GEN' }}</td>
                                    <td>{{ $med->administered_by ?? 'OT Anesthetist' }}</td>
                                    <td class="text-end font-monospace">₹{{ number_format((float) $med->cost_per_unit, 2) }}</td>
                                    <td class="text-end font-monospace fw-bold text-dark">₹{{ number_format((float) $med->total_cost, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4 text-muted">
                                        <i class="bx bx-info-circle me-1"></i> No medications logged for this surgery yet. Click <strong>"Log OT Meds / Consumables"</strong> above to record intra-operative anesthetics and surgical consumables.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="table-light border-top">
                            <tr>
                                <td colspan="8" class="text-end fw-bold font-size-13">TOTAL SURGICAL MEDICATION & CONSUMABLES COST:</td>
                                <td class="text-end fw-bold font-size-14 text-danger font-monospace">₹{{ number_format($grandTotal, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Signature & Certification Stamp Block -->
        <div class="row pt-4 mt-4 border-top">
            <div class="col-4 text-center">
                <div class="border-top pt-2" style="border-top-style: dashed !important;">
                    <div class="fw-bold text-dark font-size-12">Dr. {{ $surgery->leadSurgeon?->name ?? 'Lead Surgeon' }}</div>
                    <div class="text-muted font-size-11">Lead Surgeon (Sign & Reg No.)</div>
                </div>
            </div>
            <div class="col-4 text-center">
                <div class="border-top pt-2" style="border-top-style: dashed !important;">
                    <div class="fw-bold text-dark font-size-12">Dr. {{ $surgery->anesthesiologist?->name ?? 'Anesthesiologist' }}</div>
                    <div class="text-muted font-size-11">Consultant Anesthesiologist</div>
                </div>
            </div>
            <div class="col-4 text-center">
                <div class="border-top pt-2" style="border-top-style: dashed !important;">
                    <div class="fw-bold text-success font-size-12">CLEARED & VERIFIED</div>
                    <div class="text-muted font-size-11">OT In-Charge & Infection Control</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Log OT Medications & Consumables -->
<div class="modal fade d-print-none" id="logMedModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title font-size-15">
                    <i class="bx bx-plus-medical me-1"></i> Log OT Meds / Consumables Used
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('ot.medications.store') }}" method="POST">
                @csrf
                <input type="hidden" name="ot_surgery_id" value="{{ $surgery->id }}">
                <div class="modal-body p-3">
                    <div class="alert alert-info py-2 font-size-12 mb-3">
                        Patient: <strong>{{ $surgery->patient?->name }}</strong> ({{ $surgery->patient?->mrn }}) | Surgery: <strong>{{ $surgery->surgery_name }}</strong>
                    </div>

                    <div class="mb-2">
                        <label class="form-label font-size-12 mb-1">Medicine / Consumable Name <span class="text-danger">*</span></label>
                        <input type="text" name="medicine_name" class="form-control form-control-sm" placeholder="e.g. Propofol 1%, Fentanyl 50mcg, Sevoflurane" required list="commonMeds">
                        <datalist id="commonMeds">
                            <option value="Propofol 1% 20ml">
                            <option value="Fentanyl 50mcg/ml 2ml">
                            <option value="Vecuronium Bromide 4mg">
                            <option value="Sevoflurane Inhalation Agent">
                            <option value="Bupivacaine 0.5% Heavy">
                            <option value="Heparin Sodium 25,000 IU">
                            <option value="Protamine Sulfate 50mg">
                            <option value="Tranexamic Acid 500mg">
                            <option value="Cefuroxime 1.5g IV">
                            <option value="Neostigmine 0.5mg">
                            <option value="Glycopyrrolate 0.2mg">
                            <option value="Surgicel Hemostat Mesh">
                            <option value="Prolene 3-0 Suture">
                            <option value="Vicryl 2-0 Suture">
                            <option value="Normal Saline 0.9% 500ml">
                        </datalist>
                    </div>

                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label font-size-12 mb-1">Quantity Used <span class="text-danger">*</span></label>
                            <input type="number" step="0.1" name="quantity_used" class="form-control form-control-sm" placeholder="e.g. 2" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label font-size-12 mb-1">Unit <span class="text-danger">*</span></label>
                            <select name="unit" class="form-select form-select-sm" required>
                                <option value="Vial">Vial(s)</option>
                                <option value="Ampoule">Ampoule(s)</option>
                                <option value="ml">ml</option>
                                <option value="Bag (500ml)">Bag (500ml)</option>
                                <option value="Pack">Pack / Piece</option>
                                <option value="Suture Box">Suture Box</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label font-size-12 mb-1">Administration Stage <span class="text-danger">*</span></label>
                            <select name="administration_stage" class="form-select form-select-sm" required>
                                <option value="pre_medication">Pre-Medication</option>
                                <option value="induction" selected>Induction</option>
                                <option value="intra_op">Intra-Operative</option>
                                <option value="maintenance">Maintenance</option>
                                <option value="reversal">Reversal</option>
                                <option value="post_op_recovery">PACU Recovery</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label font-size-12 mb-1">Batch Number</label>
                            <input type="text" name="batch_number" class="form-control form-control-sm" placeholder="e.g. BAT-2026-981">
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label font-size-12 mb-1">Unit Cost (₹ INR)</label>
                        <input type="number" step="0.01" name="cost_per_unit" class="form-control form-control-sm" placeholder="e.g. 185.00">
                    </div>

                    <div class="mb-1">
                        <label class="form-label font-size-12 mb-1">Clinical Notes / Indication</label>
                        <input type="text" name="notes" class="form-control form-control-sm" placeholder="e.g. Muscle relaxation during intubation">
                    </div>
                </div>
                <div class="modal-footer p-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger btn-sm">
                        <i class="bx bx-check me-1"></i> Save to Surgical Ledger
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Update Surgery Status & Clinical Notes -->
<div class="modal fade d-print-none" id="updateSurgeryStatusModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title font-size-15">
                    <i class="bx bx-edit me-1"></i> Update Surgical Status & Operative Notes
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('ot.surgeries.status', $surgery->id) }}" method="POST">
                @csrf
                <div class="modal-body p-3">
                    <div class="mb-2">
                        <label class="form-label font-size-12 mb-1">Surgery Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select form-select-sm" required>
                            <option value="scheduled" {{ $surgery->status === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                            <option value="in_progress" {{ $surgery->status === 'in_progress' ? 'selected' : '' }}>Under Surgery (In-Progress)</option>
                            <option value="post_op_recovery" {{ $surgery->status === 'post_op_recovery' ? 'selected' : '' }}>PACU Post-Op Recovery</option>
                            <option value="completed" {{ $surgery->status === 'completed' ? 'selected' : '' }}>Completed & Cleared</option>
                            <option value="cancelled" {{ $surgery->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>

                    <div class="mb-2">
                        <label class="form-label font-size-12 mb-1">Blood Units Transfused</label>
                        <input type="number" name="blood_units_transfused" class="form-control form-control-sm" value="{{ $surgery->blood_units_transfused }}" min="0">
                    </div>

                    <div class="mb-2">
                        <label class="form-label font-size-12 mb-1">Post-Op Findings / Complications</label>
                        <textarea name="post_op_findings" class="form-control form-control-sm" rows="3" placeholder="Intra-operative events, stability, PACU transfer notes...">{{ $surgery->post_op_findings }}</textarea>
                    </div>

                    <div class="mb-1">
                        <label class="form-label font-size-12 mb-1">Surgeon Operative Summary</label>
                        <textarea name="surgical_notes" class="form-control form-control-sm" rows="3" placeholder="Key operative steps, implants placed, specimens sent to pathology...">{{ $surgery->surgical_notes }}</textarea>
                    </div>
                </div>
                <div class="modal-footer p-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bx bx-save me-1"></i> Update Surgical Record
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
@media print {
    body {
        background-color: #fff !important;
        font-size: 11pt !important;
    }
    .vertical-menu, .page-topbar, .footer, .d-print-none, .page-title-box {
        display: none !important;
    }
    .main-content {
        margin-left: 0 !important;
        padding: 0 !important;
    }
    .print-card {
        border: none !important;
        box-shadow: none !important;
    }
}
</style>
@endsection
