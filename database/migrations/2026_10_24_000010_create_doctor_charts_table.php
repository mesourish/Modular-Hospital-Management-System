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
        Schema::create('doctor_charts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->string('doctor_name', 150);
            $table->string('degrees', 200); // e.g. MBBS, MD, DM (Cardiology), MS (Ortho)
            $table->string('specialty', 150); // e.g. Interventional Cardiology, Pediatric Neurology
            $table->string('opd_days', 150); // e.g. "Mon, Wed, Fri" or "Daily (Mon-Sat)"
            $table->date('opd_date')->nullable(); // Optional specific date if single-session OPD
            $table->time('start_time'); // e.g. 09:00:00
            $table->time('end_time'); // e.g. 13:00:00
            $table->string('time_slot_label', 150)->nullable(); // e.g. "Morning Slot (09:00 AM - 01:00 PM)"
            $table->string('room_number', 100); // e.g. "OPD Cabin 104, Block B"
            $table->decimal('consultation_fee', 10, 2)->default(500.00); // In INR ₹
            $table->decimal('follow_up_fee', 10, 2)->default(250.00); // In INR ₹
            $table->decimal('emergency_fee', 10, 2)->nullable(); // In INR ₹
            $table->integer('max_patients_per_session')->default(25);
            $table->integer('avg_consultation_time_mins')->default(15);
            $table->enum('status', ['available', 'on_leave', 'busy', 'inactive'])->default('available');
            $table->text('instructions')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['hospital_id', 'branch_id', 'status']);
            $table->index(['department_id']);
            $table->index(['doctor_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctor_charts');
    }
};
