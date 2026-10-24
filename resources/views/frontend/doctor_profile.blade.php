@extends('frontend.layout')

@section('title', $doctorChart->doctor_name . ' | ' . $doctorChart->specialty)

@section('content')
<!-- Doctor Profile Hero -->
<section class="py-5" style="background: linear-gradient(135deg, #182038 0%, #252d48 50%, #171d33 100%); color: #ffffff;">
    <div class="container py-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb font-size-12 mb-3">
                <li class="breadcrumb-item"><a href="{{ route('frontend.home') }}" class="text-light opacity-75">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('frontend.doctors') }}" class="text-light opacity-75">{{ __('Our Doctors') }}</a></li>
                <li class="breadcrumb-item active text-warning" aria-current="page">{{ $doctorChart->doctor_name }}</li>
            </ol>
        </nav>

        <div class="row align-items-center g-4">
            <div class="col-lg-8">
                <div class="d-flex flex-wrap align-items-center gap-4 mb-3">
                    <div class="avatar-xl rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center fw-bold font-size-36 shadow-lg border border-3 border-white border-opacity-25">
                        {{ substr($doctorChart->doctor_name, 4, 1) }}
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="badge bg-success-subtle text-success font-size-11 px-2 py-1">
                                <i class="bx bx-check-circle me-1"></i> Verified Senior Faculty
                            </span>
                            <span class="badge bg-warning text-dark font-size-11 px-2 py-1">
                                <i class="mdi mdi-star"></i> {{ $doctorChart->average_rating }} / 5.0 Rating
                            </span>
                        </div>
                        <h1 class="display-6 fw-bold text-white mb-1">{{ $doctorChart->doctor_name }}</h1>
                        <p class="text-light opacity-90 font-size-14 mb-1">
                            <i class="bx bx-award text-warning me-1"></i> {{ $doctorChart->degrees }}
                        </p>
                        <p class="text-info fw-semibold font-size-15 mb-0">
                            {{ $doctorChart->specialty }}
                        </p>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-4 pt-3 border-top border-white border-opacity-10 font-size-13 text-light">
                    <div>
                        <i class="bx bx-building text-warning me-1"></i>
                        <strong>Hospital Branch:</strong> {{ $doctorChart->branch?->name }} ({{ $doctorChart->branch?->city }})
                    </div>
                    <div>
                        <i class="bx bx-door-open text-info me-1"></i>
                        <strong>Chamber:</strong> {{ $doctorChart->room_number }}
                    </div>
                    <div>
                        <i class="bx bx-calendar text-success me-1"></i>
                        <strong>OPD Days:</strong> {{ $doctorChart->opd_days }}
                    </div>
                </div>
            </div>

            <!-- Transparent Fees Card -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-lg rounded-4 p-4 text-dark" style="background: rgba(255, 255, 255, 0.98);">
                    <span class="text-muted text-uppercase font-size-11 fw-bold mb-1 d-block">Consultation Fee Schedule</span>
                    <div class="d-flex align-items-baseline gap-2 mb-3">
                        <h2 class="text-success fw-bold mb-0 font-size-32">{{ $doctorChart->formatted_fee }}</h2>
                        <span class="text-muted font-size-12">/ Standard OPD Consultation</span>
                    </div>

                    <div class="bg-light p-3 rounded-3 mb-3 font-size-12">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">{{ __('Follow-up Fee') }}:</span>
                            <strong class="text-dark">{{ $doctorChart->formatted_follow_up_fee }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">{{ __('Emergency Fee') }}:</span>
                            <strong class="text-danger">{{ $doctorChart->formatted_emergency_fee }}</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Chamber Timing:</span>
                            <strong class="text-primary">{{ $doctorChart->formatted_timing }}</strong>
                        </div>
                    </div>

                    <a href="#quick-appointment" class="btn btn-portal-primary w-100 py-2 fw-semibold">
                        <i class="bx bx-calendar-check me-1"></i> {{ __('Quick Appointment') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Main Body: Quick Appointment & Customer Testimonials -->
<section class="py-5 bg-white">
    <div class="container py-3">
        <div class="row g-5">
            <!-- Left Column: Doctor Biography & Instructions -->
            <div class="col-lg-7">
                <!-- Clinical Biography -->
                <div class="mb-5">
                    <h4 class="fw-bold text-dark mb-3">
                        <i class="bx bx-book-content text-primary me-2"></i> Clinical Profile & Expertise
                    </h4>
                    <p class="text-muted font-size-14" style="line-height: 1.8;">
                        {{ $doctorChart->doctor_name }} is a distinguished medical professional serving at {{ $doctorChart->branch?->name ?? 'our flagship hospital' }}. Holding advanced qualifications including {{ $doctorChart->degrees }}, they specialize in evidence-based management of {{ $doctorChart->specialty }}.
                    </p>
                    @if($doctorChart->instructions)
                        <div class="alert alert-info border-0 shadow-sm d-flex align-items-center font-size-13 mt-3">
                            <i class="bx bx-info-circle font-size-22 me-2"></i>
                            <div>
                                <strong>Patient Preparation Instructions:</strong><br>
                                {{ $doctorChart->instructions }}
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Customer Testimonials Section -->
                <div id="testimonials-section">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h4 class="fw-bold text-dark mb-1">
                                <i class="bx bx-comment-detail text-success me-2"></i> {{ __('Customer Testimonials') }} ({{ $doctorChart->testimonials->count() }})
                            </h4>
                            <p class="text-muted font-size-13 mb-0">Direct reviews and ratings from patients treated by {{ $doctorChart->doctor_name }}</p>
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#writeReviewModal">
                            <i class="bx bx-edit me-1"></i> {{ __('Write a Review') }}
                        </button>
                    </div>

                    <!-- Rating Scorecard -->
                    <div class="card border-0 bg-light p-4 rounded-4 mb-4">
                        <div class="row align-items-center text-center text-md-start">
                            <div class="col-md-4 border-end border-md-end pb-3 pb-md-0">
                                <h1 class="display-4 fw-bold text-dark mb-0">{{ $doctorChart->average_rating }}</h1>
                                <div class="text-warning font-size-18 mb-1">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="mdi mdi-star"></i>
                                    @endfor
                                </div>
                                <small class="text-muted">{{ $doctorChart->testimonials->count() }} Verified Patient Reviews</small>
                            </div>
                            <div class="col-md-8 ps-md-4">
                                <div class="font-size-13">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span>Clinical Expertise & Accurate Diagnosis</span>
                                        <strong class="text-success">5.0 / 5.0</strong>
                                    </div>
                                    <div class="d-flex justify-content-between mb-1">
                                        <span>Bedside Manner & Patient Communication</span>
                                        <strong class="text-success">4.9 / 5.0</strong>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span>OPD Chamber Punctuality & Minimal Wait</span>
                                        <strong class="text-success">4.8 / 5.0</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Testimonials List -->
                    <div class="d-flex flex-column gap-3">
                        @forelse($doctorChart->testimonials as $testimonial)
                            <div class="card border-0 shadow-sm p-4 rounded-3" style="border-left: 4px solid var(--portal-primary) !important;">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div>
                                        <strong class="text-dark font-size-14 d-block">{{ $testimonial->patient_name }}</strong>
                                        <small class="text-muted">{{ $testimonial->patient_location }} • {{ $testimonial->created_at->format('d M, Y') }}</small>
                                    </div>
                                    <div class="text-end">
                                        <div class="text-warning font-size-14">
                                            {!! $testimonial->star_html !!}
                                        </div>
                                        <span class="badge bg-success-subtle text-success font-size-10">
                                            <i class="bx bx-check-shield me-1"></i> {{ __('Verified Patient') }}
                                        </span>
                                    </div>
                                </div>
                                <h6 class="fw-bold text-dark mb-1">"{{ $testimonial->headline }}"</h6>
                                <p class="text-muted font-size-13 mb-2">
                                    {{ $testimonial->comment }}
                                </p>
                                @if($testimonial->treatment_received)
                                    <div>
                                        <span class="badge bg-light text-muted font-size-11">
                                            Treatment: {{ $testimonial->treatment_received }}
                                        </span>
                                    </div>
                                @endif
                            </div>
                        @empty
                            <div class="text-center py-4 bg-light rounded-3 text-muted">
                                <i class="bx bx-message-square-dots font-size-36 d-block mb-2"></i>
                                <p class="mb-0">Be the first patient to share a review for {{ $doctorChart->doctor_name }}.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Right Column: Quick Appointment Booking Form -->
            <div class="col-lg-5" id="quick-appointment">
                <div class="card border-0 shadow-lg rounded-4 sticky-top overflow-hidden" style="top: 90px;">
                    <div class="card-header bg-primary text-white py-3 px-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-0 text-white font-size-16 fw-bold">
                                    <i class="bx bx-calendar-star me-1"></i> {{ __('Quick Appointment') }}
                                </h5>
                                <small class="text-white-50 font-size-11">Instant Confirmed Token with {{ $doctorChart->doctor_name }}</small>
                            </div>
                            <span class="badge bg-warning text-dark font-size-11">No Queue</span>
                        </div>
                    </div>
                    <div class="card-body p-4 bg-white">
                        <form method="POST" action="{{ route('portal.book_appointment') }}">
                            @csrf
                            <input type="hidden" name="doctor_id" value="{{ $doctorChart->user_id }}">
                            <input type="hidden" name="branch_id" value="{{ $doctorChart->branch_id }}">
                            <input type="hidden" name="department_id" value="{{ $doctorChart->department_id }}">

                            <div class="p-3 bg-light rounded-3 mb-3 font-size-12">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-muted">Doctor:</span>
                                    <strong class="text-dark">{{ $doctorChart->doctor_name }}</strong>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-muted">Chamber:</span>
                                    <span class="text-dark">{{ $doctorChart->room_number }}</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Consultation Fee:</span>
                                    <strong class="text-success font-size-13">{{ $doctorChart->formatted_fee }}</strong>
                                </div>
                            </div>

                            <div class="mb-2">
                                <label class="form-label font-size-12 fw-semibold mb-1">{{ __('Patient Name') }} *</label>
                                <input type="text" name="patient_name" class="form-control form-control-sm" placeholder="Full name of patient" required>
                            </div>

                            <div class="row g-2 mb-2">
                                <div class="col-md-6">
                                    <label class="form-label font-size-12 fw-semibold mb-1">{{ __('Phone Number') }} *</label>
                                    <input type="tel" name="patient_phone" class="form-control form-control-sm" placeholder="10-digit mobile" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label font-size-12 fw-semibold mb-1">ABHA Health ID</label>
                                    <input type="text" name="patient_abha" class="form-control form-control-sm" placeholder="Optional">
                                </div>
                            </div>

                            <div class="row g-2 mb-2">
                                <div class="col-md-6">
                                    <label class="form-label font-size-12 fw-semibold mb-1">{{ __('Age') }} *</label>
                                    <input type="number" name="patient_age" class="form-control form-control-sm" placeholder="Years" min="1" max="120" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label font-size-12 fw-semibold mb-1">{{ __('Gender') }} *</label>
                                    <select name="patient_gender" class="form-select form-select-sm" required>
                                        <option value="male">{{ __('Male') }}</option>
                                        <option value="female">{{ __('Female') }}</option>
                                        <option value="other">{{ __('Other') }}</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label font-size-12 fw-semibold mb-1">{{ __('Appointment Date') }} *</label>
                                    <input type="date" name="appointment_date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" min="{{ date('Y-m-d') }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label font-size-12 fw-semibold mb-1">{{ __('Select Time Slot') }} *</label>
                                    <select name="appointment_time" class="form-select form-select-sm" required>
                                        <option value="{{ $doctorChart->start_time ?? '09:00:00' }}">{{ $doctorChart->formatted_timing }}</option>
                                        <option value="10:00:00">10:00 AM</option>
                                        <option value="11:30:00">11:30 AM</option>
                                        <option value="14:00:00">02:00 PM</option>
                                        <option value="16:00:00">04:00 PM</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label font-size-12 fw-semibold mb-1">{{ __('Symptoms / Notes') }}</label>
                                <textarea name="symptoms" class="form-control form-control-sm" rows="2" placeholder="Brief description of current symptoms..."></textarea>
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

<!-- Modal: Write a Customer Testimonial -->
<div class="modal fade" id="writeReviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('portal.submit_testimonial') }}">
                @csrf
                <input type="hidden" name="doctor_chart_id" value="{{ $doctorChart->id }}">
                <div class="modal-header">
                    <h5 class="modal-title font-size-16">
                        <i class="bx bx-star text-warning me-1"></i> {{ __('Write a Review') }} for {{ $doctorChart->doctor_name }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3 text-center">
                        <label class="form-label d-block fw-semibold mb-1">{{ __('Select Rating') }} *</label>
                        <select name="rating" class="form-select w-50 mx-auto text-center font-size-14 fw-bold text-warning" required>
                            <option value="5" selected>★★★★★ 5.0 - Excellent Care</option>
                            <option value="4">★★★★☆ 4.0 - Very Good</option>
                            <option value="3">★★★☆☆ 3.0 - Good</option>
                            <option value="2">★★☆☆☆ 2.0 - Average</option>
                            <option value="1">★☆☆☆☆ 1.0 - Poor</option>
                        </select>
                    </div>

                    <div class="mb-2">
                        <label class="form-label font-size-12 fw-semibold mb-1">{{ __('Patient Name') }} *</label>
                        <input type="text" name="patient_name" class="form-control" placeholder="e.g. Anand Singhania" required>
                    </div>

                    <div class="row g-2 mb-2">
                        <div class="col-md-6">
                            <label class="form-label font-size-12 fw-semibold mb-1">{{ __('Your City') }} *</label>
                            <input type="text" name="patient_location" class="form-control" placeholder="e.g. Mumbai, MH" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label font-size-12 fw-semibold mb-1">Treatment / Procedure</label>
                            <input type="text" name="treatment_received" class="form-control" placeholder="e.g. Consultation">
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label font-size-12 fw-semibold mb-1">Review Headline *</label>
                        <input type="text" name="headline" class="form-control" placeholder="e.g. Compassionate and accurate diagnosis" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label font-size-12 fw-semibold mb-1">{{ __('Your Review') }} *</label>
                        <textarea name="comment" class="form-control" rows="4" placeholder="Share your experience regarding the consultation, doctor guidance, and treatment outcome..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bx bx-check me-1"></i> {{ __('Submit Review') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
