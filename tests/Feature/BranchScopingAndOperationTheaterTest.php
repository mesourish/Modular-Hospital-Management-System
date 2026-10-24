<?php

namespace Tests\Feature;

use App\Core\Tenancy\TenantManager;
use App\Models\Branch;
use App\Models\Hospital;
use App\Models\OperationTheater;
use App\Models\OtMedicationLog;
use App\Models\OtSurgery;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->artisan('db:seed', ['--class' => 'ModularHospitalSeeder']);
});

test('super admin has global cross-branch visibility across all hospital branches', function () {
    $superAdmin = User::where('role', 'super_admin')->first();
    $apollo = Hospital::where('code', 'ACH-01')->first();
    TenantManager::setActiveHospital($apollo);

    // 1. Branches & Financials
    $response = $this->actingAs($superAdmin)
        ->withSession(['active_hospital_id' => $apollo->id])
        ->get('/branches');

    $response->assertStatus(200);
    $response->assertSee('Apollo Care Flagship Hospital - South Mumbai');
    $response->assertSee('Apollo Cardiac & Specialty Clinic - Bandra West');
    $response->assertSee('Apollo Outpatient & Diagnostic Center - Thane');
    $response->assertSee('Apollo Advanced Imaging & Diagnostics - Pune');

    // 2. Doctor Schedule: Cross-branch visibility
    $scheduleResponse = $this->actingAs($superAdmin)
        ->withSession(['active_hospital_id' => $apollo->id])
        ->get('/doctor-schedule');

    $scheduleResponse->assertStatus(200);
    $scheduleResponse->assertSee('All Branches (Consolidated)');

    // 3. Operation Theater: Cross-branch OT suites
    $otResponse = $this->actingAs($superAdmin)
        ->withSession(['active_hospital_id' => $apollo->id])
        ->get('/operation-theater');

    $otResponse->assertStatus(200);
    $otResponse->assertSee('Super Admin Cross-Branch Visibility');
    $otResponse->assertSee('OT-MUM-01');
    $otResponse->assertSee('OT-BND-01');
    $otResponse->assertSee('OT-THN-01');
    $otResponse->assertSee('OT-PUN-01');
});

test('branch admin is strictly scoped to their assigned branch only', function () {
    $bandraAdmin = User::where('email', 'bandra.head@apollocare.in')->first();
    $apollo = Hospital::where('code', 'ACH-01')->first();
    $bandraBranch = Branch::where('code', 'APO-BAN-02')->first();
    TenantManager::setActiveHospital($apollo);

    expect($bandraAdmin->isRestrictedToBranch())->toBeTrue();
    expect($bandraAdmin->branch_id)->toBe($bandraBranch->id);

    // 1. Branch Financials: Only Bandra branch data is visible
    $branchesResponse = $this->actingAs($bandraAdmin)
        ->withSession(['active_hospital_id' => $apollo->id])
        ->get('/branches');

    $branchesResponse->assertStatus(200);
    $branchesResponse->assertSee('Apollo Cardiac & Specialty Clinic - Bandra West');
    // Cannot see financial matrices of other branches
    $branchesResponse->assertDontSee('Apollo Outpatient & Diagnostic Center - Thane');
    $branchesResponse->assertDontSee('Apollo Advanced Imaging & Diagnostics - Pune');

    // 2. Doctor Schedule: Locked to Bandra branch
    $scheduleResponse = $this->actingAs($bandraAdmin)
        ->withSession(['active_hospital_id' => $apollo->id])
        ->get('/doctor-schedule');

    $scheduleResponse->assertStatus(200);
    $scheduleResponse->assertSee($bandraBranch->name);
    $scheduleResponse->assertDontSee('All Branches (Consolidated)');

    // 3. Operation Theater: Locked to Bandra OT suites and surgeries
    $otResponse = $this->actingAs($bandraAdmin)
        ->withSession(['active_hospital_id' => $apollo->id])
        ->get('/operation-theater');

    $otResponse->assertStatus(200);
    $otResponse->assertSee('Locked to Assigned Branch');
    $otResponse->assertSee('OT-BND-01');
    // Cannot see South Mumbai or Pune suites
    $otResponse->assertDontSee('Cardiac & Neuro Modular OT 1');
    $otResponse->assertDontSee('Ophthalmic Micro-Surgery Suite');
});

test('branch admin cannot create new global hospital branches', function () {
    $bandraAdmin = User::where('email', 'bandra.head@apollocare.in')->first();
    $apollo = Hospital::where('code', 'ACH-01')->first();
    TenantManager::setActiveHospital($apollo);

    $response = $this->actingAs($bandraAdmin)
        ->withSession(['active_hospital_id' => $apollo->id])
        ->post('/branches', [
            'name' => 'Illegal Unauthorized Branch',
            'code' => 'ILLEGAL-01',
            'city' => 'Goa',
            'state' => 'Goa',
        ]);

    $response->assertSessionHas('error');
    expect(Branch::where('code', 'ILLEGAL-01')->exists())->toBeFalse();
});

test('branch admin is blocked from modifying or logging meds for surgeries of other branches', function () {
    $bandraAdmin = User::where('email', 'bandra.head@apollocare.in')->first();
    $apollo = Hospital::where('code', 'ACH-01')->first();
    $mumbaiSurgery = OtSurgery::where('surgery_code', 'SURG-2026-1042')->first(); // South Mumbai CABG
    TenantManager::setActiveHospital($apollo);

    // 1. Blocked from viewing surgery case sheet of South Mumbai
    $viewResponse = $this->actingAs($bandraAdmin)
        ->withSession(['active_hospital_id' => $apollo->id])
        ->get('/operation-theater/surgeries/'.$mumbaiSurgery->id);

    $viewResponse->assertRedirect(route('ot.index'));
    $viewResponse->assertSessionHas('error', 'Access restricted to your assigned branch.');

    // 2. Blocked from logging meds for surgery of another branch
    $medResponse = $this->actingAs($bandraAdmin)
        ->withSession(['active_hospital_id' => $apollo->id])
        ->post('/operation-theater/medications', [
            'ot_surgery_id' => $mumbaiSurgery->id,
            'medicine_name' => 'Unauthorized Drug IV',
            'quantity_used' => 1,
            'unit' => 'Vial',
            'administration_stage' => 'intra_op',
        ]);

    $medResponse->assertSessionHas('error', 'Unauthorized to modify surgeries outside your branch.');
    expect(OtMedicationLog::where('medicine_name', 'Unauthorized Drug IV')->exists())->toBeFalse();

    // 3. Blocked from modifying surgery status
    $statusResponse = $this->actingAs($bandraAdmin)
        ->withSession(['active_hospital_id' => $apollo->id])
        ->post('/operation-theater/surgeries/'.$mumbaiSurgery->id.'/status', [
            'status' => 'completed',
        ]);

    $statusResponse->assertSessionHas('error', 'Unauthorized to modify surgeries outside your branch.');
    expect($mumbaiSurgery->fresh()->status)->toBe('in_progress');
});

test('operation theater configuration can be created with equipment and sterilization details', function () {
    $superAdmin = User::where('role', 'super_admin')->first();
    $apollo = Hospital::where('code', 'ACH-01')->first();
    $bandraBranch = Branch::where('code', 'APO-BAN-02')->first();
    TenantManager::setActiveHospital($apollo);

    $response = $this->actingAs($superAdmin)
        ->withSession(['active_hospital_id' => $apollo->id])
        ->post('/operation-theater/theaters', [
            'branch_id' => $bandraBranch->id,
            'name' => 'Advanced Robotic Surgery Suite 3',
            'code' => 'OT-BND-03',
            'type' => 'major_modular',
            'floor' => '3rd Floor - Surgical Pavilion',
            'status' => 'available',
            'anesthesia_workstation' => 'Mindray BeneFusion eSeries Workstation',
            'c_arm_model' => 'Siemens Cios Spin 3D C-Arm',
            'heart_lung_machine' => null,
            'laparoscopic_tower' => 'Stryker 1688 4K Advanced Imaging System',
            'hepa_filter_status' => 'certified_active',
            'air_pressure_type' => 'positive_laminar_flow',
        ]);

    $response->assertSessionHas('success');
    expect(OperationTheater::where('code', 'OT-BND-03')->exists())->toBeTrue();

    $newOt = OperationTheater::where('code', 'OT-BND-03')->first();
    expect($newOt->air_pressure_type)->toBe('positive_laminar_flow');
    expect($newOt->hepa_filter_status)->toBe('certified_active');
    expect($newOt->branch_id)->toBe($bandraBranch->id);
});

test('patient surgery can be scheduled and specific medications logged with exact quantities and stages', function () {
    $superAdmin = User::where('role', 'super_admin')->first();
    $apollo = Hospital::where('code', 'ACH-01')->first();
    $bandraBranch = Branch::where('code', 'APO-BAN-02')->first();
    $bandraOt = OperationTheater::where('code', 'OT-BND-01')->first();
    $patient = Patient::where('hospital_id', $apollo->id)->first();
    $surgeon = User::where('role', 'doctor')->first();
    TenantManager::setActiveHospital($apollo);

    // 1. Schedule a Surgery
    $surgeryResponse = $this->actingAs($superAdmin)
        ->withSession(['active_hospital_id' => $apollo->id])
        ->post('/operation-theater/surgeries', [
            'branch_id' => $bandraBranch->id,
            'operation_theater_id' => $bandraOt->id,
            'patient_id' => $patient->id,
            'lead_surgeon_id' => $surgeon->id,
            'surgery_name' => 'Arthroscopic Anterior Cruciate Ligament (ACL) Reconstruction',
            'surgery_category' => 'major',
            'pre_op_diagnosis' => 'Complete ACL tear with medial meniscus bucket-handle tear',
            'scheduled_start_time' => now()->addDays(2)->setTime(9, 0)->toDateTimeString(),
            'scheduled_end_time' => now()->addDays(2)->setTime(11, 0)->toDateTimeString(),
            'anesthesia_type' => 'spinal_epidural',
        ]);

    $surgeryResponse->assertSessionHas('success');
    $surgery = OtSurgery::where('surgery_name', 'Arthroscopic Anterior Cruciate Ligament (ACL) Reconstruction')->first();
    expect($surgery)->not->toBeNull();
    expect($surgery->status)->toBe('scheduled');

    // 2. Log exact medicines used during the surgery
    $med1Response = $this->actingAs($superAdmin)
        ->withSession(['active_hospital_id' => $apollo->id])
        ->post('/operation-theater/medications', [
            'ot_surgery_id' => $surgery->id,
            'medicine_name' => 'Bupivacaine Heavy 0.5% 4ml',
            'quantity_used' => 1.5,
            'unit' => 'Ampoule',
            'administration_stage' => 'induction',
            'batch_number' => 'BAT-BUP-992',
            'cost_per_unit' => 95.00,
            'notes' => 'Spinal block at L3-L4 interspace with 25G needle',
        ]);

    $med1Response->assertSessionHas('success');

    $med2Response = $this->actingAs($superAdmin)
        ->withSession(['active_hospital_id' => $apollo->id])
        ->post('/operation-theater/medications', [
            'ot_surgery_id' => $surgery->id,
            'medicine_name' => 'Tranexamic Acid 500mg IV',
            'quantity_used' => 2.0,
            'unit' => 'Ampoule',
            'administration_stage' => 'intra_op',
            'batch_number' => 'BAT-TXA-410',
            'cost_per_unit' => 130.00,
            'notes' => 'Administered prior to joint arthroscopy to control bleeding',
        ]);

    $med2Response->assertSessionHas('success');

    // Verify database ledger records
    $logs = OtMedicationLog::where('ot_surgery_id', $surgery->id)->get();
    expect($logs)->toHaveCount(2);

    $bupLog = $logs->firstWhere('medicine_name', 'Bupivacaine Heavy 0.5% 4ml');
    expect((float) $bupLog->quantity_used)->toBe(1.5);
    expect($bupLog->unit)->toBe('Ampoule');
    expect($bupLog->administration_stage)->toBe('induction');
    expect((float) $bupLog->total_cost)->toBe(142.50);

    $txaLog = $logs->firstWhere('medicine_name', 'Tranexamic Acid 500mg IV');
    expect((float) $txaLog->quantity_used)->toBe(2.0);
    expect((float) $txaLog->total_cost)->toBe(260.00);

    // 3. Update Surgery Status to In-Progress and Completed
    $statusResponse = $this->actingAs($superAdmin)
        ->withSession(['active_hospital_id' => $apollo->id])
        ->post('/operation-theater/surgeries/'.$surgery->id.'/status', [
            'status' => 'in_progress',
            'post_op_findings' => 'Hamstring autograft fixed with tightrope button. Solid graft stability.',
            'blood_units_transfused' => 0,
        ]);

    $statusResponse->assertSessionHas('success');
    expect($surgery->fresh()->status)->toBe('in_progress');
    expect($bandraOt->fresh()->status)->toBe('occupied_in_surgery');

    // 4. View Detailed Case Sheet and Itemized Medication Ledger
    $showResponse = $this->actingAs($superAdmin)
        ->withSession(['active_hospital_id' => $apollo->id])
        ->get('/operation-theater/surgeries/'.$surgery->id);

    $showResponse->assertStatus(200);
    $showResponse->assertSee($surgery->surgery_code);
    $showResponse->assertSee($patient->name);
    $showResponse->assertSee('Bupivacaine Heavy 0.5% 4ml');
    $showResponse->assertSee('Tranexamic Acid 500mg IV');
    $showResponse->assertSee('142.50');
    $showResponse->assertSee('260.00');
    $showResponse->assertSee('Positive Laminar Flow');
});

test('doctor schedule renders compressed space-managed table view with patient queue and vitals', function () {
    $doctor = User::where('email', 'dr.ananya@apollocare.in')->first();
    $apollo = Hospital::where('code', 'ACH-01')->first();
    TenantManager::setActiveHospital($apollo);

    $response = $this->actingAs($doctor)
        ->withSession(['active_hospital_id' => $apollo->id])
        ->get('/doctor-schedule');

    $response->assertStatus(200);
    // Space management: High density table elements & controls
    $response->assertSee('Centralized Doctor Schedule');
    $response->assertSee('scheduleTableSearch');
    $response->assertSee('appointmentsScheduleTable');
    $response->assertSee('Fast Queue Table');
    $response->assertSee('Prescribe Medicine');
});
