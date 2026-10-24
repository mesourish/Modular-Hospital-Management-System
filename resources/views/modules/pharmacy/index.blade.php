@extends('layouts.skote')

@section('title', 'Pharmacy POS & Stock Center')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <div>
                <h4 class="mb-sm-0 font-size-18">Pharmacy POS & Medicine Stock Management</h4>
                <p class="text-muted font-size-13 mb-0">Retail Point of Sale, Batch Stock Tracking, Doctor Rx Fulfillment & Expiry Ledgers</p>
            </div>
            <div class="page-title-right d-flex gap-2">
                <button type="button" class="btn btn-outline-success shadow-sm" data-bs-toggle="modal" data-bs-target="#newStoreModal">
                    <i class="bx bx-store me-1"></i> Register Pharmacy Outlet
                </button>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newBatchModal">
                    <i class="bx bx-package me-1"></i> Replenish Stock Batch
                </button>
                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#newMedModal">
                    <i class="bx bx-plus me-1"></i> Add Formulary Item
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Stock KPI Summary Cards -->
<div class="row">
    <div class="col-xl-3 col-md-6">
        <div class="card mini-stats-wid">
            <div class="card-body">
                <div class="d-flex">
                    <div class="flex-grow-1">
                        <p class="text-muted fw-medium mb-1">Total Inventory Valuation</p>
                        <h4 class="mb-0 text-primary">₹{{ number_format($totalStockValue, 2) }}</h4>
                        <small class="text-muted font-size-11">Total retail value of medicine batches</small>
                    </div>
                    <div class="avatar-sm rounded-circle bg-primary-subtle align-self-center mini-stat-icon">
                        <span class="avatar-title rounded-circle bg-primary text-white">
                            <i class="bx bx-rupee font-size-22"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card mini-stats-wid">
            <div class="card-body">
                <div class="d-flex">
                    <div class="flex-grow-1">
                        <p class="text-muted fw-medium mb-1">Active Formulary Items</p>
                        <h4 class="mb-0 text-dark">{{ $medicines->count() }} Formulations</h4>
                        <small class="text-success font-size-11"><i class="bx bx-check"></i> Standard Indian Medicines</small>
                    </div>
                    <div class="avatar-sm rounded-circle bg-success-subtle align-self-center mini-stat-icon">
                        <span class="avatar-title rounded-circle bg-success text-white">
                            <i class="bx bx-capsule font-size-22"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card mini-stats-wid">
            <div class="card-body">
                <div class="d-flex">
                    <div class="flex-grow-1">
                        <p class="text-muted fw-medium mb-1">Low Stock Warnings</p>
                        <h4 class="mb-0 text-danger">{{ $lowStockMedicines->count() }} Medicines</h4>
                        <small class="text-danger font-size-11">Below reorder threshold</small>
                    </div>
                    <div class="avatar-sm rounded-circle bg-danger-subtle align-self-center mini-stat-icon">
                        <span class="avatar-title rounded-circle bg-danger text-white">
                            <i class="bx bx-error font-size-22"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card mini-stats-wid">
            <div class="card-body">
                <div class="d-flex">
                    <div class="flex-grow-1">
                        <p class="text-muted fw-medium mb-1">Expiring Batches (< 90d)</p>
                        <h4 class="mb-0 text-warning">{{ $nearExpiryBatches->count() }} Batches</h4>
                        <small class="text-warning font-size-11">Prioritize for dispensing</small>
                    </div>
                    <div class="avatar-sm rounded-circle bg-warning-subtle align-self-center mini-stat-icon">
                        <span class="avatar-title rounded-circle bg-warning text-white">
                            <i class="bx bx-time-five font-size-22"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- POS Receipt Notification Alert (if generated) -->
@if(session('pos_receipt'))
    @php $receipt = session('pos_receipt'); @endphp
    <div class="alert alert-success alert-dismissible fade show p-3 mb-4 shadow" role="alert">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="d-flex align-items-center">
                <i class="bx bx-receipt font-size-24 text-success me-3"></i>
                <div>
                    <h5 class="text-success font-size-15 mb-1">Sale Complete! Tax Invoice Generated: {{ $receipt['receipt_number'] }}</h5>
                    <div class="font-size-12 text-muted">
                        Customer: <strong>{{ $receipt['customer'] }}</strong> • Item: <strong>{{ $receipt['medicine_name'] }}</strong> ({{ $receipt['quantity'] }} Units) • Total: <strong class="text-success font-size-13">₹{{ number_format($receipt['total_amount'], 2) }}</strong> via {{ $receipt['payment_method'] }}
                    </div>
                </div>
            </div>
            <button type="button" class="btn btn-sm btn-success" onclick="window.print()">
                <i class="bx bx-printer me-1"></i> Print POS Receipt
            </button>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<!-- Pharmacy Nav Tabs -->
<ul class="nav nav-tabs nav-tabs-custom mb-4" role="tablist">
    <li class="nav-item">
        <a class="nav-link active" data-bs-toggle="tab" href="#posTab" role="tab">
            <i class="bx bx-cart font-size-16 align-middle me-1"></i> 
            <span class="fw-semibold">Pharmacy POS & Dispense Desk</span>
            @if($pendingPrescriptions->count() > 0)
                <span class="badge bg-danger ms-1 font-size-10">{{ $pendingPrescriptions->count() }} Doctor Rx</span>
            @endif
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-bs-toggle="tab" href="#inventoryTab" role="tab">
            <i class="bx bx-package font-size-16 align-middle me-1"></i> 
            <span class="fw-semibold">Medicine Stock & Batches</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-bs-toggle="tab" href="#formularyTab" role="tab">
            <i class="bx bx-capsule font-size-16 align-middle me-1"></i> 
            <span class="fw-semibold">Formulary Ledger</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-bs-toggle="tab" href="#auditTab" role="tab">
            <i class="bx bx-history font-size-16 align-middle me-1"></i> 
            <span class="fw-semibold">Dispense History</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-bs-toggle="tab" href="#storesTab" role="tab">
            <i class="bx bx-store-alt font-size-16 align-middle me-1"></i> 
            <span class="fw-semibold">Pharmacy Outlets (Standalone / Connected)</span>
            <span class="badge bg-primary ms-1 font-size-10">{{ $pharmacyStores->count() }}</span>
        </a>
    </li>
</ul>

<div class="tab-content text-muted">
    <!-- TAB 1: PHARMACY POS (POINT OF SALE) -->
    <div class="tab-pane active" id="posTab" role="tabpanel">
        <div class="row">
            <!-- Left: POS Billing Form -->
            <div class="col-xl-6">
                <div class="card border-primary border-top border-3">
                    <div class="card-header bg-transparent border-bottom">
                        <div class="d-flex align-items-center justify-content-between">
                            <h5 class="card-title mb-0">
                                <i class="bx bx-credit-card font-size-18 text-primary me-1"></i> Point of Sale (POS) Counter
                            </h5>
                            <span class="badge bg-soft-info text-info font-size-11">
                                GST Compliant Pharmacy
                            </span>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <form action="{{ route('pharmacy.pos') }}" method="POST" id="posForm">
                            @csrf
                            <input type="hidden" name="prescription_id" id="pos_prescription_id" value="">

                            <!-- Customer / Patient Selector -->
                            <div class="mb-3">
                                <label class="form-label fw-medium">Select Patient (or Walk-In Customer) <span class="text-danger">*</span></label>
                                <div class="row g-2">
                                    <div class="col-8">
                                        <select name="patient_id" id="pos_patient_select" class="form-select font-size-13">
                                            <option value="">-- Walk-In Customer (Over the Counter) --</option>
                                            @foreach($patients as $pt)
                                                <option value="{{ $pt->id }}">
                                                    {{ $pt->name }} (UHID: {{ $pt->uhid }} | ABHA: {{ $pt->abha_id ?? 'N/A' }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-4">
                                        <input type="text" name="customer_name" id="pos_customer_name" class="form-control font-size-13" placeholder="Walk-in Name">
                                    </div>
                                </div>
                            </div>

                            <!-- Medicine Batch Selector -->
                            <div class="mb-3">
                                <label class="form-label fw-medium">Select Medicine & Active Batch <span class="text-danger">*</span></label>
                                <select name="medicine_batch_id" id="pos_batch_select" class="form-select font-size-13" required onchange="updatePosPrice(this)">
                                    <option value="" data-price="0" data-stock="0">-- Choose Medicine Formulation & Batch --</option>
                                    @foreach($batches as $b)
                                        @php
                                            $isLow = $b->quantity_in_stock <= 10;
                                            $isOut = $b->quantity_in_stock == 0;
                                        @endphp
                                        <option value="{{ $b->id }}" data-price="{{ $b->selling_price }}" data-stock="{{ $b->quantity_in_stock }}" data-medname="{{ $b->medicine->brand_name }}" {{ $isOut ? 'disabled' : '' }}>
                                            {{ $b->medicine->brand_name }} ({{ $b->medicine->generic_name }}) • Batch: {{ $b->batch_number }} • ₹{{ number_format($b->selling_price, 2) }} [Stock: {{ $b->quantity_in_stock }} {{ $isLow ? '⚠️ LOW' : '' }}]
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Quantity to Dispense <span class="text-danger">*</span></label>
                                    <input type="number" name="quantity" id="pos_quantity" class="form-control font-size-14 fw-bold" value="1" min="1" required oninput="calculateTotal()">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-medium">Discount / Adjustment (₹)</label>
                                    <input type="number" name="discount_amount" id="pos_discount" class="form-control" value="0" min="0" step="0.5" oninput="calculateTotal()">
                                </div>
                            </div>

                            <!-- Total Price Display Box -->
                            <div class="p-3 bg-light rounded mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-muted font-size-13">Unit MRP:</span>
                                    <span class="fw-medium font-size-13 text-dark" id="display_unit_price">₹0.00</span>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-muted font-size-13">Gross Amount:</span>
                                    <span class="fw-medium font-size-13 text-dark" id="display_gross_price">₹0.00</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted font-size-13">Discount:</span>
                                    <span class="fw-medium font-size-13 text-danger" id="display_discount">-₹0.00</span>
                                </div>
                                <hr class="my-1">
                                <div class="d-flex justify-content-between align-items-center pt-1">
                                    <span class="fw-bold text-dark font-size-15">Net Payable Amount:</span>
                                    <span class="fw-bold text-success font-size-20" id="display_net_total">₹0.00</span>
                                </div>
                            </div>

                            <!-- Payment Method (UPI, Cash, Card) -->
                            <div class="mb-4">
                                <label class="form-label fw-medium d-block">Payment Method (Indian Rails) <span class="text-danger">*</span></label>
                                <div class="btn-group w-100" role="group">
                                    <input type="radio" class="btn-check" name="payment_method" id="pay_upi" value="upi" checked>
                                    <label class="btn btn-outline-info" for="pay_upi">
                                        <i class="bx bx-mobile-alt me-1"></i> UPI (GPay/PhonePe)
                                    </label>

                                    <input type="radio" class="btn-check" name="payment_method" id="pay_cash" value="cash">
                                    <label class="btn btn-outline-success" for="pay_cash">
                                        <i class="bx bx-money me-1"></i> Cash Counter
                                    </label>

                                    <input type="radio" class="btn-check" name="payment_method" id="pay_card" value="card">
                                    <label class="btn btn-outline-primary" for="pay_card">
                                        <i class="bx bx-credit-card me-1"></i> Card (POS Swipe)
                                    </label>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-medium">Pharmacist Dispensing Remarks</label>
                                <input type="text" name="notes" class="form-control font-size-13" placeholder="e.g. Advised 1-0-1 after food with warm water">
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg w-100 waves-effect waves-light font-size-15">
                                <i class="bx bx-check-circle me-1"></i> Complete POS Checkout & Print Bill
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Right: Doctor Prescriptions Queue (Live Rx ready to fulfill) -->
            <div class="col-xl-6">
                <div class="card">
                    <div class="card-header bg-transparent border-bottom d-flex align-items-center justify-content-between">
                        <div>
                            <h5 class="card-title mb-0">
                                <i class="bx bx-clipboard text-danger me-1"></i> Doctor Prescriptions Queue
                            </h5>
                            <small class="text-muted">Un-dispensed prescriptions written by hospital doctors</small>
                        </div>
                        <span class="badge bg-danger font-size-11">{{ $pendingPrescriptions->count() }} Pending Rx</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 520px; overflow-y: auto;">
                            <table class="table table-hover align-middle mb-0 font-size-12">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th>Patient & Doctor</th>
                                        <th>Prescribed Medicine</th>
                                        <th>Dosage / Schedule</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($pendingPrescriptions as $rx)
                                        <tr>
                                            <td>
                                                <div class="fw-bold text-dark font-size-13">{{ $rx->patient->name ?? 'Patient' }}</div>
                                                <small class="text-muted d-block">By: Dr. {{ $rx->doctor->name ?? 'Doctor' }}</small>
                                                <span class="badge bg-light text-muted font-size-10">UHID: {{ $rx->patient->uhid ?? '' }}</span>
                                            </td>
                                            <td>
                                                <div class="fw-bold text-primary font-size-13">
                                                    <i class="bx bx-capsule text-danger me-1"></i> {{ $rx->medicine_name }}
                                                </div>
                                                <small class="text-muted">{{ $rx->duration }} • {{ $rx->instructions }}</small>
                                            </td>
                                            <td>
                                                <span class="badge bg-soft-info text-info font-size-11">{{ $rx->frequency }}</span>
                                                <small class="text-dark d-block mt-1">{{ $rx->dosage }}</small>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-primary" onclick="loadPrescriptionIntoPos({{ $rx->id }}, {{ $rx->patient_id }}, '{{ addslashes($rx->medicine_name) }}')">
                                                    <i class="bx bx-cart-add me-1"></i> Fulfill in POS
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-4 text-muted">
                                                <i class="bx bx-check-circle font-size-24 text-success d-block mb-1"></i>
                                                All doctor prescriptions have been dispensed!
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
    </div>

    <!-- TAB 2: MEDICINE STOCK & BATCHES -->
    <div class="tab-pane" id="inventoryTab" role="tabpanel">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <h5 class="card-title mb-1">Batch-Level Stock & Expiry Ledger</h5>
                        <p class="text-muted font-size-12 mb-0">Detailed inventory tracking per batch with purchase price, selling MRP and rack coordinates</p>
                    </div>
                    <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#newBatchModal">
                        <i class="bx bx-plus me-1"></i> Receive New Stock Batch
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Medicine Brand & Generic</th>
                                <th>Batch #</th>
                                <th>Available Stock</th>
                                <th>Purchase Cost (₹)</th>
                                <th>Selling MRP (₹)</th>
                                <th>Expiry Date</th>
                                <th>Rack Location</th>
                                <th>Inventory Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($batches as $b)
                                @php
                                    $expDate = \Carbon\Carbon::parse($b->expiry_date);
                                    $daysLeft = now()->diffInDays($expDate, false);
                                    $isExpired = $daysLeft < 0;
                                    $isNearExpiry = $daysLeft >= 0 && $daysLeft < 90;
                                    $isLow = $b->quantity_in_stock <= 20;
                                @endphp
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark font-size-13">{{ $b->medicine->brand_name }}</div>
                                        <small class="text-muted">{{ $b->medicine->generic_name }} • {{ $b->medicine->dosage_form }}</small>
                                    </td>
                                    <td><code>{{ $b->batch_number }}</code></td>
                                    <td>
                                        <span class="fw-bold font-size-14 {{ $b->quantity_in_stock == 0 ? 'text-danger' : ($isLow ? 'text-warning' : 'text-success') }}">
                                            {{ $b->quantity_in_stock }} Units
                                        </span>
                                    </td>
                                    <td>₹{{ number_format($b->purchase_price, 2) }}</td>
                                    <td><span class="fw-bold text-primary">₹{{ number_format($b->selling_price, 2) }}</span></td>
                                    <td>
                                        <div>{{ $expDate->format('d M Y') }}</div>
                                        @if($isExpired)
                                            <span class="badge bg-danger font-size-10">Expired</span>
                                        @elseif($isNearExpiry)
                                            <span class="badge bg-warning font-size-10">Expires in {{ $daysLeft }} days</span>
                                        @else
                                            <small class="text-success font-size-11">{{ $daysLeft }} days left</small>
                                        @endif
                                    </td>
                                    <td><span class="badge bg-light text-dark">{{ $b->rack_location ?? 'Shelf A-1' }}</span></td>
                                    <td>
                                        @if($b->quantity_in_stock == 0)
                                            <span class="badge bg-danger font-size-11">Out of Stock</span>
                                        @elseif($isLow)
                                            <span class="badge bg-warning font-size-11">Low Stock</span>
                                        @else
                                            <span class="badge bg-success font-size-11">Good Stock</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">No stock batches recorded yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- TAB 3: FORMULARY MASTER -->
    <div class="tab-pane" id="formularyTab" role="tabpanel">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h5 class="card-title mb-0">Medicine Master Formulary</h5>
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#newMedModal">
                        <i class="bx bx-plus me-1"></i> Add Medicine Formulation
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Code / Brand</th>
                                <th>Generic Composition</th>
                                <th>Category</th>
                                <th>Dosage Form</th>
                                <th>Strength</th>
                                <th>Total In-Stock</th>
                                <th>Reorder Level</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($medicines as $m)
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark font-size-13">{{ $m->brand_name }}</div>
                                        <small class="text-muted">Code: {{ $m->code }}</small>
                                    </td>
                                    <td>{{ $m->generic_name }}</td>
                                    <td><span class="badge bg-soft-primary text-primary">{{ $m->category }}</span></td>
                                    <td><span class="badge bg-light text-dark">{{ ucfirst($m->dosage_form) }}</span></td>
                                    <td>{{ $m->strength ?? '-' }}</td>
                                    <td>
                                        <span class="fw-bold font-size-13 {{ $m->total_stock <= $m->min_reorder_level ? 'text-danger' : 'text-success' }}">
                                            {{ $m->total_stock }} Units
                                        </span>
                                    </td>
                                    <td>{{ $m->min_reorder_level }} Units</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- TAB 4: DISPENSE AUDIT HISTORY -->
    <div class="tab-pane" id="auditTab" role="tabpanel">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-3">Dispense & Sales Audit History</h5>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 font-size-13">
                        <thead class="table-light">
                            <tr>
                                <th>Date & Time</th>
                                <th>Patient / Customer</th>
                                <th>Medicine & Batch</th>
                                <th>Quantity</th>
                                <th>Total Billed</th>
                                <th>Dispensed By</th>
                                <th>Remarks / Mode</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($dispenses as $d)
                                <tr>
                                    <td><small class="text-muted">{{ $d->dispensed_at ? $d->dispensed_at->format('d M Y, h:i A') : 'N/A' }}</small></td>
                                    <td>
                                        <span class="fw-medium text-dark">{{ $d->patient->name ?? 'Walk-In Customer' }}</span>
                                    </td>
                                    <td>
                                        <span class="fw-bold text-primary">{{ $d->batch->medicine->brand_name ?? 'Medicine' }}</span>
                                        <small class="text-muted d-block font-size-11">Batch: {{ $d->batch->batch_number ?? '' }}</small>
                                    </td>
                                    <td>{{ $d->quantity }} Units</td>
                                    <td><span class="fw-bold text-success">₹{{ number_format($d->total_price, 2) }}</span></td>
                                    <td><small class="text-dark">{{ $d->dispensedBy->name ?? 'Staff' }}</small></td>
                                    <td><small class="text-muted">{{ $d->notes }}</small></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">No dispense records logged yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- TAB 5: PHARMACY OUTLETS & STORES (STANDALONE VS BRANCH CONNECTED) -->
    <div class="tab-pane" id="storesTab" role="tabpanel">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div>
                        <h5 class="card-title text-dark mb-1">
                            <i class="bx bx-store-alt text-success me-2"></i> Pharmacy Outlets & Dispense Counters
                        </h5>
                        <p class="text-muted font-size-12 mb-0">
                            Manage retail counters, IPD pharmacies, and standalone chemist stores connected directly to Super Admin or hospital branches.
                        </p>
                    </div>
                    <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#newStoreModal">
                        <i class="bx bx-plus-circle me-1"></i> Register New Outlet
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light font-size-12 text-uppercase text-muted">
                            <tr>
                                <th class="ps-3">Store Name & Code</th>
                                <th>Store Type</th>
                                <th>Network Governance</th>
                                <th>Drug License No</th>
                                <th>Manager & Contact</th>
                                <th>Location</th>
                                <th class="text-end pe-3">Dedicated POS</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pharmacyStores as $store)
                                <tr>
                                    <td class="ps-3">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-xs me-2">
                                                <span class="avatar-title rounded-circle {{ $store->is_standalone ? 'bg-purple-subtle text-purple' : 'bg-success-subtle text-success' }} font-size-14">
                                                    <i class="bx bx-capsule"></i>
                                                </span>
                                            </div>
                                            <div>
                                                <span class="fw-bold text-dark d-block font-size-13">{{ $store->name }}</span>
                                                <span class="text-muted font-size-11">Code: <code>{{ $store->code }}</code></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark font-size-11 text-capitalize">
                                            {{ str_replace('_', ' ', $store->store_type) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($store->is_standalone)
                                            <span class="badge bg-purple-subtle text-purple font-size-11">
                                                <i class="bx bx-shield-quarter me-1"></i> Standalone (Direct Super Admin)
                                            </span>
                                            <small class="d-block text-muted font-size-10">Direct Central Platform HQ Governance</small>
                                        @else
                                            <span class="badge bg-info-subtle text-info font-size-11">
                                                <i class="bx bx-git-branch me-1"></i> Connected to Branch
                                            </span>
                                            <small class="d-block text-dark font-size-10"><strong>{{ $store->branch->name ?? 'Hospital Branch' }}</strong></small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="font-size-12 font-monospace">{{ $store->license_number ?? 'DL-20B-PENDING' }}</span>
                                    </td>
                                    <td>
                                        <div class="font-size-12 text-dark fw-medium">{{ $store->manager_name ?? 'Chief Pharmacist' }}</div>
                                        <small class="text-muted font-size-11">{{ $store->phone ?? 'N/A' }}</small>
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $store->address ?? 'Main Campus Ground Floor' }}</small>
                                    </td>
                                    <td class="text-end pe-3">
                                        <a href="{{ route('outlet.pos', ['outlet' => $store->slug ?? Str::slug($store->code)]) }}" class="btn btn-sm btn-outline-success">
                                            <i class="bx bx-cart me-1"></i> Open POS
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">No pharmacy outlets registered yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Receive Stock Batch -->
<div class="modal fade" id="newBatchModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title text-white">
                    <i class="bx bx-package me-1"></i> Stock Replenishment / Receive Batch
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('pharmacy.batches.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-medium">Medicine Formulary Item <span class="text-danger">*</span></label>
                        <select name="medicine_id" class="form-select" required>
                            @foreach($medicines as $med)
                                <option value="{{ $med->id }}">{{ $med->brand_name }} ({{ $med->generic_name }}) - {{ $med->dosage_form }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-medium">Batch Number <span class="text-danger">*</span></label>
                            <input type="text" name="batch_number" class="form-control text-uppercase" placeholder="e.g. DL-2026-99" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-medium">Expiry Date <span class="text-danger">*</span></label>
                            <input type="date" name="expiry_date" class="form-control" value="{{ now()->addMonths(18)->format('Y-m-d') }}" required>
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-medium">Purchase Cost (₹) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="purchase_price" class="form-control" placeholder="e.g. 18.00" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-medium">Selling MRP (₹) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="selling_price" class="form-control" placeholder="e.g. 32.50" required>
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-medium">Quantity Received <span class="text-danger">*</span></label>
                            <input type="number" name="quantity_in_stock" class="form-control" placeholder="e.g. 200" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-medium">Storage Rack Location</label>
                            <input type="text" name="rack_location" class="form-control" placeholder="e.g. Shelf B-04">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bx bx-check me-1"></i> Add Stock to Inventory
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Add New Medicine to Formulary -->
<div class="modal fade" id="newMedModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white">
                    <i class="bx bx-plus-medical me-1"></i> Add Medicine to Hospital Formulary
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('pharmacy.medicines.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-medium">Brand Name <span class="text-danger">*</span></label>
                            <input type="text" name="brand_name" class="form-control" placeholder="e.g. Dolo 650" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-medium">Generic Composition <span class="text-danger">*</span></label>
                            <input type="text" name="generic_name" class="form-control" placeholder="e.g. Paracetamol" required>
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-medium">Category <span class="text-danger">*</span></label>
                            <input type="text" name="category" class="form-control" placeholder="e.g. Analgesic / Antipyretic" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-medium">Dosage Form <span class="text-danger">*</span></label>
                            <select name="dosage_form" class="form-select" required>
                                <option value="tablet">Tablet</option>
                                <option value="capsule">Capsule</option>
                                <option value="syrup">Syrup</option>
                                <option value="injection">Injection</option>
                                <option value="iv_fluid">IV Fluid</option>
                                <option value="ointment">Ointment</option>
                                <option value="inhaler">Inhaler</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-medium">Strength</label>
                            <input type="text" name="strength" class="form-control" placeholder="e.g. 650mg">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-medium">Min Reorder Level <span class="text-danger">*</span></label>
                            <input type="number" name="min_reorder_level" class="form-control" value="50" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-medium">Pharmaceutical Manufacturer</label>
                        <input type="text" name="manufacturer" class="form-control" placeholder="e.g. Micro Labs Ltd. / Cipla">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Formulary Item</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Register Pharmacy Store (Standalone vs Connected) -->
<div class="modal fade" id="newStoreModal" tabindex="-1" aria-labelledby="newStoreModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title text-white" id="newStoreModalLabel">
                    <i class="bx bx-store me-1"></i> Register Pharmacy Store / Outlet
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('pharmacy.stores.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Network Connection Option <span class="text-danger">*</span></label>
                            <select name="connection_mode" id="pharmacy_connection_mode" class="form-select" required onchange="handlePharmacyConnectionChange()">
                                <option value="branch_connected" selected>🏢 Connected to Hospital Branch</option>
                                <option value="standalone">⭐ Standalone Chemist / Direct Super Admin HQ</option>
                            </select>
                            <small class="text-muted">Standalone stores operate independently directly under Super Admin, not under any hospital branch.</small>
                        </div>

                        <div class="col-md-6" id="pharmacy_branch_container">
                            <label class="form-label fw-medium">Parent Hospital Branch <span class="text-danger">*</span></label>
                            <select name="branch_id" id="pharmacy_branch_id" class="form-select">
                                <option value="">-- Select Hospital Branch --</option>
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}">{{ $b->name }} ({{ $b->city }})</option>
                                @endforeach
                            </select>
                            <small class="text-muted">The hospital branch this counter/store operates inside or under.</small>
                        </div>

                        <div class="col-md-8">
                            <label class="form-label fw-medium">Pharmacy Outlet Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Apollo Retail Pharmacy Chembur or Central 24x7 Emergency Counter" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-medium">Outlet Code <span class="text-danger">*</span></label>
                            <input type="text" name="code" class="form-control text-uppercase" placeholder="e.g. PHARM-CHEMBUR-01" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-medium">Store Type <span class="text-danger">*</span></label>
                            <select name="store_type" class="form-select" required>
                                <option value="retail_counter" selected>Retail Dispense Counter (OPD)</option>
                                <option value="ipd_pharmacy">IPD & Inpatient Floor Pharmacy</option>
                                <option value="emergency_pharmacy">24x7 Emergency / Casualty Desk</option>
                                <option value="standalone_retail">Standalone Retail Chemist Store</option>
                                <option value="central_warehouse">Central Warehouse & Distribution Hub</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-medium">Drug License Number (Form 20B/21B)</label>
                            <input type="text" name="license_number" class="form-control" placeholder="e.g. DL-20B-MH-89212">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-medium">Chief Pharmacist / Manager Name</label>
                            <input type="text" name="manager_name" class="form-control" placeholder="e.g. Pharmacist Ramesh Patel, B.Pharm">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Contact Phone</label>
                            <input type="text" name="phone" class="form-control" placeholder="e.g. +91 98200 44556">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-medium">Contact Email</label>
                            <input type="email" name="email" class="form-control" placeholder="e.g. pharmacy.chembur@apollocare.in">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Physical Location / Address</label>
                            <input type="text" name="address" class="form-control" placeholder="e.g. Ground Floor, Outpatient Block A">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bx bx-check-circle me-1"></i> Register Pharmacy Outlet
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function handlePharmacyConnectionChange() {
        const mode = document.getElementById('pharmacy_connection_mode').value;
        const container = document.getElementById('pharmacy_branch_container');
        const select = document.getElementById('pharmacy_branch_id');
        if (mode === 'standalone') {
            container.style.display = 'none';
            select.value = '';
        } else {
            container.style.display = 'block';
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        if (document.getElementById('pharmacy_connection_mode')) {
            handlePharmacyConnectionChange();
        }
    });

    function updatePosPrice(select) {
        var option = select.options[select.selectedIndex];
        var price = parseFloat(option.getAttribute('data-price') || 0);
        document.getElementById('pos_quantity').value = 1;
        calculateTotal();
    }

    function calculateTotal() {
        var select = document.getElementById('pos_batch_select');
        var option = select.options[select.selectedIndex];
        var unitPrice = parseFloat(option.getAttribute('data-price') || 0);
        var qty = parseInt(document.getElementById('pos_quantity').value || 1);
        var discount = parseFloat(document.getElementById('pos_discount').value || 0);

        var gross = unitPrice * qty;
        var net = Math.max(0, gross - discount);

        document.getElementById('display_unit_price').innerText = '₹' + unitPrice.toFixed(2);
        document.getElementById('display_gross_price').innerText = '₹' + gross.toFixed(2);
        document.getElementById('display_discount').innerText = '-₹' + discount.toFixed(2);
        document.getElementById('display_net_total').innerText = '₹' + net.toFixed(2);
    }

    function loadPrescriptionIntoPos(rxId, patientId, medicineName) {
        // Set prescription ID
        document.getElementById('pos_prescription_id').value = rxId;
        
        // Select patient
        var patientSelect = document.getElementById('pos_patient_select');
        if (patientSelect) {
            patientSelect.value = patientId;
        }

        // Try to find matching batch by medicine name
        var batchSelect = document.getElementById('pos_batch_select');
        var found = false;
        for (var i = 0; i < batchSelect.options.length; i++) {
            var opt = batchSelect.options[i];
            var medName = opt.getAttribute('data-medname') || '';
            if (medName.toLowerCase().includes(medicineName.toLowerCase()) || medicineName.toLowerCase().includes(medName.toLowerCase())) {
                batchSelect.selectedIndex = i;
                updatePosPrice(batchSelect);
                found = true;
                break;
            }
        }

        // Focus POS form
        window.scrollTo({ top: 300, behavior: 'smooth' });
    }
</script>
@endsection

