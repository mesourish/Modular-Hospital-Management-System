@extends('frontend.layout')

@section('title', $settings['site_name'] . ' | ' . __('Home'))

@section('content')
<!-- Hero Section -->
<section class="portal-hero py-5" style="background: linear-gradient(135deg, #182038 0%, #252d48 50%, #171d33 100%); color: #ffffff; position: relative; overflow: hidden;">
    <div class="container py-4 position-relative" style="z-index: 2;">
        <div class="row align-items-center g-4">
            <div class="col-lg-7">
                <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill mb-3" style="background: rgba(85, 110, 230, 0.2); border: 1px solid rgba(85, 110, 230, 0.4); font-size: 0.85rem; color: #a6b0cf;">
                    <i class="bx bx-check-shield text-success font-size-16"></i>
                    <span>{{ __('NABH & JCI Accredited') }} • {{ __('Clinical Excellence') }}</span>
                </div>
                <h1 class="display-5 fw-bold text-white mb-3" style="letter-spacing: -0.5px;">
                    {{ $settings['hero_title'] }}
                </h1>
                <p class="lead text-light opacity-90 mb-4" style="font-size: 1.05rem; line-height: 1.6;">
                    {{ $settings['hero_subtitle'] }}
                </p>

                <!-- Search & Quick Action Bar -->
                <form action="{{ route('frontend.doctors') }}" method="GET" class="card border-0 p-2 shadow-lg rounded-3 mb-4" style="background: rgba(255, 255, 255, 0.96);">
                    <div class="row g-2 align-items-center">
                        <div class="col-md-5">
                            <div class="input-group">
                                <span class="input-group-text bg-transparent border-0 text-primary">
                                    <i class="bx bx-search font-size-18"></i>
                                </span>
                                <input type="text" name="search" class="form-control border-0 bg-transparent" placeholder="{{ __('Search Doctor, Specialty or Hospital') }}">
                            </div>
                        </div>
                        <div class="col-md-4 border-start border-md-start">
                            <select name="branch_id" class="form-select border-0 bg-transparent text-secondary">
                                <option value="all">{{ __('All Branches') }}</option>
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}">{{ $b->name }} ({{ $b->city }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-portal-primary w-100 py-2">
                                <i class="bx bx-search me-1"></i> {{ __('Search') }}
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Key Metrics Ribbon -->
                <div class="row g-3">
                    <div class="col-6 col-sm-3">
                        <div class="p-3 rounded-3" style="background: rgba(255, 255, 255, 0.08); border: 1px solid rgba(255, 255, 255, 0.12);">
                            <h3 class="fw-bold mb-0 text-warning">{{ $metrics['total_branches'] }}</h3>
                            <small class="text-light opacity-75 font-size-11">{{ __('Operational Branches') }}</small>
                        </div>
                    </div>
                    <div class="col-6 col-sm-3">
                        <div class="p-3 rounded-3" style="background: rgba(255, 255, 255, 0.08); border: 1px solid rgba(255, 255, 255, 0.12);">
                            <h3 class="fw-bold mb-0 text-info">{{ $metrics['total_doctors'] }}+</h3>
                            <small class="text-light opacity-75 font-size-11">{{ __('Active Doctors') }}</small>
                        </div>
                    </div>
                    <div class="col-6 col-sm-3">
                        <div class="p-3 rounded-3" style="background: rgba(255, 255, 255, 0.08); border: 1px solid rgba(255, 255, 255, 0.12);">
                            <h3 class="fw-bold mb-0 text-success">{{ $metrics['total_specialties'] }}+</h3>
                            <small class="text-light opacity-75 font-size-11">{{ __('All Specialties') }}</small>
                        </div>
                    </div>
                    <div class="col-6 col-sm-3">
                        <div class="p-3 rounded-3" style="background: rgba(255, 255, 255, 0.08); border: 1px solid rgba(255, 255, 255, 0.12);">
                            <h3 class="fw-bold mb-0 text-danger">24/7</h3>
                            <small class="text-light opacity-75 font-size-11">{{ __('24x7 Trauma & ICU') }}</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Book Card in Hero -->
            <div class="col-lg-5" id="book-section">
                <div class="card border-0 shadow-2xl rounded-4 overflow-hidden" style="box-shadow: 0 20px 45px rgba(0,0,0,0.25);">
                    <div class="card-header bg-primary text-white py-3 px-4">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h5 class="mb-0 text-white font-size-16 fw-bold">
                                    <i class="bx bx-calendar-star me-1"></i> {{ __('Quick Appointment') }}
                                </h5>
                                <small class="text-white-50 font-size-11">Instant OPD Token Booking • Confirmed Consultation</small>
                            </div>
                            <span class="badge bg-warning text-dark font-size-11">No Waiting</span>
                        </div>
                    </div>
                    <div class="card-body p-4 bg-white text-dark">
                        <form method="POST" action="{{ route('portal.book_appointment') }}">
                            @csrf
                            <div class="row g-2 mb-2">
                                <div class="col-md-6">
                                    <label class="form-label font-size-12 fw-semibold mb-1">{{ __('Patient Name') }} *</label>
                                    <input type="text" name="patient_name" class="form-control form-control-sm" placeholder="e.g. Ramesh Patel" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label font-size-12 fw-semibold mb-1">{{ __('Phone Number') }} *</label>
                                    <input type="tel" name="patient_phone" class="form-control form-control-sm" placeholder="10-digit mobile" required>
                                </div>
                            </div>

                            <div class="row g-2 mb-2">
                                <div class="col-md-4">
                                    <label class="form-label font-size-12 fw-semibold mb-1">{{ __('Age') }} *</label>
                                    <input type="number" name="patient_age" class="form-control form-control-sm" placeholder="Age" min="1" max="120" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label font-size-12 fw-semibold mb-1">{{ __('Gender') }} *</label>
                                    <select name="patient_gender" class="form-select form-select-sm" required>
                                        <option value="male">{{ __('Male') }}</option>
                                        <option value="female">{{ __('Female') }}</option>
                                        <option value="other">{{ __('Other') }}</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label font-size-12 fw-semibold mb-1">ABHA Health ID</label>
                                    <input type="text" name="patient_abha" class="form-control form-control-sm" placeholder="Optional">
                                </div>
                            </div>

                            <div class="mb-2">
                                <label class="form-label font-size-12 fw-semibold mb-1">Select Hospital Branch *</label>
                                <select name="branch_id" id="quickBranchSelect" class="form-select form-select-sm" required>
                                    @foreach($branches as $b)
                                        <option value="{{ $b->id }}">{{ $b->name }} ({{ $b->city }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-2">
                                <label class="form-label font-size-12 fw-semibold mb-1">Specialist Doctor *</label>
                                <select name="doctor_id" id="quickDoctorSelect" class="form-select form-select-sm" required>
                                    @foreach($doctorCharts as $dc)
                                        <option value="{{ $dc->user_id }}" data-branch="{{ $dc->branch_id }}" data-fee="{{ $dc->consultation_fee }}">
                                            {{ $dc->doctor_name }} ({{ $dc->specialty }}) - {{ $dc->formatted_fee }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label font-size-12 fw-semibold mb-1">{{ __('Appointment Date') }} *</label>
                                    <input type="date" name="appointment_date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" min="{{ date('Y-m-d') }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label font-size-12 fw-semibold mb-1">{{ __('Select Time Slot') }} *</label>
                                    <select name="appointment_time" class="form-select form-select-sm" required>
                                        <option value="09:00:00">09:00 AM - Morning</option>
                                        <option value="11:00:00">11:00 AM - Mid-Day</option>
                                        <option value="14:00:00">02:00 PM - Afternoon</option>
                                        <option value="17:00:00">05:00 PM - Evening</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label font-size-12 fw-semibold mb-1">{{ __('Symptoms / Notes') }}</label>
                                <input type="text" name="symptoms" class="form-control form-control-sm" placeholder="e.g. Chest pain, High fever, Routine checkup">
                            </div>

                            <button type="submit" class="btn btn-portal-primary w-100 py-2 shadow-sm font-size-14">
                                <i class="bx bx-check-circle me-1"></i> {{ __('Confirm OPD Booking') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Health Packages Section -->
<section class="py-5" style="background-color: #f1f5f9;">
    <div class="container py-3">
        <div class="d-flex flex-wrap justify-content-between align-items-end mb-4">
            <div>
                <span class="badge bg-warning-subtle text-warning font-size-12 px-3 py-1 mb-2 fw-bold text-uppercase">
                    <i class="bx bx-package me-1"></i> Preventive Health Checkups
                </span>
                <h2 class="fw-bold text-dark mb-0">{{ __('Featured Health Checkup Packages') }}</h2>
                <p class="text-muted font-size-14 mb-0">Configured by Medical Directors with up to 50% savings on diagnostic tests</p>
            </div>
            <a href="{{ route('frontend.packages') }}" class="btn btn-outline-primary btn-sm fw-semibold">
                {{ __('View All Packages') }} <i class="bx bx-right-arrow-alt align-middle ms-1"></i>
            </a>
        </div>

        <div class="row g-4">
            @forelse($packages as $pkg)
                <div class="col-lg-3 col-md-6">
                    <div class="glass-card h-100 d-flex flex-column p-4 position-relative">
                        @if($pkg->is_popular)
                            <span class="badge bg-danger position-absolute top-0 end-0 m-3 px-2 py-1 font-size-11">
                                <i class="bx bxs-hot me-1"></i> Popular
                            </span>
                        @endif
                        <span class="badge bg-primary-subtle text-primary badge-package-category mb-2 align-self-start">
                            {{ $pkg->category }}
                        </span>
                        <h5 class="fw-bold text-dark mb-2">{{ $pkg->name }}</h5>
                        <p class="text-muted font-size-12 mb-3 line-clamp-2">
                            {{ Str::limit($pkg->description, 80) }}
                        </p>

                        <div class="bg-light p-3 rounded-3 mb-3">
                            <div class="d-flex align-items-baseline gap-2 mb-1">
                                <h3 class="text-success fw-bold mb-0">{{ $pkg->formatted_discounted_price }}</h3>
                                @if($pkg->discount_percentage > 0)
                                    <span class="text-muted text-decoration-line-through font-size-13">{{ $pkg->formatted_original_price }}</span>
                                    <span class="badge bg-danger-subtle text-danger font-size-11">{{ $pkg->discount_percentage }}% OFF</span>
                                @endif
                            </div>
                            <small class="text-muted d-block font-size-11">
                                <i class="bx bx-test-tube text-primary me-1"></i> <strong>{{ $pkg->tests_count }}</strong> Parameters & Diagnostic Tests
                            </small>
                            <small class="text-muted d-block font-size-11">
                                <i class="bx bx-time text-warning me-1"></i> Reports in {{ $pkg->turnaround_time }}
                            </small>
                        </div>

                        <div class="mt-auto">
                            <button type="button" class="btn btn-outline-primary btn-sm w-100 fw-semibold" data-bs-toggle="modal" data-bs-target="#homeBookPkgModal{{ $pkg->id }}">
                                <i class="bx bx-calendar-plus me-1"></i> {{ __('Book Checkup Package') }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- 1-Click Package Booking Modal -->
                <div class="modal fade text-start" id="homeBookPkgModal{{ $pkg->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form method="POST" action="{{ route('portal.book_package') }}">
                                @csrf
                                <input type="hidden" name="package_id" value="{{ $pkg->id }}">
                                <div class="modal-header bg-primary text-white">
                                    <h5 class="modal-title font-size-16 text-white">
                                        <i class="bx bx-package me-1"></i> {{ __('Book Checkup Package') }}: {{ $pkg->name }}
                                    </h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body p-4 text-dark">
                                    <div class="p-3 bg-light rounded-3 mb-3 font-size-13">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span class="text-muted">{{ __('Package Price:') }}</span>
                                            <strong class="text-success font-size-15">{{ $pkg->formatted_discounted_price }}</strong>
                                        </div>
                                        <div class="d-flex justify-content-between mb-1">
                                            <span class="text-muted">{{ __('Diagnostic Tests:') }}</span>
                                            <strong>{{ $pkg->tests_count }} {{ __('Parameters & Diagnostic Tests') }}</strong>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span class="text-muted">{{ __('Fasting Rule:') }}</span>
                                            <span>{{ $pkg->fasting_required ? $pkg->fasting_hours . ' ' . __('Hours Overnight Fasting') : __('No Fasting') }}</span>
                                        </div>
                                    </div>

                                    <div class="mb-2">
                                        <label class="form-label font-size-12 fw-semibold mb-1">{{ __('Select Hospital Branch *') }}</label>
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
                                        <input type="text" name="patient_name" class="form-control" placeholder="{{ __('Full name of person undergoing checkup') }}" required>
                                    </div>

                                    <div class="row g-2 mb-2">
                                        <div class="col-md-6">
                                            <label class="form-label font-size-12 fw-semibold mb-1">{{ __('Phone Number') }} *</label>
                                            <input type="tel" name="patient_phone" class="form-control" placeholder="{{ __('10-digit mobile number') }}" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label font-size-12 fw-semibold mb-1">{{ __('Email Address') }}</label>
                                            <input type="email" name="patient_email" class="form-control" placeholder="name@example.com">
                                        </div>
                                    </div>

                                    <div class="row g-2 mb-2">
                                        <div class="col-6">
                                            <label class="form-label font-size-12 fw-semibold mb-1">{{ __('Age Group') }} *</label>
                                            <input type="number" name="patient_age" class="form-control" placeholder="e.g. 35" min="1" max="120" required>
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label font-size-12 fw-semibold mb-1">{{ __('Gender') }} *</label>
                                            <select name="patient_gender" class="form-select" required>
                                                <option value="male">{{ __('Male') }}</option>
                                                <option value="female">{{ __('Female') }}</option>
                                                <option value="other">{{ __('Other') }}</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="mb-2">
                                        <label class="form-label font-size-12 fw-semibold mb-1">{{ __('Preferred Checkup Date *') }}</label>
                                        <input type="date" name="preferred_date" class="form-control" min="{{ date('Y-m-d') }}" value="{{ date('Y-m-d', strtotime('+1 day')) }}" required>
                                    </div>

                                    <div class="mb-2">
                                        <label class="form-label font-size-12 fw-semibold mb-1">{{ __('Special Health Notes / Medical Conditions') }}</label>
                                        <textarea name="notes" class="form-control" rows="2" placeholder="e.g. Diabetics needing morning fast token, wheelchair assistance required..."></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bx bx-check me-1"></i> {{ __('Confirm & Book Package') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center text-muted py-4">
                    <p>No health packages configured yet.</p>
                </div>
            @endforelse
        </div>
    </div>
</section>

<!-- Specialist Doctors Showcase with 'View Profile' Button -->
<section class="py-5 bg-white">
    <div class="container py-3">
        <div class="d-flex flex-wrap justify-content-between align-items-end mb-4">
            <div>
                <span class="badge bg-info-subtle text-info font-size-12 px-3 py-1 mb-2 fw-bold text-uppercase">
                    <i class="bx bx-user-check me-1"></i> {{ __('Clinical Board & OPD') }}
                </span>
                <h2 class="fw-bold text-dark mb-0">{{ __('Our Doctors') }} • {{ __('Doctor OPD Duty Schedules & Fees') }}</h2>
                <p class="text-muted font-size-14 mb-0">{{ __("Consult India's top surgical and medical super-specialists") }}</p>
            </div>
            <a href="{{ route('frontend.doctors') }}" class="btn btn-outline-primary btn-sm fw-semibold">
                {{ __('View All Specialists') }} <i class="bx bx-right-arrow-alt align-middle ms-1"></i>
            </a>
        </div>

        <div class="row g-4">
            @forelse($doctorCharts->take(4) as $doc)
                <div class="col-lg-3 col-md-6">
                    <div class="glass-card h-100 d-flex flex-column p-4">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="avatar-md rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center fw-bold font-size-18 shadow-sm">
                                {{ substr($doc->doctor_name, 4, 1) }}
                            </div>
                            <div>
                                <h6 class="fw-bold text-dark mb-1 font-size-15">{{ $doc->doctor_name }}</h6>
                                <span class="badge bg-light text-primary font-size-11">{{ $doc->department?->name ?? __('Specialist OPD') }}</span>
                            </div>
                        </div>

                        <p class="text-muted font-size-12 mb-2">
                            <i class="bx bx-award text-warning me-1"></i> {{ $doc->degrees }}
                        </p>
                        <p class="text-dark fw-semibold font-size-13 mb-3">
                            {{ $doc->specialty }}
                        </p>

                        <div class="bg-light p-2 rounded-2 mb-3 font-size-12">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">{{ __('Consultation Fee') }}:</span>
                                <strong class="text-success">{{ $doc->formatted_fee }}</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted">{{ __('Available Days') }}:</span>
                                <span class="text-dark">{{ $doc->opd_days }}</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">{{ __('Patient Rating') }}:</span>
                                <span class="text-warning fw-bold">
                                    <i class="mdi mdi-star"></i> {{ $doc->average_rating }} ({{ $doc->testimonials_count }})
                                </span>
                            </div>
                        </div>

                        <div class="mt-auto d-flex gap-2">
                            <a href="{{ route('frontend.doctor_profile', $doc) }}" class="btn btn-primary btn-sm w-100 fw-semibold">
                                <i class="bx bx-user me-1"></i> {{ __('View Profile') }}
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-4 text-muted">
                    <p>{{ __('No active doctor charts available.') }}</p>
                </div>
            @endforelse
        </div>
    </div>
</section>

<!-- Live OPD Token Tracker Widget with Real-Time Search -->
<section class="py-5" id="track-status" style="background: linear-gradient(135deg, #1f2747 0%, #2a3042 100%); color: #ffffff;">
    <div class="container py-3">
        <div class="row align-items-center g-4">
            <div class="col-lg-5">
                <span class="badge bg-success-subtle text-success font-size-12 px-3 py-1 mb-2 fw-bold text-uppercase">
                    <i class="bx bx-radar me-1"></i> {{ __('Real-Time Queue Tracker') }}
                </span>
                <h2 class="fw-bold text-white mb-2">{{ __('Live Token Status Tracker') }}</h2>
                <p class="text-light opacity-90 font-size-14 mb-4">
                    {{ __('Check your live appointment position in queue, assigned chamber room, or prescription status using your token or registered phone number.') }}
                </p>
                <form id="tokenTrackForm" action="{{ route('frontend.home') }}#track-status" method="GET">
                    <div class="input-group mb-3">
                        <input type="text" id="tokenTrackInput" name="track_query" class="form-control" placeholder="{{ __('Enter Token (e.g. 1) or Mobile Phone') }}" value="{{ $trackingQuery }}" autocomplete="off">
                        <button class="btn btn-warning fw-semibold px-4" type="submit">
                            <i class="bx bx-search-alt me-1"></i> {{ __('Track') }}
                        </button>
                    </div>
                    <small class="text-white-50 font-size-11">
                        <i class="bx bx-info-circle me-1"></i> {{ __('Instant real-time search active. Type token # or phone number above.') }}
                    </small>
                </form>
            </div>

            <div class="col-lg-7" id="tokenResultsContainer">
                @if($trackedAppointments->count() > 0)
                    <div class="card border-0 shadow-lg rounded-3 bg-white text-dark">
                        <div class="card-header bg-light py-3 d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 fw-bold text-dark">{{ __('Matching Tokens') }} ({{ $trackedAppointments->count() }})</h6>
                            <span class="badge bg-success"><i class="bx bx-pulse me-1"></i> {{ __('Live Status') }}</span>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0 font-size-13">
                                    <thead class="table-light">
                                        <tr>
                                            <th>{{ __('Token') }}</th>
                                            <th>{{ __('Patient') }}</th>
                                            <th>{{ __('Doctor & Chamber') }}</th>
                                            <th>{{ __('Date & Time') }}</th>
                                            <th>{{ __('Status') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($trackedAppointments as $tApp)
                                            <tr>
                                                <td><strong class="text-primary font-monospace font-size-14">Token #{{ $tApp->token_number }}</strong></td>
                                                <td><strong>{{ $tApp->patient?->name }}</strong></td>
                                                <td>
                                                    <strong>{{ $tApp->doctor?->name }}</strong>
                                                    <small class="text-muted d-block">{{ $tApp->branch?->name }}</small>
                                                </td>
                                                <td>{{ \Carbon\Carbon::parse($tApp->appointment_date)->format('d M') }} at {{ \Carbon\Carbon::parse($tApp->appointment_time)->format('h:i A') }}</td>
                                                <td>
                                                    <span class="badge bg-info-subtle text-info px-2 py-1">
                                                        {{ ucfirst($tApp->status) }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="p-4 rounded-3 text-center" style="background: rgba(255, 255, 255, 0.05); border: 1px dashed rgba(255, 255, 255, 0.2);">
                        <i class="bx bx-search-alt text-warning font-size-36 d-block mb-2"></i>
                        <h6 class="text-white">{{ __('Track Your OPD Appointment') }}</h6>
                        <p class="text-light opacity-75 font-size-13 mb-0">{{ __('Enter your registered mobile phone or token number above to view real-time status.') }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>

<!-- Customer Testimonials Section -->
<section class="py-5 bg-white">
    <div class="container py-3">
        <div class="text-center mb-5">
            <span class="badge bg-success-subtle text-success font-size-12 px-3 py-1 mb-2 fw-bold text-uppercase">
                <i class="bx bx-heart me-1"></i> Verified Patient Reviews
            </span>
            <h2 class="fw-bold text-dark mb-1">{{ __('Customer Testimonials') }}</h2>
            <p class="text-muted font-size-14">Hear directly from patients treated across our multi-super specialty hospital branches</p>
        </div>

        <div class="row g-4">
            @forelse($testimonials as $test)
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 border-0 shadow-sm p-4 rounded-4" style="background: #fdfdfd; border: 1px solid #edf2f7;">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="text-warning font-size-16">
                                {!! $test->star_html !!}
                            </div>
                            <span class="badge bg-success-subtle text-success font-size-11">
                                <i class="bx bx-check-shield me-1"></i> {{ __('Verified Patient') }}
                            </span>
                        </div>
                        <h6 class="fw-bold text-dark mb-2">"{{ $test->headline }}"</h6>
                        <p class="text-muted font-size-13 line-clamp-3 mb-4 flex-grow-1">
                            {{ $test->comment }}
                        </p>
                        <div class="border-top pt-3 mt-auto d-flex align-items-center justify-content-between font-size-12">
                            <div>
                                <strong class="text-dark d-block">{{ $test->patient_name }}</strong>
                                <small class="text-muted">{{ $test->patient_location }}</small>
                            </div>
                            <div class="text-end">
                                <small class="text-primary fw-semibold d-block">{{ $test->doctorChart?->doctor_name }}</small>
                                <small class="text-muted">{{ $test->treatment_received }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-4 text-muted">
                    <p>No patient testimonials published yet.</p>
                </div>
            @endforelse
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const input = document.getElementById('tokenTrackInput');
    const container = document.getElementById('tokenResultsContainer');
    let debounceTimer = null;

    if (input && container) {
        input.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            const val = this.value.trim();
            if (val.length === 0) {
                return;
            }
            debounceTimer = setTimeout(() => {
                fetch(`{{ route('portal.track_token') }}?query=${encodeURIComponent(val)}`, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(r => r.json())
                .then(res => {
                    if (res.success && res.data && res.data.length > 0) {
                        let html = `
                        <div class="card border-0 shadow-lg rounded-3 bg-white text-dark">
                            <div class="card-header bg-light py-3 d-flex justify-content-between align-items-center">
                                <h6 class="mb-0 fw-bold text-dark">{{ __('Matching Tokens') }} (${res.data.length})</h6>
                                <span class="badge bg-success"><i class="bx bx-pulse me-1"></i> {{ __('Live Status') }}</span>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0 font-size-13">
                                        <thead class="table-light">
                                            <tr>
                                                <th>{{ __('Token') }}</th>
                                                <th>{{ __('Patient') }}</th>
                                                <th>{{ __('Doctor & Chamber') }}</th>
                                                <th>{{ __('Date & Time') }}</th>
                                                <th>{{ __('Status') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>`;
                        res.data.forEach(item => {
                            html += `
                                <tr>
                                    <td><strong class="text-primary font-monospace font-size-14">${item.token_display}</strong></td>
                                    <td><strong>${item.patient_name || '-'}</strong></td>
                                    <td>
                                        <strong>${item.doctor_name || '-'}</strong>
                                        <small class="text-muted d-block">${item.branch_name || ''}</small>
                                    </td>
                                    <td>${item.appointment_date} at ${item.appointment_time}</td>
                                    <td>
                                        <span class="badge ${item.status_badge} px-2 py-1">
                                            ${item.status}
                                        </span>
                                    </td>
                                </tr>`;
                        });
                        html += `
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>`;
                        container.innerHTML = html;
                    } else {
                        container.innerHTML = `
                        <div class="p-4 rounded-3 text-center" style="background: rgba(255, 255, 255, 0.05); border: 1px dashed rgba(255, 255, 255, 0.2);">
                            <i class="bx bx-error-circle text-warning font-size-36 d-block mb-2"></i>
                            <h6 class="text-white">{{ __('Track Your OPD Appointment') }}</h6>
                            <p class="text-light opacity-75 font-size-13 mb-0">{{ __('No matching tokens found for this query.') }}</p>
                        </div>`;
                    }
                })
                .catch(err => console.error('Tracking fetch error:', err));
            }, 300);
        });
    }
});
</script>
@endpush
