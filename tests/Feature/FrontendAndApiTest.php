<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\DoctorChart;
use App\Models\HealthCheckupPackage;
use App\Models\User;
use Database\Seeders\ModularHospitalSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FrontendAndApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ModularHospitalSeeder::class);
    }

    public function test_frontend_home_page_renders_successfully(): void
    {
        $response = $this->get(route('frontend.home'));
        $response->assertStatus(200);
        $response->assertSee('Apollo Hospitals Healthcare Network');
        $response->assertSee('Quick Appointment');
        $response->assertSee('Featured Health Checkup Packages');
        $response->assertSee('Our Doctors');
    }

    public function test_frontend_about_page_renders_successfully(): void
    {
        $response = $this->get(route('frontend.about'));
        $response->assertStatus(200);
        $response->assertSee('NABH Accredited');
        $response->assertSee('JCI Certified');
        $response->assertSee('Clinical Excellence With Compassion');
    }

    public function test_frontend_locations_page_and_filtering(): void
    {
        $response = $this->get(route('frontend.locations'));
        $response->assertStatus(200);
        $response->assertSee('Apollo Care Flagship Hospital - South Mumbai');
        $response->assertSee('Apollo Cardiac & Specialty Clinic - Bandra West');

        // Filter by city Mumbai
        $mumbaiResponse = $this->get(route('frontend.locations', ['city' => 'Mumbai']));
        $mumbaiResponse->assertStatus(200);
        $mumbaiResponse->assertSee('South Mumbai');
    }

    public function test_frontend_doctors_list_renders_with_view_profile_button(): void
    {
        $response = $this->get(route('frontend.doctors'));
        $response->assertStatus(200);
        $response->assertSee('Dr. Rajesh Sharma');
        $response->assertSee('View Profile');
        $response->assertSee('₹1,200.00');
    }

    public function test_frontend_doctor_profile_renders_quick_appointment_and_testimonials(): void
    {
        $doctor = DoctorChart::first();
        $response = $this->get(route('frontend.doctor_profile', $doctor));
        $response->assertStatus(200);
        $response->assertSee($doctor->doctor_name);
        $response->assertSee('Quick Appointment');
        $response->assertSee('Customer Testimonials');
        $response->assertSee('Write a Review');
    }

    public function test_customer_can_submit_testimonial_for_doctor(): void
    {
        $doctor = DoctorChart::first();
        $response = $this->post(route('portal.submit_testimonial'), [
            'doctor_chart_id' => $doctor->id,
            'patient_name' => 'Ketan Mehta',
            'patient_location' => 'Juhu, Mumbai',
            'rating' => 5,
            'headline' => 'Exceptional patient care',
            'treatment_received' => 'Cardiology Checkup',
            'comment' => 'Very attentive doctor who explained the medication dosage clearly.',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('doctor_testimonials', [
            'doctor_chart_id' => $doctor->id,
            'patient_name' => 'Ketan Mehta',
            'rating' => 5,
        ]);
    }

    public function test_frontend_packages_page_renders_with_inclusions_and_prices(): void
    {
        $response = $this->get(route('frontend.packages'));
        $response->assertStatus(200);
        $response->assertSee('Apollo Executive Whole Body Health Checkup');
        $response->assertSee('₹4,999.00');
        $response->assertSee('Book Now');
    }

    public function test_patient_can_book_health_package(): void
    {
        $package = HealthCheckupPackage::first();
        $branch = Branch::first();

        $response = $this->post(route('portal.book_package'), [
            'package_id' => $package->id,
            'branch_id' => $branch->id,
            'patient_name' => 'Aditi Rao',
            'patient_phone' => '9820011223',
            'patient_email' => 'aditi@example.com',
            'patient_age' => 32,
            'patient_gender' => 'female',
            'preferred_date' => now()->addDays(3)->toDateString(),
            'notes' => 'Pre-employment wellness screening',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('package_bookings', [
            'package_id' => $package->id,
            'patient_name' => 'Aditi Rao',
            'patient_phone' => '9820011223',
        ]);
    }

    public function test_multi_language_switcher_updates_locale(): void
    {
        $response = $this->get(route('portal.set_language', 'hi'));
        $response->assertSessionHas('app_locale', 'hi');

        $response = $this->get(route('portal.set_language', 'bn'));
        $response->assertSessionHas('app_locale', 'bn');

        $response = $this->get(route('portal.set_language', 'mr'));
        $response->assertSessionHas('app_locale', 'mr');
    }

    public function test_admin_can_manage_health_packages_in_backend(): void
    {
        $admin = User::where('role', 'hospital_admin')->first();
        $response = $this->actingAs($admin)->get(route('packages.index'));
        $response->assertStatus(200);
        $response->assertSee('Health Checkup Packages');
        $response->assertSee('Create Health Package');

        // Create new package
        $createResponse = $this->actingAs($admin)->post(route('packages.store'), [
            'name' => 'Custom Cardiac Advanced Screen',
            'code' => 'APO-PKG-CUST-99',
            'category' => 'Cardiac Care',
            'original_price' => 5000.00,
            'discounted_price' => 2500.00,
            'tests_count' => 30,
            'turnaround_time' => 'Same Day (4 Hours)',
            'gender_target' => 'All',
            'age_group' => '18+ Years',
            'fasting_required' => 1,
            'fasting_hours' => 10,
            'tests_included' => "ECG\nEcho\nLipid Profile",
        ]);

        $createResponse->assertSessionHas('success');
        $this->assertDatabaseHas('health_checkup_packages', [
            'code' => 'APO-PKG-CUST-99',
            'name' => 'Custom Cardiac Advanced Screen',
        ]);
    }

    public function test_api_v1_endpoints_return_structured_json(): void
    {
        // 1. GET /api/v1/hospitals
        $hospitalsRes = $this->getJson('/api/v1/hospitals');
        $hospitalsRes->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'message',
                'count',
                'data' => [
                    '*' => [
                        'id',
                        'hospital_name',
                        'branch_name',
                        'city',
                        'emergency_phone',
                    ],
                ],
            ]);

        // 2. GET /api/v1/doctors
        $doctorsRes = $this->getJson('/api/v1/doctors');
        $doctorsRes->assertStatus(200)
            ->assertJsonPath('success', true);

        // 3. GET /api/v1/doctors/{id}
        $doctor = DoctorChart::first();
        $doctorRes = $this->getJson("/api/v1/doctors/{$doctor->id}");
        $doctorRes->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.doctor_name', $doctor->doctor_name);

        // 4. GET /api/v1/packages
        $packagesRes = $this->getJson('/api/v1/packages');
        $packagesRes->assertStatus(200)
            ->assertJsonPath('success', true);

        // 5. POST /api/v1/appointments
        $appointmentRes = $this->postJson('/api/v1/appointments', [
            'patient_name' => 'Manish Gupta',
            'patient_phone' => '9820012399',
            'patient_gender' => 'male',
            'patient_age' => 40,
            'branch_id' => $doctor->branch_id,
            'doctor_id' => $doctor->user_id,
            'appointment_date' => now()->addDay()->toDateString(),
            'appointment_time' => '10:00:00',
            'symptoms' => 'Routine cardiology consultation',
        ]);
        $appointmentRes->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.patient_name', 'Manish Gupta');

        // 6. POST /api/v1/packages/book
        $package = HealthCheckupPackage::first();
        $pkgBookingRes = $this->postJson('/api/v1/packages/book', [
            'package_id' => $package->id,
            'branch_id' => $doctor->branch_id,
            'patient_name' => 'Sunil Grover',
            'patient_phone' => '9820098765',
            'patient_age' => 50,
            'patient_gender' => 'male',
            'preferred_date' => now()->addDays(2)->toDateString(),
        ]);
        $pkgBookingRes->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.patient_name', 'Sunil Grover');

        // 7. GET /api/v1/testimonials
        $testimonialsRes = $this->getJson('/api/v1/testimonials');
        $testimonialsRes->assertStatus(200)
            ->assertJsonPath('success', true);
    }
}
