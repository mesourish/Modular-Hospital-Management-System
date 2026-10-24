<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Birth Certificate #{{ $birthRecord->certificate_number }} | {{ $hospital->name ?? 'Apollo Care Hospital' }}</title>
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Playfair+Display:ital,wght@0,600;0,800;1,600&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
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
            border: 8px double #0d9488;
            border-radius: 12px;
            padding: 40px 50px;
            position: relative;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        .cert-title {
            font-family: 'Playfair Display', serif;
            font-size: 32px;
            font-weight: 800;
            color: #0f766e;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .font-mono {
            font-family: 'JetBrains Mono', monospace;
        }
        .footprint-box {
            border: 1px dashed #cbd5e1;
            border-radius: 8px;
            height: 90px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #94a3b8;
            font-size: 11px;
            text-transform: uppercase;
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
                padding: 30px;
                max-width: 100%;
            }
        }
    </style>
</head>
<body>

    <!-- On-screen Print Toolbar -->
    <div class="no-print-toolbar">
        <span class="fw-semibold font-size-13"><i class="bx bx-child text-success me-1"></i> Birth Certificate #{{ $birthRecord->certificate_number }}</span>
        <button onclick="window.print()" class="btn btn-sm btn-success rounded-pill px-3">
            <i class="bx bx-printer me-1"></i> Print Certificate
        </button>
        <a href="{{ route('vital_records.index') }}" class="btn btn-sm btn-outline-light rounded-pill px-3">
            Back to Vital Registry
        </a>
    </div>

    <div class="cert-border">
        <!-- Certificate Header -->
        <div class="text-center mb-4">
            <div class="badge bg-success-subtle text-success px-3 py-1 font-size-12 fw-bold text-uppercase mb-2">
                Statutory Registration of Births and Deaths Act • Form 1 (Hospital Copy)
            </div>
            <h1 class="cert-title mb-1">Certificate of Birth</h1>
            <div class="fw-bold font-size-16 text-dark">{{ $hospital->name ?? 'Apollo Care Super Speciality Hospital' }}</div>
            <div class="text-muted font-size-12">
                {{ $branch->name ?? 'Maternity & Neonatal Pavilion' }} • {{ $branch->address ?? 'Plot 14, Healthcare Avenue' }}, {{ $branch->city ?? 'Mumbai' }}, {{ $branch->state ?? 'Maharashtra' }}
            </div>
            <div class="font-mono font-size-13 text-primary mt-2">
                <strong>Certificate No:</strong> {{ $birthRecord->certificate_number }}
            </div>
        </div>

        <hr style="border-top: 2px solid #0d9488; opacity: 0.3;">

        <!-- Certificate Declaration Body -->
        <div class="my-4 font-size-14 lh-lg text-dark">
            <p>
                This is to officially certify that the following birth information has been recorded in the statutory register of births maintained by <strong>{{ $hospital->name ?? 'Apollo Care Hospital' }}</strong> pursuant to the provisions of the <em>Registration of Births and Deaths Act</em>.
            </p>

            <table class="table table-bordered font-size-13 mt-3">
                <tbody>
                    <tr>
                        <th class="bg-light" style="width: 30%;">Full Name of Child</th>
                        <td class="fw-bold font-size-15 text-primary">{{ $birthRecord->baby_name }}</td>
                    </tr>
                    <tr>
                        <th class="bg-light">Gender</th>
                        <td class="text-capitalize fw-semibold">{{ $birthRecord->gender }}</td>
                    </tr>
                    <tr>
                        <th class="bg-light">Date & Time of Birth</th>
                        <td>
                            <strong>{{ \Carbon\Carbon::parse($birthRecord->birth_date)->format('d F Y') }}</strong> at
                            <strong>{{ \Carbon\Carbon::parse($birthRecord->birth_time)->format('h:i A') }}</strong>
                        </td>
                    </tr>
                    <tr>
                        <th class="bg-light">Place of Birth</th>
                        <td>{{ $branch->name ?? 'Apollo Care Maternity Ward' }}, Delivery Suite 03</td>
                    </tr>
                    <tr>
                        <th class="bg-light">Full Name of Mother</th>
                        <td class="fw-bold">{{ $birthRecord->mother_name }}
                            @if($birthRecord->mother)
                                <span class="badge bg-light text-secondary font-mono">UHID: {{ $birthRecord->mother->mrn }}</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th class="bg-light">Full Name of Father</th>
                        <td>{{ $birthRecord->father_name ?: 'Not Stated' }}</td>
                    </tr>
                    <tr>
                        <th class="bg-light">Birth Weight</th>
                        <td class="font-mono fw-bold text-success">{{ $birthRecord->birth_weight_kg }} Kilograms</td>
                    </tr>
                    <tr>
                        <th class="bg-light">Delivery Method</th>
                        <td class="text-capitalize">{{ str_replace('_', ' ', $birthRecord->delivery_type) }} (Gestational: {{ $birthRecord->gestational_age_weeks }} Weeks)</td>
                    </tr>
                    <tr>
                        <th class="bg-light">APGAR Score / Blood Group</th>
                        <td>
                            Score: <strong>{{ $birthRecord->apgar_score_1min }}/10</strong> (1 min) • <strong>{{ $birthRecord->apgar_score_5min }}/10</strong> (5 min) |
                            Blood Group: <span class="badge bg-danger">{{ $birthRecord->blood_group }}</span>
                        </td>
                    </tr>
                    <tr>
                        <th class="bg-light">Attending Obstetrician</th>
                        <td>Dr. {{ $birthRecord->obstetrician->name ?? 'Chief Obstetrician' }}, MD, DGO</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Footprint & Verification Boxes -->
        <div class="row g-3 mt-4 pt-2">
            <div class="col-4">
                <div class="footprint-box">
                    <span>Infant Left Sole Print</span>
                </div>
            </div>
            <div class="col-4">
                <div class="footprint-box">
                    <span>Mother Left Thumb Impression</span>
                </div>
            </div>
            <div class="col-4">
                <div class="border rounded p-2 text-center bg-light" style="height: 90px; display: flex; flex-direction: column; justify-content: center;">
                    <div class="font-mono font-size-10 text-muted">VERIFIED DIGITAL RECORD</div>
                    <div class="fw-bold font-size-11 text-success"><i class="bx bx-check-circle"></i> TAMPER-PROOF ARCHIVE</div>
                    <div class="font-size-9 text-muted">{{ now()->format('d-M-Y H:i:s') }}</div>
                </div>
            </div>
        </div>

        <!-- Signatures Footer -->
        <div class="row mt-5 pt-3">
            <div class="col-6 text-center">
                <div style="border-top: 1px dashed #94a3b8; padding-top: 6px; width: 80%; margin: 0 auto;">
                    <div class="fw-bold font-size-12">Attending Obstetrician / Surgeon</div>
                    <div class="text-muted font-size-11">Dr. {{ $birthRecord->obstetrician->name ?? 'Obstetrician' }}</div>
                </div>
            </div>
            <div class="col-6 text-center">
                <div style="border-top: 1px dashed #94a3b8; padding-top: 6px; width: 80%; margin: 0 auto;">
                    <div class="fw-bold font-size-12">Hospital Medical Superintendent / Registrar</div>
                    <div class="text-muted font-size-11">{{ $hospital->name ?? 'Apollo Care Hospital' }}</div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
