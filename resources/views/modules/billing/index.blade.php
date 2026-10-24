@extends('layouts.skote')

@section('title', 'Hospital Billing & Cashier')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <div>
                <h4 class="mb-sm-0 font-size-18">Hospital Billing, Invoices & Cashier</h4>
                <p class="text-muted font-size-13 mb-0">Cross-Department Fee Consolidation, Multi-Mode Payments & Audit Reconciliation</p>
            </div>
            <div class="page-title-right d-flex gap-2">
                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#newInvoiceModal">
                    <i class="bx bx-receipt me-1"></i> Generate Invoice
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Financial Summary Widgets -->
<div class="row">
    <div class="col-md-4">
        <div class="card mini-stats-wid">
            <div class="card-body">
                <div class="d-flex">
                    <div class="flex-grow-1">
                        <p class="text-muted fw-medium">Total Invoiced Amount</p>
                        <h4 class="mb-0">Rs {{ number_format($totalBilled, 2) }}</h4>
                    </div>
                    <div class="avatar-sm rounded-circle bg-primary align-self-center mini-stat-icon">
                        <span class="avatar-title rounded-circle bg-primary">
                            <i class="bx bx-receipt font-size-24"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card mini-stats-wid">
            <div class="card-body">
                <div class="d-flex">
                    <div class="flex-grow-1">
                        <p class="text-muted fw-medium">Payments Realized</p>
                        <h4 class="mb-0 text-success">Rs {{ number_format($totalPaid, 2) }}</h4>
                    </div>
                    <div class="avatar-sm rounded-circle bg-success align-self-center mini-stat-icon">
                        <span class="avatar-title rounded-circle bg-success">
                            <i class="bx bx-check-circle font-size-24"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card mini-stats-wid">
            <div class="card-body">
                <div class="d-flex">
                    <div class="flex-grow-1">
                        <p class="text-muted fw-medium">Outstanding Patient Balance</p>
                        <h4 class="mb-0 text-danger">Rs {{ number_format($totalOutstanding, 2) }}</h4>
                    </div>
                    <div class="avatar-sm rounded-circle bg-danger align-self-center mini-stat-icon">
                        <span class="avatar-title rounded-circle bg-danger">
                            <i class="bx bx-error-circle font-size-24"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <h4 class="card-title mb-3">Invoices & Financial Settlements</h4>
        <div class="table-responsive">
            <table class="table align-middle table-hover table-nowrap mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Invoice #</th>
                        <th>Patient</th>
                        <th>Date</th>
                        <th>Gross Total</th>
                        <th>Tax / Discount</th>
                        <th>Net Payable</th>
                        <th>Paid</th>
                        <th>Balance Due</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $inv)
                        <tr>
                            <td>
                                <span class="badge bg-soft-primary text-primary font-size-12"><code>{{ $inv->invoice_number }}</code></span>
                                @if($inv->admission_id)
                                    <small class="d-block text-muted">IPD Admission</small>
                                @else
                                    <small class="d-block text-muted">OPD Walk-in</small>
                                @endif
                            </td>
                            <td>
                                <div class="fw-bold text-dark font-size-13">{{ $inv->patient->fullName }}</div>
                                <small class="text-muted">MRN: {{ $inv->patient->mrn }}</small>
                            </td>
                            <td>{{ \Carbon\Carbon::parse($inv->invoice_date)->format('d M Y') }}</td>
                            <td>Rs {{ number_format($inv->total_amount, 2) }}</td>
                            <td>
                                <small class="text-muted">Disc: Rs {{ number_format($inv->discount_amount, 2) }}</small><br>
                                <small class="text-muted">Tax: Rs {{ number_format($inv->tax_amount, 2) }}</small>
                            </td>
                            <td><strong>Rs {{ number_format($inv->net_payable, 2) }}</strong></td>
                            <td><span class="text-success">Rs {{ number_format($inv->paid_amount, 2) }}</span></td>
                            <td>
                                <span class="{{ $inv->balance_due > 0 ? 'text-danger fw-bold' : 'text-success' }}">
                                    Rs {{ number_format($inv->balance_due, 2) }}
                                </span>
                            </td>
                            <td>
                                @if($inv->payment_status === 'paid')
                                    <span class="badge bg-success">Paid in Full</span>
                                @elseif($inv->payment_status === 'partially_paid')
                                    <span class="badge bg-warning">Partially Paid</span>
                                @else
                                    <span class="badge bg-danger">Unpaid</span>
                                @endif
                            </td>
                            <td>
                                @if($inv->balance_due > 0)
                                    <button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#paymentModal" onclick="document.getElementById('pay_inv_id').value = '{{ $inv->id }}'; document.getElementById('pay_amount').value = '{{ $inv->balance_due }}'; document.getElementById('pay_inv_label').innerText = '{{ $inv->invoice_number }} - Due: Rs {{ $inv->balance_due }}';">
                                        <i class="bx bx-credit-card me-1"></i> Collect
                                    </button>
                                @else
                                    <span class="text-muted font-size-12">Settled</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-4 text-muted">No invoices generated.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Generate Invoice -->
<div class="modal fade" id="newInvoiceModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('billing.invoices.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Generate Cross-Department Patient Bill</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Patient <span class="text-danger">*</span></label>
                            <select name="patient_id" class="form-select" required>
                                @foreach($patients as $p)
                                    <option value="{{ $p->id }}">{{ $p->fullName }} ({{ $p->mrn }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Linked Inpatient Admission (Optional)</label>
                            <select name="admission_id" class="form-select">
                                <option value="">None (OPD / Walk-in)</option>
                                @foreach($admissions as $adm)
                                    <option value="{{ $adm->id }}">{{ $adm->admission_number }} - {{ $adm->patient->fullName }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Itemized Charges -->
                        <div class="col-12 mt-3">
                            <h6 class="font-size-13 text-primary text-uppercase fw-semibold border-bottom pb-1 mb-2">Itemized Service Charges</h6>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Department Service</label>
                            <select name="items[0][service_type]" class="form-select">
                                <option value="consultation">Consultation Fee</option>
                                <option value="bed_charge">Inpatient Bed Charge</option>
                                <option value="radiology">Radiology Imaging Scan</option>
                                <option value="blood_bank">Blood Product Transfusion</option>
                                <option value="pharmacy">Pharmacy Dispense</option>
                                <option value="procedure">Nursing & Procedure</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Item Description</label>
                            <input type="text" name="items[0][item_name]" class="form-control" value="Doctor Consultation & Clinical Encounter" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Qty</label>
                            <input type="number" name="items[0][quantity]" class="form-control" value="1" min="1" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Unit Price (Rs)</label>
                            <input type="number" step="0.01" name="items[0][unit_price]" class="form-control" value="1500.00" required>
                        </div>

                        <!-- Second Item -->
                        <div class="col-md-4">
                            <select name="items[1][service_type]" class="form-select">
                                <option value="procedure" selected>Hospital Admin & Nursing</option>
                                <option value="radiology">Radiology Scan</option>
                                <option value="pharmacy">Pharmacy</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <input type="text" name="items[1][item_name]" class="form-control" value="Triage & Nursing Assessment" required>
                        </div>
                        <div class="col-md-2">
                            <input type="number" name="items[1][quantity]" class="form-control" value="1" min="1" required>
                        </div>
                        <div class="col-md-2">
                            <input type="number" step="0.01" name="items[1][unit_price]" class="form-control" value="500.00" required>
                        </div>

                        <div class="col-md-6 mt-3">
                            <label class="form-label">Discount Concession (Rs)</label>
                            <input type="number" step="0.01" name="discount_amount" class="form-control" value="0.00">
                        </div>
                        <div class="col-md-6 mt-3">
                            <label class="form-label">Billing Notes</label>
                            <input type="text" name="notes" class="form-control" placeholder="Insurance / Cash desk remarks...">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Generate Tax Invoice</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Record Payment -->
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('billing.payments.store') }}" method="POST">
                @csrf
                <input type="hidden" name="invoice_id" id="pay_inv_id">
                <div class="modal-header">
                    <h5 class="modal-title">Collect Patient Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info font-size-13">
                        <i class="bx bx-receipt me-1"></i> <strong id="pay_inv_label">Invoice</strong>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Amount (Rs) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="amount" id="pay_amount" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Payment Mode <span class="text-danger">*</span></label>
                            <select name="payment_method" class="form-select" required>
                                <option value="cash">Cash Desk</option>
                                <option value="card">Credit / Debit Card</option>
                                <option value="upi">UPI / QR Payment</option>
                                <option value="insurance">TPA / Health Insurance</option>
                                <option value="bank_transfer">Direct Bank NEFT</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Transaction Reference #</label>
                            <input type="text" name="transaction_ref" class="form-control" placeholder="e.g. UPI-TXN-992019 / POS Auth Code">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success"><i class="bx bx-check-circle me-1"></i> Print Receipt & Settle</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
