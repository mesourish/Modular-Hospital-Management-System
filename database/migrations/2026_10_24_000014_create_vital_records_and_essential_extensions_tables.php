<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add slug to branches if not present
        if (! Schema::hasColumn('branches', 'slug')) {
            Schema::table('branches', function (Blueprint $table) {
                $table->string('slug', 100)->nullable()->after('code');
            });
        }

        // 2. Add HSN code to medicines
        if (! Schema::hasColumn('medicines', 'hsn_code')) {
            Schema::table('medicines', function (Blueprint $table) {
                $table->string('hsn_code', 50)->default('30049099')->after('category');
            });
        }

        // 3. Add shelf & box numbers to medicine_batches
        Schema::table('medicine_batches', function (Blueprint $table) {
            if (! Schema::hasColumn('medicine_batches', 'shelf_number')) {
                $table->string('shelf_number', 50)->nullable()->after('rack_location');
            }
            if (! Schema::hasColumn('medicine_batches', 'box_number')) {
                $table->string('box_number', 50)->nullable()->after('shelf_number');
            }
        });

        // 4. Add clinical history fields to patients
        Schema::table('patients', function (Blueprint $table) {
            if (! Schema::hasColumn('patients', 'allergies')) {
                $table->text('allergies')->nullable()->after('blood_group');
            }
            if (! Schema::hasColumn('patients', 'chronic_conditions')) {
                $table->text('chronic_conditions')->nullable()->after('allergies');
            }
            if (! Schema::hasColumn('patients', 'past_surgeries')) {
                $table->text('past_surgeries')->nullable()->after('chronic_conditions');
            }
            if (! Schema::hasColumn('patients', 'family_medical_history')) {
                $table->text('family_medical_history')->nullable()->after('past_surgeries');
            }
        });

        // 5. Add GST breakdown columns to invoices and invoice_items
        Schema::table('invoices', function (Blueprint $table) {
            if (! Schema::hasColumn('invoices', 'cgst_amount')) {
                $table->decimal('cgst_amount', 12, 2)->default(0.00)->after('tax_amount');
            }
            if (! Schema::hasColumn('invoices', 'sgst_amount')) {
                $table->decimal('sgst_amount', 12, 2)->default(0.00)->after('cgst_amount');
            }
            if (! Schema::hasColumn('invoices', 'gstin_number')) {
                $table->string('gstin_number', 50)->nullable()->after('sgst_amount');
            }
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            if (! Schema::hasColumn('invoice_items', 'hsn_code')) {
                $table->string('hsn_code', 50)->default('3004')->after('item_name');
            }
            if (! Schema::hasColumn('invoice_items', 'batch_number')) {
                $table->string('batch_number', 50)->nullable()->after('hsn_code');
            }
            if (! Schema::hasColumn('invoice_items', 'expiry_date')) {
                $table->date('expiry_date')->nullable()->after('batch_number');
            }
            if (! Schema::hasColumn('invoice_items', 'rack_location')) {
                $table->string('rack_location', 100)->nullable()->after('expiry_date');
            }
            if (! Schema::hasColumn('invoice_items', 'cgst_amount')) {
                $table->decimal('cgst_amount', 10, 2)->default(0.00)->after('total_price');
            }
            if (! Schema::hasColumn('invoice_items', 'sgst_amount')) {
                $table->decimal('sgst_amount', 10, 2)->default(0.00)->after('cgst_amount');
            }
        });

        // 6. Birth Records
        Schema::create('birth_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained('hospitals')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('mother_id')->nullable()->constrained('patients')->nullOnDelete();
            $table->foreignId('doctor_id')->constrained('users')->cascadeOnDelete(); // Attending Obstetrician
            $table->foreignId('pediatrician_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('certificate_number', 50)->unique();
            $table->string('baby_name', 200)->default('Baby of Patient');
            $table->enum('gender', ['male', 'female', 'other'])->default('male');
            $table->date('birth_date');
            $table->time('birth_time');
            $table->string('mother_name', 200);
            $table->string('father_name', 200)->nullable();
            $table->decimal('birth_weight_kg', 5, 2)->default(3.10);
            $table->string('delivery_type', 50)->default('normal_vaginal'); // normal_vaginal, lscs_caesarean, vacuum_instrumental
            $table->integer('gestational_age_weeks')->default(38);
            $table->integer('apgar_score_1min')->default(9);
            $table->integer('apgar_score_5min')->default(10);
            $table->string('blood_group', 10)->default('O+');
            $table->string('status', 50)->default('registered'); // registered, certificate_issued, discharged
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['hospital_id', 'branch_id']);
            $table->index(['birth_date']);
        });

        // 7. Death Records
        Schema::create('death_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained('hospitals')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignId('admission_id')->nullable()->constrained('admissions')->nullOnDelete();
            $table->foreignId('certifying_doctor_id')->constrained('users')->cascadeOnDelete();
            $table->string('certificate_number', 50)->unique();
            $table->date('date_of_death');
            $table->time('time_of_death');
            $table->string('place_of_death', 100)->default('Intensive Care Unit (ICU)');
            $table->text('primary_cause');
            $table->text('secondary_cause')->nullable();
            $table->string('icd10_code', 50)->default('I46.9'); // Cardiac Arrest / Circulatory Failure
            $table->string('next_of_kin_name', 200);
            $table->string('next_of_kin_phone', 50)->nullable();
            $table->string('next_of_kin_relation', 100)->nullable();
            $table->string('body_released_to', 200)->nullable();
            $table->boolean('cremation_pass_issued')->default(true);
            $table->boolean('autopsy_required')->default(false);
            $table->string('status', 50)->default('certified');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['hospital_id', 'branch_id']);
            $table->index(['date_of_death']);
        });

        // 8. Ambulance Fleet & Emergency Dispatch
        Schema::create('ambulances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained('hospitals')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->string('vehicle_number', 50)->unique(); // e.g. MH-01-CR-1088
            $table->string('vehicle_model', 100)->default('Force Traveller Cardiac Care Ambulance');
            $table->string('type', 50)->default('advanced_life_support'); // advanced_life_support (ALS), basic_life_support (BLS), neonatal_mobile_icu
            $table->string('driver_name', 150);
            $table->string('driver_phone', 50);
            $table->string('paramedic_name', 150)->nullable();
            $table->string('status', 50)->default('available'); // available, on_call_dispatch, at_patient_site, en_route_hospital, maintenance
            $table->string('current_location', 255)->default('Hospital Base ER Bay');
            $table->json('equipment_checklist')->nullable(); // Oxygen, Ventilator, Defibrillator, Suction
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['hospital_id', 'branch_id', 'status']);
        });

        Schema::create('ambulance_trips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained('hospitals')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('ambulance_id')->constrained('ambulances')->cascadeOnDelete();
            $table->foreignId('patient_id')->nullable()->constrained('patients')->nullOnDelete();
            $table->string('trip_number', 50)->unique();
            $table->string('patient_name', 200);
            $table->string('patient_phone', 50);
            $table->string('pickup_address', 255);
            $table->string('destination_hospital', 255)->default('Apollo Care Emergency Pavilion');
            $table->string('emergency_category', 100)->default('Acute Coronary Syndrome / STEMI');
            $table->dateTime('dispatched_at');
            $table->dateTime('reached_patient_at')->nullable();
            $table->dateTime('arrived_hospital_at')->nullable();
            $table->string('status', 50)->default('dispatched'); // dispatched, patient_picked_up, completed, cancelled
            $table->decimal('distance_km', 6, 2)->default(12.5);
            $table->decimal('fare_amount', 10, 2)->default(1500.00);
            $table->text('paramedic_triage_notes')->nullable();
            $table->timestamps();

            $table->index(['hospital_id', 'branch_id']);
        });

        // 9. Dietary & Inpatient Clinical Meals
        Schema::create('dietary_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained('hospitals')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignId('admission_id')->nullable()->constrained('admissions')->nullOnDelete();
            $table->foreignId('prescribed_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('diet_type', 100)->default('Diabetic & Low Sodium'); // Diabetic, Renal, Low Sodium, Post-Op Liquid, High Protein
            $table->string('meal_slot', 50)->default('lunch'); // breakfast, lunch, evening_snack, dinner
            $table->date('order_date');
            $table->string('room_bed_number', 100)->nullable();
            $table->string('calorie_target', 50)->default('1800 kcal');
            $table->text('allergies_and_restrictions')->nullable();
            $table->text('kitchen_instructions')->nullable();
            $table->string('status', 50)->default('prescribed'); // prescribed, cooking, quality_checked, delivered, cancelled
            $table->timestamps();

            $table->index(['hospital_id', 'order_date', 'meal_slot']);
        });

        // 10. Biomedical Equipment Maintenance
        Schema::create('biomedical_equipment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained('hospitals')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->string('name', 200); // e.g. Dräger Fabius Plus Anesthesia Workstation
            $table->string('asset_code', 50)->unique(); // e.g. BME-MUM-ANES-01
            $table->string('category', 100)->default('Life Support & Critical Care');
            $table->string('manufacturer', 150)->nullable();
            $table->string('model_number', 100)->nullable();
            $table->string('serial_number', 100)->nullable();
            $table->string('location_room', 100)->nullable();
            $table->date('installation_date')->nullable();
            $table->date('warranty_expiry')->nullable();
            $table->date('last_calibration_date')->nullable();
            $table->date('next_calibration_due')->nullable();
            $table->string('status', 50)->default('operational'); // operational, calibration_due, under_repair, condemned
            $table->text('technical_specifications')->nullable();
            $table->timestamps();

            $table->index(['hospital_id', 'branch_id', 'status']);
        });

        Schema::create('biomedical_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained('hospitals')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('equipment_id')->constrained('biomedical_equipment')->cascadeOnDelete();
            $table->foreignId('reported_by_id')->constrained('users')->cascadeOnDelete();
            $table->string('ticket_number', 50)->unique();
            $table->string('severity', 50)->default('major'); // critical_life_support, major_downtime, routine_check
            $table->text('fault_description');
            $table->dateTime('reported_at');
            $table->dateTime('resolved_at')->nullable();
            $table->string('technician_assigned', 150)->nullable();
            $table->text('corrective_action')->nullable();
            $table->decimal('repair_cost', 10, 2)->default(0.00);
            $table->string('status', 50)->default('open'); // open, in_progress, resolved, closed
            $table->timestamps();

            $table->index(['hospital_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('biomedical_tickets');
        Schema::dropIfExists('biomedical_equipment');
        Schema::dropIfExists('dietary_orders');
        Schema::dropIfExists('ambulance_trips');
        Schema::dropIfExists('ambulances');
        Schema::dropIfExists('death_records');
        Schema::dropIfExists('birth_records');
    }
};
