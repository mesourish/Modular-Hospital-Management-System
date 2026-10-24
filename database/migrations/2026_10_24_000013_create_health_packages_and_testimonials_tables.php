<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Health Checkup Packages (Configured by Super Admin & Hospital Admin)
        Schema::create('health_checkup_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained('hospitals')->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->string('name', 255);
            $table->string('code', 50)->unique();
            $table->string('slug', 255)->nullable();
            $table->string('category', 100)->default('General Wellness');
            $table->decimal('original_price', 10, 2);
            $table->decimal('discounted_price', 10, 2);
            $table->integer('tests_count')->default(1);
            $table->string('turnaround_time', 100)->default('Same Day (6-8 Hours)');
            $table->boolean('fasting_required')->default(true);
            $table->integer('fasting_hours')->default(10);
            $table->string('gender_target', 50)->default('All'); // All, Male, Female
            $table->string('age_group', 50)->default('All Ages'); // 18+ Years, 40+ Years, 60+ Years
            $table->json('tests_included')->nullable(); // Array of test names
            $table->text('description')->nullable();
            $table->boolean('is_popular')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['hospital_id', 'branch_id']);
            $table->index(['hospital_id', 'category']);
            $table->index(['hospital_id', 'is_active']);
        });

        // 2. Doctor Testimonials & Patient Reviews
        Schema::create('doctor_testimonials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained('hospitals')->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('doctor_chart_id')->constrained('doctor_charts')->cascadeOnDelete();
            $table->string('patient_name', 255);
            $table->string('patient_location', 255)->default('Mumbai, Maharashtra');
            $table->integer('rating')->default(5); // 1 to 5
            $table->string('headline', 255)->nullable();
            $table->text('comment');
            $table->string('treatment_received', 255)->nullable();
            $table->boolean('is_verified')->default(true);
            $table->boolean('is_published')->default(true);
            $table->timestamps();

            $table->index(['doctor_chart_id', 'is_published']);
            $table->index(['hospital_id', 'is_published']);
        });

        // 3. Health Checkup Package Bookings
        Schema::create('package_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained('hospitals')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('package_id')->constrained('health_checkup_packages')->cascadeOnDelete();
            $table->string('booking_number', 50)->unique();
            $table->string('patient_name', 255);
            $table->string('patient_phone', 50);
            $table->string('patient_email', 255)->nullable();
            $table->integer('patient_age')->default(30);
            $table->string('patient_gender', 20)->default('male');
            $table->date('preferred_date');
            $table->text('notes')->nullable();
            $table->string('status', 50)->default('confirmed'); // confirmed, sample_collected, processing, completed, cancelled
            $table->timestamps();

            $table->index(['hospital_id', 'branch_id']);
            $table->index(['booking_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('package_bookings');
        Schema::dropIfExists('doctor_testimonials');
        Schema::dropIfExists('health_checkup_packages');
    }
};
