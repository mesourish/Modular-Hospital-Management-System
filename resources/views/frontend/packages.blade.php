@extends('frontend.layout')

@section('title', __('Health Packages') . ' | ' . $settings['site_name'])

@section('content')
<!-- Header Banner -->
<section class="py-5" style="background: linear-gradient(135deg, #182038 0%, #252d48 50%, #171d33 100%); color: #ffffff;">
    <div class="container py-3 text-center">
        <span class="badge bg-warning-subtle text-warning font-size-12 px-3 py-1 mb-2 fw-bold text-uppercase">
            <i class="bx bx-package me-1"></i> Preventive Health Screening Catalogue
        </span>
        <h1 class="display-6 fw-bold text-white mb-2">{{ __('Featured Health Checkup Packages') }}</h1>
        <p class="text-light opacity-90 mx-auto font-size-15" style="max-width: 680px;">
            Designed by clinical directors to identify silent cardiac, diabetic, and metabolic risks early. Available across our hospital network with same-day digital reports.
        </p>

        <!-- Category & Branch Filter Bar -->
        <div class="card border-0 shadow-lg p-3 rounded-4 mx-auto mt-4" style="max-width: 800px; background: rgba(255, 255, 255, 0.98);">
            <form action="{{ route('frontend.packages') }}" method="GET">
                <div class="row g-2 align-items-center">
                    <div class="col-md-6">
                        <select name="category" class="form-select border-0 bg-transparent text-secondary">
                            <option value="all">{{ __('All Categories') }}</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat }}" {{ $category == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 border-start border-md-start">
                        <select name="branch_id" class="form-select border-0 bg-transparent text-secondary">
                            <option value="all">{{ __('All Branches') }}</option>
                            @foreach($branches as $b)
                                <option value="{{ $b->id }}" {{ $branchId == $b->id ? 'selected' : '' }}>{{ $b->name }} ({{ $b->city }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-portal-primary w-100 py-2">
                            <i class="bx bx-filter-alt me-1"></i> Filter
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>

<!-- Packages Directory Grid -->
<section class="py-5 bg-white">
    <div class="container py-3">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold text-dark mb-1">
                    Available Health Checkups ({{ $packages->count() }})
                </h4>
                <p class="text-muted font-size-13 mb-0">Transparent pricing with no hidden charges. All packages include specialist physician consultation</p>
            </div>
            @if(request()->hasAny(['category', 'branch_id']))
                <a href="{{ route('frontend.packages') }}" class="btn btn-sm btn-light text-muted">
                    <i class="bx bx-reset me-1"></i> Reset Filters
                </a>
            @endif
        </div>

        <div class="row g-4">
            @forelse($packages as $pkg)
                <div class="col-lg-6" id="pkg-{{ $pkg->id }}">
                    <div class="glass-card h-100 p-4 d-flex flex-column border-top border-4 {{ $pkg->is_popular ? 'border-primary' : 'border-secondary' }}">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <span class="badge bg-primary-subtle text-primary badge-package-category mb-1">
                                    {{ $pkg->category }}
                                </span>
                                <h4 class="fw-bold text-dark mb-1">{{ $pkg->name }}</h4>
                                <span class="text-muted font-monospace font-size-11">{{ $pkg->code }}</span>
                            </div>
                            @if($pkg->is_popular)
                                <span class="badge bg-danger px-2 py-1 font-size-11">
                                    <i class="bx bxs-hot me-1"></i> Most Popular
                                </span>
                            @endif
                        </div>

                        <p class="text-muted font-size-13 mb-3">
                            {{ $pkg->description }}
                        </p>

                        <!-- Pricing & Parameters Strip -->
                        <div class="p-3 bg-light rounded-3 mb-3">
                            <div class="row align-items-center g-2">
                                <div class="col-sm-6 border-end border-sm-end">
                                    <small class="text-muted d-block font-size-11">All-Inclusive Offer Price</small>
                                    <div class="d-flex align-items-baseline gap-2">
                                        <h2 class="text-success fw-bold mb-0 font-size-28">{{ $pkg->formatted_discounted_price }}</h2>
                                        @if($pkg->discount_percentage > 0)
                                            <span class="text-muted text-decoration-line-through font-size-13">{{ $pkg->formatted_original_price }}</span>
                                            <span class="badge bg-danger-subtle text-danger font-size-11">{{ $pkg->discount_percentage }}% OFF</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-sm-6 ps-sm-3 font-size-12">
                                    <div class="mb-1">
                                        <i class="bx bx-test-tube text-primary me-1"></i>
                                        <strong>{{ $pkg->tests_count }}</strong> Parameters & Diagnostic Tests
                                    </div>
                                    <div class="mb-1">
                                        <i class="bx bx-time-five text-warning me-1"></i>
                                        {{ $pkg->turnaround_time }}
                                    </div>
                                    <div>
                                        <i class="bx bx-building text-info me-1"></i>
                                        {{ $pkg->branch?->name ?? 'All Hospital Branches' }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Preparation Guidelines -->
                        <div class="d-flex flex-wrap gap-2 mb-3 font-size-12">
                            @if($pkg->fasting_required)
                                <span class="badge bg-warning-subtle text-warning px-2 py-1">
                                    <i class="bx bx-time-five me-1"></i> {{ $pkg->fasting_hours }}h Fasting Required
                                </span>
                            @else
                                <span class="badge bg-success-subtle text-success px-2 py-1">
                                    No Fasting Required
                                </span>
                            @endif
                            <span class="badge bg-secondary-subtle text-dark px-2 py-1">
                                Target: {{ $pkg->gender_target }}
                            </span>
                            <span class="badge bg-secondary-subtle text-dark px-2 py-1">
                                Age: {{ $pkg->age_group }}
                            </span>
                        </div>

                        <!-- Included Tests Accordion / List -->
                        <div class="mb-4 flex-grow-1">
                            <span class="fw-semibold text-dark font-size-13 d-block mb-2">
                                <i class="bx bx-check-circle text-success me-1"></i> Tests & Consultations Included:
                            </span>
                            <div class="p-2 border rounded-3 bg-white" style="max-height: 140px; overflow-y: auto;">
                                <ul class="list-unstyled mb-0 font-size-12 text-secondary">
                                    @if(is_array($pkg->tests_included))
                                        @foreach($pkg->tests_included as $test)
                                            <li class="py-1 border-bottom border-light d-flex align-items-center">
                                                <i class="bx bx-check text-success font-size-15 me-1"></i> {{ $test }}
                                            </li>
                                        @endforeach
                                    @else
                                        <li>{{ $pkg->tests_included }}</li>
                                    @endif
                                </ul>
                            </div>
                        </div>

                        <!-- Book Button -->
                        <div class="mt-auto pt-3 border-top d-flex justify-content-between align-items-center">
                            <span class="text-muted font-size-12">Same-day sample collection & digital report delivery</span>
                            <button type="button" class="btn btn-portal-primary btn-sm px-4 py-2 fw-semibold" data-bs-toggle="modal" data-bs-target="#bookPkgModal{{ $pkg->id }}">
                                <i class="bx bx-calendar-plus me-1"></i> {{ __('Book Now') }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Modal: Book Package -->
                <div class="modal fade" id="bookPkgModal{{ $pkg->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form method="POST" action="{{ route('portal.book_package') }}">
                                @csrf
                                <input type="hidden" name="package_id" value="{{ $pkg->id }}">
                                <div class="modal-header">
                                    <h5 class="modal-title font-size-16">
                                        <i class="bx bx-package text-primary me-1"></i> Book {{ $pkg->name }}
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body p-4">
                                    <div class="p-3 bg-light rounded-3 mb-3 font-size-13">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span>Package Price:</span>
                                            <strong class="text-success font-size-15">{{ $pkg->formatted_discounted_price }}</strong>
                                        </div>
                                        <div class="d-flex justify-content-between mb-1">
                                            <span>Diagnostic Tests:</span>
                                            <strong>{{ $pkg->tests_count }} Parameters</strong>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span>Fasting Rule:</span>
                                            <span>{{ $pkg->fasting_required ? $pkg->fasting_hours . ' Hours Overnight Fasting' : 'No Fasting' }}</span>
                                        </div>
                                    </div>

                                    <div class="mb-2">
                                        <label class="form-label font-size-12 fw-semibold mb-1">Select Hospital Branch *</label>
                                        <select name="branch_id" class="form-select" required>
                                            @if($pkg->branch)
                                                <option value="{{ $pkg->branch_id }}" selected>{{ $pkg->branch->name }} ({{ $pkg->branch->city }})</option>
                                            @else
                                                @foreach($branches as $b)
                                                    <option value="{{ $b->id }}">{{ $b->name }} ({{ $b->city }})</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>

                                    <div class="mb-2">
                                        <label class="form-label font-size-12 fw-semibold mb-1">{{ __('Patient Name') }} *</label>
                                        <input type="text" name="patient_name" class="form-control" placeholder="Full name of person undergoing checkup" required>
                                    </div>

                                    <div class="row g-2 mb-2">
                                        <div class="col-md-6">
                                            <label class="form-label font-size-12 fw-semibold mb-1">{{ __('Phone Number') }} *</label>
                                            <input type="tel" name="patient_phone" class="form-control" placeholder="10-digit mobile number" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label font-size-12 fw-semibold mb-1">Email Address</label>
                                            <input type="email" name="patient_email" class="form-control" placeholder="For digital reports">
                                        </div>
                                    </div>

                                    <div class="row g-2 mb-2">
                                        <div class="col-md-6">
                                            <label class="form-label font-size-12 fw-semibold mb-1">{{ __('Age') }} *</label>
                                            <input type="number" name="patient_age" class="form-control" placeholder="Age in years" min="1" max="120" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label font-size-12 fw-semibold mb-1">{{ __('Gender') }} *</label>
                                            <select name="patient_gender" class="form-select" required>
                                                <option value="male">{{ __('Male') }}</option>
                                                <option value="female">{{ __('Female') }}</option>
                                                <option value="other">{{ __('Other') }}</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label font-size-12 fw-semibold mb-1">Preferred Checkup Date *</label>
                                        <input type="date" name="preferred_date" class="form-control" value="{{ date('Y-m-d', strtotime('+1 day')) }}" min="{{ date('Y-m-d') }}" required>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label font-size-12 fw-semibold mb-1">Special Clinical Requests / Medical History</label>
                                        <textarea name="notes" class="form-control" rows="2" placeholder="e.g. Diabetics needing morning fast token, wheelchair assistance required..."></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bx bx-check me-1"></i> Confirm Checkup Booking
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5">
                    <i class="bx bx-package font-size-48 text-muted d-block mb-3"></i>
                    <h5 class="text-dark">No checkup packages match your filter selection.</h5>
                    <p class="text-muted font-size-13">Try clearing the category or branch filter above.</p>
                    <a href="{{ route('frontend.packages') }}" class="btn btn-primary btn-sm mt-2">
                        View All Packages
                    </a>
                </div>
            @endforelse
        </div>
    </div>
</section>
@endsection
