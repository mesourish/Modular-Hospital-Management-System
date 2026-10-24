<?php

use App\Core\Tenancy\TenantManager;
use App\Models\Appointment;
use App\Models\Branch;
use App\Models\Hospital;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->artisan('db:seed', ['--class' => 'ModularHospitalSeeder']);
});

test('public customer frontend renders hospital branding and doctor opd charts', function () {
    $apollo = Hospital::where('code', 'ACH-01')->first();
    TenantManager::setActiveHospital($apollo);

    $response = $this->get('/');

    $response->assertStatus(200);
    $response->assertSee('Apollo Hospitals Healthcare Network');
    $response->assertSee('Instant OPD Token Booking');
    $response->assertSee('Live Token Status Tracker');
    $response->assertSee('Doctor OPD Duty Schedules & Fees', false);
    $response->assertSee('Dr. Ananya Sen');
    $response->assertSee('1066');
});

test('patient can book online opd appointment and receive token confirmation', function () {
    $apollo = Hospital::where('code', 'ACH-01')->first();
    $branch = Branch::where('hospital_id', $apollo->id)->first();
    $doctor = User::where('email', 'dr.ananya@apollocare.in')->first();
    TenantManager::setActiveHospital($apollo);

    $response = $this->post('/portal/book-appointment', [
        'patient_name' => 'Vikram Malhotra',
        'patient_phone' => '9820554433',
        'patient_gender' => 'male',
        'patient_age' => 45,
        'patient_abha' => '91-1234-5678-9012',
        'branch_id' => $branch->id,
        'doctor_id' => $doctor->id,
        'appointment_date' => now()->toDateString(),
        'appointment_time' => '10:00:00',
        'symptoms' => 'Occasional palpitation and shortness of breath during morning walk.',
    ]);

    $response->assertStatus(302);
    $response->assertSessionHas('success');
    $response->assertSessionHas('booking_confirmation');

    $appointment = Appointment::where('hospital_id', $apollo->id)
        ->where('doctor_id', $doctor->id)
        ->whereHas('patient', function ($q) {
            $q->where('phone', '9820554433');
        })
        ->first();

    expect($appointment)->not->toBeNull();
    expect($appointment->status)->toBe('scheduled');
    expect($appointment->token_number)->toBeGreaterThanOrEqual(1);
});

test('patient can track live token queue by mobile number', function () {
    $apollo = Hospital::where('code', 'ACH-01')->first();
    TenantManager::setActiveHospital($apollo);

    $appointment = Appointment::where('hospital_id', $apollo->id)->with('patient')->first();
    $patientPhone = $appointment->patient->phone;

    $response = $this->get("/?track_query={$patientPhone}");

    $response->assertStatus(200);
    $response->assertSee('Matching Tokens');
    $response->assertSee($appointment->patient->name);
    $response->assertSee("Token #{$appointment->token_number}");
});

test('online booking is prevented when disabled in settings', function () {
    $apollo = Hospital::where('code', 'ACH-01')->first();
    $branch = Branch::where('hospital_id', $apollo->id)->first();
    $doctor = User::where('email', 'dr.ananya@apollocare.in')->first();
    TenantManager::setActiveHospital($apollo);

    // Disable online booking in settings
    SystemSetting::set('allow_online_booking', '0', $branch->id, $apollo->id, 'frontend', 'boolean');

    $response = $this->post('/portal/book-appointment', [
        'patient_name' => 'Kavita Roy',
        'patient_phone' => '9820998877',
        'patient_gender' => 'female',
        'patient_age' => 38,
        'branch_id' => $branch->id,
        'doctor_id' => $doctor->id,
        'appointment_date' => now()->toDateString(),
        'appointment_time' => '11:00:00',
        'symptoms' => 'Fever and cold',
    ]);

    $response->assertStatus(302);
    $response->assertSessionHas('error');
});

test('super admin can access and update platform settings', function () {
    $superAdmin = User::where('email', 'superadmin@medisystem.io')->first();

    $response = $this->actingAs($superAdmin)->get('/settings');
    $response->assertStatus(200);
    $response->assertSee('System Settings');
    $response->assertSee('Customer Portal & Hero', false);

    // Update settings
    $postResponse = $this->actingAs($superAdmin)->post('/settings', [
        'site_name' => 'Apollo Global Super-Specialty Healthcare',
        'frontend_hero_title' => 'Redefining Clinical Precision Across India',
        'emergency_hotline' => '1066 / +91 22 9999 8888',
        'announcement_enabled' => '1',
        'announcement_text' => 'National Health Award Winner 2026',
        'allow_online_booking' => '1',
        'show_doctor_fees' => '1',
    ]);

    $postResponse->assertStatus(302);
    $postResponse->assertSessionHas('success');

    expect(SystemSetting::get('site_name'))->toBe('Apollo Global Super-Specialty Healthcare');
    expect(SystemSetting::get('frontend_hero_title'))->toBe('Redefining Clinical Precision Across India');
    expect(SystemSetting::get('emergency_hotline'))->toBe('1066 / +91 22 9999 8888');
});

test('branch head can customize branch-specific settings and notices', function () {
    $admin = User::where('email', 'admin@apollocare.in')->first();
    $apollo = Hospital::where('code', 'ACH-01')->first();
    $bandraBranch = Branch::where('code', 'APO-BAN-02')->first();
    TenantManager::setActiveHospital($apollo);

    $postResponse = $this->actingAs($admin)
        ->withSession(['active_hospital_id' => $apollo->id])
        ->post('/settings', [
            'scope_hospital_id' => $apollo->id,
            'scope_branch_id' => $bandraBranch->id,
            'branch_notice' => 'Bandra Branch: 24-Hour Digital X-Ray & Cardiac TMT Available.',
        ]);

    $postResponse->assertStatus(302);
    $postResponse->assertSessionHas('success');

    // Verify branch-specific override
    $notice = SystemSetting::get('branch_notice', null, $bandraBranch->id, $apollo->id);
    expect($notice)->toBe('Bandra Branch: 24-Hour Digital X-Ray & Cardiac TMT Available.');
});

test('super admin can view organization chart with full relation hierarchy', function () {
    $superAdmin = User::where('email', 'superadmin@medisystem.io')->first();

    $response = $this->actingAs($superAdmin)->get('/superadmin/organization-chart');

    $response->assertStatus(200);
    $response->assertSee('Organizational Hierarchy');
    $response->assertSee('Platform Apex');
    $response->assertSee('Apollo Care Flagship Hospital');
    $response->assertSee('Bandra West');
    $response->assertSee('Coronary Intensive Care');
    $response->assertSee('Dr. Ananya Sen');
});

test('organization chart data endpoint returns json hierarchy tree', function () {
    $superAdmin = User::where('email', 'superadmin@medisystem.io')->first();

    $response = $this->actingAs($superAdmin)->get('/superadmin/organization-chart/data');

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'name',
        'title',
        'type',
        'children' => [
            '*' => [
                'name',
                'code',
                'title',
                'type',
                'children' => [
                    '*' => [
                        'name',
                        'code',
                        'title',
                        'children',
                    ],
                ],
            ],
        ],
    ]);
});

test('clinical staff without admin rights cannot access settings or organization chart', function () {
    $nurse = User::where('email', 'mary.fernandez@apollocare.in')->first();
    $apollo = Hospital::where('code', 'ACH-01')->first();
    TenantManager::setActiveHospital($apollo);

    $responseSettings = $this->actingAs($nurse)
        ->withSession(['active_hospital_id' => $apollo->id])
        ->get('/settings');
    $responseSettings->assertStatus(302);

    $responseOrgChart = $this->actingAs($nurse)
        ->withSession(['active_hospital_id' => $apollo->id])
        ->get('/superadmin/organization-chart');
    $responseOrgChart->assertStatus(302);
});
