@extends('frontend.layout')

@section('title', __('About Us') . ' | ' . $settings['site_name'])

@section('content')
<!-- About Hero -->
<section class="py-5" style="background: linear-gradient(135deg, #182038 0%, #252d48 50%, #171d33 100%); color: #ffffff;">
    <div class="container py-4 text-center">
        <span class="badge bg-primary-subtle text-primary font-size-12 px-3 py-1 mb-2 fw-bold text-uppercase">
            <i class="bx bx-buildings me-1"></i> {{ __('About Our Healthcare Network') }}
        </span>
        <h1 class="display-5 fw-bold text-white mb-3">{{ __('Clinical Excellence') }} With Compassion</h1>
        <p class="lead text-light opacity-90 mx-auto" style="max-width: 750px; font-size: 1.1rem;">
            Pioneering super-specialty healthcare delivery across India with NABH and JCI accredited hospitals, world-class modular operation theaters, and leading clinician-scientists.
        </p>
    </div>
</section>

<!-- Mission, Vision & Accreditations -->
<section class="py-5 bg-white">
    <div class="container py-3">
        <div class="row g-5 align-items-center mb-5">
            <div class="col-lg-6">
                <span class="badge bg-success-subtle text-success font-size-12 px-3 py-1 mb-2 fw-bold">OUR HERITAGE</span>
                <h2 class="fw-bold text-dark mb-3">Redefining Tertiary & Quaternary Healthcare in India</h2>
                <p class="text-muted font-size-14" style="line-height: 1.7;">
                    Founded with the enduring vision of making world-class healthcare accessible and affordable to every citizen, {{ $settings['site_name'] }} has grown into a premier healthcare network encompassing comprehensive multi-specialty hospitals, dedicated cardiac catheterization suites, advanced organ transplant institutes, and round-the-clock emergency trauma facilities.
                </p>
                <p class="text-muted font-size-14" style="line-height: 1.7;">
                    We integrate digital clinical workflows, NABH patient safety protocols, and personalized nursing care to ensure seamless patient journeys from outpatient consultation to post-operative recovery.
                </p>

                <div class="d-flex flex-wrap gap-3 mt-4">
                    <div class="p-3 bg-light rounded-3 d-flex align-items-center gap-3">
                        <i class="bx bx-shield-quarter text-warning font-size-32"></i>
                        <div>
                            <strong class="text-dark d-block">NABH Accredited</strong>
                            <small class="text-muted">National Quality Standards</small>
                        </div>
                    </div>
                    <div class="p-3 bg-light rounded-3 d-flex align-items-center gap-3">
                        <i class="bx bx-check-shield text-success font-size-32"></i>
                        <div>
                            <strong class="text-dark d-block">JCI Certified</strong>
                            <small class="text-muted">International Clinical Benchmark</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="row g-3">
                    <div class="col-6">
                        <div class="card border-0 shadow-sm p-4 text-center rounded-4 h-100 bg-primary-subtle">
                            <i class="bx bx-git-branch text-primary font-size-36 mb-2"></i>
                            <h2 class="fw-bold text-primary mb-1">{{ $branches->count() }}</h2>
                            <span class="fw-semibold text-dark">{{ __('Operational Branches') }}</span>
                            <small class="text-muted d-block mt-1">Mumbai, Bandra, Thane, Pune</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card border-0 shadow-sm p-4 text-center rounded-4 h-100 bg-success-subtle">
                            <i class="bx bx-user-check text-success font-size-36 mb-2"></i>
                            <h2 class="fw-bold text-success mb-1">{{ $doctorsCount }}+</h2>
                            <span class="fw-semibold text-dark">{{ __('Active Doctors') }}</span>
                            <small class="text-muted d-block mt-1">Super-Specialists & Surgeons</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card border-0 shadow-sm p-4 text-center rounded-4 h-100 bg-warning-subtle">
                            <i class="bx bx-bed text-warning font-size-36 mb-2"></i>
                            <h2 class="fw-bold text-warning mb-1">500+</h2>
                            <span class="fw-semibold text-dark">Inpatient Bed Strength</span>
                            <small class="text-muted d-block mt-1">ICU, CCU, NICU & Suites</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card border-0 shadow-sm p-4 text-center rounded-4 h-100 bg-info-subtle">
                            <i class="bx bx-pulse text-info font-size-36 mb-2"></i>
                            <h2 class="fw-bold text-info mb-1">50,000+</h2>
                            <span class="fw-semibold text-dark">{{ __('Satisfied Patients') }}</span>
                            <small class="text-muted d-block mt-1">Annual Consultations & Care</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Infrastructure & Modular OT Highlights -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-5" style="background: linear-gradient(135deg, #1f2747 0%, #2a3042 100%); color: #ffffff;">
            <div class="card-body p-5">
                <div class="row align-items-center g-4">
                    <div class="col-lg-8">
                        <span class="badge bg-danger px-3 py-1 font-size-11 text-uppercase mb-2">Advanced Surgical Architecture</span>
                        <h3 class="fw-bold text-white mb-3">State-of-the-Art Positive Laminar Flow Modular OTs</h3>
                        <p class="text-light opacity-90 font-size-14 mb-4" style="line-height: 1.7;">
                            Every major hospital branch features HEPA-filtered Class 100 modular surgical suites equipped with Siemens C-Arm fluoroscopy, Karl Storz 4K 3D laparoscopy towers, Dräger anesthesia workstations, and integrated surgical medication ledger tracking for complete clinical governance.
                        </p>
                        <div class="d-flex flex-wrap gap-4 font-size-13">
                            <div><i class="bx bx-check-circle text-success me-1"></i> Zero-Infection Protocol</div>
                            <div><i class="bx bx-check-circle text-success me-1"></i> Dedicated Heart-Lung Perfusion</div>
                            <div><i class="bx bx-check-circle text-success me-1"></i> Real-Time Meds Ledger Log</div>
                        </div>
                    </div>
                    <div class="col-lg-4 text-center">
                        <a href="{{ route('frontend.locations') }}" class="btn btn-warning btn-lg fw-bold px-4 py-3 shadow-lg">
                            <i class="bx bx-map-pin me-1"></i> Find Nearest Branch
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Operational Hospital Branches Summary -->
        <div class="text-center mb-4">
            <span class="badge bg-primary-subtle text-primary font-size-12 px-3 py-1 mb-2 fw-bold text-uppercase">NETWORK DIRECTORY</span>
            <h2 class="fw-bold text-dark mb-1">Our Hospital Branches</h2>
            <p class="text-muted font-size-14">Discover centers of excellence delivering dedicated patient-first healthcare</p>
        </div>

        <div class="row g-4">
            @foreach($branches as $b)
                <div class="col-md-6 col-lg-3">
                    <div class="glass-card h-100 p-4 d-flex flex-column">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <span class="badge bg-primary-subtle text-primary font-size-11 px-2 py-1 font-monospace">{{ $b->code }}</span>
                            @if($b->is_main_branch)
                                <span class="badge bg-success font-size-10">Flagship HQ</span>
                            @endif
                        </div>
                        <h6 class="fw-bold text-dark mb-2 font-size-15">{{ $b->name }}</h6>
                        <p class="text-muted font-size-12 mb-3 flex-grow-1">
                            <i class="bx bx-map text-danger me-1"></i> {{ $b->address }}, {{ $b->city }}, {{ $b->state }} - {{ $b->pincode }}
                        </p>
                        <div class="border-top pt-3 font-size-12 text-muted">
                            <div class="mb-1"><i class="bx bx-phone text-primary me-1"></i> {{ $b->phone }}</div>
                            <div><i class="bx bx-envelope text-info me-1"></i> {{ $b->email }}</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endsection
