<?php

use App\Core\Audit\AuditLogger;
use App\Core\ModuleRegistry\ModuleRegistry;
use App\Core\Tenancy\TenantManager;
use App\Models\Appointment;
use App\Models\AuditEvent;
use App\Models\Branch;
use App\Models\Consultation;
use App\Models\DispenseRecord;
use App\Models\DoctorChart;
use App\Models\Hospital;
use App\Models\MedicineBatch;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Seed initial platform data for testing
    $this->artisan('db:seed', ['--class' => 'ModularHospitalSeeder']);
});

test('single login screen renders successfully for all roles', function () {
    $response = $this->get('/login');
    $response->assertStatus(200);
    $response->assertSee('Unified Healthcare Login Portal');
    $response->assertSee('Platform Super Admin');
    $response->assertSee('1-Click Role Demo Switcher');
    $response->assertSee('superadmin@medisystem.io');
});

test('super admin logs in and redirects to platform governance dashboard', function () {
    $response = $this->post('/login', [
        'email' => 'superadmin@medisystem.io',
        'password' => 'password',
    ]);

    $response->assertStatus(302);
    $response->assertRedirect(route('superadmin.dashboard'));
    $this->assertAuthenticated();
});

test('doctor logs in and redirects directly to centralized doctor schedule calendar', function () {
    $doctor = User::where('email', 'dr.ananya@apollocare.in')->first();

    $response = $this->post('/login', [
        'email' => $doctor->email,
        'password' => 'password',
    ]);

    $response->assertStatus(302);
    $response->assertRedirect(route('doctor.schedule'));
    $this->assertAuthenticatedAs($doctor);
});

test('super admin subscription center renders and allows upgrading tenant subscription', function () {
    $superadmin = User::where('role', 'super_admin')->first();
    $apollo = Hospital::where('code', 'ACH-01')->first();

    $response = $this->actingAs($superadmin)->get('/superadmin/subscriptions');
    $response->assertStatus(200);
    $response->assertSee('Platform Subscription');
    $response->assertSee('Apollo Care Healthcare Network India');
    $response->assertSee('₹1,20,000');

    // Change Fortis subscription plan
    $fortis = Hospital::where('code', 'FCC-02')->first();
    $updateResponse = $this->actingAs($superadmin)->post("/superadmin/subscriptions/{$fortis->id}", [
        'subscription_plan' => 'multi_specialty',
    ]);

    $updateResponse->assertStatus(302);
    expect($fortis->fresh()->subscription_plan)->toBe('multi_specialty');
});

test('multi-branch hierarchical operations and financial center displays accurate branch records', function () {
    $admin = User::where('email', 'admin@apollocare.in')->first();
    $apollo = Hospital::where('code', 'ACH-01')->first();
    TenantManager::setActiveHospital($apollo);

    $response = $this->actingAs($admin)
        ->withSession(['active_hospital_id' => $apollo->id])
        ->get('/branches');

    $response->assertStatus(200);
    $response->assertSee('Hierarchical Multi-Branch Operations');
    $response->assertSee('Apollo Care Flagship Hospital - South Mumbai');
    $response->assertSee('MAIN BRANCH / PLATFORM HQ');
    $response->assertSee('Bandra West');
    $response->assertSee('Total Invoiced (Billed)');
    $response->assertSee('Total Collections (Received)');
    $response->assertSee('Payment Method Breakdown');
});

test('new hospital branch can be added to the hierarchical network', function () {
    $admin = User::where('email', 'admin@apollocare.in')->first();
    $apollo = Hospital::where('code', 'ACH-01')->first();
    TenantManager::setActiveHospital($apollo);

    $response = $this->actingAs($admin)
        ->withSession(['active_hospital_id' => $apollo->id])
        ->post('/branches', [
            'name' => 'Apollo Care Navi Mumbai Diagnostic Annex',
            'code' => 'ACH-NM-05',
            'city' => 'Navi Mumbai',
            'state' => 'Maharashtra',
            'pincode' => '400703',
            'manager_name' => 'Dr. Suresh Patel',
            'phone' => '+91 98200 99881',
            'is_main_branch' => 0,
        ]);

    $response->assertStatus(302);
    $response->assertRedirect(route('branches.index'));

    $created = Branch::where('code', 'ACH-NM-05')->first();
    expect($created)->not->toBeNull()
        ->and($created->name)->toBe('Apollo Care Navi Mumbai Diagnostic Annex')
        ->and($created->is_main_branch)->toBeFalse();
});

test('centralized doctor schedule calendar renders with appointments and allows prescribing medicine', function () {
    $doctor = User::where('email', 'dr.ananya@apollocare.in')->first();
    $apollo = Hospital::where('code', 'ACH-01')->first();
    TenantManager::setActiveHospital($apollo);

    $appointment = Appointment::where('hospital_id', $apollo->id)->first();

    $response = $this->actingAs($doctor)
        ->withSession(['active_hospital_id' => $apollo->id])
        ->get('/doctor-schedule?date='.$appointment->appointment_date->toDateString());

    $response->assertStatus(200);
    $response->assertSee('Centralized Doctor Schedule');
    $response->assertSee($appointment->patient->name);
    $response->assertSee($appointment->token_number);

    // Prescribe medicine for appointment
    $rxResponse = $this->actingAs($doctor)->post('/doctor-schedule/prescription', [
        'appointment_id' => $appointment->id,
        'medicine_name' => 'Dolo 650 (Paracetamol)',
        'dosage' => '650mg',
        'frequency' => '1-0-1 After food',
        'duration' => '5 days',
        'instructions' => 'Take with warm water',
        'diagnosis' => 'Acute Viral Pyrexia',
    ]);

    $rxResponse->assertStatus(302);

    $prescription = Prescription::where('medicine_name', 'Dolo 650 (Paracetamol)')->latest()->first();
    expect($prescription)->not->toBeNull()
        ->and($prescription->dosage)->toBe('650mg')
        ->and($prescription->patient_id)->toBe($appointment->patient_id);
});

test('hospital with enabled blood_bank module can access blood-bank department', function () {
    $admin = User::where('email', 'admin@apollocare.in')->first();
    $apollo = Hospital::where('code', 'ACH-01')->first();
    TenantManager::setActiveHospital($apollo);

    $response = $this->actingAs($admin)->withSession(['active_hospital_id' => $apollo->id])->get('/blood-bank');
    $response->assertStatus(200);
    $response->assertSee('Blood Bank');
});

test('hospital with disabled blood_bank module is strictly blocked with 403 by EnsureModuleEnabled middleware', function () {
    $admin = User::where('email', 'admin@apollocare.in')->first();
    $fortis = Hospital::where('code', 'FCC-02')->first();
    TenantManager::setActiveHospital($fortis);

    // Fortis has blood_bank disabled
    $response = $this->actingAs($admin)->withSession(['active_hospital_id' => $fortis->id])->get('/blood-bank');
    $response->assertStatus(403);
    $response->assertSee('Extension Not Activated');
    $response->assertSee('blood_bank');
});

test('super admin can toggle module entitlement and activate disabled module', function () {
    $superadmin = User::where('role', 'super_admin')->first();
    $admin = User::where('email', 'admin@apollocare.in')->first();
    $fortis = Hospital::where('code', 'FCC-02')->first();

    // Toggle blood_bank for Fortis
    $response = $this->actingAs($superadmin)->post("/superadmin/hospitals/{$fortis->id}/toggle-module", [
        'module_key' => 'blood_bank',
    ]);

    $response->assertStatus(302);

    // Verify it is now enabled
    expect(ModuleRegistry::isModuleEnabled('blood_bank', $fortis->id))->toBeTrue();

    // Fortis can now access blood-bank
    TenantManager::setActiveHospital($fortis);
    $access = $this->actingAs($admin)->withSession(['active_hospital_id' => $fortis->id])->get('/blood-bank');
    $access->assertStatus(200);

    // Toggle back to disabled for consistency
    $this->actingAs($superadmin)->post("/superadmin/hospitals/{$fortis->id}/toggle-module", [
        'module_key' => 'blood_bank',
    ]);
});

test('strict tenant isolation ensures Hospital A patients are not visible in Hospital B', function () {
    $apollo = Hospital::where('code', 'ACH-01')->first();
    $fortis = Hospital::where('code', 'FCC-02')->first();

    // Create a patient scoped to Apollo
    TenantManager::setOverrideHospitalId($apollo->id);
    $patientApollo = Patient::create([
        'hospital_id' => $apollo->id,
        'mrn' => 'UHID-APOLLO-TEST',
        'first_name' => 'Rahul',
        'last_name' => 'Verma',
        'gender' => 'male',
        'phone' => '9820011111',
    ]);

    // Create a patient scoped to Fortis
    TenantManager::setOverrideHospitalId($fortis->id);
    $patientFortis = Patient::create([
        'hospital_id' => $fortis->id,
        'mrn' => 'UHID-FORTIS-TEST',
        'first_name' => 'Priya',
        'last_name' => 'Sharma',
        'gender' => 'female',
        'phone' => '9820022222',
    ]);

    // Query under Apollo hospital context
    TenantManager::setOverrideHospitalId($apollo->id);
    $apolloPatients = Patient::all();
    expect($apolloPatients->pluck('uhid')->toArray())->toContain('UHID-APOLLO-TEST')
        ->and($apolloPatients->pluck('uhid')->toArray())->not->toContain('UHID-FORTIS-TEST');

    // Query under Fortis hospital context
    TenantManager::setOverrideHospitalId($fortis->id);
    $fortisPatients = Patient::all();
    expect($fortisPatients->pluck('uhid')->toArray())->toContain('UHID-FORTIS-TEST')
        ->and($fortisPatients->pluck('uhid')->toArray())->not->toContain('UHID-APOLLO-TEST');

    TenantManager::setOverrideHospitalId(null);
});

test('audit logger creates immutable audit records for security events', function () {
    $initialCount = AuditEvent::count();

    AuditLogger::log(
        action: 'security.test_privileged_action',
        auditableType: 'TestEntity',
        auditableId: 999,
        payload: ['unit_test' => true]
    );

    expect(AuditEvent::count())->toBe($initialCount + 1);

    $last = AuditEvent::latest('id')->first();
    expect($last->action)->toBe('security.test_privileged_action')
        ->and($last->payload['unit_test'])->toBeTrue();
});

test('doctor sees role-specific sidebar and cannot see admin or pharmacy menus', function () {
    $doctor = User::where('email', 'dr.ananya@apollocare.in')->first();
    $apollo = Hospital::where('code', 'ACH-01')->first();
    TenantManager::setActiveHospital($apollo);

    $response = $this->actingAs($doctor)
        ->withSession(['active_hospital_id' => $apollo->id])
        ->get('/doctor-schedule');

    $response->assertStatus(200);
    // Doctor specific sidebar & topbar
    $response->assertSee('Doctor Clinical Care');
    $response->assertSee('My Schedule');
    $response->assertSee('OPD Consultations');
    $response->assertSee('Inpatient Ward Rounds');
    $response->assertSee('Diagnostic Scans');
    $response->assertSee('Doctor Clinical Station');

    // Doctor must NOT see pharmacy POS or admin menus
    $response->assertDontSee('Pharmacy POS');
    $response->assertDontSee('Multi-Branch Financials');
    $response->assertDontSee('Security Audit Trail');
});

test('doctor is blocked from accessing pharmacy and redirected gracefully to doctor schedule', function () {
    $doctor = User::where('email', 'dr.ananya@apollocare.in')->first();
    $apollo = Hospital::where('code', 'ACH-01')->first();
    TenantManager::setActiveHospital($apollo);

    $response = $this->actingAs($doctor)
        ->withSession(['active_hospital_id' => $apollo->id])
        ->get('/pharmacy');

    $response->assertStatus(302);
    $response->assertRedirect(route('doctor.schedule'));
    $response->assertSessionHas('error');
});

test('pharmacist sees pharmacy pos and medicine stock without doctor schedule or admin menus', function () {
    $pharmacist = User::where('email', 'pooja.pharmacy@apollocare.in')->first();
    $apollo = Hospital::where('code', 'ACH-01')->first();
    TenantManager::setActiveHospital($apollo);

    $response = $this->actingAs($pharmacist)
        ->withSession(['active_hospital_id' => $apollo->id])
        ->get('/pharmacy');

    $response->assertStatus(200);
    // Pharmacist specific sidebar & topbar
    $response->assertSee('Pharmacy');
    $response->assertSee('Dispense');
    $response->assertSee('Pharmacy POS');
    $response->assertSee('Medicine Stock');
    $response->assertSee('Formulary Catalog');
    $response->assertSee('Open POS Terminal');
    $response->assertSee('Formulary Desk');

    // Pharmacist must NOT see doctor schedule or financial branch management
    $response->assertDontSee('Doctor Clinical Care');
    $response->assertDontSee('Multi-Branch Financials');

    // Pharmacist visiting doctor schedule is blocked and redirected
    $docAttempt = $this->actingAs($pharmacist)
        ->withSession(['active_hospital_id' => $apollo->id])
        ->get('/doctor-schedule');
    $docAttempt->assertStatus(302);
    $docAttempt->assertRedirect(route('pharmacy.index'));
});

test('super admin alone can access user management and update user roles and permissions', function () {
    $superadmin = User::where('role', 'super_admin')->first();
    $doctor = User::where('email', 'dr.ananya@apollocare.in')->first();
    $nurse = User::where('email', 'mary.fernandez@apollocare.in')->first();

    // Doctor cannot access superadmin users
    $denied = $this->actingAs($doctor)->get('/superadmin/users');
    $denied->assertStatus(302);

    // Super admin can access superadmin users
    $allowed = $this->actingAs($superadmin)->get('/superadmin/users');
    $allowed->assertStatus(200);
    $allowed->assertSee('Platform User Roles');
    $allowed->assertSee('Super Admin Exclusive Privilege');

    // Super admin updates nurse permissions
    $updateResponse = $this->actingAs($superadmin)->post("/superadmin/users/{$nurse->id}/role-permissions", [
        'role' => 'nurse',
        'permissions' => ['inpatient_admission', 'blood_bank_access', 'er_emergency_duty'],
        'is_active' => 1,
    ]);

    $updateResponse->assertStatus(302);
    $nurse->refresh();
    expect($nurse->permissions)->toContain('blood_bank_access')
        ->and($nurse->hasPermission('blood_bank_access'))->toBeTrue();
});

test('pharmacy pos checkout completes sale, decrements batch stock, marks rx as dispensed, and produces receipt', function () {
    $pharmacist = User::where('email', 'pooja.pharmacy@apollocare.in')->first();
    $apollo = Hospital::where('code', 'ACH-01')->first();
    TenantManager::setActiveHospital($apollo);

    $batch = MedicineBatch::where('quantity_in_stock', '>', 10)->first();
    $initialStock = $batch->quantity_in_stock;

    // Use or create prescription for testing
    $patient = Patient::where('hospital_id', $apollo->id)->first();
    $prescription = Prescription::where('is_dispensed', false)->first()
        ?? Prescription::create([
            'patient_id' => $patient->id,
            'consultation_id' => Consultation::first()->id,
            'doctor_id' => User::where('role', 'doctor')->first()->id,
            'hospital_id' => $apollo->id,
            'medicine_name' => 'Augmentin 625 Duo',
            'dosage' => '625mg',
            'frequency' => '1-0-1',
            'duration' => '5 days',
            'is_dispensed' => false,
        ]);

    $response = $this->actingAs($pharmacist)
        ->withSession(['active_hospital_id' => $apollo->id])
        ->post('/pharmacy/pos', [
            'medicine_batch_id' => $batch->id,
            'quantity' => 2,
            'payment_method' => 'upi',
            'patient_id' => $patient->id,
            'prescription_id' => $prescription->id,
            'discount_amount' => 10,
            'notes' => 'UPI GPay Ref #99212001',
        ]);

    $response->assertStatus(302);
    $response->assertSessionHas('pos_receipt');

    // Verify batch stock was decremented
    expect($batch->fresh()->quantity_in_stock)->toBe($initialStock - 2);

    // Verify doctor prescription marked as dispensed
    expect($prescription->fresh()->is_dispensed)->toBeTrue();

    // Verify dispense record created
    $dispense = DispenseRecord::where('medicine_batch_id', $batch->id)->latest()->first();
    expect($dispense)->not->toBeNull()
        ->and($dispense->quantity)->toBe(2)
        ->and($dispense->notes)->toContain('POS Paid via UPI');
});

test('hospital admin can view hospital-wise doctor charts with degrees, opd timing, and consultation fees', function () {
    $admin = User::where('email', 'admin@apollocare.in')->first();
    $apollo = Hospital::where('code', 'ACH-01')->first();
    TenantManager::setActiveHospital($apollo);

    $response = $this->actingAs($admin)
        ->withSession(['active_hospital_id' => $apollo->id])
        ->get('/doctor-charts');

    $response->assertStatus(200);
    $response->assertSee('Hospital-Wise Doctor Charts');
    $response->assertSee('Dr. Ananya Sen');
    $response->assertSee('DM (Cardiology, AIIMS Delhi)');
    $response->assertSee('1,000.00');
    $response->assertSee('OPD Cabin 104');
    $response->assertSee('Print OPD Duty Chart');
});

test('hospital admin can add a new doctor chart with degrees, timing, cabin, and fees', function () {
    $admin = User::where('email', 'admin@apollocare.in')->first();
    $apollo = Hospital::where('code', 'ACH-01')->first();
    $bandra = Branch::where('code', 'APO-BAN-02')->first();
    TenantManager::setActiveHospital($apollo);

    $response = $this->actingAs($admin)
        ->withSession(['active_hospital_id' => $apollo->id])
        ->post('/doctor-charts', [
            'doctor_name' => 'Dr. Hemant Kulkarni, MS, DNB',
            'degrees' => 'MBBS, MS (ENT), DNB (Otorhinolaryngology)',
            'specialty' => 'Consultant ENT & Cochlear Implant Surgeon',
            'branch_id' => $bandra->id,
            'opd_days' => 'Mon, Wed, Fri',
            'start_time' => '10:00',
            'end_time' => '14:00',
            'room_number' => 'Room 304 - ENT Care',
            'consultation_fee' => 850.00,
            'follow_up_fee' => 400.00,
            'emergency_fee' => 1500.00,
            'max_patients_per_session' => 20,
            'status' => 'available',
        ]);

    $response->assertStatus(302);
    $response->assertSessionHas('success');

    $chart = DoctorChart::where('doctor_name', 'Dr. Hemant Kulkarni, MS, DNB')->first();
    expect($chart)->not->toBeNull()
        ->and($chart->degrees)->toBe('MBBS, MS (ENT), DNB (Otorhinolaryngology)')
        ->and((float) $chart->consultation_fee)->toBe(850.00)
        ->and($chart->branch_id)->toBe($bandra->id);
});

test('hospital admin can edit an existing doctor chart and update fees and timing', function () {
    $admin = User::where('email', 'admin@apollocare.in')->first();
    $apollo = Hospital::where('code', 'ACH-01')->first();
    TenantManager::setActiveHospital($apollo);

    $chart = DoctorChart::where('hospital_id', $apollo->id)->first();

    $response = $this->actingAs($admin)
        ->withSession(['active_hospital_id' => $apollo->id])
        ->put("/doctor-charts/{$chart->id}", [
            'doctor_name' => $chart->doctor_name,
            'degrees' => $chart->degrees,
            'specialty' => $chart->specialty,
            'branch_id' => $chart->branch_id,
            'department_id' => $chart->department_id,
            'opd_days' => 'Daily (Mon - Sat)',
            'start_time' => '08:30',
            'end_time' => '13:30',
            'room_number' => $chart->room_number,
            'consultation_fee' => 1350.00,
            'follow_up_fee' => 650.00,
            'status' => 'available',
        ]);

    $response->assertStatus(302);
    $response->assertSessionHas('success');

    $chart->refresh();
    expect((float) $chart->consultation_fee)->toBe(1350.00)
        ->and($chart->opd_days)->toBe('Daily (Mon - Sat)');
});

test('hospital admin can toggle doctor availability status between available and on_leave', function () {
    $admin = User::where('email', 'admin@apollocare.in')->first();
    $apollo = Hospital::where('code', 'ACH-01')->first();
    TenantManager::setActiveHospital($apollo);

    $chart = DoctorChart::where('hospital_id', $apollo->id)->where('status', 'available')->first();

    // Toggle to on_leave
    $response = $this->actingAs($admin)
        ->withSession(['active_hospital_id' => $apollo->id])
        ->post("/doctor-charts/{$chart->id}/toggle-status");

    $response->assertStatus(302);
    expect($chart->fresh()->status)->toBe('on_leave');

    // Toggle back to available
    $this->actingAs($admin)
        ->withSession(['active_hospital_id' => $apollo->id])
        ->post("/doctor-charts/{$chart->id}/toggle-status");

    expect($chart->fresh()->status)->toBe('available');
});

test('unauthorized clinical staff without admin permissions cannot add doctor charts', function () {
    $nurse = User::where('email', 'mary.fernandez@apollocare.in')->first();
    $apollo = Hospital::where('code', 'ACH-01')->first();
    $branch = Branch::where('hospital_id', $apollo->id)->first();
    TenantManager::setActiveHospital($apollo);

    $response = $this->actingAs($nurse)
        ->withSession(['active_hospital_id' => $apollo->id])
        ->post('/doctor-charts', [
            'doctor_name' => 'Unauthorized Physician',
            'degrees' => 'MBBS',
            'specialty' => 'General',
            'branch_id' => $branch->id,
            'opd_days' => 'Mon',
            'start_time' => '09:00',
            'end_time' => '12:00',
            'room_number' => 'Room 101',
            'consultation_fee' => 500,
            'status' => 'available',
        ]);

    // Nurse is redirected away by role middleware
    $response->assertStatus(302);
    expect(DoctorChart::where('doctor_name', 'Unauthorized Physician')->exists())->toBeFalse();
});

test('doctor charts printable reception view renders correctly', function () {
    $admin = User::where('email', 'admin@apollocare.in')->first();
    $apollo = Hospital::where('code', 'ACH-01')->first();
    TenantManager::setActiveHospital($apollo);

    $response = $this->actingAs($admin)
        ->withSession(['active_hospital_id' => $apollo->id])
        ->get('/doctor-charts/print');

    $response->assertStatus(200);
    $response->assertSee('Official OPD Doctor Duty Chart');
    $response->assertSee('Consultation Tariffs');
    $response->assertSee($apollo->name);
});

test('doctor schedule renders redesigned 7-day ribbon, chamber profile card, and metrics', function () {
    $doctor = User::where('email', 'dr.ananya@apollocare.in')->first();
    $apollo = Hospital::where('code', 'ACH-01')->first();
    TenantManager::setActiveHospital($apollo);

    $response = $this->actingAs($doctor)
        ->withSession(['active_hospital_id' => $apollo->id])
        ->get('/doctor-schedule');

    $response->assertStatus(200);
    $response->assertSee('OPD Chamber');
    $response->assertSee('Consultation Fee');
    $response->assertSee('Scheduled Patients');
    $response->assertSee('Completed OPD');
    $response->assertSee('Waiting Lounge');
    $response->assertSee('Clinical Cards');
    $response->assertSee('Fast Queue Table');
    $response->assertSee('Prescribe Medicine');
});

test('doctor can record vitals and save prescription from schedule', function () {
    $doctor = User::where('email', 'dr.ananya@apollocare.in')->first();
    $apollo = Hospital::where('code', 'ACH-01')->first();
    TenantManager::setActiveHospital($apollo);

    $appointment = Appointment::where('hospital_id', $apollo->id)
        ->where('doctor_id', $doctor->id)
        ->first();

    $response = $this->actingAs($doctor)
        ->withSession(['active_hospital_id' => $apollo->id])
        ->post('/doctor-schedule/prescription', [
            'appointment_id' => $appointment->id,
            'diagnosis' => 'Acute Viral Bronchitis & Pyrexia',
            'bp' => '120/80',
            'pulse' => '78',
            'temperature' => '99.4',
            'spo2' => '98',
            'weight_kg' => 65,
            'clinical_notes' => 'Chest clear, advised hydration and rest for 3 days.',
            'medicine_name' => 'Dolo 650 (Paracetamol 650mg)',
            'dosage' => '650mg',
            'frequency' => '1-0-1 (After Food)',
            'duration' => '3 Days',
            'instructions' => 'Take with warm water after meals',
        ]);

    $response->assertStatus(302);
    $response->assertSessionHas('success');

    $consultation = Consultation::where('appointment_id', $appointment->id)->first();
    expect($consultation)->not->toBeNull();
    expect($consultation->diagnosis)->toBe('Acute Viral Bronchitis & Pyrexia');
    expect($consultation->bp)->toBe('120/80');
    expect($consultation->pulse)->toBe('78');
    expect($consultation->temperature)->toBe('99.4');
    expect($consultation->spo2)->toBe('98');

    expect(Prescription::where('consultation_id', $consultation->id)->where('medicine_name', 'Dolo 650 (Paracetamol 650mg)')->exists())->toBeTrue();
});
