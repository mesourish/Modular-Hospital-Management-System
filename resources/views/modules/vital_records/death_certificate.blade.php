<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medical Certificate of Cause of Death #{{ $deathRecord->certificate_number }}</title>
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #f1f5f9;
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: #0f172a;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        .cert-border {
            max-width: 860px;
            margin: 30px auto;
            background: #ffffff;
            border: 3px solid #334155;
            border-radius: 8px;
            padding: 35px 45px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        .cert-title {
            font-size: 20px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .font-mono {
            font-family: 'JetBrains Mono', monospace;
        }
        .no-print-toolbar {
            background: #0f172a;
            color: #fff;
            padding: 12px 24px;
            border-radius: 50px;
            position: fixed;
            bottom: 24px;
            right: 24px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.3);
            z-index: 9999;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        @media print {
            body {
                background: #ffffff;
                margin: 0;
            }
            .no-print-toolbar {
                display: none !important;
            }
            .cert-border {
                box-shadow: none;
                margin: 0;
                padding: 25px;
                max-width: 100%;
            }
        }
    </style>
</head>
<body>

    <!-- On-screen Print Toolbar -->
    <div class="no-print-toolbar">
        <span class="fw-semibold font-size-13"><i class="bx bx-cross text-danger me-1"></i> MCCD #{{ $deathRecord->certificate_number }}</span>
        <button onclick="window.print()" class="btn btn-sm btn-danger rounded-pill px-3">
            <i class="bx bx-printer me-1"></i> Print Certificate
        </button>
        <a href="{{ route('vital_records.index') }}" class="btn btn-sm btn-outline-light rounded-pill px-3">
            Back to Registry
        </a>
    </div>

    <div class="cert-border">
        <!-- Header -->
        <div class="text-center mb-3 pb-2 border-bottom">
            <div class="badge bg-secondary text-white px-3 py-1 font-size-11 fw-bold text-uppercase mb-2">
                Registration of Births and Deaths Act 1969 • Form No. 4 (Hospital In-Patients)
            </div>
            <h1 class="cert-title mb-1">Medical Certificate of Cause of Death (MCCD)</h1>
            <div class="fw-bold font-size-15 text-dark">{{ $hospital->name ?? 'Apollo Care Super Speciality Hospital' }}</div>
            <div class="text-muted font-size-12">
                {{ $branch->name ?? 'Critical Care Pavilion' }} • {{ $branch->address ?? 'Plot 14, Sector 5' }}, {{ $branch->city ?? 'Mumbai' }}
            </div>
            <div class="font-mono font-size-13 text-danger mt-2">
                <strong>Certificate No:</strong> {{ $deathRecord->certificate_number }}
            </div>
        </div>

        <p class="font-size-13 text-dark">
            I hereby certify that the person whose medical particulars are given below was under my care and that the cause of death stated below has been determined according to the best of my knowledge and clinical judgment.
        </p>

        <!-- Patient Demographics -->
        <table class="table table-bordered font-size-12 mb-3">
            <tbody>
                <tr>
                    <th class="bg-light" style="width: 25%;">Name of Deceased</th>
                    <td class="fw-bold font-size-14 text-dark" style="width: 35%;">{{ $deathRecord->patient->full_name ?? 'Patient' }}</td>
                    <th class="bg-light" style="width: 20%;">UHID / MRN</th>
                    <td class="font-mono text-primary fw-bold" style="width: 20%;">{{ $deathRecord->patient->mrn ?? '' }}</td>
                </tr>
                <tr>
                    <th class="bg-light">Age / Gender</th>
                    <td>{{ $deathRecord->patient->age ?? 55 }} Years / {{ ucfirst($deathRecord->patient->gender ?? 'male') }}</td>
                    <th class="bg-light">Date of Death</th>
                    <td>{{ \Carbon\Carbon::parse($deathRecord->date_of_death)->format('d F Y') }}</td>
                </tr>
                <tr>
                    <th class="bg-light">Time of Death</th>
                    <td>{{ \Carbon\Carbon::parse($deathRecord->time_of_death)->format('h:i A') }} (IST)</td>
                    <th class="bg-light">Place of Death</th>
                    <td>{{ $deathRecord->place_of_death }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Medical Cause of Death Table (ICD-10) -->
        <div class="border rounded p-3 mb-3 bg-light">
            <div class="fw-bold font-size-13 text-uppercase text-danger mb-2">Cause of Death (As per WHO ICD-10 Classification)</div>
            <table class="table table-sm table-bordered bg-white font-size-12 mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 70%;">Clinical Condition / Morbid Sequence</th>
                        <th class="text-end" style="width: 30%;">ICD-10 Diagnostic Code</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <strong>I. Immediate Cause of Death:</strong><br>
                            <span class="text-dark">{{ $deathRecord->primary_cause }}</span>
                        </td>
                        <td class="text-end font-mono fw-bold text-danger">{{ $deathRecord->icd10_code }}</td>
                    </tr>
                    @if($deathRecord->secondary_cause)
                        <tr>
                            <td>
                                <strong>II. Antecedent / Underlying Cause:</strong><br>
                                <span class="text-muted">{{ $deathRecord->secondary_cause }}</span>
                            </td>
                            <td class="text-end font-mono text-secondary">I25.1 (Underlying)</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        <!-- Next of Kin & Cremation Pass Clearance -->
        <table class="table table-bordered font-size-12 mb-3">
            <tbody>
                <tr>
                    <th class="bg-light" style="width: 30%;">Next of Kin (Claimant)</th>
                    <td>{{ $deathRecord->next_of_kin_name }} ({{ $deathRecord->next_of_kin_relation }}) • Phone: {{ $deathRecord->next_of_kin_phone ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th class="bg-light">Body Handover To</th>
                    <td>{{ $deathRecord->body_released_to ?? $deathRecord->next_of_kin_name }}</td>
                </tr>
                <tr>
                    <th class="bg-light">Cremation / Burial Pass</th>
                    <td>
                        @if($deathRecord->cremation_pass_issued)
                            <span class="badge bg-success font-size-12"><i class="bx bx-check me-1"></i> PERMIT ISSUED FOR CREMATION / BURIAL</span>
                        @else
                            <span class="badge bg-secondary font-size-12">NOT ISSUED</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <th class="bg-light">Medico-Legal / Autopsy</th>
                    <td>
                        @if($deathRecord->autopsy_required)
                            <span class="badge bg-danger font-size-11">MLC CASE - Autopsy Mandatory</span>
                        @else
                            <span class="text-success font-weight-bold">Natural Cause - Non-MLC Clearance</span>
                        @endif
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Certifying Clinician Signature Block -->
        <div class="row mt-5 pt-3">
            <div class="col-6">
                <div class="border p-2 rounded text-center bg-light">
                    <div class="font-mono font-size-10 text-muted">HOSPITAL REGISTRATION SEAL</div>
                    <div class="fw-bold font-size-11 text-dark mt-1">{{ $hospital->name ?? 'Apollo Care Hospital' }}</div>
                    <div class="font-size-9 text-muted">Municipal Corporation Health Dept Linked</div>
                </div>
            </div>
            <div class="col-6 text-center">
                <div style="border-top: 1px dashed #334155; padding-top: 6px; width: 85%; margin: 0 auto;">
                    <div class="fw-bold font-size-13 text-dark">Dr. {{ $deathRecord->certifyingDoctor->name ?? 'Certifying Medical Officer' }}</div>
                    <div class="text-muted font-size-11">Registered Medical Practitioner (MMC Reg: 2014/09/2410)</div>
                    <div class="text-muted font-size-10 mt-1">Certified on {{ \Carbon\Carbon::parse($deathRecord->created_at)->format('d-M-Y H:i') }}</div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
