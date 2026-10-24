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
        // Blood Bank Module Tables
        Schema::create('blood_donors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained()->cascadeOnDelete();
            $table->string('donor_code')->index();
            $table->string('full_name');
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->default('male');
            $table->string('blood_group', 10); // A+, B+, O+, AB+, A-, B-, O-, AB-
            $table->string('rh_factor', 10)->default('positive');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->date('last_donation_date')->nullable();
            $table->enum('screening_status', ['eligible', 'deferred', 'rejected'])->default('eligible');
            $table->text('screening_notes')->nullable();
            $table->timestamps();

            $table->unique(['hospital_id', 'donor_code']);
        });

        Schema::create('blood_bags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained()->cascadeOnDelete();
            $table->foreignId('donor_id')->nullable()->constrained('blood_donors')->nullOnDelete();
            $table->string('bag_number')->index();
            $table->string('blood_group', 10);
            $table->string('rh_factor', 10)->default('positive');
            $table->enum('component_type', ['whole_blood', 'prbc', 'platelets', 'ffp', 'cryo'])->default('whole_blood');
            $table->integer('volume_ml')->default(350);
            $table->date('collection_date');
            $table->date('expiry_date');
            $table->string('storage_location')->nullable(); // Refrigerator Unit #3, Shelf B
            $table->enum('status', ['available', 'reserved', 'cross_matched', 'issued', 'expired', 'discarded'])->default('available');
            $table->json('serology_tests')->nullable(); // HIV, Hep B, Hep C, Syphilis, Malaria
            $table->timestamps();

            $table->unique(['hospital_id', 'bag_number']);
        });

        Schema::create('cross_match_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained()->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('admission_id')->nullable()->constrained()->nullOnDelete();
            $table->string('order_number')->index();
            $table->string('required_blood_group', 10);
            $table->enum('component_type', ['whole_blood', 'prbc', 'platelets', 'ffp', 'cryo'])->default('prbc');
            $table->integer('units_requested')->default(1);
            $table->enum('urgency', ['routine', 'urgent', 'stat_emergency'])->default('routine');
            $table->foreignId('doctor_id')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['requested', 'testing', 'compatible', 'incompatible', 'issued'])->default('requested');
            $table->timestamps();

            $table->unique(['hospital_id', 'order_number']);
        });

        Schema::create('blood_issues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cross_match_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('blood_bag_id')->constrained()->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('issued_by_id')->constrained('users')->cascadeOnDelete();
            $table->string('received_by_staff');
            $table->timestamp('issued_at')->useCurrent();
            $table->enum('transfusion_status', ['completed', 'adverse_reaction_reported', 'returned_unopened'])->default('completed');
            $table->text('transfusion_notes')->nullable();
            $table->timestamps();
        });

        // Radiology Module Tables
        Schema::create('radiology_modalities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // Digital X-Ray 500mA, 128-Slice CT Scanner, 3.0T MRI, 4D Ultrasound
            $table->enum('modality_code', ['XRAY', 'CT', 'MRI', 'USG', 'MAMMO', 'DEXA'])->default('XRAY');
            $table->string('room_number')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('radiology_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained()->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('consultation_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('admission_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('modality_id')->constrained('radiology_modalities')->cascadeOnDelete();
            $table->string('order_number')->index();
            $table->string('procedure_name'); // Chest X-Ray PA View, Brain MRI with Contrast, etc.
            $table->text('clinical_indication')->nullable();
            $table->foreignId('ordering_doctor_id')->constrained('users')->cascadeOnDelete();
            $table->enum('priority', ['routine', 'urgent', 'stat_critical'])->default('routine');
            $table->enum('status', ['ordered', 'scheduled', 'performed', 'reported', 'cancelled'])->default('ordered');
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('performed_at')->nullable();
            $table->timestamps();

            $table->unique(['hospital_id', 'order_number']);
        });

        Schema::create('radiology_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained()->cascadeOnDelete();
            $table->foreignId('radiology_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('radiologist_id')->constrained('users')->cascadeOnDelete();
            $table->text('findings');
            $table->text('impression');
            $table->text('recommendations')->nullable();
            $table->string('dicom_study_uid')->nullable();
            $table->string('image_attachment_path')->nullable();
            $table->timestamp('verified_at')->useCurrent();
            $table->enum('status', ['preliminary', 'verified', 'amended'])->default('verified');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('radiology_reports');
        Schema::dropIfExists('radiology_orders');
        Schema::dropIfExists('radiology_modalities');

        Schema::dropIfExists('blood_issues');
        Schema::dropIfExists('cross_match_orders');
        Schema::dropIfExists('blood_bags');
        Schema::dropIfExists('blood_donors');
    }
};
