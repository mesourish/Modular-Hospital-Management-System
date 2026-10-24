<?php

use App\Models\RadiologyModality;
use App\Models\RadiologyOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->artisan('db:seed', ['--class' => 'ModularHospitalSeeder']);
});

test('top navigation renders user initials fallback when user avatar is null', function () {
    $user = User::where('email', 'dr.ananya@apollocare.in')->first();
    $user->update(['avatar' => null]);

    // Dr. Ananya Sen, MD, DM (Cardiologist) should yield initials AS
    expect($user->initials)->toBe('AS');

    $response = $this->actingAs($user)->get(route('doctor.schedule'));
    $response->assertStatus(200);

    // Assert that the avatar initials circle is rendered with AS
    $response->assertSee('topbar-avatar-circle', false);
    $response->assertSee('AS');

    // Assert that no bx-plus-medical baby carriage icon is rendered in the output
    $response->assertDontSee('bx-plus-medical');
});

test('top navigation renders avatar image when user avatar is set', function () {
    $user = User::where('email', 'dr.ananya@apollocare.in')->first();
    $user->update(['avatar' => 'assets/images/users/avatar-1.jpg']);

    $response = $this->actingAs($user)->get(route('doctor.schedule'));
    $response->assertStatus(200);

    // Assert avatar image is rendered with topbar-avatar-img
    $response->assertSee('topbar-avatar-img', false);
    $response->assertSee('assets/images/users/avatar-1.jpg');
});

test('initials attribute accurately handles titles and multi-part names', function () {
    $u1 = new User(['name' => 'Dr. Ananya Sen, MD, DM (Cardiologist)']);
    expect($u1->initials)->toBe('AS');

    $u2 = new User(['name' => 'Vikramaditya Rao']);
    expect($u2->initials)->toBe('VR');

    $u3 = new User(['name' => 'Super Admin']);
    expect($u3->initials)->toBe('SA');

    $u4 = new User(['name' => 'Dr. Kevin D’Souza, MEM']);
    expect($u4->initials)->toBe('KD');

    $u5 = new User(['name' => 'Pooja Nair']);
    expect($u5->initials)->toBe('PN');
});

test('radiology RIS index renders timetable schedule view and walk-in queue', function () {
    $doctor = User::where('email', 'dr.ananya@apollocare.in')->first();

    $response = $this->actingAs($doctor)->get(route('radiology.index'));
    $response->assertStatus(200);

    // Timetable & schedule view elements
    $response->assertSee('Modality Timetable Schedule');
    $response->assertSee('Modality Timetable Matrix');
    $response->assertSee('10:00 AM');
    $response->assertSee('High-Resolution Brain MRI with Contrast');

    // Walk-in queue elements
    $response->assertSee('Walk-in Customers');
    $response->assertSee('Reception Queue');
    $response->assertSee('RAD-W01');
    $response->assertSee('Dr. P. K. Mehta (City Care Clinic)');
});

test('staff can register new walk-in diagnostic customer with auto-generated token', function () {
    $doctor = User::where('email', 'dr.ananya@apollocare.in')->first();
    $modality = RadiologyModality::where('modality_code', 'CT')->first();

    $response = $this->actingAs($doctor)->post(route('radiology.walkin.store'), [
        'patient_type' => 'new',
        'walkin_name' => 'Mahesh R. Patil',
        'walkin_phone' => '9819001122',
        'walkin_gender' => 'male',
        'modality_id' => $modality->id,
        'procedure_name' => 'High-Resolution Chest CT (HRCT)',
        'referring_doctor_name' => 'Dr. S. K. Deshmukh (Apex Clinic)',
        'slot_time' => '03:00 PM',
        'priority' => 'urgent',
        'notes' => 'Persistent fever and cough, rule out ground-glass opacities.',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $createdOrder = RadiologyOrder::where('procedure_name', 'High-Resolution Chest CT (HRCT)')->first();
    expect($createdOrder)->not->toBeNull();
    expect($createdOrder->is_walkin)->toBeTrue();
    expect($createdOrder->token_number)->toBe('RAD-W03');
    expect($createdOrder->referring_doctor_name)->toBe('Dr. S. K. Deshmukh (Apex Clinic)');
    expect($createdOrder->slot_time)->toBe('03:00 PM');
    expect($createdOrder->patient->first_name)->toBe('Mahesh');
});

test('staff can update radiology order status from scheduled to performed', function () {
    $doctor = User::where('email', 'dr.ananya@apollocare.in')->first();
    $order = RadiologyOrder::where('status', 'scheduled')->first();

    $response = $this->actingAs($doctor)->post(route('radiology.orders.status', $order), [
        'status' => 'performed',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $order->refresh();
    expect($order->status)->toBe('performed');
    expect($order->performed_at)->not->toBeNull();
});

test('radiologist can sign and verify diagnostic report for performed scan', function () {
    $radiologist = User::where('role', 'radiologist')->first();
    $order = RadiologyOrder::where('status', 'scheduled')->first();
    $order->update(['status' => 'performed', 'performed_at' => now()]);

    $response = $this->actingAs($radiologist)->post(route('radiology.reports.store'), [
        'radiology_order_id' => $order->id,
        'radiologist_id' => $radiologist->id,
        'findings' => 'Clear lung fields bilaterally. Costophrenic angles are acute.',
        'impression' => 'No focal cardiopulmonary abnormality detected.',
        'recommendations' => 'Routine follow-up.',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $order->refresh();
    expect($order->status)->toBe('reported');
    expect($order->report)->not->toBeNull();
    expect($order->report->impression)->toBe('No focal cardiopulmonary abnormality detected.');
});
