@extends('frontend.layout')

@section('title', __('Find Hospitals') . ' | ' . $settings['site_name'])

@section('content')
<!-- Header Banner -->
<section class="py-5" style="background: linear-gradient(135deg, #182038 0%, #252d48 50%, #171d33 100%); color: #ffffff;">
    <div class="container py-3 text-center">
        <span class="badge bg-primary-subtle text-primary font-size-12 px-3 py-1 mb-2 fw-bold text-uppercase">
            <i class="bx bx-map-pin me-1"></i> Multi-City Network Locator
        </span>
        <h1 class="display-6 fw-bold text-white mb-2">{{ __('Find Your Nearest Hospital') }}</h1>
        <p class="text-light opacity-90 mx-auto font-size-15" style="max-width: 650px;">
            Search our multi-super specialty hospitals, specialized cardiac clinics, and day-care diagnostic centers across Maharashtra and India.
        </p>

        <!-- Search & Filter Form -->
        <div class="card border-0 shadow-lg p-3 rounded-4 mx-auto mt-4" style="max-width: 850px; background: rgba(255, 255, 255, 0.98);">
            <form action="{{ route('frontend.locations') }}" method="GET">
                <div class="row g-2 align-items-center">
                    <div class="col-md-5">
                        <div class="input-group">
                            <span class="input-group-text bg-transparent border-0 text-primary">
                                <i class="bx bx-search font-size-18"></i>
                            </span>
                            <input type="text" name="search" class="form-control border-0 bg-transparent text-dark" placeholder="{{ __('Search Hospitals & Branches') }}" value="{{ $search }}">
                        </div>
                    </div>
                    <div class="col-md-3 border-start border-md-start">
                        <select name="city" class="form-select border-0 bg-transparent text-secondary">
                            <option value="all">{{ __('All Cities') }}</option>
                            @foreach($cities as $c)
                                <option value="{{ $c }}" {{ request('city') == $c ? 'selected' : '' }}>{{ $c }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 border-start border-md-start">
                        <select name="state" class="form-select border-0 bg-transparent text-secondary">
                            <option value="all">All States</option>
                            @foreach($states as $s)
                                <option value="{{ $s }}" {{ request('state') == $s ? 'selected' : '' }}>{{ $s }}</option>
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

<!-- Hospitals & Branches Directory Grid -->
<section class="py-5 bg-white">
    <div class="container py-3">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold text-dark mb-1">
                    {{ __('Operational Branches') }} <span class="badge bg-primary-subtle text-primary font-size-13">{{ $branches->count() }} Centers</span>
                </h4>
                <p class="text-muted font-size-13 mb-0">Full-service tertiary care hospitals, intensive care units, and outpatient centers</p>
            </div>
            @if(request()->hasAny(['search', 'city', 'state']))
                <a href="{{ route('frontend.locations') }}" class="btn btn-sm btn-light text-muted">
                    <i class="bx bx-reset me-1"></i> Reset Filters
                </a>
            @endif
        </div>

        <div class="row g-4">
            @forelse($branches as $branch)
                <div class="col-lg-6">
                    <div class="glass-card h-100 p-4 d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <span class="badge bg-primary-subtle text-primary font-monospace font-size-11 px-2 py-1 mb-2 d-inline-block">
                                    {{ $branch->code }}
                                </span>
                                <h4 class="fw-bold text-dark mb-1">{{ $branch->name }}</h4>
                                <span class="text-muted font-size-13">
                                    <i class="bx bx-map-pin text-danger me-1"></i> {{ $branch->city }}, {{ $branch->state }}
                                </span>
                            </div>
                            @if($branch->is_main_branch)
                                <span class="badge bg-success font-size-11 px-3 py-1">
                                    <i class="bx bx-star me-1"></i> Flagship Super-Specialty
                                </span>
                            @else
                                <span class="badge bg-secondary-subtle text-dark font-size-11 px-2 py-1">
                                    Satellite Center
                                </span>
                            @endif
                        </div>

                        <div class="p-3 bg-light rounded-3 mb-3">
                            <p class="text-dark font-size-13 mb-2">
                                <i class="bx bx-building text-primary me-2"></i><strong>Address:</strong> {{ $branch->address }}, {{ $branch->city }}, {{ $branch->state }} - {{ $branch->pincode }}
                            </p>
                            <div class="row g-2 font-size-12">
                                <div class="col-sm-6">
                                    <span class="text-muted d-block">Helpline / Reception:</span>
                                    <a href="tel:{{ $branch->phone }}" class="text-dark fw-semibold text-decoration-none">
                                        <i class="bx bx-phone text-success me-1"></i> {{ $branch->phone }}
                                    </a>
                                </div>
                                <div class="col-sm-6">
                                    <span class="text-muted d-block">Branch Support Email:</span>
                                    <a href="mailto:{{ $branch->email }}" class="text-dark fw-semibold text-decoration-none">
                                        <i class="bx bx-envelope text-info me-1"></i> {{ $branch->email }}
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Highlights: Emergency & OPD hours -->
                        <div class="row g-2 mb-4 font-size-12">
                            <div class="col-sm-6">
                                <div class="p-2 border rounded-2 d-flex align-items-center gap-2">
                                    <i class="bx bx-plus-medical text-danger font-size-18"></i>
                                    <div>
                                        <small class="text-muted d-block">Emergency 24x7 Hotline</small>
                                        <strong class="text-danger">{{ \App\Models\SystemSetting::get('emergency_hotline', '1066', $branch->id, $branch->hospital_id) }}</strong>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="p-2 border rounded-2 d-flex align-items-center gap-2">
                                    <i class="bx bx-time text-primary font-size-18"></i>
                                    <div>
                                        <small class="text-muted d-block">OPD Timings</small>
                                        <strong class="text-dark">{{ \App\Models\SystemSetting::get('opd_hours', '08:00 AM - 08:00 PM', $branch->id, $branch->hospital_id) }}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="mt-auto pt-3 border-top d-flex flex-wrap gap-2 justify-content-between align-items-center">
                            <a href="https://maps.google.com/?q={{ urlencode($branch->name . ' ' . $branch->address . ' ' . $branch->city) }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                                <i class="bx bx-directions me-1"></i> {{ __('Get Directions') }}
                            </a>
                            <div class="d-flex gap-2">
                                <a href="{{ route('frontend.doctors') }}?branch_id={{ $branch->id }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bx bx-user-check me-1"></i> View Doctors
                                </a>
                                <a href="{{ route('frontend.home') }}?branch_id={{ $branch->id }}#book-section" class="btn btn-sm btn-portal-primary">
                                    <i class="bx bx-calendar-plus me-1"></i> {{ __('Book Token Now') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5">
                    <i class="bx bx-buildings font-size-48 text-muted d-block mb-3"></i>
                    <h5 class="text-dark">No hospital branches match your search criteria.</h5>
                    <p class="text-muted font-size-13">Try selecting a different city or clearing the keyword search filter.</p>
                    <a href="{{ route('frontend.locations') }}" class="btn btn-primary btn-sm mt-2">
                        View All Operational Branches
                    </a>
                </div>
            @endforelse
        </div>
    </div>
</section>
@endsection
