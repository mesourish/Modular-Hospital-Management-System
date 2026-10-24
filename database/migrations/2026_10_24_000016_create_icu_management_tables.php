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
        // 1. ICU Patient Monitors & Mechanical Ventilation Telemetry
        Schema::create('icu_monitors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('admission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bed_id')->constrained()->cascadeOnDelete();
            $table->foreignId('attending_doctor_id')->nullable()->constrained('users')->nullOnDelete();

            // Mechanical Ventilation & Oxygenation
            $table->string('vent_mode', 50)->default('None (Room Air)'); // SIMV, CPAP/PSV, PRVC, BiPAP, HFNC, None
            $table->unsignedSmallInteger('fio2_percentage')->default(21); // 21% - 100%
            $table->decimal('peep', 4, 1)->default(5.0); // 5.0 - 20.0 cmH2O
            $table->unsignedSmallInteger('tidal_volume')->default(450); // mL
            $table->unsignedSmallInteger('respiratory_rate')->default(16); // bpm

            // Hemodynamics & Critical Care Vitals
            $table->unsignedSmallInteger('spo2')->default(98); // %
            $table->unsignedSmallInteger('heart_rate')->default(78); // bpm
            $table->unsignedSmallInteger('bp_systolic')->default(120); // mmHg
            $table->unsignedSmallInteger('bp_diastolic')->default(80); // mmHg
            $table->decimal('temperature_c', 4, 1)->default(37.0); // Celsius
            $table->unsignedTinyInteger('gcs_score')->default(15); // Glasgow Coma Scale (3-15)

            // Invasive Monitoring & Inotropic Support
            $table->boolean('arterial_line')->default(false);
            $table->boolean('central_line')->default(false);
            $table->string('inotrope_infusion', 150)->nullable(); // e.g. Noradrenaline 0.08 mcg/kg/min

            // Clinical Condition & Status
            $table->string('clinical_condition', 50)->default('stable_monitoring'); // critical_unstable, ventilator_dependent, step_down_ready, stable_monitoring, code_blue_alert
            $table->text('nurse_shift_notes')->nullable();
            $table->timestamp('last_assessment_at')->useCurrent();
            $table->timestamps();

            $table->index(['hospital_id', 'branch_id', 'clinical_condition']);
            $table->index(['hospital_id', 'admission_id']);
        });

        // 2. ICU Code Blue Emergency Resuscitation Logs
        Schema::create('code_blue_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bed_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('initiated_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('event_time')->useCurrent();
            $table->unsignedSmallInteger('cpr_duration_mins')->default(0);
            $table->unsignedSmallInteger('shocks_delivered')->default(0);
            $table->string('cardiac_rhythm', 50)->default('Asystole'); // VFib, Pulseless VTach, Asystole, PEA
            $table->string('outcome', 50)->default('resuscitated'); // resuscitated, deceased, ongoing
            $table->text('resuscitation_notes')->nullable();
            $table->timestamps();

            $table->index(['hospital_id', 'branch_id', 'event_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('code_blue_logs');
        Schema::dropIfExists('icu_monitors');
    }
};
