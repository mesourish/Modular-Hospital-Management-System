@extends('layouts.skote')

@section('title', 'Multi-Branch Hierarchy & Doctor Earnings Center')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <div>
                <h4 class="mb-sm-0 font-size-18">Hierarchical Multi-Branch Operations & Doctor Earnings Center</h4>
                <p class="text-muted font-size-13 mb-0">{{ $hospital->name }} • Centralized Multi-Unit Healthcare Network</p>
            </div>
            <div class="page-title-right d-flex gap-2">
                <button type="button" class="btn btn-primary waves-effect waves-light shadow-sm" data-bs-toggle="modal" data-bs-target="#newBranchModal">
                    <i class="bx bx-plus-circle me-1"></i> Add Facility / Branch (Standalone or Connected)
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Architecture & Hierarchy Context Banner -->
<div class="card border-primary border-top border-3 shadow-sm mb-4">
    <div class="card-body p-3">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="d-flex align-items-center">
                <div class="avatar-sm me-3">
                    <span class="avatar-title bg-primary-subtle text-primary rounded-circle font-size-20">
                        <i class="bx bx-git-branch"></i>
                    </span>
                </div>
                <div>
                    <h5 class="font-size-15 mb-1 text-dark">
                        @if($selectedBranch)
                            Branch Scoped Operational View: <span class="text-primary">{{ $selectedBranch->name }} ({{ $selectedBranch->code }})</span>
                            @if($selectedBranch->is_standalone)
                                <span class="badge bg-purple-subtle text-purple ms-1 font-size-11">Direct Super Admin Standalone Unit</span>
                            @endif
                        @else
                            Consolidated Network Financials & Hierarchy (All Branches & Facilities)
                        @endif
                    </h5>
                    <p class="text-muted font-size-13 mb-0">
                        Hierarchical governance: Main Flagship HQ oversees financial aggregates, doctor clinical performance, and branch/standalone satellite facilities.
                    </p>
                </div>
            </div>

            <!-- Branch Selector Filter Form -->
            <form action="{{ route('branches.index') }}" method="GET" class="d-flex align-items-center gap-2">
                <label for="branch_id" class="text-muted font-size-12 fw-medium text-nowrap mb-0">Select Branch / Facility:</label>
                <select name="branch_id" id="branch_id" class="form-select form-select-sm" onchange="this.form.submit()" style="min-width: 280px;">
                    <option value="all" {{ !$selectedBranch ? 'selected' : '' }}>🏥 Consolidated Network (All Branches)</option>
                    @foreach($branches as $b)
                        <option value="{{ $b->id }}" {{ $selectedBranch && $selectedBranch->id == $b->id ? 'selected' : '' }}>
                            @if($b->is_main_branch)
                                ⭐ [MAIN HQ] {{ $b->name }}
                            @elseif($b->is_standalone)
                                🏢 [STANDALONE] {{ $b->name }}
                            @else
                                🏢 {{ $b->name }}
                            @endif
                            ({{ $b->city }})
                        </option>
                    @endforeach
                </select>
            </form>
        </div>
    </div>
</div>

<!-- Financial & Clinical Summary KPI Cards -->
<div class="row">
    <div class="col-xl-3 col-md-6">
        <div class="card mini-stats-wid">
            <div class="card-body">
                <div class="d-flex">
                    <div class="flex-grow-1">
                        <p class="text-muted fw-medium mb-1">Total Invoiced (Billed)</p>
                        <h4 class="mb-0 text-primary">₹{{ number_format($totalInvoiced, 2) }}</h4>
                        <small class="text-muted font-size-11">Total patient billings generated</small>
                    </div>
                    <div class="avatar-sm rounded-circle bg-primary-subtle align-self-center mini-stat-icon">
                        <span class="avatar-title rounded-circle bg-primary text-white">
                            <i class="bx bx-receipt font-size-22"></i>
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
                        <p class="text-muted fw-medium mb-1">Total Collections (Received)</p>
                        <h4 class="mb-0 text-success">₹{{ number_format($totalCollected, 2) }}</h4>
                        <small class="text-success font-size-11">
                            <i class="bx bx-check"></i> {{ $totalInvoiced > 0 ? round(($totalCollected / $totalInvoiced) * 100, 1) : 0 }}% collected
                        </small>
                    </div>
                    <div class="avatar-sm rounded-circle bg-success-subtle align-self-center mini-stat-icon">
                        <span class="avatar-title rounded-circle bg-success text-white">
                            <i class="bx bx-wallet font-size-22"></i>
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
                        <p class="text-muted fw-medium mb-1">Doctor Clinical Revenue</p>
                        <h4 class="mb-0 text-info">₹{{ number_format($totalDoctorClinicalRevenue, 2) }}</h4>
                        <small class="text-info font-size-11">
                            <i class="bx bx-plus-medical"></i> OPD + OT Surgeries
                        </small>
                    </div>
                    <div class="avatar-sm rounded-circle bg-info-subtle align-self-center mini-stat-icon">
                        <span class="avatar-title rounded-circle bg-info text-white">
                            <i class="bx bx-user-voice font-size-22"></i>
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
                        <p class="text-muted fw-medium mb-1">Doctor Share vs Hospital</p>
                        <h4 class="mb-0 text-dark">₹{{ number_format($totalDoctorPayout, 0) }}</h4>
                        <small class="text-success font-size-11">
                            ₹{{ number_format($totalHospitalRetainedShare, 0) }} (30% Hospital Fee)
                        </small>
                    </div>
                    <div class="avatar-sm rounded-circle bg-warning-subtle align-self-center mini-stat-icon">
                        <span class="avatar-title rounded-circle bg-warning text-white">
                            <i class="bx bx-pie-chart-alt-2 font-size-22"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- DOCTOR EARNINGS & CLINICAL PERFORMANCE SECTION (BRANCH SCOPED) -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom py-3">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
                <h5 class="card-title text-dark mb-1">
                    <i class="bx bx-stethoscope text-primary me-2"></i> Doctor Earnings & Revenue Statistics
                    @if($selectedBranch)
                        <span class="badge bg-primary-subtle text-primary font-size-12 ms-2">Branch: {{ $selectedBranch->name }}</span>
                    @else
                        <span class="badge bg-secondary-subtle text-secondary font-size-12 ms-2">Consolidated Network</span>
                    @endif
                </h5>
                <p class="text-muted font-size-12 mb-0">
                    Live clinical revenue generated per doctor for this branch across Outpatient Consultations and Inpatient / OT Surgeries.
                </p>
            </div>
            <div class="d-flex gap-2 align-items-center">
                <span class="badge bg-soft-success text-success px-2 py-1 font-size-11">
                    <i class="bx bx-check-shield me-1"></i> Revenue Model: 70% Doctor Payout / 30% Branch Retained
                </span>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light font-size-12 text-uppercase text-muted">
                    <tr>
                        <th class="ps-3">Doctor & Clinical Specialty</th>
                        <th>Assigned Branch</th>
                        <th class="text-center">OPD Appointments</th>
                        <th class="text-end">OPD Fees (₹)</th>
                        <th class="text-center">OT Surgeries</th>
                        <th class="text-end">OT Revenue (₹)</th>
                        <th class="text-end">Gross Generated (₹)</th>
                        <th class="text-end text-success pe-3">Doctor Payout (70%)</th>
                        <th class="text-end pe-3">Branch Retained (30%)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($doctorEarnings as $docStat)
                        @php
                            $doc = $docStat['doctor'];
                            $emp = $docStat['employee'];
                        @endphp
                        <tr>
                            <td class="ps-3">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-xs me-2">
                                        <span class="avatar-title rounded-circle bg-primary-subtle text-primary fw-bold font-size-12">
                                            {{ strtoupper(substr($doc->name, 0, 2)) }}
                                        </span>
                                    </div>
                                    <div>
                                        <span class="fw-bold text-dark d-block font-size-13">{{ $doc->name }}</span>
                                        <small class="text-muted">
                                            {{ $emp->designation->name ?? 'Specialist Physician' }} • Reg: <code>{{ $emp->medical_license_no ?? 'MCI-'.rand(10000, 99999) }}</code>
                                        </small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @php
                                    $docBranch = $branches->firstWhere('id', $doc->branch_id);
                                @endphp
                                @if($docBranch)
                                    <span class="badge bg-light text-dark font-size-11">
                                        {{ $docBranch->name }}
                                    </span>
                                @else
                                    <span class="badge bg-light text-muted font-size-11">Central Medical Pool</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge bg-soft-info text-info font-size-12 px-2 py-1">
                                    {{ $docStat['appointment_count'] }} Visits
                                </span>
                                @if($docStat['completed_appointments'] > 0)
                                    <small class="d-block text-success font-size-10">{{ $docStat['completed_appointments'] }} Completed</small>
                                @endif
                            </td>
                            <td class="text-end font-size-13 fw-semibold text-dark">
                                ₹{{ number_format($docStat['opd_revenue'], 2) }}
                            </td>
                            <td class="text-center">
                                @if($docStat['surgery_count'] > 0)
                                    <span class="badge bg-soft-danger text-danger font-size-12 px-2 py-1">
                                        {{ $docStat['surgery_count'] }} Procedures
                                    </span>
                                @else
                                    <span class="text-muted font-size-12">-</span>
                                @endif
                            </td>
                            <td class="text-end font-size-13 fw-semibold text-dark">
                                ₹{{ number_format($docStat['surgical_revenue'], 2) }}
                            </td>
                            <td class="text-end font-size-14 fw-bold text-primary">
                                ₹{{ number_format($docStat['gross_earned'], 2) }}
                            </td>
                            <td class="text-end pe-3">
                                <span class="fw-bold text-success font-size-13">₹{{ number_format($docStat['doctor_share'], 2) }}</span>
                                <small class="d-block text-muted font-size-10">70% Clinical Share</small>
                            </td>
                            <td class="text-end pe-3">
                                <span class="fw-semibold text-dark font-size-13">₹{{ number_format($docStat['hospital_share'], 2) }}</span>
                                <small class="d-block text-muted font-size-10">30% Hospital Fee</small>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">
                                <i class="bx bx-user-x font-size-24 d-block mb-1"></i> No doctor clinical records found for this branch filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="table-light">
                    <tr class="fw-bold font-size-13">
                        <td colspan="6" class="ps-3 text-uppercase">Total Branch Clinical Earnings</td>
                        <td class="text-end text-primary">₹{{ number_format($totalDoctorClinicalRevenue, 2) }}</td>
                        <td class="text-end text-success pe-3">₹{{ number_format($totalDoctorPayout, 2) }}</td>
                        <td class="text-end pe-3">₹{{ number_format($totalHospitalRetainedShare, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<div class="row">
    <!-- Branch Hierarchy Breakdown Table -->
    <div class="col-xl-8">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <h5 class="card-title mb-1">Hierarchical Facilities & Branch Network Breakdown</h5>
                        <p class="text-muted font-size-12 mb-0">Consolidated operational breakdown across hospital branches and standalone facilities</p>
                    </div>
                    <span class="badge bg-soft-primary text-primary px-3 py-1 font-size-12">
                        <i class="bx bx-rupee"></i> Currency: INR (₹)
                    </span>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Facility / Branch</th>
                                <th>Type & Governance</th>
                                <th>Location & Head</th>
                                <th>Operational Stats</th>
                                <th>Total Invoiced</th>
                                <th>Collections</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($branchFinancials as $bId => $fin)
                                @php 
                                    $branch = $fin['branch'];
                                    $fType = $branch->facility_type ?? 'hospital';
                                @endphp
                                <tr class="{{ $branch->is_main_branch ? 'table-warning-subtle' : '' }}">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($branch->is_main_branch)
                                                <div class="avatar-xs me-2">
                                                    <span class="avatar-title rounded-circle bg-warning text-white font-size-14">
                                                        <i class="bx bxs-crown"></i>
                                                    </span>
                                                </div>
                                                <div>
                                                    <span class="fw-bold text-dark d-block">{{ $branch->name }}</span>
                                                    <span class="badge bg-warning text-dark font-size-10">MAIN BRANCH / PLATFORM HQ</span>
                                                </div>
                                            @elseif($fType === 'pharmacy')
                                                <div class="avatar-xs me-2">
                                                    <span class="avatar-title rounded-circle bg-success text-white font-size-14">
                                                        <i class="bx bx-capsule"></i>
                                                    </span>
                                                </div>
                                                <div>
                                                    <span class="fw-semibold text-dark d-block">{{ $branch->name }}</span>
                                                    <span class="text-muted font-size-11">Code: {{ $branch->code }}</span>
                                                </div>
                                            @elseif($fType === 'blood_bank')
                                                <div class="avatar-xs me-2">
                                                    <span class="avatar-title rounded-circle bg-danger text-white font-size-14">
                                                        <i class="bx bx-donate-blood"></i>
                                                    </span>
                                                </div>
                                                <div>
                                                    <span class="fw-semibold text-dark d-block">{{ $branch->name }}</span>
                                                    <span class="text-muted font-size-11">Code: {{ $branch->code }}</span>
                                                </div>
                                            @else
                                                <div class="avatar-xs me-2">
                                                    <span class="avatar-title rounded-circle bg-light text-primary font-size-14">
                                                        <i class="bx bx-git-branch"></i>
                                                    </span>
                                                </div>
                                                <div>
                                                    <span class="fw-semibold text-dark d-block">{{ $branch->name }}</span>
                                                    <span class="text-muted font-size-11">Code: {{ $branch->code }}</span>
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            @if($fType === 'pharmacy')
                                                <span class="badge bg-success-subtle text-success font-size-11"><i class="bx bx-capsule me-1"></i> Pharmacy Store</span>
                                            @elseif($fType === 'blood_bank')
                                                <span class="badge bg-danger-subtle text-danger font-size-11"><i class="bx bx-donate-blood me-1"></i> Blood Bank</span>
                                            @elseif($fType === 'diagnostic_lab')
                                                <span class="badge bg-info-subtle text-info font-size-11"><i class="bx bx-test-tube me-1"></i> Diagnostics</span>
                                            @else
                                                <span class="badge bg-primary-subtle text-primary font-size-11"><i class="bx bx-hospital me-1"></i> Hospital Branch</span>
                                            @endif
                                        </div>
                                        <div class="mt-1">
                                            @if($branch->is_standalone)
                                                <span class="badge bg-purple-subtle text-purple font-size-10" title="Directly connected to Super Admin / Central Platform HQ">
                                                    <i class="bx bx-shield-quarter me-1"></i> Standalone (Super Admin)
                                                </span>
                                            @elseif($branch->parent_branch_id)
                                                @php $parent = $branches->firstWhere('id', $branch->parent_branch_id); @endphp
                                                <small class="text-muted font-size-10">Connected to: <strong>{{ $parent->name ?? 'Main Branch' }}</strong></small>
                                            @else
                                                <small class="text-muted font-size-10">Network Campus</small>
                                            @endif
                                        </div>
                                        @if($branch->license_number)
                                            <div class="font-size-10 text-muted">Lic: <code>{{ $branch->license_number }}</code></div>
                                        @endif
                                    </td>
                                    <td>
                                        <div><i class="bx bx-map-pin text-danger font-size-12 me-1"></i> {{ $branch->city }}, {{ $branch->state }}</div>
                                        <small class="text-muted">Lead: {{ $branch->manager_name ?? 'Center Lead' }}</small>
                                    </td>
                                    <td>
                                        <div class="font-size-12">
                                            <span class="badge bg-soft-info text-info me-1">{{ $branch->employees_count }} Staff</span>
                                            <span class="badge bg-soft-secondary text-secondary">{{ $branch->appointments_count }} OPD</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="fw-bold text-dark font-size-13">₹{{ number_format($fin['invoiced'], 2) }}</span>
                                    </td>
                                    <td>
                                        <span class="fw-bold text-success font-size-13">₹{{ number_format($fin['collected'], 2) }}</span>
                                        <div class="progress mt-1" style="height: 4px;">
                                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $fin['collection_rate'] }}%"></div>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="{{ route('branches.index', ['branch_id' => $branch->id]) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bx bx-filter-alt me-1"></i> Scope
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Recent Branch Invoices -->
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h5 class="card-title mb-0">Recent Branch Invoices</h5>
                    <span class="text-muted font-size-12">Branch-Scoped Patient Billings</span>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Invoice #</th>
                                <th>Branch</th>
                                <th>Patient Name</th>
                                <th>Total (₹)</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentInvoices as $inv)
                                <tr>
                                    <td><span class="fw-bold text-primary font-size-12">{{ $inv->invoice_number }}</span></td>
                                    <td>
                                        <span class="badge bg-light text-dark font-size-11">{{ $inv->branch->name ?? 'Main HQ' }}</span>
                                    </td>
                                    <td>
                                        <span class="fw-medium text-dark">{{ $inv->patient->name ?? 'Patient' }}</span>
                                        <small class="text-muted d-block font-size-10">{{ $inv->patient->uhid ?? '' }}</small>
                                    </td>
                                    <td><span class="fw-bold text-dark">₹{{ number_format($inv->net_payable, 2) }}</span></td>
                                    <td>
                                        @if($inv->status === 'paid')
                                            <span class="badge bg-success font-size-10">Fully Paid</span>
                                        @elseif($inv->status === 'partial')
                                            <span class="badge bg-warning font-size-10">Partial Paid</span>
                                        @else
                                            <span class="badge bg-danger font-size-10">Unpaid</span>
                                        @endif
                                    </td>
                                    <td><small class="text-muted">{{ $inv->invoice_date->format('d M Y') }}</small></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-3 text-muted">No invoices recorded for this filter.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Payment Modes & Recent Transactions -->
    <div class="col-xl-4">
        <!-- Indian Payment Modes Distribution (UPI, Card, Cash) -->
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-3">Payment Method Breakdown (Indian Rails)</h5>
                
                @forelse($paymentModes as $mode)
                    @php
                        $percentage = $totalCollected > 0 ? round(($mode->total / $totalCollected) * 100, 1) : 0;
                        $modeColor = match(strtolower($mode->payment_method)) {
                            'upi' => 'info',
                            'card' => 'primary',
                            'cash' => 'success',
                            default => 'warning'
                        };
                        $modeIcon = match(strtolower($mode->payment_method)) {
                            'upi' => 'bx-mobile-alt',
                            'card' => 'bx-credit-card',
                            'cash' => 'bx-money',
                            default => 'bx-wallet'
                        };
                    @endphp
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-medium font-size-13 text-dark">
                                <i class="bx {{ $modeIcon }} text-{{ $modeColor }} me-1"></i> 
                                {{ strtoupper($mode->payment_method) }} ({{ $mode->payment_method === 'upi' ? 'PhonePe/GPay/BHIM' : ucfirst($mode->payment_method) }})
                            </span>
                            <span class="fw-bold font-size-13 text-{{ $modeColor }}">₹{{ number_format($mode->total, 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center text-muted font-size-11 mb-1">
                            <span>{{ $mode->count }} receipts processed</span>
                            <span>{{ $percentage }}%</span>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-{{ $modeColor }}" role="progressbar" style="width: {{ $percentage }}%"></div>
                        </div>
                    </div>
                @empty
                    <div class="text-muted text-center py-4">No collections recorded yet.</div>
                @endforelse
            </div>
        </div>

        <!-- Recent Payment Collections with Indian Transaction References -->
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-3">Live Payment Collections Audit</h5>

                <div class="vstack gap-3">
                    @forelse($recentPayments as $payment)
                        <div class="p-2 border rounded-3 bg-light">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-bold text-success font-size-14">+ ₹{{ number_format($payment->amount, 2) }}</span>
                                <span class="badge bg-primary-subtle text-primary text-uppercase font-size-10">{{ $payment->payment_method }}</span>
                            </div>
                            <div class="text-dark font-size-12 fw-medium">
                                {{ $payment->invoice->patient->name ?? 'Patient' }} • <small class="text-muted">{{ $payment->branch->name ?? 'Main HQ' }}</small>
                            </div>
                            <div class="d-flex justify-content-between align-items-center text-muted font-size-11 mt-1">
                                <span>Ref: <code>{{ $payment->transaction_reference ?? 'CASH-REC' }}</code></span>
                                <span>{{ $payment->payment_date->format('d M, h:i A') }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="text-muted text-center py-3">No payments received yet.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Add New Hospital Branch or Standalone Facility -->
<div class="modal fade" id="newBranchModal" tabindex="-1" aria-labelledby="newBranchModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white" id="newBranchModalLabel">
                    <i class="bx bx-buildings me-1"></i> Register New Facility / Branch (Standalone or Connected)
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('branches.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <!-- Facility Type -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Facility Type <span class="text-danger">*</span></label>
                            <select name="facility_type" id="facility_type" class="form-select" required onchange="handleFacilityTypeChange()">
                                <option value="hospital" selected>🏥 Hospital Branch / Multi-Specialty Unit</option>
                                <option value="pharmacy">💊 Pharmacy Store (Retail / IPD Outlet)</option>
                                <option value="blood_bank">🩸 Blood Bank & Component Center</option>
                                <option value="diagnostic_lab">🔬 Diagnostic & Imaging Center</option>
                                <option value="clinic">🩺 Primary / Day Care Outpatient Clinic</option>
                            </select>
                            <small class="text-muted">Choose whether this is a hospital campus or a specialized pharmacy/blood bank facility.</small>
                        </div>

                        <!-- Connection Mode: Standalone vs Connected -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Network Connection Mode <span class="text-danger">*</span></label>
                            <select name="connection_mode" id="connection_mode" class="form-select" required onchange="handleConnectionModeChange()">
                                <option value="branch_connected" selected>🏢 Connected to a Hospital Branch</option>
                                <option value="standalone">⭐ Standalone Facility (Direct Super Admin HQ)</option>
                            </select>
                            <small class="text-muted">Standalone units are managed directly under Super Admin and are NOT tied to any branch hospital.</small>
                        </div>

                        <!-- Parent Branch Selector (if connected) -->
                        <div class="col-md-12" id="parent_branch_container">
                            <label class="form-label fw-medium">Parent Hospital Branch <span class="text-danger">*</span></label>
                            <select name="parent_branch_id" id="parent_branch_id" class="form-select">
                                <option value="">-- Select Parent Hospital Campus --</option>
                                @foreach($parentBranches as $pb)
                                    <option value="{{ $pb->id }}">{{ $pb->name }} ({{ $pb->city }})</option>
                                @endforeach
                            </select>
                            <small class="text-muted">The hospital branch this counter/store operates inside or under.</small>
                        </div>

                        <div class="col-md-8">
                            <label class="form-label fw-medium">Facility / Branch Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Apollo Chembur Retail Pharmacy or Apollo Thane Super Specialty" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-medium">Facility Code <span class="text-danger">*</span></label>
                            <input type="text" name="code" class="form-control text-uppercase" placeholder="e.g. ACH-PHARM-01" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-medium">Statutory / Drug License Number</label>
                            <input type="text" name="license_number" class="form-control" placeholder="e.g. DL-20B-MH-49201 or Form 28C">
                            <small class="text-muted">Pharmacy Drug License (Form 20B/21B), Blood Bank License, or Clinical Est. No.</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-medium">Facility Manager / In-Charge</label>
                            <input type="text" name="manager_name" class="form-control" placeholder="e.g. Pharmacist Ramesh Patel / Dr. Sunita Rao">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-medium">City <span class="text-danger">*</span></label>
                            <input type="text" name="city" class="form-control" placeholder="e.g. Mumbai / Thane / Pune" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-medium">State <span class="text-danger">*</span></label>
                            <input type="text" name="state" class="form-control" value="Maharashtra" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-medium">PIN Code</label>
                            <input type="text" name="pincode" class="form-control" placeholder="e.g. 400601">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-medium">Official Contact Number</label>
                            <input type="text" name="phone" class="form-control" placeholder="e.g. +91 98200 11223">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Official Contact Email</label>
                            <input type="email" name="email" class="form-control" placeholder="e.g. store@apollocare.in">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-medium">Street Address</label>
                            <input type="text" name="address" class="form-control" placeholder="Building, Sector, Highway/Road details">
                        </div>

                        <div class="col-12" id="main_branch_toggle_container">
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="is_main_branch" value="1" id="is_main_branch">
                                <label class="form-check-label fw-medium" for="is_main_branch">
                                    Set as Central Flagship HQ Hospital (Will demote previous Main Branch)
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bx bx-check-circle me-1"></i> Register Facility
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function handleConnectionModeChange() {
        const mode = document.getElementById('connection_mode').value;
        const parentContainer = document.getElementById('parent_branch_container');
        const parentSelect = document.getElementById('parent_branch_id');

        if (mode === 'standalone') {
            parentContainer.style.display = 'none';
            parentSelect.value = '';
        } else {
            parentContainer.style.display = 'block';
        }
    }

    function handleFacilityTypeChange() {
        const fType = document.getElementById('facility_type').value;
        const mainToggle = document.getElementById('main_branch_toggle_container');
        if (fType !== 'hospital') {
            mainToggle.style.display = 'none';
            document.getElementById('is_main_branch').checked = false;
        } else {
            mainToggle.style.display = 'block';
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        handleConnectionModeChange();
        handleFacilityTypeChange();
    });
</script>
@endpush
