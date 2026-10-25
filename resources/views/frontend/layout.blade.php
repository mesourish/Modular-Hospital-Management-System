<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8" />
    <title>@yield('title', $settings['site_name'] . ' | Healthcare & Patient Portal')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Hospital Patient Portal, Online OPD Token Booking, Doctor Schedules, Health Checkup Packages, and Hospital Locations" name="description" />
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.ico') }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap & Icons -->
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />

    <style>
        :root {
            --bs-font-sans-serif: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            --portal-primary: #556ee6;
            --portal-primary-dark: #3b50c4;
            --portal-accent: #34c38f;
            --portal-emergency: #f46a6a;
            --portal-warning: #f1b44c;
        }
        body {
            font-family: var(--bs-font-sans-serif);
            background-color: #f8f9fa;
            color: #495057;
            overflow-x: hidden;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        main {
            flex: 1 0 auto;
        }
        .emergency-strip {
            background: linear-gradient(90deg, #1f2747 0%, #2a3042 100%);
            color: #d1d6e8;
            font-size: 0.8rem;
            padding: 8px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }
        .portal-navbar {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(0, 0, 0, 0.06);
            position: sticky;
            top: 0;
            z-index: 1030;
            transition: all 0.3s ease;
        }
        .nav-link {
            font-weight: 600;
            color: #495057;
            padding: 0.5rem 0.9rem !important;
            transition: color 0.2s ease;
            font-size: 0.92rem;
        }
        .nav-link:hover, .nav-link.active {
            color: var(--portal-primary) !important;
        }
        .portal-footer {
            background: #1a223f;
            color: #a6b0cf;
            padding: 60px 0 25px;
            margin-top: auto;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
        }
        .footer-title {
            color: #ffffff;
            font-size: 1.05rem;
            font-weight: 700;
            margin-bottom: 20px;
            position: relative;
        }
        .footer-title::after {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 0;
            width: 35px;
            height: 2px;
            background: var(--portal-primary);
            border-radius: 2px;
        }
        .footer-links li {
            margin-bottom: 10px;
        }
        .footer-links a {
            color: #a6b0cf;
            text-decoration: none;
            transition: color 0.2s ease;
            font-size: 0.88rem;
        }
        .footer-links a:hover {
            color: #ffffff;
            padding-left: 4px;
        }
        .btn-portal-primary {
            background-color: var(--portal-primary);
            color: #ffffff;
            font-weight: 600;
            border: none;
            padding: 9px 20px;
            border-radius: 8px;
            transition: all 0.25s ease;
        }
        .btn-portal-primary:hover {
            background-color: var(--portal-primary-dark);
            color: #ffffff;
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(85, 110, 230, 0.3);
        }
        .glass-card {
            background: #ffffff;
            border-radius: 14px;
            border: 1px solid #e9ecef;
            box-shadow: 0 4px 18px rgba(0, 0, 0, 0.03);
            transition: transform 0.25s ease, box-shadow 0.25s ease;
        }
        .glass-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 28px rgba(0, 0, 0, 0.07);
        }
        .badge-package-category {
            font-size: 0.75rem;
            font-weight: 700;
            padding: 5px 12px;
            border-radius: 50px;
        }
        .lang-dropdown-btn {
            background: rgba(255, 255, 255, 0.12);
            color: #ffffff;
            border: 1px solid rgba(255, 255, 255, 0.2);
            font-size: 0.78rem;
            padding: 3px 10px;
            border-radius: 6px;
        }
        .lang-dropdown-btn:hover {
            background: rgba(255, 255, 255, 0.2);
            color: #ffffff;
        }
    </style>
    @stack('styles')
</head>
<body>

    <!-- 1. Top Emergency Strip & Multi-Language Selector -->
    <div class="emergency-strip">
        <div class="container d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div class="d-flex align-items-center gap-3">
                <span class="badge bg-danger px-2 py-1 font-size-11">
                    <i class="bx bx-plus-medical me-1"></i> {{ __('Emergency 24x7') }}
                </span>
                <a href="tel:{{ preg_replace('/[^0-9]/', '', $settings['emergency_hotline'] ?? '1066') }}" class="text-white text-decoration-none fw-semibold">
                    <i class="bx bx-phone-call text-warning me-1"></i> {{ $settings['emergency_hotline'] ?? '1066' }}
                </a>
                <span class="text-muted d-none d-md-inline">•</span>
                <span class="d-none d-md-inline text-light">
                    <i class="bx bxs-ambulance text-danger me-1"></i> {{ __('Ambulance Services') }}: <strong>{{ $settings['ambulance_phone'] ?? '108' }}</strong>
                </span>
            </div>
            <div class="d-flex align-items-center gap-3">
                <span class="text-light d-none d-lg-inline">
                    <i class="bx bx-time-five text-info me-1"></i> {{ $settings['opd_hours'] ?? 'Mon - Sat: 08:00 AM - 08:00 PM' }}
                </span>
                
                <!-- Language Switcher (Hindi & Marathi Exclusive) -->
                <div class="dropdown">
                    <button class="btn btn-sm lang-dropdown-btn dropdown-toggle d-flex align-items-center gap-1" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bx bx-globe font-size-14 text-warning"></i>
                        <span>
                            @if(app()->getLocale() == 'mr')
                                🚩 मराठी (MR)
                            @else
                                🇮🇳 हिन्दी (HI)
                            @endif
                        </span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 font-size-13 py-1">
                        <li><a class="dropdown-item py-1 {{ app()->getLocale() == 'hi' ? 'active' : '' }}" href="{{ route('portal.set_language', 'hi') }}">🇮🇳 हिन्दी (Hindi)</a></li>
                        <li><a class="dropdown-item py-1 {{ app()->getLocale() == 'mr' ? 'active' : '' }}" href="{{ route('portal.set_language', 'mr') }}">🚩 मराठी (Marathi)</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Announcement Banner (if enabled) -->
    @if(!empty($settings['announcement_enabled']) && !empty($settings['announcement_text']))
        <div class="bg-primary text-white py-2 px-3 text-center font-size-13 fw-medium">
            <div class="container d-flex justify-content-center align-items-center">
                <span>{{ $settings['announcement_text'] }}</span>
            </div>
        </div>
    @endif

    <!-- 3. Primary Header Navigation -->
    <header class="portal-navbar shadow-sm">
        <div class="container">
            <nav class="navbar navbar-expand-lg navbar-light py-2">
                <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('frontend.home') }}">
                    <div class="avatar-xs rounded bg-primary d-flex align-items-center justify-content-center text-white fw-bold shadow-sm">
                        <i class="bx bx-plus-medical font-size-16"></i>
                    </div>
                    <div>
                        <span class="fs-5 fw-bold text-dark d-block lh-1">{{ $settings['site_name'] }}</span>
                        <small class="text-muted font-size-11">{{ $settings['brand_tagline'] }}</small>
                    </div>
                </a>

                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#portalNavbar" aria-controls="portalNavbar" aria-expanded="false">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="portalNavbar">
                    <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('frontend.home') ? 'active' : '' }}" href="{{ route('frontend.home') }}">
                                {{ __('Home') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('frontend.about') ? 'active' : '' }}" href="{{ route('frontend.about') }}">
                                {{ __('About Us') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('frontend.locations') ? 'active' : '' }}" href="{{ route('frontend.locations') }}">
                                <i class="bx bx-map-pin text-primary me-1"></i> {{ __('Find Hospitals') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('frontend.doctors*') ? 'active' : '' }}" href="{{ route('frontend.doctors') }}">
                                <i class="bx bx-user-check text-info me-1"></i> {{ __('Our Doctors') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('frontend.packages*') ? 'active' : '' }}" href="{{ route('frontend.packages') }}">
                                <i class="bx bx-package text-warning me-1"></i> {{ __('Health Packages') }}
                            </a>
                        </li>
                    </ul>

                    <div class="d-flex align-items-center gap-2">
                        <a href="{{ route('frontend.home') }}#track-status" class="btn btn-outline-secondary btn-sm py-2 px-3 fw-medium">
                            <i class="bx bx-search-alt me-1"></i> {{ __('Track Token') }}
                        </a>
                        <a href="{{ route('frontend.home') }}#book-section" class="btn btn-portal-primary btn-sm py-2 px-3">
                            <i class="bx bx-calendar-plus me-1"></i> {{ __('Book Appointment') }}
                        </a>
                        <a href="{{ route('login') }}" class="btn btn-light btn-sm py-2 px-2 ms-1 text-muted" title="{{ __('Login') }}">
                            <i class="bx bx-log-in font-size-16 align-middle"></i>
                        </a>
                    </div>
                </div>
            </nav>
        </div>
    </header>

    <!-- 4. Flash Notifications & Booking Confirmations -->
    <div class="container mt-3">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 d-flex align-items-center" role="alert">
                <i class="bx bx-check-circle font-size-22 text-success me-2"></i>
                <div>
                    <strong>Success!</strong> {{ session('success') }}
                </div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 d-flex align-items-center" role="alert">
                <i class="bx bx-error-circle font-size-22 text-danger me-2"></i>
                <div>
                    <strong>Attention:</strong> {{ session('error') }}
                </div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('booking_confirmation'))
            @php $conf = session('booking_confirmation'); @endphp
            <div class="card border-0 shadow-lg mb-4 bg-primary text-white" id="token-confirmation">
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-md-3 text-center border-end border-white border-opacity-25 pb-3 pb-md-0">
                            <span class="badge bg-white text-primary px-3 py-1 font-size-12 mb-2">{{ __('Token Confirmed') }}</span>
                            <h1 class="display-3 fw-bold mb-0 text-warning">{{ $conf['token_display'] }}</h1>
                            <small class="opacity-75">{{ __('Your Token Number') }}</small>
                        </div>
                        <div class="col-md-6 px-md-4">
                            <h4 class="text-white mb-2">{{ $conf['doctor_name'] }}</h4>
                            <p class="mb-1 text-light opacity-90"><i class="bx bx-award me-1"></i> {{ $conf['degrees'] }} • {{ $conf['specialty'] }}</p>
                            <p class="mb-1 text-light opacity-90"><i class="bx bx-door-open me-1"></i> {{ $conf['room_number'] }} • {{ $conf['branch_name'] }} ({{ $conf['branch_city'] }})</p>
                            <p class="mb-0 text-light opacity-90"><i class="bx bx-calendar me-1"></i> {{ $conf['appointment_date'] }} at <strong>{{ $conf['appointment_time'] }}</strong></p>
                        </div>
                        <div class="col-md-3 text-md-end pt-3 pt-md-0">
                            <div class="p-2 rounded bg-white bg-opacity-10 mb-2">
                                <small class="d-block opacity-75">{{ __('Patient Name') }}</small>
                                <strong>{{ $conf['patient_name'] }}</strong> ({{ $conf['patient_phone'] }})
                            </div>
                            <button onclick="window.print()" class="btn btn-light btn-sm w-100 fw-semibold text-primary">
                                <i class="bx bx-printer me-1"></i> Print OPD Slip
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if(session('package_confirmation'))
            @php $pConf = session('package_confirmation'); @endphp
            <div class="card border-0 shadow-lg mb-4 bg-success text-white">
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-md-4 text-center border-end border-white border-opacity-25 pb-3 pb-md-0">
                            <span class="badge bg-white text-success px-3 py-1 font-size-12 mb-2">Checkup Booked</span>
                            <h3 class="fw-bold mb-0 text-warning">{{ $pConf['booking_number'] }}</h3>
                            <small class="opacity-75">Booking Reference Number</small>
                        </div>
                        <div class="col-md-8 ps-md-4">
                            <h4 class="text-white mb-1">{{ $pConf['package_name'] }}</h4>
                            <p class="mb-1 text-light opacity-90"><i class="bx bx-building me-1"></i> {{ $pConf['branch_name'] }} ({{ $pConf['branch_city'] }})</p>
                            <p class="mb-1 text-light opacity-90"><i class="bx bx-calendar me-1"></i> Scheduled Date: <strong>{{ $pConf['preferred_date'] }}</strong></p>
                            <p class="mb-0 text-light opacity-90">Patient: <strong>{{ $pConf['patient_name'] }}</strong> ({{ $pConf['patient_phone'] }}) • Price: <strong>{{ $pConf['price'] }}</strong></p>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- 5. Main Content Injector -->
    <main>
        @yield('content')
    </main>

    <!-- 6. Footer -->
    <footer class="portal-footer">
        <div class="container">
            <div class="row g-4 mb-5">
                <div class="col-lg-4 col-md-6">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <div class="avatar-xs rounded bg-primary d-flex align-items-center justify-content-center text-white fw-bold shadow-sm">
                            <i class="bx bx-plus-medical font-size-16"></i>
                        </div>
                        <h5 class="text-white mb-0 fw-bold">{{ $settings['site_name'] }}</h5>
                    </div>
                    <p class="font-size-13 text-muted mb-3">
                        {{ $settings['brand_tagline'] }}. Equipped with state-of-the-art modular operation theaters, high-resolution diagnostic imaging, and compassionate clinical teams delivering care with dignity.
                    </p>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-secondary-subtle text-light font-size-11 border border-secondary px-2 py-1">
                            <i class="bx bx-shield-quarter text-warning me-1"></i> NABH Accredited
                        </span>
                        <span class="badge bg-secondary-subtle text-light font-size-11 border border-secondary px-2 py-1">
                            <i class="bx bx-check-shield text-success me-1"></i> JCI Certified Network
                        </span>
                    </div>
                </div>

                <div class="col-lg-2 col-md-6">
                    <h6 class="footer-title">{{ __('Hospital Network') }}</h6>
                    <ul class="list-unstyled footer-links font-size-13">
                        <li><a href="{{ route('frontend.home') }}">{{ __('Home') }}</a></li>
                        <li><a href="{{ route('frontend.about') }}">{{ __('About Us') }}</a></li>
                        <li><a href="{{ route('frontend.locations') }}">{{ __('Find Hospitals') }}</a></li>
                        <li><a href="{{ route('frontend.doctors') }}">{{ __('Our Doctors') }}</a></li>
                        <li><a href="{{ route('frontend.packages') }}">{{ __('Health Packages') }}</a></li>
                    </ul>
                </div>

                <div class="col-lg-3 col-md-6">
                    <h6 class="footer-title">{{ __('Clinical Excellence') }}</h6>
                    <ul class="list-unstyled footer-links font-size-13">
                        <li><a href="{{ route('frontend.doctors') }}?department_id=1">Cardiology & Cath Lab</a></li>
                        <li><a href="{{ route('frontend.doctors') }}?department_id=2">Neurology & Stroke Care</a></li>
                        <li><a href="{{ route('frontend.doctors') }}?department_id=3">Emergency & Acute Trauma</a></li>
                        <li><a href="{{ route('frontend.doctors') }}?department_id=4">Orthopedics & Joint Replacement</a></li>
                        <li><a href="{{ route('frontend.doctors') }}?department_id=5">Advanced CT/MRI Diagnostics</a></li>
                    </ul>
                </div>

                <div class="col-lg-3 col-md-6">
                    <h6 class="footer-title">{{ __('Emergency Helpline') }}</h6>
                    <div class="mb-3">
                        <small class="text-muted d-block font-size-11">{{ __('Emergency 24x7') }}</small>
                        <h4 class="text-danger fw-bold mb-0">
                            <i class="bx bx-phone-call me-1 font-size-20"></i> {{ $settings['emergency_hotline'] ?? '1066' }}
                        </h4>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block font-size-11">{{ __('Ambulance Services') }}</small>
                        <h5 class="text-warning fw-semibold mb-0">
                            <i class="bx bxs-ambulance me-1 font-size-18"></i> {{ $settings['ambulance_phone'] ?? '108' }}
                        </h5>
                    </div>
                    <div>
                        <small class="text-muted d-block font-size-11">Patient Care Email</small>
                        <a href="mailto:{{ $settings['support_email'] ?? 'care@apollocare.in' }}" class="text-light font-size-13">
                            {{ $settings['support_email'] ?? 'care@apollocare.in' }}
                        </a>
                    </div>
                </div>
            </div>

            <div class="border-top border-white border-opacity-10 pt-4 d-flex flex-wrap justify-content-between align-items-center font-size-12 text-muted">
                <p class="mb-0">
                    &copy; {{ date('Y') }} {{ $settings['site_name'] }}. All Rights Reserved. Indian Healthcare Standard & NABH Compliant.
                </p>
                <div class="d-flex gap-3">
                    <a href="{{ route('login') }}" class="text-muted text-decoration-none hover-white">Staff Login</a>
                    <a href="{{ route('frontend.locations') }}" class="text-muted text-decoration-none hover-white">Hospital Directory</a>
                    <a href="{{ route('frontend.packages') }}" class="text-muted text-decoration-none hover-white">Preventive Checkups</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="{{ asset('assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    @stack('scripts')
</body>
</html>
