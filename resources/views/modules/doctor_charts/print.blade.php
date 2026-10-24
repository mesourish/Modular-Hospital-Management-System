<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Official OPD Doctor Duty Chart & Consultation Tariffs - {{ $hospital->name }}</title>
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #ffffff;
            color: #212529;
            margin: 20px;
        }
        .header-box {
            border-bottom: 2px solid #000;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .hospital-title {
            font-size: 24px;
            font-weight: bold;
            color: #0d6efd;
            margin-bottom: 4px;
        }
        .hospital-sub {
            font-size: 13px;
            color: #6c757d;
        }
        .badge-available {
            color: #198754;
            font-weight: bold;
        }
        .badge-leave {
            color: #dc3545;
            font-weight: bold;
        }
        table {
            font-size: 12px;
        }
        th {
            background-color: #f8f9fa !important;
            border-bottom: 2px solid #dee2e6 !important;
            font-weight: 600;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                margin: 0;
                padding: 10px;
            }
        }
    </style>
</head>
<body>

    <div class="no-print mb-3 d-flex justify-content-between align-items-center bg-light p-2 rounded">
        <div>
            <strong>Official OPD Doctor Duty Chart Printout</strong> &bull; {{ $branchName }}
        </div>
        <div>
            <button onclick="window.print()" class="btn btn-primary btn-sm">
                <i class="bx bx-printer"></i> Print / Save as PDF
            </button>
            <button onclick="window.close()" class="btn btn-secondary btn-sm ms-2">Close</button>
        </div>
    </div>

    <div class="header-box d-flex justify-content-between align-items-end">
        <div>
            <div class="hospital-title">{{ $hospital->name }}</div>
            <div class="hospital-sub">
                {{ $hospital->address }}, {{ $hospital->city }}, {{ $hospital->state }} &bull; Phone: {{ $hospital->phone ?? '+91 22 2450 1100' }}
            </div>
            <div class="mt-1 font-size-13 fw-bold text-dark">
                Official OPD Physician Duty Chart, Cabin Allocations & Consultation Tariffs (INR ₹)
            </div>
        </div>
        <div class="text-end">
            <span class="badge bg-secondary p-2 font-size-12">{{ $branchName }}</span>
            <div class="font-size-11 text-muted mt-1">Generated: {{ now()->format('d M, Y h:i A') }}</div>
        </div>
    </div>

    <table class="table table-bordered table-striped align-middle">
        <thead>
            <tr>
                <th style="width: 50px;">#</th>
                <th style="width: 200px;">Doctor & Degrees</th>
                <th style="width: 160px;">Specialty & Department</th>
                <th style="width: 150px;">Branch & Cabin No.</th>
                <th style="width: 140px;">OPD Days</th>
                <th style="width: 130px;">Timings</th>
                <th style="width: 110px;">Regular Fee</th>
                <th style="width: 100px;">Follow-up</th>
                <th style="width: 90px;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($doctorCharts as $index => $chart)
                <tr>
                    <td class="text-center fw-bold">{{ $index + 1 }}</td>
                    <td>
                        <div class="fw-bold text-primary font-size-13">{{ $chart->doctor_name }}</div>
                        <small class="text-dark">{{ $chart->degrees }}</small>
                    </td>
                    <td>
                        <div class="fw-medium">{{ $chart->specialty }}</div>
                        <small class="text-muted">{{ $chart->department?->name ?? 'General OPD' }}</small>
                    </td>
                    <td>
                        <div>{{ $chart->branch?->name ?? 'Main Branch' }}</div>
                        <span class="badge bg-light text-dark border">{{ $chart->room_number }}</span>
                    </td>
                    <td>
                        <span class="fw-medium">{{ $chart->opd_days }}</span>
                    </td>
                    <td>
                        <div>{{ $chart->formatted_timing }}</div>
                        <small class="text-muted">Max {{ $chart->max_patients_per_session }} tokens</small>
                    </td>
                    <td class="fw-bold text-success font-size-13">
                        {{ $chart->formatted_fee }}
                    </td>
                    <td>
                        {{ $chart->formatted_follow_up_fee }}
                    </td>
                    <td>
                        @if($chart->status === 'available')
                            <span class="badge-available">Available</span>
                        @elseif($chart->status === 'on_leave')
                            <span class="badge-leave">On Leave</span>
                        @else
                            <span>{{ ucfirst($chart->status) }}</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center py-4 text-muted">
                        No doctor charts found for the selected branch.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="mt-4 p-3 bg-light rounded text-muted font-size-11 d-flex justify-content-between align-items-center">
        <div>
            * Consultation fees are inclusive of standard hospital facility charges. Emergency & priority walk-in consultations subject to doctor availability.<br>
            * Senior citizens, pregnant women, and emergency trauma cases receive prioritized token queuing.
        </div>
        <div class="text-end fw-bold">
            Medical Superintendent / Branch Authority
        </div>
    </div>

</body>
</html>
