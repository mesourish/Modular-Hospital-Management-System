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
        Schema::create('hospitals', function (Blueprint $table) {
            $table->id();
            $table->string('name', 200);
            $table->string('code', 50)->unique();
            $table->string('slug', 200)->unique();
            $table->enum('type', ['clinic', 'general_hospital', 'multi_specialty', 'diagnostic_center'])->default('general_hospital');
            $table->string('license_number', 100)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('address', 255)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 100)->nullable();
            $table->string('country', 50)->default('India');
            $table->string('currency', 10)->default('INR');
            $table->string('subscription_plan', 50)->default('enterprise');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'code']);
        });

        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained()->cascadeOnDelete();
            $table->string('name', 200);
            $table->string('code', 50);
            $table->string('phone', 30)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('address', 255)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 100)->nullable();
            $table->string('pincode', 10)->nullable();
            $table->string('manager_name', 150)->nullable();
            $table->boolean('is_main_branch')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['hospital_id', 'code']);
            $table->index(['hospital_id', 'is_main_branch']);
        });

        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name', 150);
            $table->string('code', 50);
            $table->enum('type', ['clinical', 'diagnostic', 'pharmacy', 'administrative', 'support'])->default('clinical');
            $table->string('description', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['hospital_id', 'code']);
            $table->index(['hospital_id', 'branch_id']);
        });

        // Add tenant and role fields to users table
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('hospital_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->after('hospital_id')->constrained()->nullOnDelete();
            $table->string('role', 50)->default('hospital_admin')->after('email'); // super_admin, hospital_admin, branch_manager, doctor, nurse, pharmacist, radiologist, lab_tech, hr_manager, accountant
            $table->string('phone', 30)->nullable()->after('role');
            $table->string('avatar', 255)->nullable()->after('phone');
            $table->boolean('is_active')->default(true)->after('avatar');

            $table->index(['hospital_id', 'branch_id', 'role']);
        });

        // Tenant Module Registry (controls which extensions are entitled/active for each hospital)
        Schema::create('hospital_modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained()->cascadeOnDelete();
            $table->string('module_key', 50); // blood_bank, radiology, pharmacy, opd, admissions, hr_payroll, billing, er_workforce
            $table->boolean('is_enabled')->default(true);
            $table->json('settings')->nullable();
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->unique(['hospital_id', 'module_key']);
            $table->index(['hospital_id', 'is_enabled']);
        });

        // Platform-wide Immutable Audit Logs
        Schema::create('audit_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('module_key', 50)->index();
            $table->string('action', 100);
            $table->string('entity_type', 100)->nullable();
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->json('details')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['hospital_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_events');
        Schema::dropIfExists('hospital_modules');

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['hospital_id']);
            $table->dropForeign(['branch_id']);
            $table->dropColumn(['hospital_id', 'branch_id', 'role', 'phone', 'avatar', 'is_active']);
        });

        Schema::dropIfExists('departments');
        Schema::dropIfExists('branches');
        Schema::dropIfExists('hospitals');
    }
};
