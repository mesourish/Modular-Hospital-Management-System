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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->date('appointment_date');
            $table->time('appointment_time')->nullable();
            $table->integer('token_number')->default(1);
            $table->enum('status', ['scheduled', 'checked_in', 'in_consultation', 'completed', 'cancelled'])->default('scheduled');
            $table->text('symptoms')->nullable();
            $table->timestamps();

            $table->index(['hospital_id', 'branch_id', 'appointment_date']);
            $table->index(['doctor_id', 'appointment_date']);
        });

        Schema::create('consultations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained()->cascadeOnDelete();
            $table->foreignId('appointment_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('encounter_date')->useCurrent();
            $table->text('chief_complaints')->nullable();
            $table->text('examination_findings')->nullable();
            $table->string('diagnosis', 255);
            $table->string('bp', 20)->nullable();
            $table->string('pulse', 20)->nullable();
            $table->string('temperature', 20)->nullable();
            $table->string('respiratory_rate', 20)->nullable();
            $table->string('spo2', 20)->nullable();
            $table->decimal('weight_kg', 5, 2)->nullable();
            $table->text('clinical_notes')->nullable();
            $table->date('follow_up_date')->nullable();
            $table->enum('status', ['draft', 'finalized'])->default('finalized');
            $table->timestamps();

            $table->index(['hospital_id', 'encounter_date']);
            $table->index(['patient_id', 'encounter_date']);
        });

        Schema::create('prescriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained()->cascadeOnDelete();
            $table->foreignId('consultation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained('users')->cascadeOnDelete();
            $table->string('medicine_name', 200);
            $table->string('dosage', 100); // e.g. 650mg, 1 tablet
            $table->string('frequency', 100); // e.g. 1-0-1 after food (TDS/BD)
            $table->string('duration', 100); // e.g. 5 days
            $table->text('instructions')->nullable();
            $table->boolean('is_dispensed')->default(false);
            $table->timestamps();

            $table->index(['consultation_id']);
            $table->index(['patient_id', 'is_dispensed']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prescriptions');
        Schema::dropIfExists('consultations');
        Schema::dropIfExists('appointments');
    }
};
