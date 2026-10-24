<?php

use App\Core\Tenancy\TenantManager;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\Hospital;
use App\Models\StaffAttendance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->artisan('db:seed', ['--class' => 'ModularHospitalSeeder']);
});

test('super admin dashboard displays segment-wise revenue breakdown chart and growth graph', function () {
    $superadmin = User::where('role', 'super_admin')->first();

    $response = $this->actingAs($superadmin)->get(route('superadmin.dashboard'));

    $response->assertStatus(200);
    $response->assertSee('Segment-Wise Revenue Analytics');
    $response->assertSee('Revenue Share by Service Segment');
    $response->assertSee('segmentRevenueDonutChart');
    $response->assertSee('segmentMonthlyGrowthChart');
});

test('workforce staff attendance can be filtered by branch and logged with biometric record', function () {
    $admin = User::where('email', 'admin@apollocare.in')->first();
    $apollo = Hospital::where('code', 'ACH-01')->first();
    TenantManager::setActiveHospital($apollo);

    $branch = Branch::where('hospital_id', $apollo->id)->first();
    $employee = Employee::where('hospital_id', $apollo->id)->first();

    // 1. View attendance desk
    $response = $this->actingAs($admin)
        ->withSession(['active_hospital_id' => $apollo->id])
        ->get('/workforce?branch_id='.$branch->id);

    $response->assertStatus(200);
    $response->assertSee('Branch Staff Attendance Records');
    $response->assertSee('Shift Timing');

    // 2. Post new attendance record
    $postResponse = $this->actingAs($admin)
        ->withSession(['active_hospital_id' => $apollo->id])
        ->post('/workforce/attendance', [
            'employee_id' => $employee->id,
            'branch_id' => $branch->id,
            'date' => now()->toDateString(),
            'clock_in_time' => '08:30:00',
            'clock_out_time' => '17:00:00',
            'status' => 'present',
            'work_shift' => 'morning',
            'remarks' => 'RFID Biometric Clock In',
        ]);

    $postResponse->assertStatus(302);
    $this->assertDatabaseHas('staff_attendances', [
        'hospital_id' => $apollo->id,
        'employee_id' => $employee->id,
        'status' => 'present',
        'work_shift' => 'morning',
    ]);
});

test('bulk verify staff clock in marks present and creates attendance records', function () {
    $admin = User::where('email', 'admin@apollocare.in')->first();
    $apollo = Hospital::where('code', 'ACH-01')->first();
    TenantManager::setActiveHospital($apollo);

    $branch = Branch::where('hospital_id', $apollo->id)->first();

    $response = $this->actingAs($admin)
        ->withSession(['active_hospital_id' => $apollo->id])
        ->post('/workforce/attendance/bulk', [
            'branch_id' => $branch->id,
            'date' => now()->toDateString(),
            'work_shift' => 'morning',
        ]);

    $response->assertStatus(302);
    expect(StaffAttendance::where('branch_id', $branch->id)->count())->toBeGreaterThan(0);
});

test('branch admin views statistics of each doctor how much earn for that branch', function () {
    $admin = User::where('email', 'admin@apollocare.in')->first();
    $apollo = Hospital::where('code', 'ACH-01')->first();
    TenantManager::setActiveHospital($apollo);

    $branch = Branch::where('hospital_id', $apollo->id)->where('is_main_branch', true)->first();

    $response = $this->actingAs($admin)
        ->withSession(['active_hospital_id' => $apollo->id])
        ->get('/branches?branch_id='.$branch->id);

    $response->assertStatus(200);
    $response->assertSee('Doctor Earnings');
    $response->assertSee('Doctor Payout (70%)');
    $response->assertSee('Branch Retained (30%)');
    $response->assertSee('Dr. Ananya Sen');
});

test('new facility can be created as standalone direct to super admin or connected to branch', function () {
    $admin = User::where('email', 'admin@apollocare.in')->first();
    $apollo = Hospital::where('code', 'ACH-01')->first();
    TenantManager::setActiveHospital($apollo);

    // 1. Create Standalone Pharmacy Facility
    $response1 = $this->actingAs($admin)
        ->withSession(['active_hospital_id' => $apollo->id])
        ->post('/branches', [
            'name' => 'Apollo Care Standalone Chemist Bandra',
            'code' => 'ACH-BANDRA-CHEM-01',
            'city' => 'Mumbai',
            'state' => 'Maharashtra',
            'pincode' => '400050',
            'facility_type' => 'pharmacy',
            'connection_mode' => 'standalone',
            'license_number' => 'DL-20B-MH-99441',
            'manager_name' => 'Pharmacist Alok Verma',
        ]);

    $response1->assertStatus(302);
    $this->assertDatabaseHas('branches', [
        'code' => 'ACH-BANDRA-CHEM-01',
        'facility_type' => 'pharmacy',
        'is_standalone' => true,
        'parent_branch_id' => null,
    ]);

    // Synchronized to pharmacy_stores
    $this->assertDatabaseHas('pharmacy_stores', [
        'code' => 'ACH-BANDRA-CHEM-01',
        'is_standalone' => true,
    ]);

    // 2. Create Standalone Blood Bank Unit
    $response2 = $this->actingAs($admin)
        ->withSession(['active_hospital_id' => $apollo->id])
        ->post('/branches', [
            'name' => 'Apollo Standalone Blood Bank Regional Hub',
            'code' => 'ACH-REG-BB-02',
            'city' => 'Navi Mumbai',
            'state' => 'Maharashtra',
            'facility_type' => 'blood_bank',
            'connection_mode' => 'standalone',
            'license_number' => 'BB-28C-MH-9912',
        ]);

    $response2->assertStatus(302);
    $this->assertDatabaseHas('branches', [
        'code' => 'ACH-REG-BB-02',
        'facility_type' => 'blood_bank',
        'is_standalone' => true,
    ]);
});

test('pharmacy store creation from pharmacy module supports standalone and branch connected options', function () {
    $pharmacist = User::where('role', 'pharmacist')->first();
    $apollo = Hospital::where('code', 'ACH-01')->first();
    TenantManager::setActiveHospital($apollo);

    $branch = Branch::where('hospital_id', $apollo->id)->first();

    // Create branch connected pharmacy outlet
    $response = $this->actingAs($pharmacist)
        ->withSession(['active_hospital_id' => $apollo->id])
        ->post('/pharmacy/stores', [
            'name' => 'Apollo Thane Branch IPD Pharmacy Counter',
            'code' => 'PHARM-THANE-IPD-02',
            'store_type' => 'ipd_pharmacy',
            'connection_mode' => 'branch_connected',
            'branch_id' => $branch->id,
            'license_number' => 'DL-20B-MH-88192',
            'manager_name' => 'Pharmacist Meena Rao',
        ]);

    $response->assertStatus(302);
    $this->assertDatabaseHas('pharmacy_stores', [
        'code' => 'PHARM-THANE-IPD-02',
        'branch_id' => $branch->id,
        'is_standalone' => false,
    ]);
});

test('operation theater page loads without SQL status column error', function () {
    $doctor = User::where('role', 'doctor')->first();
    $apollo = Hospital::where('code', 'ACH-01')->first();
    TenantManager::setActiveHospital($apollo);

    $response = $this->actingAs($doctor)
        ->withSession(['active_hospital_id' => $apollo->id])
        ->get('/operation-theater');

    $response->assertStatus(200);
    $response->assertSee('Operation Theater (OT) Suites');
    $response->assertDontSee('Unknown column');
});
