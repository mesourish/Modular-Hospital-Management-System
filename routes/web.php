<?php

use App\Http\Controllers\AdmissionsController;
use App\Http\Controllers\AmbulanceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\BiomedicalController;
use App\Http\Controllers\BloodBankController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\ConstantsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DietaryController;
use App\Http\Controllers\DoctorChartController;
use App\Http\Controllers\DoctorScheduleController;
use App\Http\Controllers\FrontendController;
use App\Http\Controllers\HealthPackageController;
use App\Http\Controllers\HrPayrollController;
use App\Http\Controllers\IcuController;
use App\Http\Controllers\OpdController;
use App\Http\Controllers\OperationTheaterController;
use App\Http\Controllers\OrganizationChartController;
use App\Http\Controllers\PatientHistoryController;
use App\Http\Controllers\PharmacyController;
use App\Http\Controllers\PharmacyPosController;
use App\Http\Controllers\RadiologyController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\VitalRecordsController;
use App\Http\Controllers\WorkforceController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Public Single Unified Login & Authentication
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/login-quick', [AuthController::class, 'quickLogin'])->name('login.quick');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Public Customer Frontend & Multi-Language Patient Portal
Route::get('/', [FrontendController::class, 'home'])->name('frontend.home');
Route::get('/about', [FrontendController::class, 'about'])->name('frontend.about');
Route::get('/locations', [FrontendController::class, 'locations'])->name('frontend.locations');
Route::get('/doctors', [FrontendController::class, 'doctors'])->name('frontend.doctors');
Route::get('/doctors/{doctorChart}', [FrontendController::class, 'doctorProfile'])->name('frontend.doctor_profile');
Route::get('/packages', [FrontendController::class, 'packages'])->name('frontend.packages');
Route::post('/portal/book-appointment', [FrontendController::class, 'bookAppointment'])->name('portal.book_appointment');
Route::post('/portal/book-package', [FrontendController::class, 'bookPackage'])->name('portal.book_package');
Route::post('/portal/submit-testimonial', [FrontendController::class, 'submitTestimonial'])->name('portal.submit_testimonial');
Route::get('/portal/track-token', [FrontendController::class, 'trackToken'])->name('portal.track_token');
Route::get('/set-language/{locale}', [FrontendController::class, 'setLanguage'])->name('portal.set_language');

// Staff Role-Based Workspace Router
Route::get('/workspace', function () {
    if (! Auth::check()) {
        return redirect()->route('login');
    }

    $user = Auth::user();
    if ($user->isSuperAdmin()) {
        return redirect()->route('superadmin.dashboard');
    }
    if ($user->isDoctor()) {
        return redirect()->route('doctor.schedule');
    }
    if ($user->isPharmacist()) {
        return redirect()->route('pharmacy.index');
    }
    if ($user->isAccountant()) {
        return redirect()->route('branches.index');
    }

    return redirect()->route('hospital.dashboard');
})->name('workspace');

// Super Admin Platform Governance, Subscriptions & User Roles (Strictly Super Admin Only)
Route::prefix('superadmin')->name('superadmin.')->middleware(['role:super_admin'])->group(function () {
    Route::get('/', [SuperAdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/subscriptions', [SuperAdminController::class, 'subscriptions'])->name('subscriptions');
    Route::post('/subscriptions/{hospital}', [SuperAdminController::class, 'updateSubscription'])->name('subscriptions.update');
    Route::get('/users', [SuperAdminController::class, 'users'])->name('users');
    Route::post('/users', [SuperAdminController::class, 'storeUser'])->name('users.store');
    Route::post('/users/{user}/role-permissions', [SuperAdminController::class, 'updateUserRoleAndPermissions'])->name('users.update_role');
    Route::get('/hospitals', [SuperAdminController::class, 'hospitals'])->name('hospitals');
    Route::post('/hospitals', [SuperAdminController::class, 'storeHospital'])->name('hospitals.store');
    Route::post('/hospitals/{hospital}/toggle-module', [SuperAdminController::class, 'toggleModule'])->name('toggle_module');
    Route::post('/switch-hospital', [SuperAdminController::class, 'switchHospital'])->name('switch_hospital');
    Route::get('/audit-logs', [SuperAdminController::class, 'auditLogs'])->name('audit_logs');
    Route::get('/organization-chart', [OrganizationChartController::class, 'index'])->name('organization_chart');
    Route::get('/organization-chart/data', [OrganizationChartController::class, 'exportData'])->name('organization_chart.data');
});

// Customizable System & Customer Portal Settings (Super Admin, Hospital Admin, Branch Head)
Route::middleware(['role:super_admin,hospital_admin,branch_admin'])->group(function () {
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::post('/settings/clear-all-data', [SettingsController::class, 'clearAllData'])
        ->name('settings.clear_data')
        ->middleware('role:super_admin,hospital_admin');
});

// Master Constants, Departments, Sub-Departments & Designations (Super Admin, Hospital Admin, Branch Admin)
Route::middleware(['role:super_admin,hospital_admin,branch_admin'])->group(function () {
    Route::get('/constants', [ConstantsController::class, 'index'])->name('constants.index');
    Route::post('/constants/departments', [ConstantsController::class, 'storeDepartment'])->name('constants.departments.store');
    Route::post('/constants/sub-departments', [ConstantsController::class, 'storeSubDepartment'])->name('constants.sub_departments.store');
    Route::post('/constants/designations', [ConstantsController::class, 'storeDesignation'])->name('constants.designations.store');
    Route::post('/constants/system-constants', [ConstantsController::class, 'storeConstant'])->name('constants.system_constants.store');
    Route::post('/constants/{constant}/toggle', [ConstantsController::class, 'toggleConstant'])->name('constants.toggle');
    Route::post('/constants/seed-defaults', [ConstantsController::class, 'seedDefaults'])->name('constants.seed_defaults');
});

// Active Hospital Workspace Dashboard (Hospital Admin & Management)
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['role:hospital_admin,super_admin'])
    ->name('hospital.dashboard');

// Multi-Branch Hierarchical Structure & Branch Financial Records (Accountant & Admin, Branch Admin)
Route::middleware(['role:accountant,hospital_admin,super_admin,branch_admin'])->group(function () {
    Route::get('/branches', [BranchController::class, 'index'])->name('branches.index');
    Route::post('/branches', [BranchController::class, 'store'])->name('branches.store');
});

// Centralized Calendar-Based Doctor Schedule & Prescriptions Workspace (Doctor, Admin, Branch Admin)
Route::middleware(['role:doctor,hospital_admin,super_admin,branch_admin'])->group(function () {
    Route::get('/doctor-schedule', [DoctorScheduleController::class, 'index'])->name('doctor.schedule');
    Route::post('/doctor-schedule/appointment', [DoctorScheduleController::class, 'storeAppointment'])->name('doctor.schedule.appointment');
    Route::post('/doctor-schedule/prescription', [DoctorScheduleController::class, 'storePrescription'])->name('doctor.schedule.prescription');
    Route::post('/doctor-schedule/appointments/{appointment}/status', [DoctorScheduleController::class, 'updateStatus'])->name('doctor.schedule.status');
});

// Operation Theater (OT) Management, Patient Surgeries & Meds Usage Ledger
Route::middleware(['role:super_admin,hospital_admin,branch_admin,doctor,nurse'])->group(function () {
    Route::get('/operation-theater', [OperationTheaterController::class, 'index'])->name('ot.index');
    Route::post('/operation-theater/theaters', [OperationTheaterController::class, 'storeOt'])->name('ot.theaters.store');
    Route::post('/operation-theater/surgeries', [OperationTheaterController::class, 'storeSurgery'])->name('ot.surgeries.store');
    Route::post('/operation-theater/medications', [OperationTheaterController::class, 'storeMedication'])->name('ot.medications.store');
    Route::post('/operation-theater/surgeries/{surgery}/status', [OperationTheaterController::class, 'updateStatus'])->name('ot.surgeries.status');
    Route::get('/operation-theater/surgeries/{surgery}', [OperationTheaterController::class, 'showSurgery'])->name('ot.surgeries.show');
});

// Hospital-Wise Doctor Charts, Degrees, OPD Schedule & Consultation Fees (Managed by Hospital Admin & Branch Admin)
Route::middleware(['role:hospital_admin,branch_admin,super_admin,doctor,accountant'])->group(function () {
    Route::get('/doctor-charts', [DoctorChartController::class, 'index'])->name('doctor_charts.index');
    Route::get('/doctor-charts/print', [DoctorChartController::class, 'print'])->name('doctor_charts.print');
});

Route::middleware(['role:hospital_admin,branch_admin,super_admin'])->group(function () {
    Route::post('/doctor-charts', [DoctorChartController::class, 'store'])->name('doctor_charts.store');
    Route::put('/doctor-charts/{doctorChart}', [DoctorChartController::class, 'update'])->name('doctor_charts.update');
    Route::delete('/doctor-charts/{doctorChart}', [DoctorChartController::class, 'destroy'])->name('doctor_charts.destroy');
    Route::post('/doctor-charts/{doctorChart}/toggle-status', [DoctorChartController::class, 'toggleStatus'])->name('doctor_charts.toggle_status');
});

// Hospital-Wise Health Checkup Packages Catalogue & Configuration (Super Admin, Hospital Admin, Branch Admin)
Route::middleware(['role:super_admin,hospital_admin,branch_admin'])->group(function () {
    Route::get('/packages-admin', [HealthPackageController::class, 'index'])->name('packages.index');
    Route::post('/packages-admin', [HealthPackageController::class, 'store'])->name('packages.store');
    Route::put('/packages-admin/{healthPackage}', [HealthPackageController::class, 'update'])->name('packages.update');
    Route::delete('/packages-admin/{healthPackage}', [HealthPackageController::class, 'destroy'])->name('packages.destroy');
    Route::post('/packages-admin/{healthPackage}/toggle-status', [HealthPackageController::class, 'toggleStatus'])->name('packages.toggle_status');
});

// Patient Master & ADT (Doctor, Nurse, Hospital Admin)
Route::middleware(['module.enabled:admissions', 'role:doctor,nurse,hospital_admin,super_admin'])->group(function () {
    Route::get('/admissions', [AdmissionsController::class, 'index'])->name('admissions.index');
    Route::post('/admissions/patients', [AdmissionsController::class, 'storePatient'])->name('admissions.patients.store');
    Route::post('/admissions', [AdmissionsController::class, 'storeAdmission'])->name('admissions.store');
    Route::post('/admissions/{admission}/discharge', [AdmissionsController::class, 'discharge'])->name('admissions.discharge');
});

// OPD & Consultations (Doctor, Hospital Admin)
Route::middleware(['module.enabled:opd', 'role:doctor,hospital_admin,super_admin'])->group(function () {
    Route::get('/opd', [OpdController::class, 'index'])->name('opd.index');
    Route::post('/opd/appointments', [OpdController::class, 'storeAppointment'])->name('opd.appointments.store');
    Route::post('/opd/consultations', [OpdController::class, 'storeConsultation'])->name('opd.consultations.store');
});

// HR & Payroll (Accountant, Hospital Admin)
Route::middleware(['module.enabled:hr_payroll', 'role:accountant,hospital_admin,super_admin'])->group(function () {
    Route::get('/hr', [HrPayrollController::class, 'index'])->name('hr.index');
    Route::post('/hr/employees', [HrPayrollController::class, 'storeEmployee'])->name('hr.employees.store');
    Route::post('/hr/payroll/generate', [HrPayrollController::class, 'generatePayroll'])->name('hr.payroll.generate');
});

// ER Workforce & Shift Duty (Nurse, Doctor, Hospital Admin, Branch Admin)
Route::middleware(['module.enabled:er_workforce', 'role:nurse,doctor,hospital_admin,branch_admin,super_admin'])->group(function () {
    Route::get('/workforce', [WorkforceController::class, 'index'])->name('workforce.index');
    Route::post('/workforce/roster', [WorkforceController::class, 'storeRoster'])->name('workforce.roster.store');
    Route::post('/workforce/assignment', [WorkforceController::class, 'storePatientAssignment'])->name('workforce.assignment.store');
    Route::post('/workforce/attendance', [WorkforceController::class, 'storeAttendance'])->name('workforce.attendance.store');
    Route::post('/workforce/attendance/bulk', [WorkforceController::class, 'bulkClockIn'])->name('workforce.attendance.bulk');
});

// Blood Bank (Nurse, Hospital Admin)
Route::middleware(['module.enabled:blood_bank', 'role:nurse,hospital_admin,super_admin'])->group(function () {
    Route::get('/blood-bank', [BloodBankController::class, 'index'])->name('blood_bank.index');
    Route::post('/blood-bank/donors', [BloodBankController::class, 'storeDonor'])->name('blood_bank.donors.store');
    Route::post('/blood-bank/bags', [BloodBankController::class, 'storeBloodBag'])->name('blood_bank.bags.store');
    Route::post('/blood-bank/cross-match', [BloodBankController::class, 'crossMatch'])->name('blood_bank.cross_match.store');
    Route::post('/blood-bank/issue', [BloodBankController::class, 'issueBlood'])->name('blood_bank.issue.store');
});

// Radiology & Imaging Scans (Doctor, Radiologist, Hospital Admin, Branch Admin)
Route::middleware(['module.enabled:radiology', 'role:doctor,radiologist,hospital_admin,super_admin,branch_admin'])->group(function () {
    Route::get('/radiology', [RadiologyController::class, 'index'])->name('radiology.index');
    Route::post('/radiology/orders', [RadiologyController::class, 'storeOrder'])->name('radiology.orders.store');
    Route::post('/radiology/walkin', [RadiologyController::class, 'storeWalkin'])->name('radiology.walkin.store');
    Route::post('/radiology/orders/{order}/status', [RadiologyController::class, 'updateStatus'])->name('radiology.orders.status');
    Route::post('/radiology/reports', [RadiologyController::class, 'storeReport'])->name('radiology.reports.store');
});

// Pharmacy POS & Stock Center (Pharmacist, Hospital Admin, Super Admin, Branch Admin)
Route::middleware(['module.enabled:pharmacy', 'role:pharmacist,hospital_admin,super_admin,branch_admin'])->group(function () {
    Route::get('/pharmacy', [PharmacyController::class, 'index'])->name('pharmacy.index');
    Route::post('/pharmacy/pos', [PharmacyController::class, 'posCheckout'])->name('pharmacy.pos');
    Route::post('/pharmacy/medicines', [PharmacyController::class, 'storeMedicine'])->name('pharmacy.medicines.store');
    Route::post('/pharmacy/batches', [PharmacyController::class, 'storeBatch'])->name('pharmacy.batches.store');
    Route::post('/pharmacy/dispense', [PharmacyController::class, 'dispense'])->name('pharmacy.dispense.store');
    Route::post('/pharmacy/stores', [PharmacyController::class, 'storeStore'])->name('pharmacy.stores.store');
});

// Billing & Accounts (Accountant, Hospital Admin)
Route::middleware(['module.enabled:billing', 'role:accountant,hospital_admin,super_admin'])->group(function () {
    Route::get('/billing', [BillingController::class, 'index'])->name('billing.index');
    Route::post('/billing/invoices', [BillingController::class, 'storeInvoice'])->name('billing.invoices.store');
    Route::post('/billing/payments', [BillingController::class, 'recordPayment'])->name('billing.payments.store');
});

// Printable Indian GST Tax Invoice (Open for billing & pharmacy desks)
Route::middleware(['role:accountant,pharmacist,hospital_admin,super_admin,branch_admin,doctor'])->group(function () {
    Route::get('/billing/invoices/{invoice}/print', [BillingController::class, 'printGstInvoice'])->name('billing.invoices.print');
    Route::get('/pharmacy/invoices/{invoice}/print', [BillingController::class, 'printGstInvoice'])->name('pharmacy.invoices.print');
});

// Dedicated Pharmacy Point-Of-Sale Terminal
Route::middleware(['role:pharmacist,hospital_admin,super_admin,branch_admin'])->group(function () {
    Route::get('/pharmacy/pos', [PharmacyPosController::class, 'index'])->name('pharmacy.pos.index');
    Route::post('/pharmacy/pos/checkout', [PharmacyPosController::class, 'checkout'])->name('pharmacy.pos.checkout');
});

// Patient Comprehensive Lifetime Clinical History & Journey
Route::middleware(['role:doctor,nurse,hospital_admin,super_admin,branch_admin'])->group(function () {
    Route::get('/patients/{patient}/history', [PatientHistoryController::class, 'show'])->name('patients.history');
    Route::post('/patients/{patient}/history', [PatientHistoryController::class, 'update'])->name('patients.history.update');
    Route::get('/admissions/patient/{patient}/history', [PatientHistoryController::class, 'show'])->name('admissions.patient.history');
});

// Statutory Vital Records: Births & Deaths Registry
Route::middleware(['role:doctor,nurse,hospital_admin,super_admin,branch_admin'])->group(function () {
    Route::get('/vital-records', [VitalRecordsController::class, 'index'])->name('vital_records.index');
    Route::post('/vital-records/births', [VitalRecordsController::class, 'storeBirth'])->name('vital_records.births.store');
    Route::post('/vital-records/deaths', [VitalRecordsController::class, 'storeDeath'])->name('vital_records.deaths.store');
    Route::get('/vital-records/births/{birthRecord}/certificate', [VitalRecordsController::class, 'printBirthCertificate'])->name('vital_records.birth_certificate');
    Route::get('/vital-records/deaths/{deathRecord}/certificate', [VitalRecordsController::class, 'printDeathCertificate'])->name('vital_records.death_certificate');
});

// Essential Real-Life Extensions: Ambulance, Dietary, Biomedical
Route::middleware(['role:hospital_admin,super_admin,branch_admin,doctor,nurse'])->group(function () {
    // Ambulance Fleet & GPS Emergency Dispatch
    Route::get('/ambulances', [AmbulanceController::class, 'index'])->name('ambulances.index');
    Route::post('/ambulances', [AmbulanceController::class, 'storeAmbulance'])->name('ambulances.store');
    Route::post('/ambulances/trips', [AmbulanceController::class, 'storeTrip'])->name('ambulances.trips.store');
    Route::post('/ambulances/schedule', [AmbulanceController::class, 'scheduleTrip'])->name('ambulances.schedule.store');
    Route::post('/ambulances/trips/{trip}/status', [AmbulanceController::class, 'updateTripStatus'])->name('ambulances.trips.status');

    // ICU & Critical Care Management Suite
    Route::get('/icu', [IcuController::class, 'index'])->name('icu.index');
    Route::post('/icu/vitals', [IcuController::class, 'updateVitals'])->name('icu.vitals.update');
    Route::post('/icu/admit', [IcuController::class, 'admitToIcu'])->name('icu.admit');
    Route::post('/icu/step-down/{admission}', [IcuController::class, 'stepDown'])->name('icu.step_down');
    Route::post('/icu/code-blue', [IcuController::class, 'logCodeBlue'])->name('icu.code_blue');

    // Inpatient Dietary & Clinical Meal Orders
    Route::get('/dietary', [DietaryController::class, 'index'])->name('dietary.index');
    Route::post('/dietary/orders', [DietaryController::class, 'storeOrder'])->name('dietary.orders.store');
    Route::post('/dietary/orders/{dietaryOrder}/status', [DietaryController::class, 'updateStatus'])->name('dietary.orders.status');

    // Biomedical Equipment & Asset Maintenance
    Route::get('/biomedical', [BiomedicalController::class, 'index'])->name('biomedical.index');
    Route::post('/biomedical/equipment', [BiomedicalController::class, 'storeEquipment'])->name('biomedical.equipment.store');
    Route::post('/biomedical/tickets', [BiomedicalController::class, 'storeTicket'])->name('biomedical.tickets.store');
    Route::post('/biomedical/tickets/{ticket}/resolve', [BiomedicalController::class, 'resolveTicket'])->name('biomedical.tickets.resolve');
});

// Professional Hospital Outlet Slug Routing (Branch-Scoped SaaS Navigation)
Route::prefix('outlet/{outlet:slug}')->name('outlet.')->middleware(['role:super_admin,hospital_admin,branch_admin,doctor,pharmacist,nurse,accountant'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/pos', [PharmacyPosController::class, 'index'])->name('pos');
    Route::post('/pos/checkout', [PharmacyPosController::class, 'checkout'])->name('pos.checkout');
    Route::get('/schedule', [DoctorScheduleController::class, 'index'])->name('schedule');
    Route::get('/vital-records', [VitalRecordsController::class, 'index'])->name('vital_records');
    Route::get('/ambulances', [AmbulanceController::class, 'index'])->name('ambulances');
    Route::get('/icu', [IcuController::class, 'index'])->name('icu');
    Route::get('/dietary', [DietaryController::class, 'index'])->name('dietary');
    Route::get('/biomedical', [BiomedicalController::class, 'index'])->name('biomedical');
    Route::get('/ot', [OperationTheaterController::class, 'index'])->name('ot');
    Route::get('/billing', [BillingController::class, 'index'])->name('billing');
    Route::get('/pharmacy', [PharmacyController::class, 'index'])->name('pharmacy');
    Route::get('/admissions', [AdmissionsController::class, 'index'])->name('admissions');
});
