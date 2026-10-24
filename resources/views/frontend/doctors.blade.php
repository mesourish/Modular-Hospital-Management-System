@extends('frontend.layout')

@section('title', __('Our Doctors') . ' | ' . $settings['site_name'])

@section('content')
<!-- Header Banner -->
<section class="py-5" style="background: linear-gradient(135deg, #182038 0%, #252d48 50%, #171d33 100%); color: #ffffff;">
    <div class="container py-3 text-center">
        <span class="badge bg-primary-subtle text-primary font-size-12 px-3 py-1 mb-2 fw-bold text-uppercase">
            <i class="bx bx-user-check me-1"></i> Medical Faculty & Super-Specialists
        </span>
        <h1 class="display-6 fw-bold text-white mb-2">{{ __('Our Doctors') }}</h1>
        <p class="text-light opacity-90 mx-auto font-size-15" style="max-width: 650px;">
            Consult with distinguished surgical oncologists, interventional cardiologists, neurologists, and emergency physicians across our hospital network.
        </p>

        <!-- Search & Filter Card -->
        <div class="card border-0 shadow-lg p-3 rounded-4 mx-auto mt-4" style="max-width: 900px; background: rgba(255, 255, 255, 0.98);">
            <form action="{{ route('frontend.doctors') }}" method="GET">
                <div class="row g-2 align-items-center">
                    <div class="col-md-5">
                        <div class="input-group">
                            <span class="input-group-text bg-transparent border-0 text-primary">
                                <i class="bx bx-search font-size-18"></i>
                            </span>
                            <input type="text" name="search" class="form-control border-0 bg-transparent text-dark" placeholder="{{ __('Search Doctor, Specialty or Hospital') }}" value="{{ $search }}">
                        </div>
                    </div>
                    <div class="col-md-3 border-start border-md-start">
                        <select name="department_id" class="form-select border-0 bg-transparent text-secondary">
                            <option value="all">{{ __('All Specialties') }}</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ $departmentId == $dept->id ? 'selected' : '' }}>
                                    {{ $dept->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 border-start border-md-start">
                        <select name="branch_id" class="form-select border-0 bg-transparent text-secondary">
                            <option value="all">{{ __('All Branches') }}</option>
                            @foreach($branches as $b)
                                <option value="{{ $b->id }}" {{ $branchId == $b->id ? 'selected' : '' }}>
                                    {{ $b->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-1">
                        <button type="submit" class="btn btn-portal-primary w-100 py-2">
                            <i class="bx bx-search"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>

<!-- Doctors Directory Grid -->
<section class="py-5 bg-white">
    <div class="container py-3">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold text-dark mb-1">
                    Available Specialists ({{ $doctorCharts->count() }})
                </h4>
                <p class="text-muted font-size-13 mb-0">Verified medical credentials, chamber hours, and transparent consultation fees</p>
            </div>
            @if(request()->hasAny(['search', 'branch_id', 'department_id']))
                <a href="{{ route('frontend.doctors') }}" class="btn btn-sm btn-light text-muted">
                    <i class="bx bx-reset me-1"></i> Reset Filters
                </a>
            @endif
        </div>

        <div class="row g-4">
            @forelse($doctorCharts as $doc)
                <div class="col-lg-4 col-md-6">
                    <div class="glass-card h-100 d-flex flex-column p-4">
                        <div class="d-flex align-items-start gap-3 mb-3">
                            <div class="avatar-lg rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center fw-bold font-size-22 shadow-sm flex-shrink-0">
                                {{ substr($doc->doctor_name, 4, 1) }}
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="fw-bold text-dark mb-1 font-size-16">{{ $doc->doctor_name }}</h5>
                                <span class="badge bg-primary-subtle text-primary font-size-11 px-2 py-1 mb-1">
                                    {{ $doc->department?->name ?? 'Specialist OPD' }}
                                </span>
                                <small class="text-muted d-block font-size-11">
                                    <i class="bx bx-building me-1"></i> {{ $doc->branch?->name }} ({{ $doc->branch?->city }})
                                </small>
                            </div>
                        </div>

                        <p class="text-muted font-size-12 mb-2">
                            <i class="bx bx-award text-warning me-1"></i> {{ $doc->degrees }}
                        </p>
                        <p class="text-dark fw-semibold font-size-13 mb-3 line-clamp-2">
                            {{ $doc->specialty }}
                        </p>

                        <div class="bg-light p-3 rounded-3 mb-4 font-size-12">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="text-muted">{{ __('Consultation Fee') }}:</span>
                                <strong class="text-success font-size-14">{{ $doc->formatted_fee }}</strong>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="text-muted">{{ __('Available Days') }}:</span>
                                <span class="text-dark fw-medium">{{ $doc->opd_days }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="text-muted">{{ __('Time Slot') }}:</span>
                                <span class="text-dark">{{ $doc->formatted_timing }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted">Patient Rating:</span>
                                <span class="text-warning fw-bold">
                                    <i class="mdi mdi-star"></i> {{ $doc->average_rating }} / 5.0 ({{ $doc->testimonials_count }} reviews)
                                </span>
                            </div>
                        </div>

                        <div class="mt-auto d-flex gap-2">
                            <a href="{{ route('frontend.doctor_profile', $doc) }}" class="btn btn-primary btn-sm w-100 py-2 fw-semibold shadow-sm">
                                <i class="bx bx-user me-1"></i> {{ __('View Profile') }}
                            </a>
                            <a href="{{ route('frontend.doctor_profile', $doc) }}#quick-appointment" class="btn btn-outline-success btn-sm py-2 px-3 fw-semibold">
                                <i class="bx bx-calendar-plus me-1"></i> {{ __('Book Token') }}
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5">
                    <i class="bx bx-user-x font-size-48 text-muted d-block mb-3"></i>
                    <h5 class="text-dark">No doctors match your filter criteria.</h5>
                    <p class="text-muted font-size-13">Try clearing the search query or selecting a different department/branch.</p>
                    <a href="{{ route('frontend.doctors') }}" class="btn btn-primary btn-sm mt-2">
                        View All Doctors
                    </a>
                </div>
            @endforelse
        </div>
    </div>
</section>
@endsection
