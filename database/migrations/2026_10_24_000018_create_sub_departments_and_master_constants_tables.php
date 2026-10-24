<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Sub-Departments (Clinical Units under Departments)
        Schema::create('sub_departments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained('hospitals')->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('department_id')->constrained('departments')->cascadeOnDelete();
            $table->string('name', 150);
            $table->string('code', 50);
            $table->string('description', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['hospital_id', 'code']);
            $table->index(['hospital_id', 'department_id']);
        });

        // 2. Master System & Healthcare Lookup Constants
        Schema::create('hospital_constants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->nullable()->constrained('hospitals')->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->string('category', 60)->index(); // specialization, triage_level, ambulance_type, shift_type, blood_component, payment_mode, ward_type
            $table->string('key', 60);
            $table->string('name', 150);
            $table->string('code', 50)->nullable();
            $table->string('badge_color', 50)->default('primary');
            $table->integer('sort_order')->default(0);
            $table->string('description', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['category', 'hospital_id', 'branch_id']);
        });

        // 3. Extend Ambulance Trips with Scheduler & Custom Driver Assignment
        Schema::table('ambulance_trips', function (Blueprint $table) {
            $table->string('assigned_driver_name', 150)->nullable()->after('destination_hospital');
            $table->string('assigned_driver_phone', 50)->nullable()->after('assigned_driver_name');
            $table->dateTime('scheduled_for')->nullable()->after('emergency_category');
            $table->text('customer_notes')->nullable()->after('paramedic_triage_notes');
        });
    }

    public function down(): void
    {
        Schema::table('ambulance_trips', function (Blueprint $table) {
            $table->dropColumn([
                'assigned_driver_name',
                'assigned_driver_phone',
                'scheduled_for',
                'customer_notes',
            ]);
        });

        Schema::dropIfExists('hospital_constants');
        Schema::dropIfExists('sub_departments');
    }
};
