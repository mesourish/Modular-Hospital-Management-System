<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained()->cascadeOnDelete();
            $table->string('mrn', 50)->index(); // Medical Record Number
            $table->string('abha_id', 50)->nullable()->index(); // Ayushman Bharat Health Account
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->default('male');
            $table->string('blood_group', 10)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('address', 255)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 100)->nullable();
            $table->string('pincode', 10)->nullable();
            $table->string('emergency_contact_name', 150)->nullable();
            $table->string('emergency_contact_phone', 30)->nullable();
            $table->text('allergies')->nullable();
            $table->text('chronic_conditions')->nullable();
            $table->timestamps();

            $table->unique(['hospital_id', 'mrn']);
            $table->index(['hospital_id', 'phone']);
        });

        Schema::create('wards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name', 150);
            $table->enum('ward_type', ['general', 'semi_private', 'private', 'icu', 'emergency', 'maternity'])->default('general');
            $table->string('floor', 50)->nullable();
            $table->unsignedSmallInteger('capacity')->default(10);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['hospital_id', 'branch_id']);
        });

        Schema::create('beds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ward_id')->constrained()->cascadeOnDelete();
            $table->string('bed_number', 50);
            $table->enum('bed_type', ['standard', 'icu_ventilator', 'pediatric', 'orthopedic'])->default('standard');
            $table->enum('status', ['available', 'occupied', 'maintenance', 'reserved'])->default('available');
            $table->decimal('daily_charge', 10, 2)->default(0.00);
            $table->timestamps();

            $table->unique(['hospital_id', 'ward_id', 'bed_number']);
            $table->index(['hospital_id', 'status']);
        });

        Schema::create('admissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->string('admission_number', 50)->index();
            $table->timestamp('admission_date');
            $table->timestamp('discharge_date')->nullable();
            $table->foreignId('ward_id')->constrained();
            $table->foreignId('bed_id')->constrained();
            $table->foreignId('attending_doctor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('admission_type', ['emergency', 'planned', 'transfer', 'day_care'])->default('planned');
            $table->text('admission_reason')->nullable();
            $table->text('initial_vitals')->nullable();
            $table->enum('status', ['admitted', 'discharged', 'transferred'])->default('admitted');
            $table->text('discharge_summary')->nullable();
            $table->timestamps();

            $table->unique(['hospital_id', 'admission_number']);
            $table->index(['hospital_id', 'branch_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admissions');
        Schema::dropIfExists('beds');
        Schema::dropIfExists('wards');
        Schema::dropIfExists('patients');
    }
};
