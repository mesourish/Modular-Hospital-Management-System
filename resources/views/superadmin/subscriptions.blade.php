@extends('layouts.skote')

@section('title', 'Subscription & Licensing Center')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <div>
                <h4 class="mb-sm-0 font-size-18">Platform Subscription & Licensing Governance</h4>
                <p class="text-muted font-size-13 mb-0">Platform HQ (Main Central Branch) • SaaS Revenue & Tenant Subscriptions</p>
            </div>
            <div class="page-title-right">
                <span class="badge bg-warning font-size-13 px-3 py-2">
                    <i class="bx bx-crown me-1"></i> Platform HQ Authority
                </span>
            </div>
        </div>
    </div>
</div>

<!-- Super Admin Role Boundary Alert -->
<div class="alert alert-info alert-top-border p-3 mb-4 shadow-sm">
    <div class="d-flex align-items-center">
        <div class="avatar-sm me-3">
            <span class="avatar-title bg-info text-white rounded-circle font-size-20">
                <i class="bx bx-shield-quarter"></i>
            </span>
        </div>
        <div>
            <h5 class="text-info font-size-15 mb-1">Strict Platform HQ Boundary: Subscriptions & Tenant Governance Only</h5>
            <p class="text-muted font-size-13 mb-0">
                Super Admin serves as the Main Central Platform Authority managing recurring subscription plans, tenant contracts, and modular feature entitlements. Super Admin does <strong>not</strong> access individual patient consultations or write prescriptions, adhering strictly to ABDM and hospital clinical privacy protocols.
            </p>
        </div>
    </div>
</div>

<!-- Subscription Financial KPIs -->
<div class="row">
    <div class="col-xl-4 col-md-6">
        <div class="card mini-stats-wid">
            <div class="card-body">
                <div class="d-flex">
                    <div class="flex-grow-1">
                        <p class="text-muted fw-medium mb-1">Platform MRR (Monthly Recurring)</p>
                        <h4 class="mb-0 text-primary">₹{{ number_format($totalMrr, 2) }}</h4>
                        <small class="text-success font-size-11"><i class="bx bx-trending-up"></i> Indian Healthcare Cloud</small>
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

    <div class="col-xl-4 col-md-6">
        <div class="card mini-stats-wid">
            <div class="card-body">
                <div class="d-flex">
                    <div class="flex-grow-1">
                        <p class="text-muted fw-medium mb-1">Projected Annual ARR</p>
                        <h4 class="mb-0 text-success">₹{{ number_format($totalMrr * 12, 2) }}</h4>
                        <small class="text-muted font-size-11">Based on active tenant contracts</small>
                    </div>
                    <div class="avatar-sm rounded-circle bg-success-subtle align-self-center mini-stat-icon">
                        <span class="avatar-title rounded-circle bg-success text-white">
                            <i class="bx bx-chart font-size-22"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-md-6">
        <div class="card mini-stats-wid">
            <div class="card-body">
                <div class="d-flex">
                    <div class="flex-grow-1">
                        <p class="text-muted fw-medium mb-1">Subscribed Hospital Networks</p>
                        <h4 class="mb-0 text-dark">{{ $hospitals->count() }} Healthcare Tenants</h4>
                        <small class="text-muted font-size-11">Active multi-unit hospital networks</small>
                    </div>
                    <div class="avatar-sm rounded-circle bg-warning-subtle align-self-center mini-stat-icon">
                        <span class="avatar-title rounded-circle bg-warning text-white">
                            <i class="bx bx-buildings font-size-22"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Standard Subscription Plans Overview -->
<div class="row mb-4">
    <div class="col-12">
        <h5 class="card-title mb-3">Modular Subscription Tiers (India Healthcare)</h5>
    </div>
    @foreach($plans as $pKey => $pInfo)
        <div class="col-lg-4">
            <div class="card h-100 border {{ $pKey === 'enterprise' ? 'border-warning shadow' : 'border-light' }}">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="badge {{ $pInfo['badge'] }} px-2 py-1 font-size-11 text-uppercase">{{ $pKey }}</span>
                        @if($pKey === 'enterprise')
                            <span class="badge bg-warning-subtle text-warning font-size-11"><i class="bx bx-star"></i> Flagship</span>
                        @endif
                    </div>
                    <h5 class="card-title text-dark font-size-16 mb-1">{{ $pInfo['name'] }}</h5>
                    <p class="text-muted font-size-12">{{ $pInfo['description'] }}</p>
                    <div class="py-2">
                        <span class="fw-bold text-primary font-size-24">₹{{ number_format($pInfo['price_monthly']) }}</span>
                        <span class="text-muted font-size-12">/ month + GST</span>
                    </div>
                    <hr>
                    <small class="fw-bold text-dark d-block mb-2 text-uppercase font-size-11">Included Extensions:</small>
                    <div class="d-flex flex-wrap gap-1">
                        @foreach($pInfo['included_modules'] as $mod)
                            <span class="badge bg-light text-dark font-size-11">
                                <i class="bx bx-check text-success me-1"></i> {{ ucwords(str_replace('_', ' ', $mod)) }}
                            </span>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<!-- Active Tenant Subscriptions Table -->
<div class="card">
    <div class="card-body">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <div>
                <h5 class="card-title mb-1">Tenant Hospital Subscriptions & Licensing Status</h5>
                <p class="text-muted font-size-12 mb-0">Super Admin can modify subscription plans and provision enterprise extensions</p>
            </div>
            <a href="{{ route('superadmin.hospitals') }}" class="btn btn-sm btn-outline-primary">
                <i class="bx bx-cog me-1"></i> Extension Matrix
            </a>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Hospital / Healthcare Tenant</th>
                        <th>Hierarchy / Branches</th>
                        <th>Current Subscription Tier</th>
                        <th>Monthly Fee (₹)</th>
                        <th>Active Extensions</th>
                        <th>License Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($hospitals as $h)
                        @php
                            $fee = $plans[$h->subscription_plan]['price_monthly'] ?? 15000;
                            $enabledCount = $h->modules->where('is_enabled', true)->count();
                        @endphp
                        <tr>
                            <td>
                                <div class="fw-bold text-dark font-size-14">{{ $h->name }}</div>
                                <div class="text-muted font-size-11">Code: <code>{{ $h->code }}</code> • {{ $h->city ?? 'India' }}</div>
                            </td>
                            <td>
                                <span class="badge bg-soft-info text-info font-size-12">
                                    <i class="bx bx-git-branch me-1"></i> {{ $h->branches->count() }} Branches
                                </span>
                                @if($h->branches->where('is_main_branch', true)->first())
                                    <small class="text-muted d-block font-size-10">HQ: {{ $h->branches->where('is_main_branch', true)->first()->name }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $plans[$h->subscription_plan]['badge'] ?? 'bg-secondary' }} px-2 py-1 font-size-11 text-uppercase">
                                    {{ $plans[$h->subscription_plan]['name'] ?? ucfirst($h->subscription_plan) }}
                                </span>
                            </td>
                            <td>
                                <span class="fw-bold text-primary font-size-14">₹{{ number_format($fee, 2) }}</span>
                                <small class="text-muted d-block font-size-10">Monthly Billing</small>
                            </td>
                            <td>
                                <span class="badge bg-success font-size-11">{{ $enabledCount }} Modules Active</span>
                            </td>
                            <td>
                                <span class="badge bg-success font-size-11"><i class="bx bx-check-circle me-1"></i> Active License</span>
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#changePlanModal-{{ $h->id }}">
                                    <i class="bx bx-edit me-1"></i> Change Tier
                                </button>
                            </td>
                        </tr>

                        <!-- Modal: Change Plan -->
                        <div class="modal fade" id="changePlanModal-{{ $h->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header bg-primary text-white">
                                        <h5 class="modal-title text-white">
                                            <i class="bx bx-crown me-1"></i> Update Subscription: {{ $h->name }}
                                        </h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <form action="{{ route('superadmin.subscriptions.update', $h->id) }}" method="POST">
                                        @csrf
                                        <div class="modal-body p-4">
                                            <div class="mb-3">
                                                <label class="form-label fw-medium">Select Subscription Tier</label>
                                                <select name="subscription_plan" class="form-select" required>
                                                    <option value="starter" {{ $h->subscription_plan === 'starter' ? 'selected' : '' }}>
                                                        Essential Clinic Tier (₹15,000 / mo)
                                                    </option>
                                                    <option value="multi_specialty" {{ $h->subscription_plan === 'multi_specialty' ? 'selected' : '' }}>
                                                        Multi-Specialty Hospital Tier (₹45,000 / mo)
                                                    </option>
                                                    <option value="enterprise" {{ $h->subscription_plan === 'enterprise' ? 'selected' : '' }}>
                                                        Enterprise Healthcare Network Tier (₹1,20,000 / mo)
                                                    </option>
                                                </select>
                                            </div>
                                            <div class="p-2 bg-light rounded text-muted font-size-12">
                                                <i class="bx bx-info-circle me-1"></i> Changing subscription tier updates billing schedules and contract entitlements automatically.
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-primary">Save Tier Changes</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
