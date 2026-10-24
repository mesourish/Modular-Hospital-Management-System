@extends('layouts.skote')

@section('title', 'Super Admin Governance')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0 font-size-18">Platform Super Admin Control Center</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">Governance</a></li>
                    <li class="breadcrumb-item active">Platform Dashboard</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Architecture Notice Alert -->
<div class="alert alert-primary alert-top-border p-3 mb-4 shadow-sm">
    <div class="d-flex align-items-center">
        <div class="avatar-sm me-3">
            <span class="avatar-title bg-primary rounded-circle font-size-20">
                <i class="bx bx-shield-quarter"></i>
            </span>
        </div>
        <div>
            <h5 class="text-primary font-size-15 mb-1">Central Platform Governance & Strict Tenant Isolation</h5>
            <p class="text-muted font-size-13 mb-0">
                Super Admin governs tenant onboarding, global module licensing, and security baselines. In accordance with clinical security principles (PDF Page 4 & 11), Super Admin controls platform entitlements while individual hospital clinical data remains strictly isolated.
            </p>
        </div>
    </div>
</div>

<!-- Metrics Row -->
<div class="row">
    <div class="col-xl-3 col-md-6">
        <div class="card mini-stats-wid border-warning border-start border-3">
            <div class="card-body">
                <div class="d-flex">
                    <div class="flex-grow-1">
                        <p class="text-muted fw-medium mb-1">Platform MRR (Subscriptions)</p>
                        <h4 class="mb-0 text-primary">₹{{ number_format($monthlyRevenue ?? 165000, 2) }}</h4>
                        <a href="{{ route('superadmin.subscriptions') }}" class="text-warning font-size-11 fw-medium">
                            Manage Subscriptions <i class="bx bx-right-arrow-alt"></i>
                        </a>
                    </div>
                    <div class="avatar-sm rounded-circle bg-warning-subtle align-self-center mini-stat-icon">
                        <span class="avatar-title rounded-circle bg-warning text-white">
                            <i class="bx bx-crown font-size-22"></i>
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
                        <p class="text-muted fw-medium mb-1">Onboarded Hospitals</p>
                        <h4 class="mb-0 text-dark">{{ $hospitalsCount }} Networks</h4>
                        <small class="text-muted font-size-11">{{ $activeHospitalsCount }} Active Tenants</small>
                    </div>
                    <div class="avatar-sm rounded-circle bg-primary align-self-center mini-stat-icon">
                        <span class="avatar-title rounded-circle bg-primary">
                            <i class="bx bx-buildings font-size-24"></i>
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
                        <p class="text-muted fw-medium mb-1">Available Extensions</p>
                        <h4 class="mb-0">{{ count($availableModules) }} Modules</h4>
                        <small class="text-success font-size-11"><i class="bx bx-check"></i> Modular Gating Active</small>
                    </div>
                    <div class="avatar-sm rounded-circle bg-info align-self-center mini-stat-icon">
                        <span class="avatar-title rounded-circle bg-info">
                            <i class="bx bx-extension font-size-24"></i>
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
                        <p class="text-muted fw-medium mb-1">Security Audit Trail</p>
                        <h4 class="mb-0">{{ $auditEventsCount }} Events</h4>
                        <a href="{{ route('superadmin.audit_logs') }}" class="text-muted font-size-11">
                            Immutable logs <i class="bx bx-right-arrow-alt"></i>
                        </a>
                    </div>
                    <div class="avatar-sm rounded-circle bg-success align-self-center mini-stat-icon">
                        <span class="avatar-title rounded-circle bg-success">
                            <i class="bx bx-shield-check font-size-24"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Segment-Wise Clinical & Operational Revenue Analytics -->
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h5 class="card-title mb-0 font-size-16 text-dark fw-bold">
                        <i class="bx bx-pie-chart-alt-2 text-primary me-2 font-size-20 align-middle"></i>
                        Network Clinical & Retail Segment-Wise Revenue Analytics
                    </h5>
                    <p class="text-muted font-size-12 mb-0">Consolidated financial performance across Inpatient Beds, OT Surgeries, Pharmacy POS, Radiology, Blood Bank & Diagnostics</p>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-success-subtle text-success font-size-13 px-3 py-2 border border-success-subtle">
                        <i class="bx bx-rupee"></i> Total Segment Gross: <strong>₹{{ number_format($totalSegmentRevenue, 2) }}</strong>
                    </span>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()">
                        <i class="bx bx-printer me-1"></i> Print P&L
                    </button>
                </div>
            </div>
            <div class="card-body p-4">
                <div class="row align-items-center">
                    <!-- Left: Interactive Donut Chart -->
                    <div class="col-xl-5 col-lg-6 border-end-xl">
                        <div class="text-center mb-2">
                            <h6 class="font-size-13 text-uppercase text-muted fw-bold mb-1">Revenue Share by Service Segment</h6>
                            <small class="text-secondary font-size-11">Hover segments for detailed collections and percentage share</small>
                        </div>
                        <div id="segmentRevenueDonutChart" style="min-height: 320px;"></div>
                    </div>

                    <!-- Right: Multi-Month Area Growth Graph -->
                    <div class="col-xl-7 col-lg-6 mt-4 mt-lg-0">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <h6 class="font-size-13 text-uppercase text-muted fw-bold mb-1">6-Month Clinical Segment Growth Trajectory</h6>
                                <small class="text-secondary font-size-11">May 2026 - October 2026 Monthly Trend (in ₹ INR)</small>
                            </div>
                            <span class="badge bg-primary-subtle text-primary font-size-11 px-2 py-1">
                                <i class="bx bx-trending-up"></i> +18.4% MoM
                            </span>
                        </div>
                        <div id="segmentMonthlyGrowthChart" style="min-height: 320px;"></div>
                    </div>
                </div>

                <!-- Itemized Breakdown Cards -->
                <div class="row g-2 mt-3 pt-3 border-top">
                    @php
                        $segmentIcons = [
                            'Inpatient Bed & CCU Tariffs' => ['icon' => 'bx-bed', 'bg' => 'bg-primary-subtle', 'text' => 'text-primary'],
                            'Operation Theater & Surgeries' => ['icon' => 'bx-plus-medical', 'bg' => 'bg-danger-subtle', 'text' => 'text-danger'],
                            'Pharmacy & Counter POS' => ['icon' => 'bx-capsule', 'bg' => 'bg-success-subtle', 'text' => 'text-success'],
                            'Diagnostic Radiology & PACS' => ['icon' => 'bx-scan', 'bg' => 'bg-info-subtle', 'text' => 'text-info'],
                            'Blood Transfusion Services' => ['icon' => 'bx-donate-blood', 'bg' => 'bg-danger-subtle', 'text' => 'text-danger'],
                            'Doctor OPD Consultations' => ['icon' => 'bx-user-voice', 'bg' => 'bg-warning-subtle', 'text' => 'text-warning'],
                            'Emergency Ambulance Fleet' => ['icon' => 'bx-car', 'bg' => 'bg-secondary-subtle', 'text' => 'text-secondary'],
                            'Nursing & Consumables' => ['icon' => 'bx-pulse', 'bg' => 'bg-primary-subtle', 'text' => 'text-primary'],
                        ];
                    @endphp

                    @foreach($segmentRevenue as $label => $amt)
                        @php
                            $pct = $totalSegmentRevenue > 0 ? round(($amt / $totalSegmentRevenue) * 100, 1) : 0;
                            $meta = $segmentIcons[$label] ?? ['icon' => 'bx-money', 'bg' => 'bg-light', 'text' => 'text-dark'];
                        @endphp
                        <div class="col-xl-3 col-md-6">
                            <div class="p-2 border rounded bg-light d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="avatar-xs rounded {{ $meta['bg'] }} {{ $meta['text'] }} d-flex align-items-center justify-content-center font-size-16">
                                        <i class="bx {{ $meta['icon'] }}"></i>
                                    </span>
                                    <div>
                                        <div class="font-size-11 text-muted text-truncate" style="max-width: 140px;" title="{{ $label }}">{{ $label }}</div>
                                        <div class="fw-bold font-size-13 text-dark font-mono">₹{{ number_format($amt, 2) }}</div>
                                    </div>
                                </div>
                                <span class="badge bg-white text-dark border font-mono font-size-11">{{ $pct }}%</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Extension Ecosystem Status -->
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="card-title mb-0">Platform Module Registry & Entitlement Adoption</h4>
                    <a href="{{ route('superadmin.hospitals') }}" class="btn btn-sm btn-primary">
                        <i class="bx bx-slider-alt me-1"></i> Configure Hospital Entitlements
                    </a>
                </div>
                <p class="text-muted font-size-13">All modular extensions can be toggled per hospital. Different hospitals have independent active modules.</p>

                <div class="row g-3">
                    @foreach($moduleStats as $key => $st)
                        <div class="col-md-6">
                            <div class="border rounded p-3 h-100 bg-light d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm me-3">
                                        <span class="avatar-title rounded bg-soft-primary text-primary font-size-20">
                                            <i class="bx {{ $st['icon'] }}"></i>
                                        </span>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 font-size-14 text-dark">{{ $st['name'] }}</h6>
                                        <small class="text-muted">{{ $st['category'] }} • <code>{{ $key }}</code></small>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-success font-size-12">{{ $st['active_count'] }} Active</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Onboarded Hospitals -->
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="card-title mb-0">Recent Onboarded Hospitals</h4>
                    <a href="{{ route('superadmin.hospitals') }}" class="btn btn-sm btn-outline-secondary">View All Hospitals</a>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle table-nowrap table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Hospital Name</th>
                                <th>Code</th>
                                <th>Type</th>
                                <th>Plan</th>
                                <th>Active Modules</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentHospitals as $h)
                                <tr>
                                    <td>
                                        <h5 class="font-size-14 mb-0"><a href="javascript:void(0);" class="text-dark">{{ $h->name }}</a></h5>
                                        <small class="text-muted">{{ $h->city ?? 'Central' }}, {{ $h->country }}</small>
                                    </td>
                                    <td><span class="badge bg-soft-primary text-primary">{{ $h->code }}</span></td>
                                    <td><span class="badge bg-soft-info text-info">{{ ucfirst(str_replace('_', ' ', $h->type)) }}</span></td>
                                    <td><code>{{ $h->subscription_plan }}</code></td>
                                    <td>
                                        <span class="badge bg-soft-success text-success font-size-12">{{ $h->modules_count }} Modules</span>
                                    </td>
                                    <td>
                                        <form action="{{ route('superadmin.switch_hospital') }}" method="POST" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="hospital_id" value="{{ $h->id }}">
                                            <button type="submit" class="btn btn-sm btn-outline-primary waves-effect">
                                                <i class="bx bx-log-in-circle me-1"></i> Switch View
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Security Audit Stream -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="card-title mb-0">Security Audit Trail</h4>
                    <a href="{{ route('superadmin.audit_logs') }}" class="btn btn-sm btn-link p-0">View All</a>
                </div>
                <p class="text-muted font-size-12">Tamper-evident logs of tenant actions, security checks, and module state changes.</p>

                <div class="activity-feed">
                    @forelse($recentAudits as $audit)
                        <div class="border-bottom py-2">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="badge bg-soft-secondary text-secondary font-size-11">{{ $audit->module_key }}</span>
                                <small class="text-muted">{{ $audit->created_at->diffForHumans() }}</small>
                            </div>
                            <div class="font-size-13 fw-semibold text-dark">{{ str_replace('_', ' ', $audit->action) }}</div>
                            <small class="text-muted d-block">
                                Tenant: {{ $audit->hospital ? $audit->hospital->name : 'Platform Wide' }}
                            </small>
                            <small class="text-secondary font-size-11">
                                IP: {{ $audit->ip_address }} • {{ $audit->user ? $audit->user->name : 'System' }}
                            </small>
                        </div>
                    @empty
                        <div class="text-muted text-center py-4">No audit records logged yet.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/libs/apexcharts/apexcharts.min.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Donut Chart: Revenue by Clinical Segment
    const segmentLabels = {!! json_encode(array_keys($segmentRevenue)) !!};
    const segmentValues = {!! json_encode(array_values($segmentRevenue)) !!};

    const donutOptions = {
        chart: {
            type: 'donut',
            height: 330,
            fontFamily: 'inherit'
        },
        series: segmentValues,
        labels: segmentLabels,
        colors: ['#0284c7', '#ef4444', '#10b981', '#06b6d4', '#e11d48', '#f59e0b', '#64748b', '#8b5cf6'],
        legend: {
            position: 'bottom',
            fontSize: '11px',
            markers: { radius: 12 }
        },
        dataLabels: {
            enabled: true,
            formatter: function (val) {
                return Math.round(val) + "%";
            }
        },
        plotOptions: {
            pie: {
                donut: {
                    size: '68%',
                    labels: {
                        show: true,
                        total: {
                            show: true,
                            label: 'TOTAL GROSS',
                            fontSize: '11px',
                            fontWeight: 600,
                            color: '#64748b',
                            formatter: function (w) {
                                return '₹' + Number({{ $totalSegmentRevenue }}).toLocaleString('en-IN');
                            }
                        }
                    }
                }
            }
        },
        tooltip: {
            y: {
                formatter: function (val) {
                    return '₹' + Number(val).toLocaleString('en-IN', { minimumFractionDigits: 2 });
                }
            }
        }
    };

    if (document.querySelector("#segmentRevenueDonutChart")) {
        const donutChart = new ApexCharts(document.querySelector("#segmentRevenueDonutChart"), donutOptions);
        donutChart.render();
    }

    // 2. 6-Month Growth Area Trajectory Graph
    const monthlyMonths = {!! json_encode($months) !!};
    const monthlySeries = {!! json_encode($monthlySeries) !!};

    const growthOptions = {
        chart: {
            type: 'area',
            height: 330,
            toolbar: { show: false },
            fontFamily: 'inherit'
        },
        series: monthlySeries,
        xaxis: {
            categories: monthlyMonths,
            labels: {
                style: { fontSize: '11px', fontWeight: 500 }
            }
        },
        yaxis: {
            labels: {
                formatter: function (val) {
                    return '₹' + (val / 1000).toFixed(0) + 'k';
                }
            }
        },
        colors: ['#0284c7', '#ef4444', '#10b981', '#06b6d4', '#f59e0b'],
        stroke: { curve: 'smooth', width: 2 },
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.35,
                opacityTo: 0.05,
                stops: [0, 95, 100]
            }
        },
        dataLabels: { enabled: false },
        legend: {
            position: 'top',
            horizontalAlign: 'right',
            fontSize: '11px'
        },
        tooltip: {
            y: {
                formatter: function (val) {
                    return '₹' + Number(val).toLocaleString('en-IN');
                }
            }
        }
    };

    if (document.querySelector("#segmentMonthlyGrowthChart")) {
        const growthChart = new ApexCharts(document.querySelector("#segmentMonthlyGrowthChart"), growthOptions);
        growthChart.render();
    }
});
</script>
@endpush
