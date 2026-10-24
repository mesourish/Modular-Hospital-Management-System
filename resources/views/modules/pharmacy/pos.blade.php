@extends('layouts.skote')

@section('title', 'Pharmacy Point-Of-Sale (POS) & Real-Time Stock Counter')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <div>
                <h4 class="mb-sm-0 font-size-18">Pharmacy POS Terminal & Stock Counter</h4>
                <div class="text-muted font-size-13 mt-1">
                    Outlet: <span class="badge bg-primary-subtle text-primary fw-bold font-size-12">{{ $activeBranch->name ?? 'Main Hospital Pharmacy' }}</span>
                    @if($activeBranch && $activeBranch->slug)
                        <span class="badge bg-light text-secondary border font-mono">/outlet/{{ $activeBranch->slug }}/pos</span>
                    @endif
                    • Exact Rack, Shelf & Box Tracking • Real-time Atomic Deductions
                </div>
            </div>
            <div class="page-title-right d-flex gap-2">
                <a href="{{ route('pharmacy.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bx bx-package me-1"></i> Stock Ledger & Batches
                </a>
                <a href="{{ route('billing.index') }}" class="btn btn-sm btn-outline-primary">
                    <i class="bx bx-receipt me-1"></i> Billing Desk
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

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bx bx-error-circle me-2 font-size-16 align-middle"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="row g-3">
    <!-- Left Column: Search & Medicine Inventory Browser -->
    <div class="col-xl-7">
        <div class="card h-100 shadow-sm border-0">
            <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <div class="avatar-xs bg-primary text-white rounded-circle d-flex align-items-center justify-content-center">
                        <i class="bx bx-search-alt font-size-16"></i>
                    </div>
                    <h5 class="card-title mb-0 font-size-15">Medication Formulary & Rack Locator</h5>
                </div>
                <span class="badge bg-success-subtle text-success font-size-12 px-2 py-1">
                    {{ $batches->count() }} Batches In Stock
                </span>
            </div>
            <div class="card-body p-3">
                <!-- Search Bar -->
                <div class="input-group mb-3">
                    <span class="input-group-text bg-light border-end-0"><i class="bx bx-search text-muted"></i></span>
                    <input type="text" id="medicineSearchInput" class="form-control border-start-0" placeholder="Search by brand name, generic salt, batch number, or rack..." autofocus>
                    <button class="btn btn-outline-secondary" type="button" onclick="document.getElementById('medicineSearchInput').value=''; filterMedicines();">Clear</button>
                </div>

                <!-- Live Inventory Items Table -->
                <div class="table-responsive" style="max-height: 520px; overflow-y: auto;">
                    <table class="table table-hover align-middle table-nowrap mb-0" id="medicinesTable">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th>Medicine & Generic</th>
                                <th>Rack & Shelf</th>
                                <th>Batch / Exp</th>
                                <th>Available</th>
                                <th>Price</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($batches as $b)
                                <tr class="medicine-row"
                                    data-id="{{ $b->id }}"
                                    data-brand="{{ strtolower($b->medicine->brand_name ?? '') }}"
                                    data-generic="{{ strtolower($b->medicine->generic_name ?? '') }}"
                                    data-batch="{{ strtolower($b->batch_number ?? '') }}"
                                    data-rack="{{ strtolower(($b->rack_location ?? '').' '.($b->shelf_number ?? '')) }}"
                                    data-price="{{ $b->selling_price }}"
                                    data-stock="{{ $b->quantity_in_stock }}"
                                    data-hsn="{{ $b->medicine->hsn_code ?? '3004' }}">
                                    <td>
                                        <div class="fw-bold text-dark font-size-13">{{ $b->medicine->brand_name ?? 'Medicine' }}</div>
                                        <div class="text-muted font-size-11">{{ $b->medicine->generic_name ?? '' }} • <span class="badge bg-light text-secondary">{{ $b->medicine->dosage_form ?? 'tab' }}</span></div>
                                    </td>
                                    <td>
                                        <span class="badge bg-info-subtle text-info border font-size-11">
                                            <i class="bx bx-map-pin me-1"></i>{{ $b->rack_location ?: 'Rack A' }}
                                        </span>
                                        @if($b->shelf_number || $b->box_number)
                                            <div class="text-muted font-size-10 mt-1">
                                                {{ $b->shelf_number ? 'Shelf: '.$b->shelf_number : '' }} {{ $b->box_number ? 'Box: '.$b->box_number : '' }}
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="font-mono font-size-11 fw-semibold text-primary">#{{ $b->batch_number }}</div>
                                        <div class="text-muted font-size-10">Exp: {{ $b->expiry_date ? \Carbon\Carbon::parse($b->expiry_date)->format('m/y') : 'N/A' }}</div>
                                    </td>
                                    <td>
                                        <span class="badge {{ $b->quantity_in_stock > 20 ? 'bg-success' : 'bg-warning' }} font-mono font-size-12 px-2 py-1">
                                            {{ $b->quantity_in_stock }} units
                                        </span>
                                    </td>
                                    <td>
                                        <div class="font-mono fw-bold text-dark font-size-13">₹{{ number_format($b->selling_price, 2) }}</div>
                                    </td>
                                    <td class="text-end">
                                        <button type="button" class="btn btn-sm btn-primary waves-effect waves-light px-2"
                                                onclick="addToCart({{ $b->id }}, '{{ addslashes($b->medicine->brand_name ?? 'Medicine') }}', '{{ $b->batch_number }}', '{{ $b->rack_location }} {{ $b->shelf_number }}', {{ $b->selling_price }}, {{ $b->quantity_in_stock }}, '{{ $b->medicine->hsn_code ?? '3004' }}')">
                                            <i class="bx bx-cart-add font-size-15 align-middle"></i> Add
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        No active medicine batches in stock. Please receive a batch first.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Interactive POS Register & GST Checkout -->
    <div class="col-xl-5">
        <div class="card h-100 shadow-sm border-0">
            <div class="card-header bg-primary text-white py-3 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <i class="bx bx-cart font-size-18"></i>
                    <h5 class="card-title text-white mb-0 font-size-15">Sales Basket & Checkout</h5>
                </div>
                <button type="button" class="btn btn-sm btn-light text-primary py-0 px-2 font-size-11 fw-bold" onclick="clearCart()">
                    Reset Cart
                </button>
            </div>
            <div class="card-body p-3">
                <form id="posCheckoutForm" action="{{ route('pharmacy.pos.checkout') }}" method="POST">
                    @csrf
                    <input type="hidden" name="cart_data" id="cartDataInput" value="[]">

                    <!-- Customer / Patient Selector -->
                    <div class="mb-3">
                        <label class="form-label font-size-12 fw-semibold text-uppercase text-muted">Patient / Customer</label>
                        <div class="row g-2">
                            <div class="col-7">
                                <select name="patient_id" id="patientSelect" class="form-select font-size-13" onchange="toggleWalkIn()">
                                    <option value="">-- OTC Walk-in Customer --</option>
                                    @foreach($patients as $p)
                                        <option value="{{ $p->id }}" data-name="{{ $p->full_name }}" data-phone="{{ $p->phone }}" data-allergies="{{ $p->allergies }}">
                                            {{ $p->mrn }} - {{ $p->full_name }} ({{ $p->phone }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-5">
                                <input type="text" name="walkin_name" id="walkinName" class="form-control font-size-13" placeholder="Walk-in Name">
                            </div>
                        </div>
                        <div id="allergyAlert" class="alert alert-danger p-1 mt-1 font-size-11 mb-0 d-none">
                            <i class="bx bx-error me-1"></i><strong>Patient Allergies:</strong> <span id="allergyText"></span>
                        </div>
                    </div>

                    <!-- Prescribing Doctor -->
                    <div class="mb-3">
                        <label class="form-label font-size-12 fw-semibold text-uppercase text-muted">Prescribing Clinician</label>
                        <select name="doctor_id" class="form-select font-size-13">
                            <option value="">-- Self / Over-The-Counter --</option>
                            @foreach($doctors as $d)
                                <option value="{{ $d->id }}">Dr. {{ $d->name }} ({{ ucfirst($d->role) }})</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Cart Items List -->
                    <div class="border rounded p-2 mb-3 bg-light" style="min-height: 180px; max-height: 240px; overflow-y: auto;">
                        <table class="table table-sm table-borderless align-middle mb-0" id="cartTable">
                            <thead>
                                <tr class="text-muted font-size-11 border-bottom">
                                    <th>Item</th>
                                    <th style="width: 25%;">Qty</th>
                                    <th class="text-end" style="width: 25%;">Amount</th>
                                    <th style="width: 5%;"></th>
                                </tr>
                            </thead>
                            <tbody id="cartTableBody">
                                <tr id="emptyCartRow">
                                    <td colspan="4" class="text-center py-4 text-muted font-size-12">
                                        <i class="bx bx-basket font-size-24 d-block mb-1 text-secondary"></i>
                                        Basket is empty. Select items from left table.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Financial Summary & GST Computation -->
                    <div class="border rounded p-3 mb-3 bg-white">
                        <div class="d-flex justify-content-between font-size-12 mb-1">
                            <span class="text-muted">Sub Total Taxable:</span>
                            <span class="font-mono fw-semibold" id="subTotalDisplay">₹0.00</span>
                        </div>
                        <div class="d-flex justify-content-between font-size-12 mb-1">
                            <span class="text-muted">CGST (6%):</span>
                            <span class="font-mono text-primary" id="cgstDisplay">₹0.00</span>
                        </div>
                        <div class="d-flex justify-content-between font-size-12 mb-1">
                            <span class="text-muted">SGST (6%):</span>
                            <span class="font-mono text-primary" id="sgstDisplay">₹0.00</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="font-size-12 text-muted">Instant Cash Discount (₹):</span>
                            <input type="number" step="0.5" min="0" name="overall_discount" id="overallDiscount" class="form-control form-control-sm font-mono text-end" style="width: 100px;" value="0" oninput="recalcTotals()">
                        </div>

                        <div class="p-2 rounded bg-primary text-white d-flex justify-content-between align-items-center">
                            <span class="fw-bold font-size-13">NET AMOUNT:</span>
                            <span class="font-mono fw-bolder font-size-18" id="netPayableDisplay">₹0.00</span>
                        </div>
                    </div>

                    <!-- Payment Mode -->
                    <div class="mb-3">
                        <label class="form-label font-size-12 fw-semibold text-uppercase text-muted">Payment Tender Mode</label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="payment_method" id="modeCash" value="cash" checked>
                            <label class="btn btn-outline-secondary font-size-12" for="modeCash"><i class="bx bx-money me-1"></i> Cash</label>

                            <input type="radio" class="btn-check" name="payment_method" id="modeUpi" value="upi">
                            <label class="btn btn-outline-secondary font-size-12" for="modeUpi"><i class="bx bx-qr-scan me-1"></i> UPI QR</label>

                            <input type="radio" class="btn-check" name="payment_method" id="modeCard" value="card">
                            <label class="btn btn-outline-secondary font-size-12" for="modeCard"><i class="bx bx-credit-card me-1"></i> Card</label>

                            <input type="radio" class="btn-check" name="payment_method" id="modeIns" value="insurance">
                            <label class="btn btn-outline-secondary font-size-12" for="modeIns"><i class="bx bx-shield me-1"></i> TPA/Claim</label>
                        </div>
                    </div>

                    <button type="submit" id="checkoutBtn" class="btn btn-success btn-lg w-100 font-size-15 fw-bold waves-effect waves-light shadow" disabled>
                        <i class="bx bx-printer me-1"></i> Complete Sale & Print GST Bill
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Recent Counter Invoices Table -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                <h5 class="card-title mb-0 font-size-15"><i class="bx bx-history me-1 text-secondary"></i> Recent POS Counter Invoices</h5>
                <span class="text-muted font-size-12">Click print to re-generate Indian GST tax invoice</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 font-size-13">
                        <thead class="table-light">
                            <tr>
                                <th>Invoice No</th>
                                <th>Date & Time</th>
                                <th>Customer / Patient</th>
                                <th>Items</th>
                                <th>Tax (CGST+SGST)</th>
                                <th>Net Amount</th>
                                <th>Status</th>
                                <th class="text-end">Print Receipt</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentInvoices as $inv)
                                <tr>
                                    <td class="font-mono fw-bold text-primary">{{ $inv->invoice_number }}</td>
                                    <td>{{ \Carbon\Carbon::parse($inv->created_at)->format('d M Y, h:i A') }}</td>
                                    <td>
                                        @if($inv->patient)
                                            <span class="fw-semibold text-dark">{{ $inv->patient->full_name }}</span>
                                            <span class="badge bg-light text-secondary font-mono">{{ $inv->patient->mrn }}</span>
                                        @else
                                            <span class="text-muted">{{ $inv->notes }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $inv->items->count() }} line items</td>
                                    <td class="font-mono text-muted">₹{{ number_format($inv->tax_amount, 2) }}</td>
                                    <td class="font-mono fw-bold text-dark">₹{{ number_format($inv->net_payable, 2) }}</td>
                                    <td>
                                        <span class="badge bg-success font-size-11">{{ strtoupper($inv->payment_status) }}</span>
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('billing.invoices.print', $inv->id) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                            <i class="bx bx-printer me-1"></i> Print GST Bill
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-3 text-muted">No recent POS sales recorded today.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let cart = [];

    function filterMedicines() {
        const query = document.getElementById('medicineSearchInput').value.toLowerCase();
        const rows = document.querySelectorAll('#medicinesTable tbody .medicine-row');

        rows.forEach(row => {
            const brand = row.dataset.brand;
            const generic = row.dataset.generic;
            const batch = row.dataset.batch;
            const rack = row.dataset.rack;

            if (brand.includes(query) || generic.includes(query) || batch.includes(query) || rack.includes(query)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    document.getElementById('medicineSearchInput').addEventListener('input', filterMedicines);

    function toggleWalkIn() {
        const select = document.getElementById('patientSelect');
        const selected = select.options[select.selectedIndex];
        const walkin = document.getElementById('walkinName');
        const alertBox = document.getElementById('allergyAlert');
        const allergyText = document.getElementById('allergyText');

        if (select.value) {
            walkin.value = selected.dataset.name;
            walkin.readOnly = true;

            if (selected.dataset.allergies && selected.dataset.allergies.trim() !== '') {
                allergyText.innerText = selected.dataset.allergies;
                alertBox.classList.remove('d-none');
            } else {
                alertBox.classList.add('d-none');
            }
        } else {
            walkin.value = '';
            walkin.readOnly = false;
            alertBox.classList.add('d-none');
        }
    }

    function addToCart(batchId, brandName, batchNo, rackLocation, unitPrice, maxStock, hsn) {
        const existing = cart.find(item => item.batch_id === batchId);
        if (existing) {
            if (existing.quantity + 1 > maxStock) {
                alert(`Cannot exceed available batch stock (${maxStock} units).`);
                return;
            }
            existing.quantity += 1;
        } else {
            cart.push({
                batch_id: batchId,
                name: brandName,
                batch_no: batchNo,
                rack: rackLocation,
                unit_price: parseFloat(unitPrice),
                max_stock: parseInt(maxStock),
                hsn: hsn,
                quantity: 1,
                discount_percent: 0,
                gst_rate: 12
            });
        }
        renderCart();
    }

    function updateCartQty(batchId, newQty) {
        const item = cart.find(i => i.batch_id === batchId);
        if (!item) return;

        newQty = parseInt(newQty);
        if (newQty <= 0) {
            removeFromCart(batchId);
            return;
        }
        if (newQty > item.max_stock) {
            alert(`Stock limit reached! Only ${item.max_stock} units available in ${item.rack}.`);
            item.quantity = item.max_stock;
        } else {
            item.quantity = newQty;
        }
        renderCart();
    }

    function removeFromCart(batchId) {
        cart = cart.filter(i => i.batch_id !== batchId);
        renderCart();
    }

    function clearCart() {
        cart = [];
        renderCart();
    }

    function renderCart() {
        const tbody = document.getElementById('cartTableBody');
        const checkoutBtn = document.getElementById('checkoutBtn');

        if (cart.length === 0) {
            tbody.innerHTML = `
                <tr id="emptyCartRow">
                    <td colspan="4" class="text-center py-4 text-muted font-size-12">
                        <i class="bx bx-basket font-size-24 d-block mb-1 text-secondary"></i>
                        Basket is empty. Select items from left table.
                    </td>
                </tr>
            `;
            checkoutBtn.disabled = true;
            document.getElementById('cartDataInput').value = '[]';
            recalcTotals();
            return;
        }

        checkoutBtn.disabled = false;
        let html = '';

        cart.forEach(item => {
            const lineTotal = item.quantity * item.unit_price;
            html += `
                <tr class="border-bottom">
                    <td>
                        <div class="fw-bold font-size-12 text-dark">${item.name}</div>
                        <div class="text-muted font-size-10 font-mono">#${item.batch_no} • ${item.rack}</div>
                    </td>
                    <td>
                        <div class="input-group input-group-sm">
                            <input type="number" min="1" max="${item.max_stock}" class="form-control form-control-sm text-center font-mono"
                                   value="${item.quantity}" onchange="updateCartQty(${item.batch_id}, this.value)">
                        </div>
                    </td>
                    <td class="text-end font-mono font-size-12 fw-bold text-dark">
                        ₹${lineTotal.toFixed(2)}
                    </td>
                    <td class="text-end">
                        <button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="removeFromCart(${item.batch_id})">
                            <i class="bx bx-trash font-size-14"></i>
                        </button>
                    </td>
                </tr>
            `;
        });

        tbody.innerHTML = html;
        document.getElementById('cartDataInput').value = JSON.stringify(cart);
        recalcTotals();
    }

    function recalcTotals() {
        let subtotal = 0;
        cart.forEach(item => {
            subtotal += item.quantity * item.unit_price;
        });

        const overallDiscount = parseFloat(document.getElementById('overallDiscount').value) || 0;
        const taxable = Math.max(0, subtotal - overallDiscount);

        const cgst = taxable * 0.06;
        const sgst = taxable * 0.06;
        const net = taxable + cgst + sgst;

        document.getElementById('subTotalDisplay').innerText = '₹' + subtotal.toFixed(2);
        document.getElementById('cgstDisplay').innerText = '₹' + cgst.toFixed(2);
        document.getElementById('sgstDisplay').innerText = '₹' + sgst.toFixed(2);
        document.getElementById('netPayableDisplay').innerText = '₹' + net.toFixed(2);
    }
</script>
@endpush
