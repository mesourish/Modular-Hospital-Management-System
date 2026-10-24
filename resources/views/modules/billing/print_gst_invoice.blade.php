<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GST Tax Invoice #{{ $invoice->invoice_number }} | {{ $hospital->name ?? 'Apollo Care Hospital' }}</title>
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #f1f5f9;
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            color: #0f172a;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        .invoice-container {
            max-width: 860px;
            margin: 30px auto;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            padding: 40px;
            border: 1px solid #e2e8f0;
        }
        .invoice-header {
            border-bottom: 2px solid #0284c7;
            padding-bottom: 20px;
            margin-bottom: 24px;
        }
        .invoice-title {
            font-size: 20px;
            font-weight: 800;
            color: #0369a1;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .hosp-badge {
            display: inline-block;
            background: #e0f2fe;
            color: #0369a1;
            font-size: 11px;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 6px;
            text-transform: uppercase;
        }
        .meta-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 14px 18px;
            height: 100%;
        }
        .table-gst {
            width: 100%;
            border-collapse: collapse;
            font-size: 12.5px;
            margin-top: 15px;
        }
        .table-gst th {
            background-color: #0f172a;
            color: #ffffff;
            font-weight: 600;
            padding: 9px 10px;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.5px;
            border: 1px solid #1e293b;
        }
        .table-gst td {
            padding: 9px 10px;
            border: 1px solid #e2e8f0;
            vertical-align: middle;
        }
        .table-gst tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .font-mono {
            font-family: 'JetBrains Mono', monospace;
        }
        .totals-card {
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 16px;
        }
        .grand-total-row {
            background: #0284c7;
            color: #ffffff;
            border-radius: 6px;
            padding: 10px 14px;
            font-weight: 800;
            font-size: 16px;
        }
        .signature-box {
            border-top: 1px dashed #94a3b8;
            padding-top: 8px;
            margin-top: 50px;
            text-align: center;
            font-size: 12px;
            font-weight: 600;
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
                padding: 0;
            }
            .no-print-toolbar {
                display: none !important;
            }
            .invoice-container {
                box-shadow: none;
                border: none;
                margin: 0;
                padding: 10px;
                max-width: 100%;
            }
        }
    </style>
</head>
<body>

    <!-- On-Screen Floating Control Bar -->
    <div class="no-print-toolbar">
        <span class="fw-semibold font-size-13"><i class="bx bx-receipt text-warning me-1"></i> GST Bill #{{ $invoice->invoice_number }}</span>
        <button onclick="window.print()" class="btn btn-sm btn-primary rounded-pill px-3">
            <i class="bx bx-printer me-1"></i> Print Invoice
        </button>
        <a href="{{ route('pharmacy.pos.index') }}" class="btn btn-sm btn-outline-light rounded-pill px-3">
            <i class="bx bx-cart me-1"></i> Back to POS
        </a>
        <a href="{{ route('billing.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
            Billing Desk
        </a>
    </div>

    <div class="invoice-container">
        <!-- Header -->
        <div class="invoice-header">
            <div class="row align-items-center">
                <div class="col-8">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="invoice-title">{{ $hospital->name ?? 'Apollo Care Super Speciality Hospital' }}</span>
                        <span class="hosp-badge">NABH Digital Accredited</span>
                    </div>
                    <div class="text-muted font-size-13">
                        <strong>Branch / Outlet:</strong> {{ $branch->name ?? 'Main Hospital Campus' }}
                        @if($branch && $branch->slug)
                            <span class="badge bg-light text-secondary border font-mono">/outlet/{{ $branch->slug }}</span>
                        @endif
                    </div>
                    <div class="text-muted font-size-12 mt-1">
                        {{ $branch->address ?? 'Plot 14, Healthcare Avenue, Sector 5' }}, {{ $branch->city ?? 'Mumbai' }}, {{ $branch->state ?? 'Maharashtra' }} - {{ $branch->pincode ?? '400001' }}
                    </div>
                    <div class="text-secondary font-size-12 mt-1">
                        <strong>Phone:</strong> {{ $branch->phone ?? '+91 22 2847 9000' }} • <strong>Email:</strong> {{ $branch->email ?? 'billing@apollocare.in' }}
                    </div>
                </div>
                <div class="col-4 text-end">
                    <div class="badge bg-primary-subtle text-primary border border-primary px-3 py-2 font-size-12 fw-bold text-uppercase mb-2">
                        Tax Invoice / Cash Memo
                    </div>
                    <div class="font-size-12 font-mono text-dark">
                        <strong>GSTIN:</strong> {{ $invoice->gstin_number ?? '27AAAAA0000A1Z5' }}<br>
                        <strong>State:</strong> Maharashtra (Code: 27)<br>
                        <strong>D.L. No:</strong> 20B/21B-MH-102938<br>
                        <strong>FSSAI:</strong> 11521018000452
                    </div>
                </div>
            </div>
        </div>

        <!-- Invoice & Patient Metadata -->
        <div class="row g-3 mb-3">
            <div class="col-6">
                <div class="meta-box">
                    <div class="fw-bold font-size-12 text-uppercase text-primary mb-2">Billed To (Patient Details)</div>
                    @if($invoice->patient)
                        <div class="fw-bold font-size-14 text-dark">{{ $invoice->patient->full_name }}</div>
                        <div class="text-muted font-size-12">
                            <strong>UHID / MRN:</strong> <span class="font-mono text-primary fw-bold">{{ $invoice->patient->mrn }}</span>
                            @if($invoice->patient->abha_id)
                                • <strong>ABHA:</strong> <span class="font-mono">{{ $invoice->patient->abha_id }}</span>
                            @endif
                        </div>
                        <div class="text-muted font-size-12">
                            <strong>Age / Gender:</strong> {{ $invoice->patient->age }} Y / {{ ucfirst($invoice->patient->gender) }} • <strong>Blood:</strong> {{ $invoice->patient->blood_group }}
                        </div>
                        <div class="text-muted font-size-12">
                            <strong>Mobile:</strong> {{ $invoice->patient->phone }}
                        </div>
                    @else
                        <div class="fw-bold font-size-14 text-dark">{{ $invoice->notes ?? 'Walk-In Customer (Over-The-Counter)' }}</div>
                        <div class="text-muted font-size-12">Retail Pharmacy Counter Cash Patient</div>
                    @endif

                    @if($invoice->admission)
                        <div class="badge bg-info-subtle text-info mt-2">
                            Inpatient ADT: {{ $invoice->admission->bed->ward->name ?? 'Ward' }} (Bed {{ $invoice->admission->bed->bed_number ?? 'General' }})
                        </div>
                    @endif
                </div>
            </div>

            <div class="col-6">
                <div class="meta-box">
                    <div class="fw-bold font-size-12 text-uppercase text-primary mb-2">Invoice & Payment Info</div>
                    <div class="row font-size-12">
                        <div class="col-6 text-muted">Invoice Number:</div>
                        <div class="col-6 font-mono fw-bold text-dark">{{ $invoice->invoice_number }}</div>

                        <div class="col-6 text-muted mt-1">Invoice Date:</div>
                        <div class="col-6 text-dark mt-1">{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d M Y') }}</div>

                        <div class="col-6 text-muted mt-1">Payment Status:</div>
                        <div class="col-6 mt-1">
                            <span class="badge {{ $invoice->payment_status === 'paid' ? 'bg-success' : 'bg-warning' }} text-uppercase">
                                {{ $invoice->payment_status }}
                            </span>
                        </div>

                        <div class="col-6 text-muted mt-1">Payment Mode:</div>
                        <div class="col-6 text-dark fw-bold mt-1">
                            {{ $invoice->payments->first()?->payment_method ? strtoupper($invoice->payments->first()->payment_method) : 'CASH / COUNTER' }}
                        </div>

                        <div class="col-6 text-muted mt-1">Receipt Ref:</div>
                        <div class="col-6 font-mono text-dark mt-1">
                            {{ $invoice->payments->first()?->receipt_number ?? 'POS-INSTANT' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Line Items Table -->
        <table class="table-gst">
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 32%;">Item Description / Medicine</th>
                    <th style="width: 10%;">HSN Code</th>
                    <th style="width: 14%;">Batch & Exp</th>
                    <th style="width: 12%;">Rack / Shelf</th>
                    <th style="width: 6%; text-align: center;">Qty</th>
                    <th style="width: 9%; text-align: right;">Rate (₹)</th>
                    <th style="width: 12%; text-align: right;">Total (₹)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoice->items as $idx => $item)
                    <tr>
                        <td class="text-center font-mono font-size-11">{{ $idx + 1 }}</td>
                        <td>
                            <strong class="text-dark">{{ $item->item_name }}</strong>
                            <div class="text-muted font-size-11 text-uppercase">{{ str_replace('_', ' ', $item->service_type) }}</div>
                        </td>
                        <td class="font-mono font-size-11">{{ $item->hsn_code ?? '3004' }}</td>
                        <td class="font-mono font-size-11">
                            @if($item->batch_number)
                                <span class="fw-semibold text-primary">{{ $item->batch_number }}</span><br>
                                <small class="text-muted">Exp: {{ $item->expiry_date ? \Carbon\Carbon::parse($item->expiry_date)->format('m/y') : 'N/A' }}</small>
                            @else
                                <span class="text-muted">SERVICE</span>
                            @endif
                        </td>
                        <td class="font-size-11 text-secondary">
                            {{ $item->rack_location ?: 'Central Store' }}
                        </td>
                        <td class="text-center font-mono fw-bold">{{ $item->quantity }}</td>
                        <td class="text-end font-mono">₹{{ number_format($item->unit_price, 2) }}</td>
                        <td class="text-end font-mono fw-bold text-dark">₹{{ number_format($item->total_price, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-3 text-muted">No billable line items attached.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Summary & GST Breakdown -->
        <div class="row mt-3">
            <div class="col-7">
                <div class="border rounded p-3 bg-light">
                    <div class="fw-bold font-size-12 text-uppercase text-dark mb-2"><i class="bx bx-shield-check text-success me-1"></i> GST Tax Computation Summary</div>
                    <table class="table table-sm table-borderless font-size-12 mb-0">
                        <tr>
                            <td class="text-muted">Taxable Value:</td>
                            <td class="text-end font-mono fw-semibold">₹{{ number_format($invoice->total_amount - $invoice->discount_amount, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Central GST (CGST @ 6%):</td>
                            <td class="text-end font-mono text-primary">₹{{ number_format($invoice->cgst_amount > 0 ? $invoice->cgst_amount : ($invoice->tax_amount / 2), 2) }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">State GST (SGST @ 6%):</td>
                            <td class="text-end font-mono text-primary">₹{{ number_format($invoice->sgst_amount > 0 ? $invoice->sgst_amount : ($invoice->tax_amount / 2), 2) }}</td>
                        </tr>
                        <tr class="border-top">
                            <td class="fw-bold text-dark">Total Integrated Tax Amount:</td>
                            <td class="text-end font-mono fw-bold text-dark">₹{{ number_format($invoice->tax_amount, 2) }}</td>
                        </tr>
                    </table>

                    <div class="mt-2 pt-2 border-top font-size-11 text-muted">
                        <strong>Amount in Words:</strong><br>
                        <span class="fst-italic text-dark">
                            Rupees {{ ucwords(\Illuminate\Support\Str::headline(\NumberFormatter::create('en_IN', \NumberFormatter::SPELLOUT)->format($invoice->net_payable))) }} Only
                        </span>
                    </div>
                </div>
            </div>

            <div class="col-5">
                <div class="totals-card">
                    <div class="d-flex justify-content-between font-size-13 mb-1">
                        <span class="text-muted">Sub Total Gross:</span>
                        <span class="font-mono fw-semibold">₹{{ number_format($invoice->total_amount, 2) }}</span>
                    </div>
                    @if($invoice->discount_amount > 0)
                        <div class="d-flex justify-content-between font-size-13 mb-1 text-danger">
                            <span>Special Discount:</span>
                            <span class="font-mono">- ₹{{ number_format($invoice->discount_amount, 2) }}</span>
                        </div>
                    @endif
                    <div class="d-flex justify-content-between font-size-13 mb-2">
                        <span class="text-muted">CGST + SGST (12%):</span>
                        <span class="font-mono text-primary fw-semibold">+ ₹{{ number_format($invoice->tax_amount, 2) }}</span>
                    </div>

                    <div class="grand-total-row d-flex justify-content-between align-items-center mb-2">
                        <span>NET PAYABLE:</span>
                        <span class="font-mono font-size-18">₹{{ number_format($invoice->net_payable, 2) }}</span>
                    </div>

                    <div class="d-flex justify-content-between font-size-12 text-muted">
                        <span>Amount Paid:</span>
                        <span class="font-mono text-success fw-bold">₹{{ number_format($invoice->paid_amount, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between font-size-12 text-muted">
                        <span>Balance Due:</span>
                        <span class="font-mono text-danger fw-bold">₹{{ number_format($invoice->balance_due, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer Disclaimers & Signatures -->
        <div class="row mt-4 pt-3 border-top">
            <div class="col-8">
                <div class="font-size-11 text-muted">
                    <strong>Statutory Terms & Conditions:</strong><br>
                    1. Goods once sold will not be returned or exchanged without original tax invoice.<br>
                    2. Refrigerated & cold-chain medications (insulin, vaccines) cannot be returned under any circumstances.<br>
                    3. This invoice is generated electronically under Rule 48(4) of CGST Rules 2017. Valid in all courts of law.<br>
                    4. Please consult your physician before taking any scheduled drugs (Schedule H & H1).
                </div>
            </div>
            <div class="col-4">
                <div class="signature-box">
                    <div>Authorized Pharmacist / Signatory</div>
                    <div class="text-muted font-size-11 mt-1">Apollo Care Hospitals & Pharmacy</div>
                </div>
            </div>
        </div>

    </div>

</body>
</html>
