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
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // Morning Shift, Evening Shift, Night Shift, ER Emergency Duty
            $table->time('start_time');
            $table->time('end_time');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('duty_rosters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shift_id')->constrained()->cascadeOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->date('duty_date');
            $table->enum('role_in_shift', ['er_incharge', 'triage_officer', 'attending_physician', 'staff_nurse', 'on_call_consultant', 'code_blue_team'])->default('staff_nurse');
            $table->enum('status', ['scheduled', 'on_duty', 'completed', 'absent', 'swapped'])->default('scheduled');
            $table->text('duty_notes')->nullable();
            $table->timestamps();
        });

        Schema::create('patient_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained()->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('admission_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->enum('assignment_type', ['attending_doctor', 'primary_nurse', 'er_care_team', 'visiting_specialist', 'anesthetist'])->default('attending_doctor');
            $table->timestamp('assigned_at')->useCurrent();
            $table->timestamp('unassigned_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('clinical_handover_instructions')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_assignments');
        Schema::dropIfExists('duty_rosters');
        Schema::dropIfExists('shifts');
    }
};
