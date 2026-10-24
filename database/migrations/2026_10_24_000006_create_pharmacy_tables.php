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
        Schema::create('medicines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained()->cascadeOnDelete();
            $table->string('code')->index();
            $table->string('brand_name');
            $table->string('generic_name');
            $table->string('category'); // Antibiotic, Analgesic, Antipyretic, Cardiac, etc.
            $table->enum('dosage_form', ['tablet', 'capsule', 'syrup', 'injection', 'iv_fluid', 'ointment', 'inhaler'])->default('tablet');
            $table->string('strength')->nullable(); // e.g. 500mg, 10mg/ml
            $table->string('manufacturer')->nullable();
            $table->integer('min_reorder_level')->default(50);
            $table->boolean('is_prescription_required')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['hospital_id', 'code']);
        });

        Schema::create('medicine_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained()->cascadeOnDelete();
            $table->foreignId('medicine_id')->constrained()->cascadeOnDelete();
            $table->string('batch_number');
            $table->date('expiry_date');
            $table->decimal('purchase_price', 10, 2);
            $table->decimal('selling_price', 10, 2);
            $table->integer('quantity_in_stock')->default(0);
            $table->string('rack_location')->nullable();
            $table->timestamps();

            $table->unique(['hospital_id', 'medicine_id', 'batch_number']);
        });

        Schema::create('dispense_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained()->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('prescription_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('medicine_batch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('dispensed_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_price', 10, 2);
            $table->timestamp('dispensed_at')->useCurrent();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dispense_records');
        Schema::dropIfExists('medicine_batches');
        Schema::dropIfExists('medicines');
    }
};
