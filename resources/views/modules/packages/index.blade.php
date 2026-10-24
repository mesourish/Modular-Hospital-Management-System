@extends('layouts.skote')

@section('title', 'Hospital-Wise Health Checkup Packages')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0 font-size-18">
                <i class="bx bx-package text-primary me-2"></i> Health Checkup Packages & Preventive Care Catalogue
            </h4>
            <div class="page-title-right d-flex gap-2 align-items-center">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show py-1 px-3 mb-0" role="alert">
                        <i class="bx bx-check-circle me-1"></i> {{ session('success') }}
                        <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                <button type="button" class="btn btn-primary btn-sm waves-effect waves-light shadow-sm" data-bs-toggle="modal" data-bs-target="#newPackageModal">
                    <i class="bx bx-plus font-size-15 align-middle me-1"></i> Create Health Package
                </button>
            </div>
        </div>
    </div>
</div>

<!-- KPI Metric Cards -->
<div class="row mb-3">
    <div class="col-xl-3 col-md-6">
        <div class="card mini-stats-wid border-0 shadow-sm mb-3">
            <div class="card-body p-3">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <p class="text-muted fw-medium font-size-12 mb-1">Total Packages</p>
                        <h4 class="mb-0 text-primary fw-bold">{{ $metrics['total_packages'] }}</h4>
                    </div>
                    <div class="avatar-sm rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center">
                        <i class="bx bx-package font-size-22"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card mini-stats-wid border-0 shadow-sm mb-3">
            <div class="card-body p-3">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <p class="text-muted fw-medium font-size-12 mb-1">Active for Booking</p>
                        <h4 class="mb-0 text-success fw-bold">{{ $metrics['active_packages'] }}</h4>
                    </div>
                    <div class="avatar-sm rounded-circle bg-success-subtle text-success d-flex align-items-center justify-content-center">
                        <i class="bx bx-check-circle font-size-22"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card mini-stats-wid border-0 shadow-sm mb-3">
            <div class="card-body p-3">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <p class="text-muted fw-medium font-size-12 mb-1">Featured / Popular</p>
                        <h4 class="mb-0 text-warning fw-bold">{{ $metrics['popular_packages'] }}</h4>
                    </div>
                    <div class="avatar-sm rounded-circle bg-warning-subtle text-warning d-flex align-items-center justify-content-center">
                        <i class="bx bx-star font-size-22"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card mini-stats-wid border-0 shadow-sm mb-3">
            <div class="card-body p-3">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <p class="text-muted fw-medium font-size-12 mb-1">Patient Bookings</p>
                        <h4 class="mb-0 text-info fw-bold">{{ $metrics['total_bookings'] }}</h4>
                    </div>
                    <div class="avatar-sm rounded-circle bg-info-subtle text-info d-flex align-items-center justify-content-center">
                        <i class="bx bx-calendar-check font-size-22"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tabs: Catalogue vs Bookings -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom py-3">
        <ul class="nav nav-tabs-custom card-header-tabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#packages-list" role="tab">
                    <i class="bx bx-list-ul me-1"></i> Checkup Packages Directory ({{ $packages->count() }})
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#package-bookings" role="tab">
                    <i class="bx bx-receipt me-1"></i> Patient Bookings Ledger ({{ $recentBookings->count() }})
                </a>
            </li>
        </ul>
    </div>
    <div class="card-body p-3">
        <div class="tab-content">
            <!-- Tab 1: Packages Catalogue -->
            <div class="tab-pane active" id="packages-list" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-hover align-middle table-sm font-size-13 mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Package Code & Name</th>
                                <th>Category</th>
                                <th>Applicable Branch</th>
                                <th>Tests Count</th>
                                <th>Price (INR)</th>
                                <th>Turnaround</th>
                                <th>Fasting</th>
                                <th>Target</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($packages as $pkg)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($pkg->is_popular)
                                                <span class="badge bg-warning-subtle text-warning me-2" title="Featured Popular Package">
                                                    <i class="bx bxs-star"></i>
                                                </span>
                                            @endif
                                            <div>
                                                <strong class="text-dark d-block">{{ $pkg->name }}</strong>
                                                <small class="text-muted font-monospace">{{ $pkg->code }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary-subtle text-primary font-size-11 px-2 py-1">
                                            {{ $pkg->category }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($pkg->branch)
                                            <span class="badge bg-secondary-subtle text-dark">
                                                <i class="bx bx-building me-1"></i> {{ $pkg->branch->name }}
                                            </span>
                                        @else
                                            <span class="badge bg-info-subtle text-info">
                                                <i class="bx bx-globe me-1"></i> All Branches (Hospital-Wide)
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="fw-semibold text-primary">{{ $pkg->tests_count }}</span> <small class="text-muted">Tests</small>
                                    </td>
                                    <td>
                                        <div>
                                            <strong class="text-success">{{ $pkg->formatted_discounted_price }}</strong>
                                            @if($pkg->discount_percentage > 0)
                                                <span class="text-muted text-decoration-line-through font-size-11 ms-1">{{ $pkg->formatted_original_price }}</span>
                                                <span class="badge bg-danger-subtle text-danger font-size-10 ms-1">{{ $pkg->discount_percentage }}% OFF</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>{{ $pkg->turnaround_time }}</td>
                                    <td>
                                        @if($pkg->fasting_required)
                                            <span class="badge bg-warning-subtle text-warning">
                                                <i class="bx bx-time-five me-1"></i> {{ $pkg->fasting_hours }}h Fasting
                                            </span>
                                        @else
                                            <span class="badge bg-success-subtle text-success">No Fasting</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small class="text-muted d-block">{{ $pkg->gender_target }}</small>
                                        <small class="text-muted">{{ $pkg->age_group }}</small>
                                    </td>
                                    <td>
                                        <form method="POST" action="{{ route('packages.toggle_status', $pkg) }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm py-0 px-2 {{ $pkg->is_active ? 'btn-soft-success' : 'btn-soft-secondary' }}" title="Click to toggle status">
                                                <i class="bx {{ $pkg->is_active ? 'bx-check-circle' : 'bx-x-circle' }} me-1"></i>
                                                {{ $pkg->is_active ? 'Active' : 'Inactive' }}
                                            </button>
                                        </form>
                                    </td>
                                    <td class="text-end">
                                        <button type="button" class="btn btn-sm btn-light py-1 px-2" data-bs-toggle="modal" data-bs-target="#editModal{{ $pkg->id }}" title="Edit Package">
                                            <i class="bx bx-edit text-primary"></i>
                                        </button>
                                        <form method="POST" action="{{ route('packages.destroy', $pkg) }}" class="d-inline" onsubmit="return confirm('Delete package {{ $pkg->name }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-light py-1 px-2 text-danger" title="Delete">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>

                                <!-- Edit Modal -->
                                <div class="modal fade" id="editModal{{ $pkg->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <form method="POST" action="{{ route('packages.update', $pkg) }}">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-header">
                                                    <h5 class="modal-title font-size-16">
                                                        <i class="bx bx-edit text-primary me-1"></i> Edit Health Package: {{ $pkg->code }}
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body p-4">
                                                    <div class="row g-3">
                                                        <div class="col-md-8">
                                                            <label class="form-label">Package Name *</label>
                                                            <input type="text" name="name" class="form-control" value="{{ $pkg->name }}" required>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label">Category *</label>
                                                            <input type="text" name="category" class="form-control" value="{{ $pkg->category }}" required>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label">Original Price (₹) *</label>
                                                            <input type="number" step="0.01" name="original_price" class="form-control" value="{{ $pkg->original_price }}" required>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label">Discounted Offer Price (₹) *</label>
                                                            <input type="number" step="0.01" name="discounted_price" class="form-control" value="{{ $pkg->discounted_price }}" required>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label">Tests Count *</label>
                                                            <input type="number" name="tests_count" class="form-control" value="{{ $pkg->tests_count }}" required>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label">Turnaround Time *</label>
                                                            <input type="text" name="turnaround_time" class="form-control" value="{{ $pkg->turnaround_time }}" required>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="form-label">Fasting Required Hours</label>
                                                            <input type="number" name="fasting_hours" class="form-control" value="{{ $pkg->fasting_hours }}">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label">Gender Target *</label>
                                                            <select name="gender_target" class="form-select">
                                                                <option value="All" {{ $pkg->gender_target == 'All' ? 'selected' : '' }}>All (Unisex)</option>
                                                                <option value="Male" {{ $pkg->gender_target == 'Male' ? 'selected' : '' }}>Male Only</option>
                                                                <option value="Female" {{ $pkg->gender_target == 'Female' ? 'selected' : '' }}>Female Only</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label">Age Group *</label>
                                                            <input type="text" name="age_group" class="form-control" value="{{ $pkg->age_group }}" required>
                                                        </div>
                                                        <div class="col-12">
                                                            <label class="form-label">Tests Included (One per line)</label>
                                                            <textarea name="tests_included" class="form-control font-monospace font-size-12" rows="4">{{ is_array($pkg->tests_included) ? implode("\n", $pkg->tests_included) : $pkg->tests_included }}</textarea>
                                                        </div>
                                                        <div class="col-12">
                                                            <label class="form-label">Package Description</label>
                                                            <textarea name="description" class="form-control" rows="2">{{ $pkg->description }}</textarea>
                                                        </div>
                                                        <div class="col-12">
                                                            <div class="form-check form-switch">
                                                                <input class="form-check-input" type="checkbox" name="is_popular" value="1" id="pop{{ $pkg->id }}" {{ $pkg->is_popular ? 'checked' : '' }}>
                                                                <label class="form-check-label" for="pop{{ $pkg->id }}">Mark as Featured / Most Popular Package</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-primary">Save Changes</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center py-4 text-muted">
                                        <i class="bx bx-package font-size-36 d-block mb-2 text-secondary"></i>
                                        No health checkup packages configured yet. Click 'Create Health Package' to add one.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tab 2: Bookings Ledger -->
            <div class="tab-pane" id="package-bookings" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-hover align-middle table-sm font-size-13 mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Booking Ref</th>
                                <th>Patient Name</th>
                                <th>Contact Phone</th>
                                <th>Package Name</th>
                                <th>Branch</th>
                                <th>Preferred Date</th>
                                <th>Age / Gender</th>
                                <th>Status</th>
                                <th>Booked On</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentBookings as $bk)
                                <tr>
                                    <td><strong class="text-primary font-monospace">{{ $bk->booking_number }}</strong></td>
                                    <td class="fw-semibold text-dark">{{ $bk->patient_name }}</td>
                                    <td>{{ $bk->patient_phone }}</td>
                                    <td>
                                        <span class="badge bg-primary-subtle text-primary">{{ $bk->package?->name ?? 'Package' }}</span>
                                    </td>
                                    <td>{{ $bk->branch?->name }}</td>
                                    <td>{{ $bk->preferred_date->format('d M, Y') }}</td>
                                    <td>{{ $bk->patient_age }}y / {{ ucfirst($bk->patient_gender) }}</td>
                                    <td>
                                        <span class="badge bg-success-subtle text-success px-2 py-1">
                                            <i class="bx bx-check me-1"></i> {{ ucfirst($bk->status) }}
                                        </span>
                                    </td>
                                    <td><small class="text-muted">{{ $bk->created_at->format('d M, Y h:i A') }}</small></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4 text-muted">
                                        No health package bookings received yet.
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

<!-- Modal: Create New Package -->
<div class="modal fade" id="newPackageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="{{ route('packages.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title font-size-16">
                        <i class="bx bx-plus-circle text-primary me-1"></i> Create Hospital Health Checkup Package
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Package Name *</label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Apollo Master Cardiac Screening" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Package Code *</label>
                            <input type="text" name="code" class="form-control" placeholder="e.g. APO-PKG-CARD-02" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Category *</label>
                            <input type="text" name="category" class="form-control" placeholder="e.g. Cardiac Care, Whole Body, Diabetic" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Branch Availability</label>
                            <select name="branch_id" class="form-select">
                                <option value="">All Network Branches (Hospital-Wide)</option>
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}">{{ $b->name }} ({{ $b->city }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Original Standard Price (₹) *</label>
                            <input type="number" step="0.01" name="original_price" class="form-control" placeholder="e.g. 6800.00" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Discounted Offer Price (₹) *</label>
                            <input type="number" step="0.01" name="discounted_price" class="form-control" placeholder="e.g. 3499.00" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Total Tests Count *</label>
                            <input type="number" name="tests_count" class="form-control" placeholder="e.g. 42" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Turnaround Time *</label>
                            <input type="text" name="turnaround_time" class="form-control" value="Same Day (4-6 Hours)" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Fasting Required Hours</label>
                            <input type="number" name="fasting_hours" class="form-control" value="10">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Target Gender *</label>
                            <select name="gender_target" class="form-select">
                                <option value="All">All (Unisex)</option>
                                <option value="Male">Male Only</option>
                                <option value="Female">Female Only</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Target Age Group *</label>
                            <input type="text" name="age_group" class="form-control" value="All Ages" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Tests Included (One per line)</label>
                            <textarea name="tests_included" class="form-control font-monospace font-size-12" rows="4" placeholder="Complete Hemogram&#10;Lipid Profile&#10;2D Echo with Color Doppler&#10;12-Lead ECG&#10;Physician Consultation"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Brief Description / Clinical Significance</label>
                            <textarea name="description" class="form-control" rows="2" placeholder="Describe the focus of this health screening package..."></textarea>
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_popular" value="1" id="isPopularNew">
                                <label class="form-check-label" for="isPopularNew">Feature as Popular Package on Patient Portal</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bx bx-check me-1"></i> Save Package
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
