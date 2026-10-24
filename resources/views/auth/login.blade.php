<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Apollo Care Health Network • Single Sign-On HMS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Hospital Information Management System - Unified Multi-Speciality Health Network Portal" name="description" />
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.ico') }}">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
    <!-- Bootstrap Css -->
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" id="bootstrap-style" rel="stylesheet" type="text/css" />
    <!-- Icons Css -->
    <link href="{{ asset('assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- App Css-->
    <link href="{{ asset('assets/css/app.min.css') }}" id="app-style" rel="stylesheet" type="text/css" />

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: radial-gradient(circle at 10% 20%, #06182c 0%, #031022 90%);
            min-height: 100vh;
            color: #f8fafc;
            overflow-x: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 30px 15px;
        }
        .hospital-auth-card {
            background: #ffffff;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.45);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .hospital-hero-side {
            background: linear-gradient(145deg, #0f2d4a 0%, #084874 50%, #0d9488 100%);
            padding: 50px 40px;
            color: #ffffff;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .hospital-hero-side::before {
            content: "";
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: radial-gradient(circle at 80% 20%, rgba(13, 148, 136, 0.35) 0%, transparent 60%);
            pointer-events: none;
        }
        .hospital-logo-badge {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(8px);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 26px;
            color: #38bdf8;
            border: 1px solid rgba(255, 255, 255, 0.25);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        }
        .stat-pill {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 12px;
            padding: 12px 16px;
            backdrop-filter: blur(4px);
        }
        .form-side {
            padding: 45px 40px;
            background: #ffffff;
            color: #0f172a;
        }
        .form-control {
            border-radius: 10px;
            padding: 11px 14px;
            border: 1.5px solid #e2e8f0;
            font-size: 13.5px;
            transition: all 0.2s ease;
        }
        .form-control:focus {
            border-color: #0284c7;
            box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.15);
        }
        .btn-hospital-primary {
            background: linear-gradient(135deg, #0284c7 0%, #0d9488 100%);
            border: none;
            color: #ffffff;
            font-weight: 700;
            padding: 12px;
            border-radius: 10px;
            font-size: 14.5px;
            letter-spacing: 0.3px;
            transition: all 0.25s ease;
        }
        .btn-hospital-primary:hover {
            background: linear-gradient(135deg, #0369a1 0%, #0f766e 100%);
            transform: translateY(-1px);
            box-shadow: 0 8px 20px rgba(13, 148, 136, 0.3);
            color: #fff;
        }
        .role-pill-btn {
            background: #f8fafc;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            padding: 9px 12px;
            text-align: left;
            transition: all 0.2s ease;
            width: 100%;
        }
        .role-pill-btn:hover {
            border-color: #0284c7;
            background: #f0f9ff;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(2, 132, 199, 0.12);
        }
        .font-mono {
            font-family: 'JetBrains Mono', monospace;
        }
        .ticker-bar {
            background: rgba(2, 132, 199, 0.15);
            border: 1px solid rgba(56, 189, 248, 0.3);
            border-radius: 8px;
            padding: 8px 14px;
            font-size: 11.5px;
            color: #bae6fd;
            display: flex;
            align-items: center;
            gap: 8px;
        }
    </style>
</head>

<body>

    <div class="container" style="max-width: 1140px;">
        <div class="hospital-auth-card">
            <div class="row g-0">

                <!-- Left Column: Clinical Hospital Branding & Metrics -->
                <div class="col-lg-5 hospital-hero-side d-none d-lg-flex">
                    <div>
                        <!-- Logo & Title -->
                        <div class="d-flex align-items-center gap-3 mb-4">
                            <div class="hospital-logo-badge">
                                <i class="bx bx-plus-medical"></i>
                            </div>
                            <div>
                                <span class="badge bg-white text-primary font-size-11 fw-bolder px-2 py-0.5 text-uppercase">Apollo Care</span>
                                <h4 class="text-white mb-0 fw-bold font-size-18">Multi-Hospital Network</h4>
                            </div>
                        </div>

                        <!-- Main Slogan -->
                        <h2 class="text-white fw-bolder font-size-26 mb-3 lh-base">
                            Smart Hospital OS with Real-Time Clinical Integrity
                        </h2>
                        <p class="text-light opacity-75 font-size-13 mb-4">
                            A secure, tenant-isolated medical operations platform governing OPD, Inpatient ADT, Operation Theaters, Blood Bank, RIS/PACS, Vital Registry & Pharmacy POS.
                        </p>

                        <!-- Accreditation Ticker -->
                        <div class="ticker-bar mb-4">
                            <span class="spinner-grow spinner-grow-sm text-info" role="status"></span>
                            <span><strong>NABH Digital Level-2</strong> • ABDM M2 Linked • 24x7 ER</span>
                        </div>

                        <!-- Metrics Grid -->
                        <div class="row g-2 mb-4">
                            <div class="col-6">
                                <div class="stat-pill">
                                    <div class="font-mono font-size-18 fw-bold text-white">4 Outlets</div>
                                    <div class="font-size-11 opacity-75">Multi-Branch Network</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stat-pill">
                                    <div class="font-mono font-size-18 fw-bold text-info">150+ Beds</div>
                                    <div class="font-size-11 opacity-75">Active Inpatient Ward</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stat-pill">
                                    <div class="font-mono font-size-18 fw-bold text-success">100% Stock</div>
                                    <div class="font-size-11 opacity-75">Atomic POS Deductions</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stat-pill">
                                    <div class="font-mono font-size-18 fw-bold text-warning">GST Bill</div>
                                    <div class="font-size-11 opacity-75">Tax Invoice Ready</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Left Bottom -->
                    <div class="pt-3 border-top border-white-50">
                        <div class="d-flex justify-content-between align-items-center font-size-12 opacity-75">
                            <span><i class="bx bx-shield-quarter me-1 text-info"></i> AES-256 Data Isolation</span>
                            <a href="{{ route('frontend.home') }}" class="text-white text-decoration-underline" target="_blank">
                                Patient Portal &rarr;
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Login Portal & 1-Click Role Switcher -->
                <div class="col-lg-7 form-side">
                    <!-- Top header -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <span class="badge bg-primary-subtle text-primary font-size-11 fw-bold text-uppercase px-2 py-1">
                                <i class="bx bx-lock-alt me-1"></i> Staff Authentication
                            </span>
                            <h4 class="text-dark fw-bold font-size-20 mb-1 mt-1">Unified Healthcare Login Portal</h4>
                            <p class="text-muted font-size-12 mb-0">Single entrance for Super Admin (Platform HQ), Hospital Admins, Doctors & Branch Staff</p>
                        </div>
                        <div class="text-end d-none d-sm-block">
                            <span class="badge bg-light text-secondary border font-mono font-size-11">
                                v2.4 Modular
                            </span>
                        </div>
                    </div>

                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show font-size-13 py-2" role="alert">
                            <i class="bx bx-check-circle me-1 align-middle"></i> {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show font-size-13 py-2" role="alert">
                            <i class="bx bx-error me-1 align-middle"></i> {{ $errors->first() }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Tabs: Standard Login vs 1-Click Switcher -->
                    <ul class="nav nav-pills nav-justified mb-3 p-1 bg-light rounded" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active font-size-13 fw-semibold py-2" data-bs-toggle="pill" data-bs-target="#standardLoginTab" type="button">
                                <i class="bx bx-user me-1"></i> Official Credentials
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link font-size-13 fw-semibold py-2" data-bs-toggle="pill" data-bs-target="#oneClickTab" type="button">
                                <i class="bx bx-play-circle me-1 text-primary"></i> 1-Click Role Demo Switcher
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <!-- TAB 1: STANDARD CREDENTIALS FORM -->
                        <div class="tab-pane fade show active" id="standardLoginTab" role="tabpanel">
                            <form action="{{ route('login.post') }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label font-size-12 fw-semibold text-muted text-uppercase">Hospital Email Address</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light text-secondary"><i class="bx bx-envelope"></i></span>
                                        <input type="email" name="email" class="form-control" value="{{ old('email', 'superadmin@medisystem.io') }}" placeholder="user@apollocare.in" required autofocus>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <label class="form-label font-size-12 fw-semibold text-muted text-uppercase mb-0">Password</label>
                                        <small class="text-muted">Default: <code>password</code></small>
                                    </div>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light text-secondary"><i class="bx bx-lock-alt"></i></span>
                                        <input type="password" name="password" id="loginPasswordInput" class="form-control" value="password" required>
                                        <button class="btn btn-outline-secondary" type="button" onclick="const p = document.getElementById('loginPasswordInput'); p.type = p.type === 'password' ? 'text' : 'password';">
                                            <i class="bx bx-show font-size-15"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="remember" name="remember" checked>
                                        <label class="form-check-label font-size-12 text-muted" for="remember">Keep session active</label>
                                    </div>
                                    <span class="text-muted font-size-11"><i class="bx bx-check-shield text-success me-1"></i> Multi-Tenant Isolated</span>
                                </div>

                                <button type="submit" class="btn btn-hospital-primary w-100">
                                    <i class="bx bx-log-in-circle me-1 font-size-16 align-middle"></i> Enter Clinical Workspace
                                </button>
                            </form>
                        </div>

                        <!-- TAB 2: 1-CLICK INSTANT DEMO ROLE SWITCHER -->
                        <div class="tab-pane fade" id="oneClickTab" role="tabpanel">
                            <div class="vstack gap-2" style="max-height: 380px; overflow-y: auto;">

                                <!-- 1. Super Admin (Platform HQ) -->
                                <form action="{{ route('login.quick') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="email" value="superadmin@medisystem.io">
                                    <button type="submit" class="role-pill-btn">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="avatar-xs bg-warning text-white rounded-circle d-flex align-items-center justify-content-center font-size-15">
                                                    <i class="bx bx-crown"></i>
                                                </div>
                                                <div>
                                                    <div class="fw-bold text-dark font-size-13">Platform Super Admin</div>
                                                    <div class="text-muted font-size-11">Subscriptions, Tenants & Module Registry</div>
                                                </div>
                                            </div>
                                            <span class="badge bg-warning-subtle text-warning font-size-10 font-mono">HQ Main</span>
                                        </div>
                                    </button>
                                </form>

                                <!-- 2. Hospital Admin (Medical Director) -->
                                <form action="{{ route('login.quick') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="email" value="admin@apollocare.in">
                                    <button type="submit" class="role-pill-btn">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="avatar-xs bg-primary text-white rounded-circle d-flex align-items-center justify-content-center font-size-15">
                                                    <i class="bx bx-buildings"></i>
                                                </div>
                                                <div>
                                                    <div class="fw-bold text-dark font-size-13">Dr. Rajesh Sharma (Medical Director)</div>
                                                    <div class="text-muted font-size-11">Hospital Admin • Full 4 Branches Overview</div>
                                                </div>
                                            </div>
                                            <span class="badge bg-primary-subtle text-primary font-size-10 font-mono">Apollo HQ</span>
                                        </div>
                                    </button>
                                </form>

                                <!-- 3. Cardiologist Doctor -->
                                <form action="{{ route('login.quick') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="email" value="dr.ananya@apollocare.in">
                                    <button type="submit" class="role-pill-btn">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="avatar-xs bg-info text-white rounded-circle d-flex align-items-center justify-content-center font-size-15">
                                                    <i class="bx bx-pulse"></i>
                                                </div>
                                                <div>
                                                    <div class="fw-bold text-dark font-size-13">Dr. Ananya Sen, MD, DM</div>
                                                    <div class="text-muted font-size-11">Cardiologist • OPD Calendar, Rx & OT Surgeries</div>
                                                </div>
                                            </div>
                                            <span class="badge bg-info-subtle text-info font-size-10 font-mono">Cardiology</span>
                                        </div>
                                    </button>
                                </form>

                                <!-- 4. Pharmacist -->
                                <form action="{{ route('login.quick') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="email" value="pharmacist@apollocare.in">
                                    <button type="submit" class="role-pill-btn">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="avatar-xs bg-success text-white rounded-circle d-flex align-items-center justify-content-center font-size-15">
                                                    <i class="bx bx-cart"></i>
                                                </div>
                                                <div>
                                                    <div class="fw-bold text-dark font-size-13">Pooja Nair, B.Pharm</div>
                                                    <div class="text-muted font-size-11">Chief Pharmacist • Dedicated POS & Rack Control</div>
                                                </div>
                                            </div>
                                            <span class="badge bg-success-subtle text-success font-size-10 font-mono">Pharmacy POS</span>
                                        </div>
                                    </button>
                                </form>

                                <!-- 5. Senior ICU Nurse -->
                                <form action="{{ route('login.quick') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="email" value="nurse.priya@apollocare.in">
                                    <button type="submit" class="role-pill-btn">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="avatar-xs bg-danger text-white rounded-circle d-flex align-items-center justify-content-center font-size-15">
                                                    <i class="bx bx-bed"></i>
                                                </div>
                                                <div>
                                                    <div class="fw-bold text-dark font-size-13">Sister Priya Verma, B.Sc Nursing</div>
                                                    <div class="text-muted font-size-11">ICU Nurse • Inpatient ADT, Blood Bank & ER</div>
                                                </div>
                                            </div>
                                            <span class="badge bg-danger-subtle text-danger font-size-10 font-mono">ICU Care</span>
                                        </div>
                                    </button>
                                </form>

                                <!-- 6. Senior Accountant -->
                                <form action="{{ route('login.quick') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="email" value="accountant@apollocare.in">
                                    <button type="submit" class="role-pill-btn">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="avatar-xs bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center font-size-15">
                                                    <i class="bx bx-receipt"></i>
                                                </div>
                                                <div>
                                                    <div class="fw-bold text-dark font-size-13">Suresh Menon, CA</div>
                                                    <div class="text-muted font-size-11">Chief Accountant • GST Invoices & Branch Ledger</div>
                                                </div>
                                            </div>
                                            <span class="badge bg-secondary-subtle text-secondary font-size-10 font-mono">Finance Desk</span>
                                        </div>
                                    </button>
                                </form>

                            </div>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-top text-center text-muted font-size-11">
                        NABH Accredited • ABDM M2 Compliant • Indian GST 2017 Format • Centralized Multi-Branch HMS
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle JS -->
    <script src="{{ asset('assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
</body>
</html>
