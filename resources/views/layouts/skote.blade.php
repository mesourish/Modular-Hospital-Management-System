<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>@yield('title', 'Hospital Platform') | Medisystem Modular HMS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Modular Multi-Tenant Hospital Management Platform" name="description" />
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.ico') }}">

    <!-- Bootstrap Css -->
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" id="bootstrap-style" rel="stylesheet" type="text/css" />
    <!-- Icons Css -->
    <link href="{{ asset('assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- App Css-->
    <link href="{{ asset('assets/css/app.min.css') }}" id="app-style" rel="stylesheet" type="text/css" />
    
    <style>
        .module-badge-active { background-color: rgba(52, 195, 143, 0.18); color: #34c38f; border: 1px solid rgba(52, 195, 143, 0.4); font-size: 0.75rem; padding: 3px 8px; border-radius: 4px; font-weight: 600; }
        .module-badge-disabled { background-color: rgba(244, 106, 106, 0.15); color: #f46a6a; border: 1px solid rgba(244, 106, 106, 0.4); font-size: 0.75rem; padding: 3px 8px; border-radius: 4px; font-weight: 600; }
        .tenant-selector-badge { background: linear-gradient(135deg, #556ee6 0%, #3b50c4 100%); color: #fff; font-weight: 600; border-radius: 6px; padding: 6px 14px; box-shadow: 0 2px 6px rgba(85, 110, 230, 0.35); }
        .card-stat-icon { width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; border-radius: 10px; font-size: 24px; }
        .table-custom th { font-weight: 600; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; color: #495057; background: #f8f9fa; }

        /* Exact Sidebar Flexbox Alignment & Icon Centering */
        #sidebar-menu ul li a {
            display: flex !important;
            align-items: center !important;
            padding: 0.65rem 1.25rem !important;
            line-height: 1.35 !important;
            font-size: 13px !important;
            color: #a6b0cf !important;
            transition: all 0.2s ease !important;
        }
        #sidebar-menu ul li a:hover, #sidebar-menu ul li a:focus {
            color: #ffffff !important;
            background: rgba(255, 255, 255, 0.05) !important;
        }
        #sidebar-menu ul li a i {
            width: 24px !important;
            min-width: 24px !important;
            max-width: 24px !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            font-size: 1.2rem !important;
            margin-right: 12px !important;
            flex-shrink: 0 !important;
            text-align: center !important;
        }
        #sidebar-menu ul li a span {
            display: inline-block !important;
            flex: 1 !important;
            white-space: normal !important;
            word-break: break-word !important;
            line-height: 1.3 !important;
        }
        #sidebar-menu .menu-title {
            padding: 16px 1.25rem 6px !important;
            font-size: 0.72rem !important;
            font-weight: 700 !important;
            letter-spacing: 0.08em !important;
            text-transform: uppercase !important;
            color: #7b8190 !important;
        }

        /* Topbar Redesign Styles */
        .topbar-avatar-circle {
            width: 38px;
            height: 38px;
            min-width: 38px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 13px;
            letter-spacing: 0.5px;
            background: #eef2ff;
            color: #556ee6;
            border: 2px solid rgba(85, 110, 230, 0.28);
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
            transition: all 0.2s ease;
        }
        .topbar-avatar-img {
            width: 38px;
            height: 38px;
            min-width: 38px;
            object-fit: cover;
            border-radius: 50%;
            border: 2px solid #556ee6;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
        }
        .topbar-station-badge {
            background: rgba(85, 110, 230, 0.1);
            border: 1px solid rgba(85, 110, 230, 0.25);
            color: #556ee6;
            font-weight: 600;
            border-radius: 30px;
            padding: 5px 14px;
            font-size: 12px;
            display: inline-flex;
            align-items: center;
        }
    </style>
    @stack('styles')
</head>

<body data-sidebar="dark">
    <!-- Begin page -->
    <div id="layout-wrapper">
        <header id="page-topbar">
            <div class="navbar-header">
                <div class="d-flex align-items-center">
                    <!-- LOGO -->
                    <div class="navbar-brand-box">
                        <a href="{{ route('hospital.dashboard') }}" class="logo logo-dark">
                            <span class="logo-sm">
                                <span class="badge bg-primary text-white p-1 font-size-14 rounded"><i class="fas fa-hospital"></i></span>
                            </span>
                            <span class="logo-lg">
                                <span class="fw-bold font-size-16 text-primary"><i class="fas fa-hospital me-1"></i> MEDISYSTEM</span>
                            </span>
                        </a>

                        <a href="{{ route('hospital.dashboard') }}" class="logo logo-light">
                            <span class="logo-sm">
                                <span class="badge bg-primary text-white p-1 font-size-14 rounded"><i class="fas fa-hospital"></i></span>
                            </span>
                            <span class="logo-lg">
                                <span class="fw-bold font-size-16 text-white"><i class="fas fa-hospital text-primary me-1"></i> MEDISYSTEM <small class="text-info font-size-11">HMS</small></span>
                            </span>
                        </a>
                    </div>

                    <button type="button" class="btn btn-sm px-3 font-size-16 header-item waves-effect" id="vertical-menu-btn">
                        <i class="fa fa-fw fa-bars"></i>
                    </button>

                    <!-- Role-Specific Topbar Context Section -->
                    @php
                        $user = auth()->user();
                        $role = $user ? $user->role : 'guest';
                        $activeHospital = \App\Core\Tenancy\TenantManager::getHospital();
                        $allHospitals = \App\Models\Hospital::where('is_active', true)->get();
                    @endphp

                    @if($role === 'super_admin')
                        <!-- Super Admin Platform HQ Topbar -->
                        <div class="dropdown d-inline-block ms-3">
                            <button type="button" class="btn header-item waves-effect dropdown-toggle d-flex align-items-center" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="badge bg-warning text-dark me-2 px-2 py-1 font-size-11">
                                    <i class="bx bx-crown me-1"></i> PLATFORM HQ
                                </span>
                                <span class="fw-bold text-dark font-size-13">
                                    Hospital Tenant: {{ $activeHospital ? $activeHospital->name : 'Global Perspective' }}
                                </span>
                                <i class="mdi mdi-chevron-down ms-1"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-lg p-2">
                                <div class="px-2 py-1 text-muted font-size-12 fw-semibold text-uppercase">
                                    Select Hospital Context (Multi-Tenant)
                                </div>
                                <div class="dropdown-divider"></div>
                                @foreach($allHospitals as $h)
                                    <form action="{{ route('superadmin.switch_hospital') }}" method="POST" class="d-block mb-1">
                                        @csrf
                                        <input type="hidden" name="hospital_id" value="{{ $h->id }}">
                                        <button type="submit" class="dropdown-item py-2 d-flex align-items-center justify-content-between rounded {{ $activeHospital && $activeHospital->id == $h->id ? 'bg-light fw-bold text-primary' : '' }}">
                                            <div>
                                                <div>{{ $h->name }}</div>
                                                <small class="text-muted">{{ $h->code }} • {{ $h->city }}</small>
                                            </div>
                                            @if($activeHospital && $activeHospital->id == $h->id)
                                                <i class="bx bx-check-circle text-success font-size-18"></i>
                                            @endif
                                        </button>
                                    </form>
                                @endforeach
                            </div>
                        </div>
                    @elseif($role === 'doctor')
                        <!-- Doctor Specific Topbar -->
                        <div class="d-flex align-items-center ms-3">
                            <span class="topbar-station-badge me-2">
                                <i class="fas fa-user-md me-1 text-primary"></i> Doctor Clinical Station
                            </span>
                            @php
                                $docName = $user ? $user->doctor_display_name : 'Doctor';
                            @endphp
                            <span class="fw-semibold text-dark font-size-13 d-none d-md-inline-block">
                                {{ $docName }} &bull; <span class="text-muted">{{ $activeHospital ? $activeHospital->name : 'Apollo Care' }}</span>
                            </span>
                            <a href="{{ route('doctor.schedule') }}" class="btn btn-sm btn-primary rounded-pill px-3 py-1 ms-3 d-none d-sm-inline-flex align-items-center shadow-sm">
                                <i class="bx bx-calendar-star me-1"></i> My Patient Schedule
                            </a>
                        </div>
                    @elseif($role === 'pharmacist')
                        <!-- Pharmacist Specific Topbar -->
                        <div class="d-flex align-items-center ms-3">
                            <span class="topbar-station-badge me-2" style="background: rgba(52, 195, 143, 0.12); border-color: rgba(52, 195, 143, 0.3); color: #34c38f;">
                                <i class="bx bx-capsule me-1 text-success"></i> Pharmacy & POS Station
                            </span>
                            <span class="fw-semibold text-dark font-size-13 d-none d-md-inline-block">
                                {{ $activeHospital ? $activeHospital->name : 'Hospital' }} Formulary Desk
                            </span>
                            <a href="{{ route('pharmacy.index') }}" class="btn btn-sm btn-success rounded-pill px-3 py-1 ms-3 d-none d-sm-inline-block">
                                <i class="bx bx-cart me-1"></i> Open POS Terminal
                            </a>
                        </div>
                    @elseif($role === 'nurse')
                        <!-- Nurse Specific Topbar -->
                        <div class="d-flex align-items-center ms-3">
                            <span class="topbar-station-badge me-2" style="background: rgba(80, 165, 241, 0.12); border-color: rgba(80, 165, 241, 0.3); color: #50a5f1;">
                                <i class="bx bx-heart me-1 text-info"></i> Nursing Station
                            </span>
                            <span class="fw-semibold text-dark font-size-13 d-none d-md-inline-block">
                                Inpatient Ward Care & ADT Rounds
                            </span>
                        </div>
                    @elseif($role === 'accountant')
                        <!-- Accountant Specific Topbar -->
                        <div class="d-flex align-items-center ms-3">
                            <span class="topbar-station-badge me-2" style="background: rgba(241, 180, 76, 0.12); border-color: rgba(241, 180, 76, 0.3); color: #f1b44c;">
                                <i class="bx bx-receipt me-1 text-warning"></i> Finance & Billing Desk
                            </span>
                            <span class="fw-semibold text-dark font-size-13 d-none d-md-inline-block">
                                {{ $activeHospital ? $activeHospital->name : 'Hospital' }} Multi-Branch Accounts
                            </span>
                        </div>
                    @else
                        <!-- Hospital Admin Topbar -->
                        <div class="d-flex align-items-center ms-3">
                            <span class="topbar-station-badge me-2">
                                <i class="bx bx-buildings me-1 text-primary"></i> Hospital Administration
                            </span>
                            <span class="fw-semibold text-dark font-size-13 d-none d-md-inline-block">
                                {{ $activeHospital ? $activeHospital->name : 'Apollo Care Healthcare' }}
                            </span>
                        </div>
                    @endif
                </div>

                <div class="d-flex align-items-center">
                    <a href="{{ route('frontend.home') }}" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 me-2 d-none d-md-inline-flex align-items-center">
                        <i class="bx bx-globe me-1"></i> Customer Portal
                    </a>
                    @if($role === 'super_admin')
                        <a href="{{ route('superadmin.users') }}" class="btn btn-sm btn-outline-warning rounded-pill px-3 py-1 me-2 d-none d-md-inline-flex align-items-center">
                            <i class="bx bx-shield-quarter me-1"></i> Roles & Permissions
                        </a>
                    @endif

                    <!-- User Profile Dropdown -->
                    <div class="dropdown d-inline-block ms-1">
                        <button type="button" class="btn header-item waves-effect d-flex align-items-center py-0 px-2" id="page-header-user-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            @if(!empty($user?->avatar))
                                <img src="{{ asset($user->avatar) }}" alt="{{ $user->name }}" class="topbar-avatar-img me-2">
                            @else
                                <div class="topbar-avatar-circle me-2">
                                    {{ $user?->initials ?? 'US' }}
                                </div>
                            @endif
                            <div class="text-start d-none d-xl-inline-block">
                                <div class="fw-bold font-size-13 text-dark lh-1 mb-1">{{ Str::limit($user->name ?? 'User', 24) }}</div>
                                @if($user)
                                    <span class="badge bg-primary-subtle text-primary font-size-10 px-2 py-0 fw-medium">
                                        {{ ucfirst(str_replace('_', ' ', $user->role)) }}
                                    </span>
                                @endif
                            </div>
                            <i class="mdi mdi-chevron-down ms-2 text-muted d-none d-xl-inline-block"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end shadow border-0" style="min-width: 260px; border-radius: 10px;">
                            <div class="p-3 bg-light rounded-top border-bottom">
                                <div class="d-flex align-items-center">
                                    @if(!empty($user?->avatar))
                                        <img src="{{ asset($user->avatar) }}" alt="{{ $user->name }}" class="topbar-avatar-img me-2">
                                    @else
                                        <div class="topbar-avatar-circle me-2">
                                            {{ $user?->initials ?? 'US' }}
                                        </div>
                                    @endif
                                    <div class="overflow-hidden">
                                        <h6 class="font-size-14 text-dark mb-0 text-truncate fw-bold">{{ $user->name ?? 'User' }}</h6>
                                        <small class="text-muted d-block text-truncate">{{ $user->email ?? 'Active Session' }}</small>
                                        <span class="badge bg-primary-subtle text-primary font-size-10 px-2 py-0 mt-1">
                                            {{ ucfirst(str_replace('_', ' ', $user->role ?? 'User')) }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            @if($role === 'super_admin')
                                <a class="dropdown-item py-2" href="{{ route('superadmin.dashboard') }}">
                                    <i class="bx bx-crown font-size-16 align-middle me-2 text-warning"></i> <span>Platform HQ</span>
                                </a>
                                <a class="dropdown-item py-2" href="{{ route('superadmin.subscriptions') }}">
                                    <i class="bx bx-rupee font-size-16 align-middle me-2 text-success"></i> <span>Subscriptions</span>
                                </a>
                                <a class="dropdown-item py-2" href="{{ route('superadmin.users') }}">
                                    <i class="bx bx-shield-quarter font-size-16 align-middle me-2 text-primary"></i> <span>Role Permissions</span>
                                </a>
                            @elseif($role === 'doctor')
                                <a class="dropdown-item py-2" href="{{ route('doctor.schedule') }}">
                                    <i class="bx bx-calendar-star font-size-16 align-middle me-2 text-info"></i> <span>My Schedule</span>
                                </a>
                                <a class="dropdown-item py-2" href="{{ route('opd.index') }}">
                                    <i class="fas fa-stethoscope font-size-15 align-middle me-2 text-primary"></i> <span>OPD Consultations</span>
                                </a>
                            @elseif($role === 'pharmacist')
                                <a class="dropdown-item py-2" href="{{ route('pharmacy.index') }}">
                                    <i class="bx bx-cart font-size-16 align-middle me-2 text-success"></i> <span>Pharmacy POS</span>
                                </a>
                            @elseif($role === 'accountant')
                                <a class="dropdown-item py-2" href="{{ route('branches.index') }}">
                                    <i class="bx bx-git-branch font-size-16 align-middle me-2 text-warning"></i> <span>Branch Financials</span>
                                </a>
                            @endif
                            <div class="dropdown-divider my-1"></div>
                            <a class="dropdown-item py-2" href="{{ route('login') }}">
                                <i class="bx bx-refresh font-size-16 align-middle me-2 text-info"></i> <span>Switch Role / Login</span>
                            </a>
                            <form action="{{ route('logout') }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="dropdown-item py-2 text-danger border-0 bg-transparent w-100 text-start">
                                    <i class="bx bx-power-off font-size-16 align-middle me-2 text-danger"></i> <span>Sign Out</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- ========== Left Sidebar Start (Role-Specific Menus) ========== -->
        <div class="vertical-menu">
            <div data-simplebar class="h-100">
                <!--- Sidemenu -->
                <div id="sidebar-menu">
                    @php
                        $currentHospitalId = $activeHospital ? $activeHospital->id : null;
                        $hasBloodBank = $currentHospitalId ? \App\Core\ModuleRegistry\ModuleRegistry::isModuleEnabled('blood_bank', $currentHospitalId) : false;
                        $hasRadiology = $currentHospitalId ? \App\Core\ModuleRegistry\ModuleRegistry::isModuleEnabled('radiology', $currentHospitalId) : false;
                        $hasPharmacy = $currentHospitalId ? \App\Core\ModuleRegistry\ModuleRegistry::isModuleEnabled('pharmacy', $currentHospitalId) : false;
                        $hasAdmissions = $currentHospitalId ? \App\Core\ModuleRegistry\ModuleRegistry::isModuleEnabled('admissions', $currentHospitalId) : false;
                        $hasOpd = $currentHospitalId ? \App\Core\ModuleRegistry\ModuleRegistry::isModuleEnabled('opd', $currentHospitalId) : false;
                        $hasHr = $currentHospitalId ? \App\Core\ModuleRegistry\ModuleRegistry::isModuleEnabled('hr_payroll', $currentHospitalId) : false;
                        $hasWorkforce = $currentHospitalId ? \App\Core\ModuleRegistry\ModuleRegistry::isModuleEnabled('er_workforce', $currentHospitalId) : false;
                        $hasBilling = $currentHospitalId ? \App\Core\ModuleRegistry\ModuleRegistry::isModuleEnabled('billing', $currentHospitalId) : false;
                    @endphp

                    <ul class="metismenu list-unstyled" id="side-menu">

                    @if($role === 'super_admin')
                        <!-- 1. SUPER ADMIN ONLY SIDEBAR (Platform HQ, Subscriptions, Roles & Permissions) -->
                        <li class="menu-title" key="t-menu">Platform HQ Governance</li>
                        <li>
                            <a href="{{ route('superadmin.dashboard') }}" class="waves-effect">
                                <i class="bx bx-shield-quarter text-primary"></i>
                                <span>Platform Overview</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('superadmin.subscriptions') }}" class="waves-effect">
                                <i class="bx bx-crown text-warning"></i>
                                <span>Subscriptions & MRR</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('superadmin.users') }}" class="waves-effect">
                                <i class="bx bx-user-check text-success"></i>
                                <span>User Roles & Permissions</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('superadmin.hospitals') }}" class="waves-effect">
                                <i class="bx bx-buildings text-info"></i>
                                <span>Hospitals & Modules</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('superadmin.audit_logs') }}" class="waves-effect">
                                <i class="bx bx-check-shield text-success"></i>
                                <span>Security Audit Trail</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('superadmin.organization_chart') }}" class="waves-effect">
                                <i class="bx bx-sitemap text-warning"></i>
                                <span>Organization Chart</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('icu.index') }}" class="waves-effect">
                                <i class="bx bx-pulse text-danger"></i>
                                <span>ICU & Critical Care</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('ambulances.index') }}" class="waves-effect">
                                <i class="bx bxs-ambulance text-danger"></i>
                                <span>Ambulance Fleet & Dispatch</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('ot.index') }}" class="waves-effect">
                                <i class="fas fa-procedures text-danger"></i>
                                <span>Operation Theater (OT)</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('packages.index') }}" class="waves-effect">
                                <i class="bx bx-package text-success"></i>
                                <span>Health Packages</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('constants.index') }}" class="waves-effect">
                                <i class="bx bx-slider-alt text-primary"></i>
                                <span>Master Constants</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('settings.index') }}" class="waves-effect">
                                <i class="bx bx-cog text-secondary"></i>
                                <span>Platform Settings</span>
                            </a>
                        </li>

                    @elseif($role === 'doctor')
                        <!-- 2. DOCTOR ONLY SIDEBAR (Patient info, Schedule, Consultations, Inpatient Rounds) -->
                        <li class="menu-title">Doctor Clinical Care</li>
                        <li>
                            <a href="{{ route('doctor.schedule') }}" class="waves-effect">
                                <i class="bx bx-calendar-star text-info"></i>
                                <span>My Schedule & Calendar</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('doctor_charts.index') }}" class="waves-effect">
                                <i class="bx bx-list-check text-warning"></i>
                                <span>Doctor OPD Duty Chart</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('opd.index') }}" class="waves-effect">
                                <i class="fas fa-stethoscope text-primary"></i>
                                <span>OPD Consultations & Rx</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admissions.index') }}" class="waves-effect">
                                <i class="bx bx-bed text-success"></i>
                                <span>Inpatient Ward Rounds</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('icu.index') }}" class="waves-effect">
                                <i class="bx bx-pulse text-danger"></i>
                                <span>ICU & Critical Care</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('ot.index') }}" class="waves-effect">
                                <i class="fas fa-procedures text-danger"></i>
                                <span>Operation Theater (OT)</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('ambulances.index') }}" class="waves-effect">
                                <i class="bx bxs-ambulance text-info"></i>
                                <span>Ambulance Fleet & Dispatch</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('radiology.index') }}" class="waves-effect">
                                <i class="bx bx-scan text-warning"></i>
                                <span>Diagnostic Scans & PACS</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('vital_records.index') }}" class="waves-effect">
                                <i class="bx bx-certification text-success"></i>
                                <span>Vital Records (Birth & Death)</span>
                            </a>
                        </li>

                    @elseif($role === 'pharmacist')
                        <!-- 3. PHARMACIST ONLY SIDEBAR (POS, Medicine Stock, Rx Queue) -->
                        <li class="menu-title">Pharmacy & Dispense</li>
                        <li>
                            <a href="{{ route('pharmacy.pos.index') }}" class="waves-effect">
                                <i class="bx bx-cart text-primary"></i>
                                <span>Dedicated POS Terminal</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('pharmacy.index') }}" class="waves-effect">
                                <i class="bx bx-package text-success"></i>
                                <span>Medicine Stock & Batches</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('pharmacy.index') }}#formularyTab" class="waves-effect">
                                <i class="bx bx-capsule text-info"></i>
                                <span>Formulary Catalog</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('pharmacy.index') }}#auditTab" class="waves-effect">
                                <i class="bx bx-history text-secondary"></i>
                                <span>Dispense Sales History</span>
                            </a>
                        </li>

                    @elseif($role === 'nurse')
                        <!-- 4. NURSE ONLY SIDEBAR (Inpatient Ward, ER Duty, Blood Bank) -->
                        <li class="menu-title">Nursing Care & Ward</li>
                        <li>
                            <a href="{{ route('admissions.index') }}" class="waves-effect">
                                <i class="bx bx-bed text-primary"></i>
                                <span>Inpatient Bed ADT</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('icu.index') }}" class="waves-effect">
                                <i class="bx bx-pulse text-danger"></i>
                                <span>ICU & Critical Care</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('workforce.index') }}" class="waves-effect">
                                <i class="bx bx-timer text-warning"></i>
                                <span>ER Duty & Assignments</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('ambulances.index') }}" class="waves-effect">
                                <i class="bx bxs-ambulance text-danger"></i>
                                <span>Ambulance Fleet & Dispatch</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('blood_bank.index') }}" class="waves-effect">
                                <i class="bx bx-droplet text-danger"></i>
                                <span>Blood Bank Issue</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('ot.index') }}" class="waves-effect">
                                <i class="fas fa-procedures text-danger"></i>
                                <span>Operation Theater (OT)</span>
                            </a>
                        </li>

                    @elseif($role === 'accountant')
                        <!-- 5. ACCOUNTANT ONLY SIDEBAR (Financials, Invoices, Salaries) -->
                        <li class="menu-title">Finance & Billing Desk</li>
                        <li>
                            <a href="{{ route('branches.index') }}" class="waves-effect">
                                <i class="bx bx-git-branch text-warning"></i>
                                <span>Multi-Branch Financials</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('billing.index') }}" class="waves-effect">
                                <i class="bx bx-receipt text-primary"></i>
                                <span>Patient Billing & Invoices</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('hr.index') }}" class="waves-effect">
                                <i class="bx bx-id-card text-success"></i>
                                <span>Employee Salaries & Payroll</span>
                            </a>
                        </li>

                    @else
                        <!-- 6. HOSPITAL ADMIN SIDEBAR (Consolidated Operations) -->
                        <li class="menu-title">Hospital Administration</li>
                        <li>
                            <a href="{{ route('hospital.dashboard') }}" class="waves-effect">
                                <i class="bx bx-home-circle text-primary"></i>
                                <span>Hospital Dashboard</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('branches.index') }}" class="waves-effect">
                                <i class="bx bx-git-branch text-warning"></i>
                                <span>Multi-Branch Financials</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('doctor.schedule') }}" class="waves-effect">
                                <i class="bx bx-calendar-star text-info"></i>
                                <span>Doctor Calendar & Rx</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('doctor_charts.index') }}" class="waves-effect">
                                <i class="bx bx-calendar-event text-success"></i>
                                <span>Doctor OPD Charts & Fees</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('packages.index') }}" class="waves-effect">
                                <i class="bx bx-package text-warning"></i>
                                <span>Health Checkup Packages</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admissions.index') }}" class="waves-effect">
                                <i class="bx bx-user-plus text-primary"></i>
                                <span>Patient Master & ADT</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('opd.index') }}" class="waves-effect">
                                <i class="bx bx-calendar-heart text-info"></i>
                                <span>OPD & Consultation</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('hr.index') }}" class="waves-effect">
                                <i class="bx bx-id-card text-success"></i>
                                <span>Employee & Salaries</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('workforce.index') }}" class="waves-effect">
                                <i class="bx bx-timer text-warning"></i>
                                <span>ER Duty & Assignments</span>
                            </a>
                        </li>
                        <li class="menu-title mt-2">Clinical Extensions</li>
                        <li>
                            <a href="{{ route('icu.index') }}" class="waves-effect">
                                <i class="bx bx-pulse text-danger"></i>
                                <span>ICU & Critical Care</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('ambulances.index') }}" class="waves-effect">
                                <i class="bx bxs-ambulance text-danger"></i>
                                <span>Ambulance Fleet & Dispatch</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('vital_records.index') }}" class="waves-effect">
                                <i class="bx bx-certification text-success"></i>
                                <span>Vital Records (Birth & Death)</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('ot.index') }}" class="waves-effect">
                                <i class="fas fa-procedures text-danger"></i>
                                <span>Operation Theater (OT)</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('ambulances.index') }}" class="waves-effect">
                                <i class="bx bx-car text-danger"></i>
                                <span>Ambulance & ER Dispatch</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('dietary.index') }}" class="waves-effect">
                                <i class="bx bx-bowl-hot text-warning"></i>
                                <span>Dietary & Nutrition</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('biomedical.index') }}" class="waves-effect">
                                <i class="bx bx-wrench text-info"></i>
                                <span>Biomedical Equipment</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('blood_bank.index') }}" class="waves-effect">
                                <i class="bx bx-droplet text-danger"></i>
                                <span>Blood Bank</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('radiology.index') }}" class="waves-effect">
                                <i class="bx bx-scan text-info"></i>
                                <span>Radiology & PACS</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('pharmacy.pos.index') }}" class="waves-effect">
                                <i class="bx bx-cart text-primary"></i>
                                <span>Dedicated Pharmacy POS</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('pharmacy.index') }}" class="waves-effect">
                                <i class="bx bx-package text-success"></i>
                                <span>Pharmacy Store & Stock</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('billing.index') }}" class="waves-effect">
                                <i class="bx bx-receipt text-secondary"></i>
                                <span>Billing & GST Invoices</span>
                            </a>
                        </li>
                        <li class="menu-title mt-2">Platform & Settings</li>
                        <li>
                            <a href="{{ route('constants.index') }}" class="waves-effect">
                                <i class="bx bx-slider-alt text-primary"></i>
                                <span>Master Constants</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('settings.index') }}" class="waves-effect">
                                <i class="bx bx-cog text-secondary"></i>
                                <span>Hospital Settings</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('frontend.home') }}" target="_blank" class="waves-effect">
                                <i class="bx bx-globe text-info"></i>
                                <span>Customer Portal Preview</span>
                            </a>
                        </li>
                    @endif

                    </ul>
                </div>
                <!-- Sidebar -->
            </div>
        </div>
        <!-- Left Sidebar End -->

        <!-- ============================================================== -->
        <!-- Start right Content here -->
        <!-- ============================================================== -->
        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">
                    <!-- Flash Notifications -->
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="mdi mdi-check-all me-2"></i> {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="mdi mdi-block-helper me-2"></i> {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if(session('info'))
                        <div class="alert alert-info alert-dismissible fade show" role="alert">
                            <i class="mdi mdi-alert-circle-outline me-2"></i> {{ session('info') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @yield('content')
                </div>
                <!-- container-fluid -->
            </div>
            <!-- End Page-content -->

            <footer class="footer">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-sm-6">
                            2026 © Medisystem Modular HMS.
                        </div>
                        <div class="col-sm-6 text-sm-end">
                            <span class="badge bg-soft-success text-success"><i class="bx bx-lock-alt"></i> Tenant Isolated • Audit Logging Active</span>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
        <!-- end main content-->
    </div>
    <!-- END layout-wrapper -->

    <!-- JAVASCRIPT -->
    <script src="{{ asset('assets/libs/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/libs/metismenu/metisMenu.min.js') }}"></script>
    <script src="{{ asset('assets/libs/simplebar/simplebar.min.js') }}"></script>
    <script src="{{ asset('assets/libs/node-waves/waves.min.js') }}"></script>

    <!-- App js -->
    <script src="{{ asset('assets/js/app.js') }}"></script>
    @stack('scripts')
</body>
</html>
