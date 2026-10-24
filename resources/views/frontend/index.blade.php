<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>{{ $settings['site_name'] }} | Patient Portal & Online OPD Booking</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Hospital Patient Portal, Online OPD Token Booking, Doctor Schedules and Queue Tracker" name="description" />
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
        }
        body {
            font-family: var(--bs-font-sans-serif);
            background-color: #f8f9fa;
            color: #495057;
            overflow-x: hidden;
        }
        .portal-navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(0, 0, 0, 0.08);
            position: sticky;
            top: 0;
            z-index: 1030;
            transition: all 0.3s ease;
        }
        .portal-hero {
            background: linear-gradient(135deg, #1f2747 0%, #2a3042 50%, #1a223f 100%);
            color: #ffffff;
            padding: 80px 0 100px;
            position: relative;
            overflow: hidden;
        }
        .portal-hero::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(85, 110, 230, 0.25) 0%, rgba(85, 110, 230, 0) 70%);
            border-radius: 50%;
            pointer-events: none;
        }
        .hero-badge {
            background: rgba(85, 110, 230, 0.2);
            border: 1px solid rgba(85, 110, 230, 0.4);
            color: #a6b0cf;
            font-size: 0.85rem;
            padding: 6px 14px;
            border-radius: 50px;
            display: inline-flex;
            align-items: center;
        }
        .stat-card-glass {
            background: rgba(255, 255, 255, 0.07);
            border: 1px solid rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(12px);
            border-radius: 12px;
            padding: 20px;
            transition: transform 0.2s ease, border-color 0.2s ease;
        }
        .stat-card-glass:hover {
            transform: translateY(-3px);
            border-color: rgba(255, 255, 255, 0.3);
        }
        .section-title-tag {
            color: var(--portal-primary);
            text-transform: uppercase;
            font-size: 0.8rem;
            font-weight: 700;
            letter-spacing: 1px;
            display: inline-block;
            margin-bottom: 8px;
        }
        .doctor-card {
            border: 1px solid #e9ecef;
            border-radius: 14px;
            transition: all 0.25s ease;
            background: #ffffff;
        }
        .doctor-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.07);
            border-color: #d0d7de;
        }
        .token-receipt-card {
            background: linear-gradient(135deg, #ffffff 0%, #f4f7ff 100%);
            border: 2px dashed #556ee6;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(85, 110, 230, 0.12);
        }
        .emergency-strip {
            background: linear-gradient(90deg, #f46a6a 0%, #e83e8c 100%);
            color: #fff;
            padding: 10px 0;
            font-size: 0.9rem;
            font-weight: 600;
        }
        .branch-pill.active {
            background-color: var(--portal-primary) !important;
            color: #fff !important;
            box-shadow: 0 4px 12px rgba(85, 110, 230, 0.35);
        }
        @media print {
            .portal-navbar, .portal-hero, .btn, .no-print, footer, .emergency-strip {
                display: none !important;
            }
            .token-receipt-card {
                border: 2px solid #000 !important;
                box-shadow: none !important;
                margin: 0 !important;
                padding: 20px !important;
            }
        }
    </style>
</head>
<body>

    <!-- Emergency Hotline Ribbon -->
    <div class="emergency-strip">
        <div class="container d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <i class="bx bx-plus-medical me-1"></i>
                <strong>24x7 Emergency Trauma & Ambulance:</strong>
                <a href="tel:{{ preg_replace('/[^0-9]/', '', $settings['emergency_hotline']) }}" class="text-white text-decoration-underline ms-1">
                    {{ $settings['emergency_hotline'] }}
                </a>
                <span class="mx-2">•</span>
                <i class="bx bxs-ambulance me-1"></i>
                <a href="tel:{{ preg_replace('/[^0-9]/', '', $settings['ambulance_phone']) }}" class="text-white text-decoration-underline">
                    Ambulance {{ $settings['ambulance_phone'] }}
                </a>
            </div>
            <div class="d-none d-md-flex align-items-center gap-3">
                <span><i class="bx bxl-whatsapp me-1 font-size-16"></i> WhatsApp: {{ $settings['whatsapp_number'] }}</span>
                <span><i class="bx bx-time-five me-1"></i> {{ $settings['opd_hours'] }}</span>
            </div>
        </div>
    </div>

    <!-- Announcement Alert Bar (Customizable by Super Admin & Branch Head) -->
    @if($settings['announcement_enabled'] && !empty($settings['announcement_text']))
        <div class="alert alert-warning mb-0 border-0 rounded-0 py-2 alert-dismissible fade show text-center" role="alert">
            <div class="container font-size-13 fw-semibold">
                <i class="bx bx-bell bx-tada me-1 text-danger font-size-16 align-middle"></i>
                {{ $settings['announcement_text'] }}
                <button type="button" class="btn-close py-2" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    @endif

    <!-- Main Navigation Header -->
    <nav class="navbar navbar-expand-lg portal-navbar py-3">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="{{ route('frontend.home') }}">
                <div class="avatar-xs me-2">
                    <span class="avatar-title bg-primary text-white rounded-circle font-size-16">
                        <i class="bx bx-plus-medical"></i>
                    </span>
                </div>
                <div>
                    <span class="fw-bold font-size-18 text-primary">{{ $settings['site_name'] }}</span>
                    <small class="d-block text-muted font-size-11">{{ $settings['brand_tagline'] }}</small>
                </div>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#portalNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="portalNav">
                <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link fw-semibold px-3" href="#doctors"><i class="bx bx-user-pin me-1"></i> Doctors & OPD</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-semibold px-3" href="#book-appointment"><i class="bx bx-calendar-plus me-1"></i> Book Token</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-semibold px-3" href="#track-status"><i class="bx bx-search-alt me-1"></i> Track Token</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-semibold px-3" href="#branches"><i class="bx bx-buildings me-1"></i> Branches</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-semibold px-3" href="#specialties"><i class="bx bx-pulse me-1"></i> Specialties</a>
                    </li>
                </ul>

                <div class="d-flex align-items-center gap-2">
                    @auth
                        <a href="{{ route('hospital.dashboard') }}" class="btn btn-outline-primary btn-sm px-3">
                            <i class="bx bx-layout me-1"></i> Staff Workspace
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-primary btn-sm px-3">
                            <i class="bx bx-lock-alt me-1"></i> Doctor & Staff Login
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Branch Custom Notice Banner (if selected or specific branch context) -->
    @if(!empty($settings['branch_notice']))
        <div class="bg-primary-subtle text-primary py-2 border-bottom">
            <div class="container font-size-13 text-center fw-medium">
                <i class="bx bx-info-circle me-1"></i>
                <strong>Notice:</strong> {{ $settings['branch_notice'] }}
            </div>
        </div>
    @endif

    <!-- Hero Section -->
    <header class="portal-hero">
        <div class="container position-relative">
            <div class="row align-items-center g-5">
                <div class="col-lg-7">
                    <div class="hero-badge mb-3">
                        <i class="bx bx-check-shield text-success me-2 font-size-16"></i>
                        <span>NABH & JCI Accredited • Ayushman Bharat (ABHA) Compliant</span>
                    </div>
                    <h1 class="display-5 fw-extrabold text-white mb-3 lh-base">
                        {{ $settings['hero_title'] }}
                    </h1>
                    <p class="lead text-white-50 mb-4 font-size-16 lh-lg">
                        {{ $settings['hero_subtitle'] }}
                    </p>
                    <div class="d-flex flex-wrap gap-3 mb-4">
                        <a href="#book-appointment" class="btn btn-success btn-lg px-4 py-2 font-size-15 fw-bold shadow-sm">
                            <i class="bx bx-calendar-plus me-1 font-size-18 align-middle"></i> Book Confirmed Token
                        </a>
                        <a href="#track-status" class="btn btn-outline-light btn-lg px-4 py-2 font-size-15 fw-bold">
                            <i class="bx bx-search-alt me-1 font-size-18 align-middle"></i> Live Token Queue
                        </a>
                        <a href="tel:{{ preg_replace('/[^0-9]/', '', $settings['emergency_hotline']) }}" class="btn btn-danger btn-lg px-3 py-2 font-size-15">
                            <i class="bx bx-phone-call me-1"></i> 1066
                        </a>
                    </div>
                    <div class="text-white-50 font-size-13">
                        <i class="bx bx-map text-primary me-1"></i> Main Campus: {{ $settings['hospital_address'] }}
                    </div>
                </div>

                <div class="col-lg-5">
                    <!-- Live Operational Metric Badges -->
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="stat-card-glass text-center">
                                <i class="bx bx-buildings text-warning font-size-28 mb-1"></i>
                                <h3 class="text-white fw-bold mb-0">{{ $metrics['total_branches'] }}</h3>
                                <small class="text-white-50 font-size-12">Multi-Specialty Branches</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-card-glass text-center">
                                <i class="bx bx-user-pin text-info font-size-28 mb-1"></i>
                                <h3 class="text-white fw-bold mb-0">{{ $metrics['total_doctors'] }}+</h3>
                                <small class="text-white-50 font-size-12">Specialist Physicians</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-card-glass text-center">
                                <i class="bx bx-pulse text-danger font-size-28 mb-1"></i>
                                <h3 class="text-white fw-bold mb-0">{{ $metrics['total_specialties'] }}</h3>
                                <small class="text-white-50 font-size-12">Clinical Departments</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="stat-card-glass text-center">
                                <i class="bx bx-check-circle text-success font-size-28 mb-1"></i>
                                <h3 class="text-white fw-bold mb-0">{{ $metrics['appointments_today'] }}</h3>
                                <small class="text-white-50 font-size-12">OPD Tokens Today</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="container my-5">

        <!-- Booking Confirmation Slip (If Just Booked) -->
        @if(session('booking_confirmation'))
            @php $conf = session('booking_confirmation'); @endphp
            <div id="token-confirmation" class="mb-5">
                <div class="token-receipt-card p-4 p-md-5">
                    <div class="row align-items-center g-4">
                        <div class="col-md-3 text-center border-end-md">
                            <div class="badge bg-success-subtle text-success px-3 py-2 rounded-pill font-size-13 mb-2">
                                <i class="bx bx-check-double me-1"></i> TOKEN CONFIRMED
                            </div>
                            <h1 class="display-4 fw-extrabold text-primary mb-0">{{ $conf['token_display'] }}</h1>
                            <small class="text-muted text-uppercase fw-semibold font-size-11">Digital OPD Token #</small>
                        </div>
                        <div class="col-md-6">
                            <h4 class="fw-bold text-dark mb-1">{{ $conf['patient_name'] }}</h4>
                            <p class="text-muted font-size-13 mb-2">
                                <i class="bx bx-phone me-1"></i> {{ $conf['patient_phone'] }} • 
                                <i class="bx bx-map-pin me-1"></i> {{ $conf['branch_name'] }} ({{ $conf['branch_city'] }})
                            </p>
                            <div class="d-flex flex-wrap gap-2 mb-2 font-size-13">
                                <span class="badge bg-light text-dark border p-2">
                                    <i class="bx bx-user-pin text-primary me-1"></i> <strong>Doctor:</strong> {{ $conf['doctor_name'] }}
                                </span>
                                <span class="badge bg-light text-dark border p-2">
                                    <i class="bx bx-door-open text-info me-1"></i> <strong>Chamber:</strong> {{ $conf['room_number'] }}
                                </span>
                                <span class="badge bg-light text-dark border p-2">
                                    <i class="bx bx-time-five text-warning me-1"></i> <strong>Slot:</strong> {{ $conf['appointment_date'] }} @ {{ $conf['appointment_time'] }}
                                </span>
                            </div>
                            <small class="text-muted">
                                Please arrive 15 minutes before your time slot. Present this digital token at the reception desk.
                            </small>
                        </div>
                        <div class="col-md-3 text-md-end text-center">
                            @if($settings['show_doctor_fees'])
                                <div class="mb-2">
                                    <small class="text-muted d-block font-size-11">Consultation Fee</small>
                                    <h4 class="text-success fw-bold mb-0">{{ $conf['currency'] }}{{ number_format($conf['consultation_fee'], 2) }}</h4>
                                </div>
                            @endif
                            <button type="button" class="btn btn-outline-primary btn-sm px-3" onclick="window.print()">
                                <i class="bx bx-printer me-1"></i> Print Token Slip
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Quick Branch Selector Strip -->
        <div class="card shadow-sm border-0 mb-5">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="fw-bold font-size-13 text-muted text-uppercase">
                            <i class="bx bx-map-pin text-danger"></i> Select Hospital Branch:
                        </span>
                        <div class="btn-group" role="group">
                            <a href="{{ route('frontend.home') }}" class="btn btn-sm btn-outline-secondary {{ empty($selectedBranchId) || $selectedBranchId === 'all' ? 'active' : '' }}">
                                All Branches ({{ $branches->count() }})
                            </a>
                            @foreach($branches as $b)
                                <a href="{{ route('frontend.home', ['branch_id' => $b->id]) }}" class="btn btn-sm btn-outline-secondary {{ $selectedBranchId == $b->id ? 'active' : '' }}">
                                    {{ $b->city }} ({{ $b->code }})
                                </a>
                            @endforeach
                        </div>
                    </div>
                    @if($branch)
                        <span class="badge bg-info-subtle text-info font-size-12 px-3 py-2">
                            Viewing: <strong>{{ $branch->name }}</strong>
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Section: Online Appointment & Token Booking Wizard -->
        <div id="book-appointment" class="mb-5 pt-3">
            <div class="row g-4">
                <div class="col-lg-7">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-white border-bottom py-3">
                            <span class="section-title-tag">Fast & Paperless</span>
                            <h4 class="card-title mb-0 fw-bold text-dark">
                                <i class="bx bx-calendar-plus text-primary me-1"></i> Instant OPD Token Booking
                            </h4>
                            <small class="text-muted">Book your guaranteed consultation token with specialist doctors. Instant digital confirmation.</small>
                        </div>
                        <div class="card-body p-4">
                            @if(!$settings['allow_online_booking'])
                                <div class="alert alert-warning">
                                    <i class="bx bx-info-circle me-1"></i> Online OPD token booking is currently paused. Please call our 24x7 desk at <strong>{{ $settings['emergency_hotline'] }}</strong>.
                                </div>
                            @else
                                <form action="{{ route('portal.book_appointment') }}" method="POST">
                                    @csrf
                                    <div class="row g-3">
                                        <!-- Step 1: Branch & Doctor -->
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold font-size-13">Hospital Branch <span class="text-danger">*</span></label>
                                            <select name="branch_id" id="book_branch_id" class="form-select" required onchange="filterDoctorsByBranch()">
                                                @foreach($branches as $b)
                                                    <option value="{{ $b->id }}" {{ $selectedBranchId == $b->id ? 'selected' : '' }}>
                                                        {{ $b->name }} ({{ $b->city }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-bold font-size-13">Consulting Doctor <span class="text-danger">*</span></label>
                                            <select name="doctor_id" id="book_doctor_id" class="form-select" required>
                                                <option value="">-- Choose Specialist Doctor --</option>
                                                @foreach($doctorCharts as $dc)
                                                    @if($dc->doctorUser)
                                                        <option value="{{ $dc->user_id }}" data-branch="{{ $dc->branch_id }}" data-fee="{{ $dc->consultation_fee }}">
                                                            {{ $dc->doctor_name }} • {{ $dc->specialty }} ({{ $dc->formatted_fee }})
                                                        </option>
                                                    @endif
                                                @endforeach
                                            </select>
                                        </div>

                                        <!-- Step 2: Date & Slot -->
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold font-size-13">Appointment Date <span class="text-danger">*</span></label>
                                            <input type="date" name="appointment_date" class="form-control" value="{{ now()->toDateString() }}" min="{{ now()->toDateString() }}" required>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-bold font-size-13">Preferred Time Slot <span class="text-danger">*</span></label>
                                            <select name="appointment_time" class="form-select" required>
                                                <option value="09:00:00">09:00 AM - Morning Slot</option>
                                                <option value="10:00:00">10:00 AM - Morning Slot</option>
                                                <option value="11:00:00">11:00 AM - Mid-Day Slot</option>
                                                <option value="12:00:00">12:00 PM - Mid-Day Slot</option>
                                                <option value="14:00:00">02:00 PM - Afternoon Slot</option>
                                                <option value="15:00:00">03:00 PM - Afternoon Slot</option>
                                                <option value="16:00:00">04:00 PM - Evening Slot</option>
                                                <option value="17:00:00">05:00 PM - Evening Slot</option>
                                            </select>
                                        </div>

                                        <!-- Step 3: Patient Demographics -->
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold font-size-13">Patient Full Name <span class="text-danger">*</span></label>
                                            <input type="text" name="patient_name" class="form-control" placeholder="e.g. Rahul Verma" required>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-bold font-size-13">Mobile Number (WhatsApp) <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light">+91</span>
                                                <input type="tel" name="patient_phone" class="form-control" placeholder="9876543210" pattern="[0-9]{10}" maxlength="10" required>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label fw-bold font-size-13">Gender <span class="text-danger">*</span></label>
                                            <select name="patient_gender" class="form-select" required>
                                                <option value="male">Male</option>
                                                <option value="female">Female</option>
                                                <option value="other">Other</option>
                                            </select>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label fw-bold font-size-13">Age (Years) <span class="text-danger">*</span></label>
                                            <input type="number" name="patient_age" class="form-control" placeholder="e.g. 42" min="1" max="120" required>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label fw-bold font-size-13">ABHA Health ID (Optional)</label>
                                            <input type="text" name="patient_abha" class="form-control" placeholder="14-digit ABHA">
                                        </div>

                                        <div class="col-12">
                                            <label class="form-label fw-bold font-size-13">Chief Complaints / Clinical Symptoms</label>
                                            <textarea name="symptoms" class="form-control" rows="2" placeholder="Describe symptoms (e.g., chest discomfort, high fever, joint pain, routine checkup)"></textarea>
                                        </div>

                                        <div class="col-12 mt-4">
                                            <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold shadow-sm">
                                                <i class="bx bx-check-circle me-1 font-size-18 align-middle"></i> Generate Confirmed OPD Token
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Section: Live Token Tracker Search Box -->
                <div class="col-lg-5" id="track-status">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-white border-bottom py-3">
                            <span class="section-title-tag text-success">Queue Transparency</span>
                            <h4 class="card-title mb-0 fw-bold text-dark">
                                <i class="bx bx-search-alt text-success me-1"></i> Live Token Status Tracker
                            </h4>
                            <small class="text-muted">Check live waiting status of your OPD consultation token.</small>
                        </div>
                        <div class="card-body p-4">
                            <form action="{{ route('frontend.home') }}" method="GET" class="mb-4">
                                <label class="form-label fw-bold font-size-13">Search by Mobile Number or Token #</label>
                                <div class="input-group">
                                    <input type="text" name="track_query" class="form-control" placeholder="e.g. 9820123456 or Token 1" value="{{ $trackingQuery }}" required>
                                    <button type="submit" class="btn btn-success">
                                        <i class="bx bx-search"></i> Track
                                    </button>
                                </div>
                            </form>

                            @if($trackingQuery !== '')
                                @if($trackedAppointments->count() > 0)
                                    <h6 class="text-dark fw-bold mb-3">Matching Tokens ({{ $trackedAppointments->count() }})</h6>
                                    @foreach($trackedAppointments as $tApp)
                                        <div class="border rounded-3 p-3 mb-3 bg-light">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span class="badge bg-primary font-size-12 px-2 py-1">
                                                    Token #{{ $tApp->token_number }}
                                                </span>
                                                @if($tApp->status === 'completed')
                                                    <span class="badge bg-success">Completed</span>
                                                @elseif($tApp->status === 'in_progress')
                                                    <span class="badge bg-warning text-dark">In Chamber</span>
                                                @else
                                                    <span class="badge bg-info">Scheduled / Waiting</span>
                                                @endif
                                            </div>
                                            <h6 class="mb-1 text-dark fw-bold">{{ $tApp->patient->name ?? 'Patient' }}</h6>
                                            <div class="font-size-12 text-muted mb-1">
                                                <i class="bx bx-user-pin text-primary me-1"></i> {{ $tApp->doctor->name ?? 'Specialist' }}
                                                • {{ $tApp->branch->city ?? '' }}
                                            </div>
                                            <div class="font-size-12 text-muted">
                                                <i class="bx bx-time text-warning me-1"></i> {{ \Carbon\Carbon::parse($tApp->appointment_date)->format('d M, Y') }} at {{ \Carbon\Carbon::parse($tApp->appointment_time)->format('h:i A') }}
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="text-center py-4 text-muted">
                                        <i class="bx bx-error-circle font-size-36 text-warning mb-2 d-block"></i>
                                        <h6>No active tokens found for "{{ $trackingQuery }}"</h6>
                                        <p class="font-size-12 mb-0">Please check your mobile number or book a new token above.</p>
                                    </div>
                                @endif
                            @else
                                <div class="bg-light p-3 rounded-3 text-center text-muted font-size-13">
                                    <i class="bx bx-info-circle text-primary me-1"></i>
                                    Enter your 10-digit registered mobile number to see your token position in the queue.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section: Doctors OPD Duty Charts & Tariffs -->
        <div id="doctors" class="mb-5 pt-3">
            <div class="d-flex justify-content-between align-items-end flex-wrap gap-2 mb-4">
                <div>
                    <span class="section-title-tag">Clinical Specialists</span>
                    <h3 class="fw-bold text-dark mb-0">Doctor OPD Duty Schedules & Fees</h3>
                    <small class="text-muted">Official duty charts, degrees, chamber rooms, and consultation fees.</small>
                </div>
                <div class="text-muted font-size-13">
                    Showing <strong>{{ $doctorCharts->count() }}</strong> credentialed consultants
                </div>
            </div>

            <div class="row g-4">
                @forelse($doctorCharts as $chart)
                    <div class="col-lg-4 col-md-6">
                        <div class="doctor-card p-4 h-100 d-flex flex-column justify-content-between">
                            <div>
                                <div class="d-flex align-items-center gap-3 mb-3">
                                    <div class="avatar-md">
                                        <span class="avatar-title bg-primary-subtle text-primary rounded-circle font-size-22 fw-bold">
                                            {{ substr($chart->doctor_name, 3, 2) }}
                                        </span>
                                    </div>
                                    <div>
                                        <h5 class="fw-bold text-dark mb-0 font-size-15">{{ $chart->doctor_name }}</h5>
                                        <span class="badge bg-soft-info text-info font-size-11">{{ $chart->specialty }}</span>
                                    </div>
                                </div>

                                <p class="font-size-12 text-muted mb-2">
                                    <i class="bx bx-certification text-warning me-1"></i>
                                    <strong>Degrees:</strong> {{ $chart->degrees }}
                                </p>

                                <div class="bg-light p-2 rounded-2 font-size-12 text-muted mb-3">
                                    <div><i class="bx bx-buildings text-primary me-1"></i> <strong>Branch:</strong> {{ $chart->branch->name ?? 'Main Hospital' }}</div>
                                    <div><i class="bx bx-door-open text-info me-1"></i> <strong>Chamber:</strong> {{ $chart->room_number }}</div>
                                    <div><i class="bx bx-time-five text-success me-1"></i> <strong>Days:</strong> {{ $chart->opd_days }} ({{ $chart->formatted_timing }})</div>
                                </div>
                            </div>

                            <div class="pt-3 border-top d-flex justify-content-between align-items-center">
                                @if($settings['show_doctor_fees'])
                                    <div>
                                        <small class="text-muted d-block font-size-11 text-uppercase">OPD Fee</small>
                                        <h5 class="text-success fw-bold mb-0">{{ $chart->formatted_fee }}</h5>
                                    </div>
                                @endif
                                <a href="#book-appointment" class="btn btn-sm btn-outline-primary" onclick="selectDoctorInBooking('{{ $chart->user_id }}', '{{ $chart->branch_id }}')">
                                    <i class="bx bx-calendar-check me-1"></i> Book Token
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center py-5">
                        <p class="text-muted">No doctors found for this branch selection.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Section: Multi-Branch Hospital Network -->
        <div id="branches" class="mb-5 pt-3">
            <div class="mb-4">
                <span class="section-title-tag">Healthcare Network</span>
                <h3 class="fw-bold text-dark mb-0">Our Hospital Branches & Diagnostics</h3>
                <small class="text-muted">Modern medical facilities conveniently located across the metropolitan region.</small>
            </div>

            <div class="row g-4">
                @foreach($branches as $br)
                    <div class="col-lg-3 col-md-6">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-body p-4">
                                <div class="badge bg-primary-subtle text-primary mb-2 font-size-11">
                                    {{ $br->code }} {{ $br->is_main_branch ? '• Main Flagship' : '' }}
                                </div>
                                <h5 class="fw-bold text-dark mb-2 font-size-15">{{ $br->name }}</h5>
                                <p class="text-muted font-size-13 mb-3">
                                    <i class="bx bx-map-pin text-danger me-1"></i> {{ $br->address }}, {{ $br->city }} - {{ $br->pincode }}
                                </p>
                                <div class="font-size-12 text-muted border-top pt-2">
                                    <div><i class="bx bx-phone text-success me-1"></i> {{ $br->phone }}</div>
                                    <div><i class="bx bx-user text-primary me-1"></i> Head: {{ $br->manager_name ?? 'Branch Superintendent' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Section: Clinical Specialties -->
        <div id="specialties" class="mb-5 pt-3">
            <div class="mb-4">
                <span class="section-title-tag">Comprehensive Care</span>
                <h3 class="fw-bold text-dark mb-0">Clinical Specialties & Centers of Excellence</h3>
            </div>

            <div class="row g-3">
                <div class="col-md-4">
                    <div class="p-3 border rounded-3 bg-white d-flex align-items-center gap-3">
                        <span class="avatar-title bg-danger-subtle text-danger rounded-3 font-size-24 p-3"><i class="bx bx-heart"></i></span>
                        <div>
                            <h6 class="mb-0 fw-bold">Cardiology & CCU</h6>
                            <small class="text-muted font-size-12">Angioplasty, 2D-Echo, Cath Lab, Heart Failure</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 border rounded-3 bg-white d-flex align-items-center gap-3">
                        <span class="avatar-title bg-info-subtle text-info rounded-3 font-size-24 p-3"><i class="bx bx-scan"></i></span>
                        <div>
                            <h6 class="mb-0 fw-bold">Radiology & PACS</h6>
                            <small class="text-muted font-size-12">3.0T MRI, 128-Slice CT, Digital Ultrasound</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 border rounded-3 bg-white d-flex align-items-center gap-3">
                        <span class="avatar-title bg-warning-subtle text-warning rounded-3 font-size-24 p-3"><i class="bx bx-droplet"></i></span>
                        <div>
                            <h6 class="mb-0 fw-bold">Blood Bank & Apheresis</h6>
                            <small class="text-muted font-size-12">PRBC, FFP, Platelets, 24x7 Cross-Matching</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 border rounded-3 bg-white d-flex align-items-center gap-3">
                        <span class="avatar-title bg-success-subtle text-success rounded-3 font-size-24 p-3"><i class="bx bx-capsule"></i></span>
                        <div>
                            <h6 class="mb-0 fw-bold">Central Pharmacy POS</h6>
                            <small class="text-muted font-size-12">100% Genuine Branded Drugs, 24x7 Dispense</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 border rounded-3 bg-white d-flex align-items-center gap-3">
                        <span class="avatar-title bg-primary-subtle text-primary rounded-3 font-size-24 p-3"><i class="bx bx-bed"></i></span>
                        <div>
                            <h6 class="mb-0 fw-bold">Inpatient & Critical Care</h6>
                            <small class="text-muted font-size-12">ICU, CCU, Executive Deluxe Wards</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 border rounded-3 bg-white d-flex align-items-center gap-3">
                        <span class="avatar-title bg-secondary-subtle text-secondary rounded-3 font-size-24 p-3"><i class="bx bx-timer"></i></span>
                        <div>
                            <h6 class="mb-0 fw-bold">Emergency & Trauma</h6>
                            <small class="text-muted font-size-12">Level-1 Trauma, Resuscitation & Critical ER</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white-50 py-5 border-top border-secondary">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4">
                    <h5 class="text-white fw-bold mb-3">
                        <i class="bx bx-plus-medical text-primary me-1"></i> {{ $settings['site_name'] }}
                    </h5>
                    <p class="font-size-13 text-white-50 mb-3">
                        {{ $settings['brand_tagline'] }}. Equipped with state-of-the-art medical technology, compassionate nursing care, and senior specialist consultants.
                    </p>
                    <div class="badge bg-success-subtle text-success p-2 font-size-12">
                        <i class="bx bx-check-shield me-1"></i> NABH & JCI Accredited Hospital Network
                    </div>
                </div>

                <div class="col-lg-3">
                    <h6 class="text-white fw-bold mb-3">Emergency Contacts</h6>
                    <ul class="list-unstyled font-size-13">
                        <li class="mb-2"><i class="bx bx-phone-call text-danger me-2"></i> Hotline: <strong class="text-white">{{ $settings['emergency_hotline'] }}</strong></li>
                        <li class="mb-2"><i class="bx bxs-ambulance text-warning me-2"></i> Ambulance: <strong class="text-white">{{ $settings['ambulance_phone'] }}</strong></li>
                        <li class="mb-2"><i class="bx bxl-whatsapp text-success me-2"></i> WhatsApp: <strong class="text-white">{{ $settings['whatsapp_number'] }}</strong></li>
                        <li class="mb-2"><i class="bx bx-envelope text-info me-2"></i> Email: <strong class="text-white">{{ $settings['support_email'] }}</strong></li>
                    </ul>
                </div>

                <div class="col-lg-3">
                    <h6 class="text-white fw-bold mb-3">Patient Resources</h6>
                    <ul class="list-unstyled font-size-13">
                        <li class="mb-2"><a href="#book-appointment" class="text-white-50 text-decoration-none">Online OPD Token</a></li>
                        <li class="mb-2"><a href="#track-status" class="text-white-50 text-decoration-none">Live Queue Status</a></li>
                        <li class="mb-2"><a href="#doctors" class="text-white-50 text-decoration-none">Doctor Duty Schedule</a></li>
                        <li class="mb-2"><a href="#branches" class="text-white-50 text-decoration-none">Hospital Branches</a></li>
                    </ul>
                </div>

                <div class="col-lg-2">
                    <h6 class="text-white fw-bold mb-3">Staff Portal</h6>
                    <a href="{{ route('login') }}" class="btn btn-outline-light btn-sm w-100 mb-2">
                        <i class="bx bx-log-in me-1"></i> Staff Login
                    </a>
                    <small class="font-size-11 d-block text-white-50">Authorized medical personnel only.</small>
                </div>
            </div>

            <div class="border-top border-secondary mt-4 pt-3 text-center font-size-12">
                2026 © {{ $settings['site_name'] }}. All Rights Reserved. Built on Medisystem Modular HMS.
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="{{ asset('assets/libs/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

    <script>
        function filterDoctorsByBranch() {
            var branchId = document.getElementById('book_branch_id').value;
            var doctorSelect = document.getElementById('book_doctor_id');
            var options = doctorSelect.options;

            for (var i = 1; i < options.length; i++) {
                var docBranch = options[i].getAttribute('data-branch');
                if (!branchId || docBranch === branchId) {
                    options[i].style.display = '';
                } else {
                    options[i].style.display = 'none';
                }
            }
        }

        function selectDoctorInBooking(userId, branchId) {
            var branchSelect = document.getElementById('book_branch_id');
            var doctorSelect = document.getElementById('book_doctor_id');
            if (branchSelect && branchId) {
                branchSelect.value = branchId;
                filterDoctorsByBranch();
            }
            if (doctorSelect && userId) {
                doctorSelect.value = userId;
            }
        }
    </script>
</body>
</html>
