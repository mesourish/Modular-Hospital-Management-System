<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Operation Theater (OT) Suites & Room Configurations
        Schema::create('operation_theaters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained('hospitals')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->string('name', 255); // e.g. Modular OT 1 - Cardiac & Cath Hybrid Suite
            $table->string('code', 50); // e.g. OT-MUM-01
            $table->string('type', 50)->default('major_modular'); // major_modular, hybrid_cath_lab, minor_emergency, ophthalmic, day_care
            $table->string('floor', 100)->nullable(); // e.g. 3rd Floor - Surgical Pavilion
            $table->string('status', 50)->default('available'); // available, occupied_in_surgery, sterilization_fumigation, under_maintenance
            $table->string('anesthesia_workstation', 255)->nullable(); // e.g. Dräger Fabius Plus Workstation
            $table->string('c_arm_model', 255)->nullable(); // e.g. Siemens Cios Select C-Arm
            $table->string('heart_lung_machine', 255)->nullable(); // e.g. Terumo System 1 Perfusion
            $table->string('laparoscopic_tower', 255)->nullable(); // e.g. Karl Storz 4K 3D Rubina Tower
            $table->string('hepa_filter_status', 50)->default('certified_active'); // certified_active, cleaning_due, replacement_required
            $table->string('air_pressure_type', 50)->default('positive_laminar_flow'); // positive_laminar_flow, negative_pressure
            $table->dateTime('last_fumigation_at')->nullable();
            $table->dateTime('next_sterilization_due')->nullable();
            $table->boolean('infection_control_cleared')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['hospital_id', 'branch_id']);
            $table->index(['branch_id', 'status']);
        });

        // 2. OT Surgeries & Patient Surgical Encounters
        Schema::create('ot_surgeries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained('hospitals')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('operation_theater_id')->constrained('operation_theaters')->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignId('lead_surgeon_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('anesthesiologist_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('scrub_nurse_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('surgery_name', 255); // e.g. Off-Pump Coronary Artery Bypass Grafting (OPCABG)
            $table->string('surgery_code', 50)->unique(); // e.g. SURG-2026-001
            $table->string('surgery_category', 50)->default('major'); // major, super_major, minor, emergency
            $table->text('pre_op_diagnosis')->nullable();
            $table->text('post_op_findings')->nullable();
            $table->dateTime('scheduled_start_time');
            $table->dateTime('scheduled_end_time');
            $table->dateTime('actual_start_time')->nullable();
            $table->dateTime('actual_end_time')->nullable();
            $table->string('status', 50)->default('scheduled'); // scheduled, in_progress, post_op_recovery, completed, cancelled
            $table->string('anesthesia_type', 50)->default('general'); // general, spinal_epidural, regional_block, local_sedation
            $table->integer('blood_units_transfused')->default(0);
            $table->text('surgical_notes')->nullable();
            $table->text('complications')->nullable();
            $table->timestamps();

            $table->index(['hospital_id', 'branch_id', 'status']);
            $table->index(['operation_theater_id', 'scheduled_start_time']);
            $table->index(['patient_id', 'lead_surgeon_id']);
        });

        // 3. Detailed OT Medication & Consumables Usage Ledger
        Schema::create('ot_medication_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained('hospitals')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('ot_surgery_id')->constrained('ot_surgeries')->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignId('medicine_id')->nullable()->constrained('medicines')->nullOnDelete();
            $table->string('medicine_name', 255); // e.g. Propofol 1% 20ml, Fentanyl 50mcg/ml, Vecuronium 4mg
            $table->decimal('quantity_used', 10, 2); // e.g. 2.00, 3.50, 500.00
            $table->string('unit', 50)->default('vials'); // vials, ampoules, ml, bags, mg, units, packs
            $table->string('administration_stage', 50)->default('intra_op'); // pre_medication, induction, intra_op, maintenance, reversal, post_op_recovery
            $table->string('batch_number', 100)->nullable();
            $table->decimal('cost_per_unit', 10, 2)->default(0.00);
            $table->decimal('total_cost', 10, 2)->default(0.00);
            $table->string('administered_by', 255)->nullable();
            $table->string('notes', 500)->nullable();
            $table->timestamps();

            $table->index(['hospital_id', 'branch_id']);
            $table->index(['ot_surgery_id', 'patient_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ot_medication_logs');
        Schema::dropIfExists('ot_surgeries');
        Schema::dropIfExists('operation_theaters');
    }
};
