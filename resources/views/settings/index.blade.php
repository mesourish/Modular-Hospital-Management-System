@extends('layouts.skote')

@section('title', 'Platform & Customer Portal Settings')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0 font-size-18">
                <i class="bx bx-cog text-primary me-1"></i> System Settings & Customer Frontend Customization
            </h4>
            <div class="page-title-right">
                <a href="{{ route('frontend.home') }}" target="_blank" class="btn btn-outline-info btn-sm">
                    <i class="bx bx-globe me-1"></i> View Live Customer Portal
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Scope Selector: Super Admin / Hospital Admin / Branch Head Context -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body p-3">
        <form action="{{ route('settings.index') }}" method="GET" class="row align-items-center g-3">
            <div class="col-md-4">
                <label class="form-label font-size-12 fw-bold text-muted text-uppercase mb-1">Customizing Hospital Scope:</label>
                @if($user->isSuperAdmin())
                    <select name="hospital_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">🌐 Global Platform Defaults</option>
                        @foreach($hospitals as $h)
                            <option value="{{ $h->id }}" {{ $selectedHospitalId == $h->id ? 'selected' : '' }}>
                                {{ $h->name }} ({{ $h->code }})
                            </option>
                        @endforeach
                    </select>
                @else
                    <input type="text" class="form-control form-control-sm bg-light" value="{{ $hospitals->first()->name ?? 'Active Hospital' }}" readonly>
                    <input type="hidden" name="hospital_id" value="{{ $selectedHospitalId }}">
                @endif
            </div>

            <div class="col-md-4">
                <label class="form-label font-size-12 fw-bold text-muted text-uppercase mb-1">Branch Specific Overrides (Optional):</label>
                @if($user->isBranchAdmin() && $user->branch_id)
                    <input type="text" class="form-control form-control-sm bg-light" value="Branch: {{ $branches->where('id', $selectedBranchId)->first()->name ?? 'My Branch' }}" readonly>
                    <input type="hidden" name="branch_id" value="{{ $selectedBranchId }}">
                @else
                    <select name="branch_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">🏢 Hospital-Wide Default (All Branches)</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}" {{ $selectedBranchId == $b->id ? 'selected' : '' }}>
                                {{ $b->name }} ({{ $b->city }})
                            </option>
                        @endforeach
                    </select>
                @endif
            </div>

            <div class="col-md-4 text-md-end pt-3">
                @if($selectedBranchId)
                    <span class="badge bg-warning-subtle text-warning font-size-12 px-3 py-2">
                        <i class="bx bx-git-branch me-1"></i> Editing Branch Specific Overrides
                    </span>
                @elseif($selectedHospitalId)
                    <span class="badge bg-primary-subtle text-primary font-size-12 px-3 py-2">
                        <i class="bx bx-buildings me-1"></i> Editing Hospital-Wide Settings
                    </span>
                @else
                    <span class="badge bg-success-subtle text-success font-size-12 px-3 py-2">
                        <i class="bx bx-globe me-1"></i> Editing Global Platform Defaults
                    </span>
                @endif
            </div>
        </form>
    </div>
</div>

<form action="{{ route('settings.update') }}" method="POST">
    @csrf
    <input type="hidden" name="scope_hospital_id" value="{{ $selectedHospitalId }}">
    <input type="hidden" name="scope_branch_id" value="{{ $selectedBranchId }}">

    <div class="row">
        <!-- Settings Category Navigation (Pills / Tabs) -->
        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body p-3">
                    <ul class="nav flex-column nav-pills" id="settings-tab" role="tablist">
                        <li class="nav-item mb-2" role="presentation">
                            <button class="nav-link active text-start w-100" id="tab-general-btn" data-bs-toggle="pill" data-bs-target="#tab-general" type="button" role="tab">
                                <i class="bx bx-palette me-2 font-size-16 align-middle"></i> Branding & Visuals
                            </button>
                        </li>
                        <li class="nav-item mb-2" role="presentation">
                            <button class="nav-link text-start w-100" id="tab-frontend-btn" data-bs-toggle="pill" data-bs-target="#tab-frontend" type="button" role="tab">
                                <i class="bx bx-globe me-2 font-size-16 align-middle"></i> Customer Portal & Hero
                            </button>
                        </li>
                        <li class="nav-item mb-2" role="presentation">
                            <button class="nav-link text-start w-100" id="tab-contact-btn" data-bs-toggle="pill" data-bs-target="#tab-contact" type="button" role="tab">
                                <i class="bx bx-phone-call me-2 font-size-16 align-middle"></i> Emergency & Contacts
                            </button>
                        </li>
                        <li class="nav-item mb-2" role="presentation">
                            <button class="nav-link text-start w-100" id="tab-clinical-btn" data-bs-toggle="pill" data-bs-target="#tab-clinical" type="button" role="tab">
                                <i class="bx bx-receipt me-2 font-size-16 align-middle"></i> Clinical & OPD Token Rules
                            </button>
                        </li>
                        <li class="nav-item mb-2" role="presentation">
                            <button class="nav-link text-start w-100" id="tab-gateway-btn" data-bs-toggle="pill" data-bs-target="#tab-gateway" type="button" role="tab">
                                <i class="bx bx-message-rounded-dots me-2 font-size-16 align-middle"></i> SMS & Gateways
                            </button>
                        </li>
                        @if($user->isSuperAdmin() || $user->role === 'hospital_admin')
                        <li class="nav-item mb-2" role="presentation">
                            <button class="nav-link text-start w-100 text-danger border border-danger-subtle" id="tab-maintenance-btn" data-bs-toggle="pill" data-bs-target="#tab-maintenance" type="button" role="tab">
                                <i class="bx bx-shield-quarter me-2 font-size-16 align-middle text-danger"></i> Factory Reset / Clear Data
                            </button>
                        </li>
                        @endif
                    </ul>

                    <div class="border-top pt-3 mt-3">
                        <button type="submit" class="btn btn-primary w-100 fw-bold">
                            <i class="bx bx-check-double me-1"></i> Save All Changes
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab Content Panels -->
        <div class="col-md-9">
            <div class="tab-content" id="settings-tabContent">

                <!-- 1. Branding & Visuals -->
                <div class="tab-pane fade show active" id="tab-general" role="tabpanel">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white border-bottom py-3">
                            <h5 class="card-title mb-0 fw-bold text-dark">
                                <i class="bx bx-palette text-primary me-1"></i> Hospital & Platform Identity
                            </h5>
                            <small class="text-muted">Branding, institutional name, and accreditation standards.</small>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold font-size-13">Hospital / Brand Display Name</label>
                                    <input type="text" name="site_name" class="form-control" value="{{ $settings['site_name'] ?? 'Apollo Hospitals Healthcare Network' }}" required>
                                    <small class="text-muted font-size-11">Shown on public navbar, footer, and invoice receipts.</small>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold font-size-13">Brand Tagline / Slogan</label>
                                    <input type="text" name="brand_tagline" class="form-control" value="{{ $settings['brand_tagline'] ?? 'Touching Lives • World-Class Clinical Excellence' }}">
                                    <small class="text-muted font-size-11">Displayed below brand logo across portal.</small>
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-bold font-size-13">Accreditation & Regulatory Badges</label>
                                    <input type="text" name="accreditation_text" class="form-control" value="{{ $settings['accreditation_text'] ?? 'NABH & JCI Accredited • Ayushman Bharat (ABHA) Compliant' }}">
                                    <small class="text-muted font-size-11">Displayed on customer hero and trust badges.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. Customer Portal & Hero Banner -->
                <div class="tab-pane fade" id="tab-frontend" role="tabpanel">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white border-bottom py-3">
                            <h5 class="card-title mb-0 fw-bold text-dark">
                                <i class="bx bx-globe text-primary me-1"></i> Customer Frontend & Landing Page Customization
                            </h5>
                            <small class="text-muted">Customize public patient landing page, headline text, and interactive features.</small>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-bold font-size-13">Hero Main Headline</label>
                                    <input type="text" name="frontend_hero_title" class="form-control" value="{{ $settings['frontend_hero_title'] ?? "India's Leading Multi-Super Specialty Healthcare Network" }}">
                                    <small class="text-muted font-size-11">Prominent main heading on the patient landing page.</small>
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-bold font-size-13">Hero Subtitle / Description</label>
                                    <textarea name="frontend_hero_subtitle" class="form-control" rows="2">{{ $settings['frontend_hero_subtitle'] ?? 'Book instant confirmed OPD consultation tokens, check specialist doctor duty schedules, and track live prescription dispensations.' }}</textarea>
                                </div>

                                <div class="col-12 border-top pt-3">
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" name="announcement_enabled" id="announcement_enabled" value="1" {{ !empty($settings['announcement_enabled']) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold font-size-13" for="announcement_enabled">Enable Top Announcement / Alert Bar</label>
                                    </div>
                                    <input type="text" name="announcement_text" class="form-control" value="{{ $settings['announcement_text'] ?? '⚡ Free Preventive Health Screening Camp this week at all branches!' }}" placeholder="Enter alert text...">
                                </div>

                                <div class="col-md-6 border-top pt-3">
                                    <div class="form-check form-switch mb-1">
                                        <input class="form-check-input" type="checkbox" name="allow_online_booking" id="allow_online_booking" value="1" {{ !empty($settings['allow_online_booking']) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold font-size-13" for="allow_online_booking">Allow Public Online OPD Token Booking</label>
                                    </div>
                                    <small class="text-muted font-size-11">Allows patients to self-generate OPD tokens online.</small>
                                </div>

                                <div class="col-md-6 border-top pt-3">
                                    <div class="form-check form-switch mb-1">
                                        <input class="form-check-input" type="checkbox" name="show_doctor_fees" id="show_doctor_fees" value="1" {{ !empty($settings['show_doctor_fees']) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold font-size-13" for="show_doctor_fees">Show Consultation Fees on Public Doctor Cards</label>
                                    </div>
                                    <small class="text-muted font-size-11">Publicly exposes consultation tariffs (₹) to customers.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3. Emergency & Contacts -->
                <div class="tab-pane fade" id="tab-contact" role="tabpanel">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white border-bottom py-3">
                            <h5 class="card-title mb-0 fw-bold text-dark">
                                <i class="bx bx-phone-call text-danger me-1"></i> Emergency Hotline & Branch Contacts
                            </h5>
                            <small class="text-muted">Telephone hotlines, ambulance numbers, and branch custom notices.</small>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold font-size-13">24x7 Emergency Trauma Hotline</label>
                                    <input type="text" name="emergency_hotline" class="form-control" value="{{ $settings['emergency_hotline'] ?? '1066 / +91 22 2656 7890' }}" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold font-size-13">Ambulance Direct Phone</label>
                                    <input type="text" name="ambulance_phone" class="form-control" value="{{ $settings['ambulance_phone'] ?? '108 / +91 22 2656 7899' }}" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold font-size-13">WhatsApp Assistance & Appointment Desk</label>
                                    <input type="text" name="whatsapp_number" class="form-control" value="{{ $settings['whatsapp_number'] ?? '+91 98200 12345' }}">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold font-size-13">Public Patient Support Email</label>
                                    <input type="email" name="support_email" class="form-control" value="{{ $settings['support_email'] ?? 'care@apollocare.in' }}">
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-bold font-size-13">Central Campus Physical Address</label>
                                    <input type="text" name="hospital_address" class="form-control" value="{{ $settings['hospital_address'] ?? 'Apollo Hospital Complex, Dr. E. Moses Road, Mahalaxmi, Mumbai, Maharashtra 400011' }}">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold font-size-13">General OPD Working Hours</label>
                                    <input type="text" name="opd_hours" class="form-control" value="{{ $settings['opd_hours'] ?? 'Mon - Sat: 08:00 AM - 08:00 PM (Emergency 24x7)' }}">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold font-size-13">Branch Custom Notice (For Selected Branch)</label>
                                    <input type="text" name="branch_notice" class="form-control" value="{{ $settings['branch_notice'] ?? '' }}" placeholder="e.g. Evening OPD timings extended...">
                                    <small class="text-muted font-size-11">Shown when patients select this branch.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 4. Clinical & OPD Token Rules -->
                <div class="tab-pane fade" id="tab-clinical" role="tabpanel">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white border-bottom py-3">
                            <h5 class="card-title mb-0 fw-bold text-dark">
                                <i class="bx bx-receipt text-success me-1"></i> Clinical & OPD Token Generation Rules
                            </h5>
                            <small class="text-muted">Currency, GSTIN, and queue sequence configuration.</small>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-bold font-size-13">Currency Symbol</label>
                                    <input type="text" name="currency_symbol" class="form-control text-center font-size-16 fw-bold" value="{{ $settings['currency_symbol'] ?? '₹' }}" required>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-bold font-size-13">GSTIN Registration Number</label>
                                    <input type="text" name="gstin_number" class="form-control" value="{{ $settings['gstin_number'] ?? '27AAACA1234F1Z5' }}">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-bold font-size-13">OPD Token Prefix</label>
                                    <input type="text" name="token_prefix" class="form-control text-center fw-bold" value="{{ $settings['token_prefix'] ?? 'TK-' }}">
                                </div>

                                <div class="col-12 border-top pt-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="auto_confirm_appointment" id="auto_confirm_appointment" value="1" {{ !empty($settings['auto_confirm_appointment']) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold font-size-13" for="auto_confirm_appointment">Auto-Confirm Public Appointments Instantly</label>
                                    </div>
                                    <small class="text-muted font-size-11">Automatically issues confirmed OPD token without receptionist triage delay.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 5. SMS & Gateways -->
                <div class="tab-pane fade" id="tab-gateway" role="tabpanel">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white border-bottom py-3">
                            <h5 class="card-title mb-0 fw-bold text-dark">
                                <i class="bx bx-message-rounded-dots text-info me-1"></i> SMS & WhatsApp Dispatch Gateways
                            </h5>
                            <small class="text-muted">SMS confirmation template and provider settings.</small>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold font-size-13">SMS Gateway Provider</label>
                                    <select name="sms_gateway_provider" class="form-select">
                                        <option value="Fast2SMS India Simulator" selected>Fast2SMS India (Simulator Active)</option>
                                        <option value="Twilio Cloud">Twilio Cloud API</option>
                                        <option value="WhatsApp Business API">WhatsApp Cloud API (Meta)</option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold font-size-13">DLT Approved Sender ID</label>
                                    <input type="text" name="sms_sender_id" class="form-control" value="{{ $settings['sms_sender_id'] ?? 'APOLLO' }}" maxlength="6">
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-bold font-size-13">Appointment Confirmation SMS Template</label>
                                    <textarea name="sms_template" class="form-control" rows="3">{{ $settings['sms_template'] ?? 'Dear {patient}, your OPD token #{token} with {doctor} at {branch} on {date} at {time} is confirmed. Room: {room}. Helpline: 1066.' }}</textarea>
                                    <small class="text-muted font-size-11">Supported variables: <code>{patient}</code>, <code>{token}</code>, <code>{doctor}</code>, <code>{branch}</code>, <code>{date}</code>, <code>{time}</code>, <code>{room}</code>.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @if($user->isSuperAdmin() || $user->role === 'hospital_admin')
                <!-- 6. Factory Reset / Clear All Data -->
                <div class="tab-pane fade" id="tab-maintenance" role="tabpanel">
                    <div class="card shadow-sm border-0 border-top border-danger border-3">
                        <div class="card-header bg-danger-subtle py-3 d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="card-title mb-0 fw-bold text-danger">
                                    <i class="bx bx-error-alt me-1"></i> Factory Data Reset & System Maintenance
                                </h5>
                                <small class="text-muted">Perform complete operational data cleaning while preserving core software baseline.</small>
                            </div>
                            <span class="badge bg-danger px-3 py-2 font-size-12">Restricted Action</span>
                        </div>
                        <div class="card-body p-4">
                            <div class="alert alert-warning border-0 d-flex align-items-center mb-4">
                                <i class="bx bx-info-circle font-size-24 me-3 text-warning"></i>
                                <div>
                                    <h6 class="fw-bold mb-1">What does "Clear All Data" do?</h6>
                                    <p class="mb-0 font-size-13 text-muted">
                                        This administrative operation purges all runtime operational, patient, and billing records (patients, appointments, consultations, prescriptions, admissions, ICU/OT records, invoices, payments, and ambulance logs) but <strong>strictly retains minimal data to run the software</strong> (tenants, branches, staff login accounts, departments, sub-departments, designations, master constants, settings, and facilities catalog).
                                    </p>
                                </div>
                            </div>

                            <div class="row g-4 mb-4">
                                <div class="col-md-6">
                                    <div class="border rounded p-3 h-100 bg-light-subtle">
                                        <h6 class="fw-bold text-danger mb-2"><i class="bx bx-trash me-1"></i> Data That Will Be Deleted:</h6>
                                        <ul class="font-size-13 text-muted ps-3 mb-0">
                                            <li>All Patient Profiles, Longitudinal Records & Health Records</li>
                                            <li>OPD Appointments, Doctor Consultations & Digital Prescriptions</li>
                                            <li>IPD Admissions, Bed Assignments & Discharge Summaries</li>
                                            <li>ICU Management, Ventilator Logs & Code Blue Events</li>
                                            <li>OT Surgeries & Intraoperative Medication Logs</li>
                                            <li>Radiology Imaging Orders & Diagnostic Reports</li>
                                            <li>Blood Bank Donors, Bag Inventory & Transfusion Logs</li>
                                            <li>Billing Invoices, GST Tax Receipts & Cashier Payments</li>
                                            <li>Ambulance Emergency Dispatch Logs & Historical Trips</li>
                                            <li>Staff Attendance Records & Monthly Payroll Slips</li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="border rounded p-3 h-100 bg-success-subtle">
                                        <h6 class="fw-bold text-success mb-2"><i class="bx bx-check-shield me-1"></i> Data Kept Intact (Minimal Baseline):</h6>
                                        <ul class="font-size-13 text-dark ps-3 mb-0">
                                            <li><strong>Hospitals & Branches:</strong> Multi-tenant hospital identities</li>
                                            <li><strong>Core Staff Accounts:</strong> Super Admin, Directors, Doctors, Nurses, Pharmacists, Radiologists, and Accountants (login credentials remain intact)</li>
                                            <li><strong>Hospital Modules:</strong> Active modular enablement configurations</li>
                                            <li><strong>Master Constants:</strong> Clinical departments, sub-departments, designations, triage levels, and system constants</li>
                                            <li><strong>System Settings:</strong> Branding, contact info, token numbering rules, and gateway configurations</li>
                                            <li><strong>Facilities Master:</strong> Wards, beds (reset to available), OT rooms, and ambulance fleet (reset to available)</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="p-3 border border-danger-subtle bg-danger-subtle rounded d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                                <div>
                                    <h6 class="fw-bold text-danger mb-1">Permanent Factory Reset</h6>
                                    <p class="font-size-12 text-muted mb-0">Requires administrator password verification and manual confirmation phrase.</p>
                                </div>
                                <button type="button" class="btn btn-danger px-4 py-2 fw-bold text-nowrap" data-bs-toggle="modal" data-bs-target="#clearAllDataModal">
                                    <i class="bx bx-trash me-1"></i> Clear All Data
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

            </div>
        </div>
    </div>
</form>

@if($user->isSuperAdmin() || $user->role === 'hospital_admin')
<!-- Modal for Clear All Data Confirmation -->
<div class="modal fade" id="clearAllDataModal" tabindex="-1" aria-labelledby="clearAllDataModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-danger text-white py-3">
                <h5 class="modal-title fw-bold" id="clearAllDataModalLabel">
                    <i class="bx bx-error-alt me-1"></i> Confirm System Factory Reset
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('settings.clear_data') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="alert alert-danger font-size-13 mb-3">
                        <strong>Warning:</strong> This action cannot be undone. All transactional, patient, and billing information will be permanently deleted while preserving essential master data and login credentials.
                    </div>

                    @if($user->isSuperAdmin())
                    <div class="mb-3">
                        <label class="form-label fw-bold font-size-13">Reset Scope</label>
                        <select name="scope_hospital_id" class="form-select">
                            <option value="">🌐 Entire Platform (All Hospitals & Branches)</option>
                            @foreach($hospitals as $h)
                                <option value="{{ $h->id }}" {{ $selectedHospitalId == $h->id ? 'selected' : '' }}>
                                    Hospital: {{ $h->name }} ({{ $h->code }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @else
                    <input type="hidden" name="scope_hospital_id" value="{{ $user->hospital_id }}">
                    @endif

                    <div class="mb-3">
                        <label class="form-label fw-bold font-size-13 text-danger">Administrator Password</label>
                        <input type="password" name="password" class="form-control" placeholder="Enter your current password" required autocomplete="current-password">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold font-size-13">Type Confirmation Phrase</label>
                        <p class="font-size-12 text-muted mb-1">To proceed, please type <strong class="text-danger">CLEAR_ALL_DATA</strong> below:</p>
                        <input type="text" name="confirm_phrase" class="form-control" placeholder="CLEAR_ALL_DATA" required>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger btn-sm fw-bold">
                        <i class="bx bx-trash me-1"></i> Yes, Clear All Operational Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection
