<?php

use App\Models\Admission;
use App\Models\Ambulance;
use App\Models\Bed;
use App\Models\CodeBlueLog;
use App\Models\IcuMonitor;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->artisan('db:seed', ['--class' => 'ModularHospitalSeeder']);
});

test('icu index renders correctly with telemetry and critical care metrics', function () {
    $doctor = User::where('email', 'dr.ananya@apollocare.in')->first();

    $response = $this->actingAs($doctor)->get('/icu');

    $response->assertStatus(200);
    $response->assertSee('Intensive Care Unit (ICU) & Critical Care Telemetry', false);
    $response->assertSee('Live ICU Bedside Telemetry Monitors');
    $response->assertSee('Mechanical Ventilator');
    $response->assertSee('Code Blue Resuscitations');
    $response->assertSee('Log Code Blue Event');
    $response->assertSee('Admit to ICU Bed');
});

test('nurse or doctor can update icu telemetry and mechanical ventilator settings', function () {
    $doctor = User::where('email', 'dr.ananya@apollocare.in')->first();
    $monitor = IcuMonitor::first();

    expect($monitor)->not->toBeNull();

    $response = $this->actingAs($doctor)->post('/icu/vitals', [
        'monitor_id' => $monitor->id,
        'vent_mode' => 'PRVC (Volume Control)',
        'fio2_percentage' => 50,
        'peep' => 10.0,
        'tidal_volume' => 500,
        'respiratory_rate' => 18,
        'spo2' => 99,
        'heart_rate' => 78,
        'bp_systolic' => 125,
        'bp_diastolic' => 80,
        'temperature_c' => 37.1,
        'gcs_score' => 15,
        'arterial_line' => 1,
        'central_line' => 1,
        'inotrope_infusion' => 'Noradrenaline 0.02 mcg/kg/min',
        'clinical_condition' => 'ventilator_dependent',
        'nurse_shift_notes' => 'Patient hemodynamically stable. Blood gases normal. FiO2 titrated to 50%.',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $monitor->refresh();
    expect($monitor->vent_mode)->toBe('PRVC (Volume Control)');
    expect($monitor->spo2)->toBe(99);
    expect($monitor->peep)->toBe('10.0');
    expect($monitor->arterial_line)->toBeTrue();
    expect($monitor->nurse_shift_notes)->toContain('FiO2 titrated to 50%');
});

test('patient can be admitted or transferred to an icu bed', function () {
    $nurse = User::where('role', 'nurse')->first();
    $doctor = User::where('email', 'dr.ananya@apollocare.in')->first();
    $admission = Admission::first();
    $icuBed = Bed::where('bed_type', 'icu_ventilator')->first();

    $response = $this->actingAs($nurse)->post('/icu/admit', [
        'admission_id' => $admission->id,
        'bed_id' => $icuBed->id,
        'attending_doctor_id' => $doctor->id,
        'vent_mode' => 'SIMV + PSV',
        'fio2_percentage' => 40,
        'peep' => 5.0,
        'spo2' => 98,
        'heart_rate' => 80,
        'bp_systolic' => 120,
        'bp_diastolic' => 80,
        'gcs_score' => 14,
        'clinical_condition' => 'ventilator_dependent',
        'nurse_shift_notes' => 'Emergency transfer from post-op recovery.',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $monitor = IcuMonitor::where('admission_id', $admission->id)->first();
    expect($monitor)->not->toBeNull();
    expect($monitor->bed_id)->toBe($icuBed->id);
    expect($icuBed->fresh()->status)->toBe('occupied');
});

test('patient can step down from icu releasing icu bed', function () {
    $doctor = User::where('email', 'dr.ananya@apollocare.in')->first();
    $monitor = IcuMonitor::first();
    $bed = $monitor->bed;

    $response = $this->actingAs($doctor)->post("/icu/step-down/{$monitor->admission_id}");

    $response->assertRedirect();
    $response->assertSessionHas('success');

    expect(IcuMonitor::find($monitor->id))->toBeNull();
    expect($bed->fresh()->status)->toBe('available');
});

test('emergency resuscitation code blue event is logged', function () {
    $doctor = User::where('email', 'dr.ananya@apollocare.in')->first();
    $patient = Patient::first();

    $response = $this->actingAs($doctor)->post('/icu/code-blue', [
        'patient_id' => $patient->id,
        'cpr_duration_mins' => 8,
        'shocks_delivered' => 2,
        'cardiac_rhythm' => 'Pulseless VT',
        'outcome' => 'resuscitated',
        'resuscitation_notes' => 'ROSC achieved after 2 shocks and 1mg epinephrine. Transferred to ICU Bed 1.',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $codeBlue = CodeBlueLog::where('patient_id', $patient->id)->latest()->first();
    expect($codeBlue)->not->toBeNull();
    expect($codeBlue->outcome)->toBe('resuscitated');
    expect($codeBlue->shocks_delivered)->toBe(2);
});

test('ambulance fleet management renders vehicles and handles emergency dispatches', function () {
    $doctor = User::where('email', 'dr.ananya@apollocare.in')->first();

    $response = $this->actingAs($doctor)->get('/ambulances');

    $response->assertStatus(200);
    $response->assertSee('Emergency Ambulance Fleet');
    $response->assertSee('GPS Dispatch');
    $response->assertSee('Total Ambulance Fleet');
    $response->assertSee('Available for Call');
    $response->assertSee('Dispatch Emergency Trip');
    $response->assertSee('Enroll Vehicle');
});

test('topbar and sidebar render properly without broken images or distorted ovals', function () {
    $doctor = User::where('email', 'dr.ananya@apollocare.in')->first();

    $response = $this->actingAs($doctor)->get('/doctor-schedule');

    $response->assertStatus(200);
    // Ensure no broken avatar-title ellipse exists in the chamber banner
    $response->assertDontSee('avatar-title bg-white text-primary rounded-circle font-size-14 fw-bold px-2 py-1');
    // Ensure duplicate Dr. Dr. does not appear in topbar
    $response->assertDontSee('Dr. Dr.');
    // Ensure ICU and Ambulance links exist in sidebar
    $response->assertSee('ICU & Critical Care', false);
    $response->assertSee('Ambulance Fleet & Dispatch', false);
});
