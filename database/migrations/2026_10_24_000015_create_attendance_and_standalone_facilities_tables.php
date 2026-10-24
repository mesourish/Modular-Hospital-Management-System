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
        // 1. Extend branches with facility type and standalone capability
        Schema::table('branches', function (Blueprint $table) {
            if (! Schema::hasColumn('branches', 'facility_type')) {
                $table->string('facility_type', 50)->default('hospital_branch')->after('code'); // hospital_branch, pharmacy, blood_bank, diagnostic_lab
            }
            if (! Schema::hasColumn('branches', 'is_standalone')) {
                $table->boolean('is_standalone')->default(false)->after('facility_type');
            }
            if (! Schema::hasColumn('branches', 'parent_branch_id')) {
                $table->foreignId('parent_branch_id')->nullable()->after('is_standalone')->constrained('branches')->nullOnDelete();
            }
            if (! Schema::hasColumn('branches', 'license_number')) {
                $table->string('license_number', 100)->nullable()->after('parent_branch_id'); // e.g. DL 20B/21B or Blood Bank Lic
            }
        });

        // 2. Staff Attendance Records (Branch-Wise & Daily Roster Clock In/Out)
        Schema::create('staff_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->time('clock_in_time')->nullable();
            $table->time('clock_out_time')->nullable();
            $table->enum('status', ['present', 'late', 'half_day', 'absent', 'on_leave', 'on_duty'])->default('present');
            $table->enum('work_shift', ['morning', 'evening', 'night', 'general'])->default('general');
            $table->decimal('total_hours', 4, 2)->default(8.00);
            $table->string('remarks')->nullable();
            $table->boolean('is_verified')->default(true);
            $table->timestamps();

            $table->unique(['hospital_id', 'employee_id', 'date'], 'emp_hospital_date_unique');
            $table->index(['hospital_id', 'branch_id', 'date']);
            $table->index(['hospital_id', 'status']);
        });

        // 3. Dedicated Pharmacy Stores (Standalone Outlets vs In-Hospital Pharmacies)
        Schema::create('pharmacy_stores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete(); // NULL if standalone direct to super admin
            $table->string('name', 200);
            $table->string('code', 50);
            $table->string('slug', 200)->index();
            $table->string('store_type', 50)->default('in_hospital');
            $table->boolean('is_standalone')->default(false);
            $table->string('license_number', 100)->nullable(); // e.g. 20B/21B-MH-492019
            $table->string('manager_name', 150)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('address', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['hospital_id', 'code']);
            $table->index(['hospital_id', 'is_standalone']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pharmacy_stores');
        Schema::dropIfExists('staff_attendances');

        Schema::table('branches', function (Blueprint $table) {
            $table->dropForeign(['parent_branch_id']);
            $table->dropColumn(['facility_type', 'is_standalone', 'parent_branch_id', 'license_number']);
        });
    }
};
