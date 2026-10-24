<?php

use App\Core\Tenancy\TenantManager;
use App\Models\Admission;
use App\Models\Ambulance;
use App\Models\AmbulanceTrip;
use App\Models\Appointment;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Hospital;
use App\Models\HospitalConstant;
use App\Models\HospitalModule;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\SubDepartment;
use App\Models\SystemSetting;
use App\Models\User;
use Database\Seeders\ModularHospitalSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(ModularHospitalSeeder::class);
});

/*
|--------------------------------------------------------------------------
| 1. Cross-Tenant Data Isolation & Penetration Defense
|--------------------------------------------------------------------------
*/

test('cross tenant penetration defense: tenant A user cannot view or query tenant B patients', function () {
    $hospitalA = Hospital::where('code', 'ACH-01')->first();

    // Create an isolated third hospital (Tenant C)
    $hospitalC = Hospital::create([
        'name' => 'Max Healthcare Super Specialty',
        'code' => 'MAX-DEL',
        'slug' => 'max-healthcare-delhi',
        'is_active' => true,
    ]);

    $branchC = Branch::create([
        'hospital_id' => $hospitalC->id,
        'name' => 'Max Saket Main Wing',
        'code' => 'MAX-SAK',
        'city' => 'New Delhi',
        'is_main_branch' => true,
        'is_active' => true,
    ]);

    // Create a private patient in Hospital C
    $patientC = Patient::create([
        'hospital_id' => $hospitalC->id,
        'mrn' => 'MRN-MAX-9999',
        'first_name' => 'Confidential',
        'last_name' => 'VipPatient',
        'phone' => '+91 99999 00000',
    ]);

    // Log in as Doctor from Hospital A
    $doctorA = User::where('email', 'dr.ananya@apollocare.in')->first();
    TenantManager::setActiveHospital($hospitalA);

    // Attempt to access admissions / patients
    $response = $this->actingAs($doctorA)->get('/admissions');
    $response->assertStatus(200);
    $response->assertDontSee('MRN-MAX-9999');
    $response->assertDontSee('VipPatient');

    // Global TenantScope verification
    $queriedPatients = Patient::all();
    expect($queriedPatients->pluck('id'))->not->toContain($patientC->id);
});

/*
|--------------------------------------------------------------------------
| 2. Privilege Escalation Defense
|--------------------------------------------------------------------------
*/

test('privilege escalation defense: non-superadmin staff cannot access platform governance', function () {
    $doctor = User::where('email', 'dr.ananya@apollocare.in')->first();
    $nurse = User::where('email', 'mary.fernandez@apollocare.in')->first();
    $pharmacist = User::where('email', 'pooja.pharmacy@apollocare.in')->first();

    foreach ([$doctor, $nurse, $pharmacist] as $staff) {
        $response = $this->actingAs($staff)->get('/superadmin/hospitals');
        // EnsureRoleAllowed intercepts and redirects non-superadmin with error alert
        $response->assertStatus(302);
        $response->assertSessionHas('error');

        $response = $this->actingAs($staff)->get('/superadmin/users');
        $response->assertStatus(302);
        $response->assertSessionHas('error');

        $response = $this->actingAs($staff)->get('/superadmin/subscriptions');
        $response->assertStatus(302);
        $response->assertSessionHas('error');
    }
});

/*
|--------------------------------------------------------------------------
| 3. Module Guard Penetration Defense
|--------------------------------------------------------------------------
*/

test('module guard defense: accessing a disabled module returns 403 forbidden', function () {
    $hospital = Hospital::where('code', 'ACH-01')->first();
    $doctor = User::where('email', 'dr.ananya@apollocare.in')->first();

    // Explicitly disable blood_bank module for Apollo
    HospitalModule::where('hospital_id', $hospital->id)
        ->where('module_key', 'blood_bank')
        ->update(['is_enabled' => false]);

    TenantManager::setActiveHospital($hospital);

    $response = $this->actingAs($doctor)->get('/blood-bank');
    $response->assertStatus(403);
});

/*
|--------------------------------------------------------------------------
| 4. System "Clear All Data" Factory Reset Authorization & Execution
|--------------------------------------------------------------------------
*/

test('clear all data security: unauthorized staff cannot trigger factory reset', function () {
    $doctor = User::where('email', 'dr.ananya@apollocare.in')->first();

    $response = $this->actingAs($doctor)->post('/settings/clear-all-data', [
        'password' => 'password',
        'confirm_phrase' => 'CLEAR_ALL_DATA',
    ]);

    // Blocked by EnsureRoleAllowed middleware
    $response->assertStatus(302);
    $response->assertSessionHas('error');
    expect(Patient::count())->toBeGreaterThan(0);
});

test('clear all data security: admin with incorrect password or confirmation is rejected', function () {
    $admin = User::where('email', 'admin@apollocare.in')->first();

    // 1. Wrong Password
    $response = $this->actingAs($admin)->post('/settings/clear-all-data', [
        'password' => 'wrong_password_attempt',
        'confirm_phrase' => 'CLEAR_ALL_DATA',
    ]);
    $response->assertSessionHas('error');
    expect(Patient::count())->toBeGreaterThan(0);

    // 2. Wrong Confirmation phrase
    $response = $this->actingAs($admin)->post('/settings/clear-all-data', [
        'password' => 'password',
        'confirm_phrase' => 'NOT_CONFIRMED',
    ]);
    $response->assertSessionHas('error');
    expect(Patient::count())->toBeGreaterThan(0);
});

test('clear all data execution: wipes operational/transactional data but strictly keeps minimal running baseline', function () {
    $admin = User::where('email', 'admin@apollocare.in')->first();
    $hospital = Hospital::where('code', 'ACH-01')->first();

    // Verify records exist before reset
    expect(Patient::count())->toBeGreaterThan(0);
    expect(Appointment::count())->toBeGreaterThan(0);
    expect(Admission::count())->toBeGreaterThan(0);
    expect(Invoice::count())->toBeGreaterThan(0);
    expect(AmbulanceTrip::count())->toBeGreaterThan(0);

    // Execute authorized Clear All Data
    $response = $this->actingAs($admin)->post('/settings/clear-all-data', [
        'password' => 'password',
        'confirm_phrase' => 'CLEAR_ALL_DATA',
        'scope_hospital_id' => $hospital->id,
    ]);

    $response->assertRedirect('/settings');
    $response->assertSessionHas('success');

    // 1. Transactional/clinical tables MUST be cleared
    TenantManager::setOverrideHospitalId($hospital->id);
    expect(Patient::count())->toBe(0);
    expect(Appointment::count())->toBe(0);
    expect(Admission::count())->toBe(0);
    expect(Invoice::count())->toBe(0);
    expect(AmbulanceTrip::count())->toBe(0);

    // 2. Minimal baseline to run the software MUST remain intact:
    // Core Hospital & Branches
    expect(Hospital::count())->toBeGreaterThan(0);
    expect(Branch::count())->toBeGreaterThan(0);

    // Core staff users still exist and can authenticate
    $staffAdmin = User::where('email', 'admin@apollocare.in')->first();
    expect($staffAdmin)->not->toBeNull();
    expect(Hash::check('password', $staffAdmin->password))->toBeTrue();

    $staffDoctor = User::where('email', 'dr.ananya@apollocare.in')->first();
    expect($staffDoctor)->not->toBeNull();

    // Hospital modules configuration
    expect(HospitalModule::count())->toBeGreaterThan(0);

    // Master Constants, Departments, Sub-Departments, Designations
    expect(Department::count())->toBeGreaterThan(0);
    expect(SubDepartment::count())->toBeGreaterThan(0);
    expect(Designation::count())->toBeGreaterThan(0);
    expect(HospitalConstant::count())->toBeGreaterThan(0);

    // System Settings
    expect(SystemSetting::count())->toBeGreaterThan(0);

    // Facilities catalog (Ambulances and Wards) remain available
    expect(Ambulance::count())->toBeGreaterThan(0);
    expect(Ambulance::where('status', 'available')->count())->toBe(Ambulance::count());

    TenantManager::setOverrideHospitalId(null);
});

/*
|--------------------------------------------------------------------------
| 5. Fleet (Ambulance) Scheduler & Driver Assignment Based on Customer Need
|--------------------------------------------------------------------------
*/

test('ambulance scheduler assigns any ambulance to any driver based on customer requirements', function () {
    $admin = User::where('email', 'admin@apollocare.in')->first();
    $hospital = Hospital::where('code', 'ACH-01')->first();
    $ambulance = Ambulance::where('hospital_id', $hospital->id)->first();

    TenantManager::setActiveHospital($hospital);

    $scheduledTime = now()->addDays(2)->format('Y-m-d H:i:s');

    $response = $this->actingAs($admin)->post('/ambulances/schedule', [
        'ambulance_id' => $ambulance->id,
        'driver_name' => 'Sukhwinder Singh (Special Pilot)',
        'driver_phone' => '+91 98200 77112',
        'patient_name' => 'Vikram Singhania',
        'patient_phone' => '+91 98111 22334',
        'pickup_address' => 'Flat 402, Sea Green Apts, Worli Sea Face, Mumbai',
        'destination_hospital' => 'Apollo Care Cardiac CCU Bay',
        'scheduled_for' => $scheduledTime,
        'emergency_category' => 'Scheduled Inter-Facility ALS Transfer',
        'customer_notes' => 'Patient requires continuous BiPAP ventilation, 5L/min O2, and hydraulic wheelchair ramp.',
        'fare_amount' => 2200.00,
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    // Verify scheduled trip was created
    $scheduledTrip = AmbulanceTrip::where('patient_name', 'Vikram Singhania')->first();
    expect($scheduledTrip)->not->toBeNull();
    expect($scheduledTrip->status)->toBe('scheduled');
    expect($scheduledTrip->assigned_driver_name)->toBe('Sukhwinder Singh (Special Pilot)');
    expect($scheduledTrip->assigned_driver_phone)->toBe('+91 98200 77112');
    expect($scheduledTrip->customer_notes)->toContain('continuous BiPAP ventilation');

    // Test transition from scheduled to dispatched
    $response = $this->actingAs($admin)->post("/ambulances/trips/{$scheduledTrip->id}/status", [
        'status' => 'dispatched',
    ]);
    $response->assertRedirect();

    $scheduledTrip->refresh();
    expect($scheduledTrip->status)->toBe('dispatched');

    // Test completion of trip resets ambulance to available
    $response = $this->actingAs($admin)->post("/ambulances/trips/{$scheduledTrip->id}/status", [
        'status' => 'completed',
    ]);
    $response->assertRedirect();

    $scheduledTrip->refresh();
    $ambulance->refresh();
    expect($scheduledTrip->status)->toBe('completed');
    expect($ambulance->status)->toBe('available');
});

/*
|--------------------------------------------------------------------------
| 6. Master Constants, Departments, Sub-Departments & Designations
|--------------------------------------------------------------------------
*/

test('master constants management allows super admin and branch admin to configure clinical taxonomies', function () {
    $admin = User::where('email', 'admin@apollocare.in')->first();
    $hospital = Hospital::where('code', 'ACH-01')->first();

    TenantManager::setActiveHospital($hospital);

    // 1. View constants index workspace
    $response = $this->actingAs($admin)->get('/constants');
    $response->assertStatus(200);
    $response->assertSee('Master Constants');
    $response->assertSee('Clinical Taxonomy');
    $response->assertSee('Departments');
    $response->assertSee('Sub-Departments');
    $response->assertSee('Designations');
    $response->assertSee('System Constants');

    // 2. Create a new Department
    $response = $this->actingAs($admin)->post('/constants/departments', [
        'hospital_id' => $hospital->id,
        'name' => 'Cardiothoracic Vascular Surgery (CTVS)',
        'code' => 'CTVS',
        'type' => 'clinical',
        'description' => 'Heart and lung surgeries, coronary artery bypass grafting.',
    ]);
    $response->assertRedirect();
    $dept = Department::where('code', 'CTVS')->first();
    expect($dept)->not->toBeNull();

    // 3. Create a Sub-Department
    $response = $this->actingAs($admin)->post('/constants/sub-departments', [
        'hospital_id' => $hospital->id,
        'department_id' => $dept->id,
        'name' => 'Minimally Invasive Cardiac Suite',
        'code' => 'MICS-SUITE',
        'description' => 'Keyhole cardiac valve and bypass procedures.',
    ]);
    $response->assertRedirect();
    expect(SubDepartment::where('code', 'MICS-SUITE')->exists())->toBeTrue();

    // 4. Create a Designation
    $response = $this->actingAs($admin)->post('/constants/designations', [
        'hospital_id' => $hospital->id,
        'department_id' => $dept->id,
        'title' => 'Chief Perfusionist & ECMO Specialist',
        'min_salary' => 80000,
        'max_salary' => 160000,
        'description' => 'Cardiopulmonary bypass and ECMO console operation.',
    ]);
    $response->assertRedirect();
    expect(Designation::where('title', 'Chief Perfusionist & ECMO Specialist')->exists())->toBeTrue();

    // 5. Store Custom System Constant
    $response = $this->actingAs($admin)->post('/constants/system-constants', [
        'hospital_id' => $hospital->id,
        'category' => 'ambulance_type',
        'key' => 'bariatric_heavy_support',
        'name' => 'Bariatric Critical Care Transport',
        'code' => 'BARIATRIC_ALS',
        'badge_color' => 'danger',
        'sort_order' => 5,
        'description' => 'Specialized heavy-duty stretcher rated up to 400kg with winch lift.',
    ]);
    $response->assertRedirect();
    $constant = HospitalConstant::where('key', 'bariatric_heavy_support')->first();
    expect($constant)->not->toBeNull();
    expect($constant->code)->toBe('BARIATRIC_ALS');

    // 6. Toggle Constant status
    $response = $this->actingAs($admin)->post("/constants/{$constant->id}/toggle");
    $response->assertRedirect();
    $constant->refresh();
    expect($constant->is_active)->toBeFalse();
});
