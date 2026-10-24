<?php

use App\Models\Ambulance;
use App\Models\AmbulanceTrip;
use App\Models\BiomedicalEquipment;
use App\Models\BiomedicalTicket;
use App\Models\BirthRecord;
use App\Models\Branch;
use App\Models\DeathRecord;
use App\Models\DietaryOrder;
use App\Models\Invoice;
use App\Models\MedicineBatch;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->artisan('db:seed', ['--class' => 'ModularHospitalSeeder']);
});

test('vital records index renders with birth and death statistics', function () {
    $doctor = User::where('email', 'dr.ananya@apollocare.in')->first();

    $response = $this->actingAs($doctor)->get('/vital-records');

    $response->assertStatus(200);
    $response->assertSee('Vital Records Registry', false);
    $response->assertSee('Birth Records', false);
    $response->assertSee('Death Records', false);
    $response->assertSee('BC-MH-2026-0001');
    $response->assertSee('DC-MH-2026-0001');
});

test('obstetrician can register a birth record and print official birth certificate', function () {
    $doctor = User::where('email', 'dr.ananya@apollocare.in')->first();
    $mother = Patient::where('gender', 'female')->first();

    $response = $this->actingAs($doctor)->post('/vital-records/births', [
        'mother_id' => $mother->id,
        'mother_name' => $mother->full_name,
        'father_name' => 'Vikram Malhotra',
        'baby_name' => 'Baby Girl of '.$mother->last_name,
        'gender' => 'female',
        'birth_date' => now()->toDateString(),
        'birth_time' => '10:30',
        'birth_weight_kg' => 3.10,
        'delivery_type' => 'normal_vaginal',
        'gestational_age_weeks' => 39,
        'apgar_score_1min' => 9,
        'apgar_score_5min' => 10,
        'doctor_id' => $doctor->id,
        'blood_group' => 'A+',
        'notes' => 'Uneventful delivery, robust cry.',
    ]);

    $birthRecord = BirthRecord::where('father_name', 'Vikram Malhotra')->first();
    expect($birthRecord)->not->toBeNull();
    expect($birthRecord->certificate_number)->toStartWith('BC-MH-');

    $response->assertRedirect(route('vital_records.birth_certificate', $birthRecord->id));

    // Test Printable Birth Certificate view
    $certResponse = $this->actingAs($doctor)->get(route('vital_records.birth_certificate', $birthRecord->id));
    $certResponse->assertStatus(200);
    $certResponse->assertSee('Certificate of Birth');
    $certResponse->assertSee($birthRecord->certificate_number);
    $certResponse->assertSee('Registration of Births and Deaths Act');
    $certResponse->assertSee('Infant Left Sole Print');
});

test('clinician can certify death record and print official MCCD Form 4 certificate', function () {
    $doctor = User::where('email', 'dr.kevin@apollocare.in')->first();
    $patient = Patient::first();

    $response = $this->actingAs($doctor)->post('/vital-records/deaths', [
        'patient_id' => $patient->id,
        'certifying_doctor_id' => $doctor->id,
        'date_of_death' => now()->toDateString(),
        'time_of_death' => '18:45',
        'place_of_death' => 'Intensive Care Unit (ICU)',
        'primary_cause' => 'Refractory Septic Shock secondary to Ventilator Associated Pneumonia',
        'secondary_cause' => 'End-Stage Renal Disease',
        'icd10_code' => 'R57.2',
        'next_of_kin_name' => 'Anil Sharma',
        'next_of_kin_phone' => '+91 98200 11990',
        'next_of_kin_relation' => 'Son',
        'cremation_pass_issued' => '1',
    ]);

    $deathRecord = DeathRecord::where('icd10_code', 'R57.2')->first();
    expect($deathRecord)->not->toBeNull();
    expect($deathRecord->certificate_number)->toStartWith('DC-MH-');

    $response->assertRedirect(route('vital_records.death_certificate', $deathRecord->id));

    // Test Printable MCCD Form 4 view
    $certResponse = $this->actingAs($doctor)->get(route('vital_records.death_certificate', $deathRecord->id));
    $certResponse->assertStatus(200);
    $certResponse->assertSee('Medical Certificate of Cause of Death (MCCD)');
    $certResponse->assertSee('Form No. 4');
    $certResponse->assertSee('R57.2');
    $certResponse->assertSee('PERMIT ISSUED FOR CREMATION / BURIAL');
});

test('patient longitudinal clinical history EMR renders lifetime timeline and allows updates', function () {
    $doctor = User::where('email', 'dr.ananya@apollocare.in')->first();
    $patient = Patient::first();

    $response = $this->actingAs($doctor)->get("/patients/{$patient->id}/history");

    $response->assertStatus(200);
    $response->assertSee('Longitudinal Electronic Medical Record (EMR)');
    $response->assertSee('Chronological Lifetime Patient Journey');
    $response->assertSee($patient->mrn);

    // Update clinical alerts
    $updateResponse = $this->actingAs($doctor)->post("/patients/{$patient->id}/history", [
        'allergies' => 'Severe Penicillin anaphylaxis, Shellfish, Latex',
        'chronic_conditions' => 'Type-2 Diabetes Mellitus, Chronic Kidney Disease Stage 3',
        'past_surgeries' => 'Coronary Artery Bypass Graft (CABG x3) 2019',
        'family_medical_history' => 'Strong maternal history of sudden cardiac death',
    ]);

    $updateResponse->assertRedirect();
    $patient->refresh();
    expect($patient->allergies)->toContain('Severe Penicillin anaphylaxis');
    expect($patient->past_surgeries)->toContain('Coronary Artery Bypass Graft');
});

test('dedicated pharmacy POS terminal displays exact rack and shelf stock and processes multi-item atomic checkout', function () {
    $pharmacist = User::where('role', 'pharmacist')->first();
    $batch = MedicineBatch::where('quantity_in_stock', '>', 10)->first();
    $initialStock = $batch->quantity_in_stock;

    $response = $this->actingAs($pharmacist)->get('/pharmacy/pos');
    $response->assertStatus(200);
    $response->assertSee('Pharmacy POS Terminal', false);
    $response->assertSee('Rack', false);
    $response->assertSee($batch->batch_number);

    // Perform atomic checkout
    $cartData = json_encode([
        [
            'batch_id' => $batch->id,
            'quantity' => 5,
            'unit_price' => (float) $batch->selling_price,
            'discount_percent' => 0,
            'gst_rate' => 12,
        ],
    ]);

    $checkoutResponse = $this->actingAs($pharmacist)->post('/pharmacy/pos/checkout', [
        'walkin_name' => 'Mrs. Leela Nair',
        'payment_method' => 'upi',
        'cart_data' => $cartData,
    ]);

    $batch->refresh();
    // Stock should be deducted atomically by 5
    expect($batch->quantity_in_stock)->toBe($initialStock - 5);

    // Invoice should be created with GST details
    $invoice = Invoice::where('invoice_number', 'like', 'INV-POS-%')->latest('id')->first();
    expect($invoice)->not->toBeNull();
    expect((float) $invoice->cgst_amount)->toBeGreaterThan(0);
    expect((float) $invoice->sgst_amount)->toBeGreaterThan(0);
    expect($invoice->payment_status)->toBe('paid');

    $checkoutResponse->assertRedirect(route('billing.invoices.print', $invoice->id));
});

test('pharmacy POS prevents checkout when stock is insufficient', function () {
    $pharmacist = User::where('role', 'pharmacist')->first();
    $batch = MedicineBatch::where('quantity_in_stock', '>', 0)->first();

    $cartData = json_encode([
        [
            'batch_id' => $batch->id,
            'quantity' => $batch->quantity_in_stock + 100, // exceeds available stock
            'unit_price' => (float) $batch->selling_price,
            'discount_percent' => 0,
            'gst_rate' => 12,
        ],
    ]);

    $response = $this->actingAs($pharmacist)->post('/pharmacy/pos/checkout', [
        'walkin_name' => 'Walkin Customer',
        'payment_method' => 'cash',
        'cart_data' => $cartData,
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('error');
});

test('printable Indian GST tax invoice renders with HSN, CGST, SGST and amount in words', function () {
    $accountant = User::where('role', 'accountant')->first();
    $invoice = Invoice::first();

    $response = $this->actingAs($accountant)->get(route('billing.invoices.print', $invoice->id));

    $response->assertStatus(200);
    $response->assertSee('Tax Invoice / Cash Memo');
    $response->assertSee('GSTIN:');
    $response->assertSee('Maharashtra (Code: 27)');
    $response->assertSee('Central GST (CGST @ 6%)');
    $response->assertSee('State GST (SGST @ 6%)');
    $response->assertSee('Rupees');
    $response->assertSee('Print Invoice');
});

test('ambulance fleet allows vehicle enrollment and emergency dispatch', function () {
    $admin = User::where('email', 'admin@apollocare.in')->first();

    $response = $this->actingAs($admin)->get('/ambulances');
    $response->assertStatus(200);
    $response->assertSee('Emergency Ambulance Fleet', false);
    $response->assertSee('MH-01-CR-1088');

    // Dispatch emergency trip
    $ambulance = Ambulance::where('status', 'available')->first();

    $tripResponse = $this->actingAs($admin)->post('/ambulances/trips', [
        'ambulance_id' => $ambulance->id,
        'patient_name' => 'Siddharth Roy',
        'patient_phone' => '+91 98200 99112',
        'pickup_address' => 'Bandra Kurla Complex Tower 4',
        'destination_hospital' => 'Apollo Care Flagship Hospital',
        'emergency_category' => 'Acute Ischemic Stroke',
        'fare_amount' => 2000.00,
    ]);

    $tripResponse->assertRedirect();
    $trip = AmbulanceTrip::where('patient_name', 'Siddharth Roy')->first();
    expect($trip)->not->toBeNull();
    expect($trip->status)->toBe('dispatched');

    // Update status to completed
    $this->actingAs($admin)->post("/ambulances/trips/{$trip->id}/status", [
        'status' => 'completed',
    ]);

    $trip->refresh();
    expect($trip->status)->toBe('completed');
});

test('dietary module allows prescribing inpatient meals and tracking kitchen status', function () {
    $doctor = User::where('email', 'dr.ananya@apollocare.in')->first();
    $patient = Patient::first();

    $response = $this->actingAs($doctor)->get('/dietary');
    $response->assertStatus(200);
    $response->assertSee('Inpatient Clinical Dietary', false);

    $orderResponse = $this->actingAs($doctor)->post('/dietary/orders', [
        'patient_id' => $patient->id,
        'diet_type' => 'Renal Diet (Low Protein / Potassium)',
        'meal_slot' => 'lunch',
        'order_date' => now()->toDateString(),
        'calorie_target' => '1700 kcal',
        'room_bed_number' => 'Renal Care Bed 02',
        'kitchen_instructions' => 'Low sodium, steamed apple, double boiled lentils',
    ]);

    $orderResponse->assertRedirect();
    $order = DietaryOrder::where('diet_type', 'Renal Diet (Low Protein / Potassium)')->first();
    expect($order)->not->toBeNull();
    expect($order->status)->toBe('prescribed');

    // Kitchen marks cooking
    $this->actingAs($doctor)->post("/dietary/orders/{$order->id}/status", [
        'status' => 'cooking',
    ]);

    $order->refresh();
    expect($order->status)->toBe('cooking');
});

test('biomedical equipment module tracks asset maintenance and service tickets', function () {
    $admin = User::where('email', 'admin@apollocare.in')->first();

    $response = $this->actingAs($admin)->get('/biomedical');
    $response->assertStatus(200);
    $response->assertSee('Biomedical Engineering', false);
    $response->assertSee('Fabius Plus Anesthesia Workstation', false);

    $equip = BiomedicalEquipment::first();

    $ticketResponse = $this->actingAs($admin)->post('/biomedical/tickets', [
        'equipment_id' => $equip->id,
        'severity' => 'critical_life_support',
        'fault_description' => 'Oxygen pressure sensor alarm triggered during pre-op check.',
        'technician_assigned' => 'Senior Bioengineer',
    ]);

    $ticketResponse->assertRedirect();
    $ticket = BiomedicalTicket::where('fault_description', 'Oxygen pressure sensor alarm triggered during pre-op check.')->first();
    expect($ticket)->not->toBeNull();
    expect($ticket->status)->toBe('open');

    // Resolve ticket
    $this->actingAs($admin)->post("/biomedical/tickets/{$ticket->id}/resolve", [
        'corrective_action' => 'Sensor swapped and calibrated. Pressure readings normalized.',
        'repair_cost' => 4500.00,
    ]);

    $ticket->refresh();
    expect($ticket->status)->toBe('resolved');
    expect($ticket->repair_cost)->toBe('4500.00');
});

test('hospital outlet slug routes resolve branch-scoped workspaces', function () {
    $doctor = User::where('email', 'dr.ananya@apollocare.in')->first();
    $branch = Branch::where('slug', 'south-mumbai-main-campus')->first();

    expect($branch)->not->toBeNull();

    // Outlet POS
    $posResponse = $this->actingAs($doctor)->get("/outlet/{$branch->slug}/pos");
    $posResponse->assertStatus(200);
    $posResponse->assertSee('Pharmacy POS Terminal', false);
    $posResponse->assertSee($branch->name);

    // Outlet Doctor Schedule
    $schedResponse = $this->actingAs($doctor)->get("/outlet/{$branch->slug}/schedule");
    $schedResponse->assertStatus(200);
    $schedResponse->assertSee('Centralized Doctor Schedule', false);

    // Outlet Vital Records
    $vitalResponse = $this->actingAs($doctor)->get("/outlet/{$branch->slug}/vital-records");
    $vitalResponse->assertStatus(200);
    $vitalResponse->assertSee('Vital Records Registry', false);
});
