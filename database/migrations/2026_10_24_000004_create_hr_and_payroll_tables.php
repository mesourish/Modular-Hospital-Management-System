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
        Schema::create('designations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained()->cascadeOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title', 150);
            $table->string('description', 255)->nullable();
            $table->decimal('min_salary', 10, 2)->default(0.00);
            $table->decimal('max_salary', 10, 2)->default(0.00);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('employee_code', 50)->index();
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('designation_id')->nullable()->constrained()->nullOnDelete();
            $table->string('email', 150)->nullable();
            $table->string('phone', 30)->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->default('male');
            $table->date('date_of_birth')->nullable();
            $table->date('date_of_joining');
            $table->string('qualification', 200)->nullable();
            $table->string('medical_license_no', 100)->nullable(); // MCI / State Medical Council Registration No.
            $table->date('license_expiry_date')->nullable();
            $table->enum('employment_type', ['full_time', 'part_time', 'visiting_consultant', 'contract'])->default('full_time');
            $table->enum('status', ['active', 'on_leave', 'resigned', 'terminated'])->default('active');
            $table->decimal('base_salary', 12, 2)->default(0.00);
            $table->string('bank_name', 100)->nullable();
            $table->string('bank_account_no', 50)->nullable();
            $table->string('pan_number', 20)->nullable(); // Indian Income Tax PAN
            $table->timestamps();

            $table->unique(['hospital_id', 'employee_code']);
            $table->index(['hospital_id', 'branch_id', 'status']);
        });

        Schema::create('salary_structures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->decimal('basic_salary', 12, 2);
            $table->decimal('hra', 12, 2)->default(0.00);
            $table->decimal('medical_allowance', 12, 2)->default(0.00);
            $table->decimal('conveyance_allowance', 12, 2)->default(0.00);
            $table->decimal('special_allowance', 12, 2)->default(0.00);
            $table->decimal('pf_deduction', 12, 2)->default(0.00); // Indian Provident Fund 12%
            $table->decimal('tax_deduction', 12, 2)->default(0.00); // TDS Indian Income Tax
            $table->decimal('gross_salary', 12, 2);
            $table->decimal('net_salary', 12, 2);
            $table->date('effective_from');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['hospital_id', 'employee_id']);
        });

        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('payslip_number', 50)->index();
            $table->string('payroll_month', 20); // e.g. "October"
            $table->integer('payroll_year'); // e.g. 2026
            $table->decimal('basic_salary', 12, 2);
            $table->decimal('total_allowances', 12, 2)->default(0.00);
            $table->decimal('total_deductions', 12, 2)->default(0.00);
            $table->decimal('net_salary', 12, 2);
            $table->enum('payment_status', ['generated', 'approved', 'paid'])->default('generated');
            $table->date('payment_date')->nullable();
            $table->string('payment_method', 50)->nullable(); // neft_rtgs, cheque, upi, cash
            $table->string('remarks', 255)->nullable();
            $table->timestamps();

            $table->unique(['hospital_id', 'employee_id', 'payroll_month', 'payroll_year'], 'payrolls_emp_period_uq');
            $table->index(['hospital_id', 'branch_id', 'payroll_year', 'payroll_month'], 'payrolls_branch_period_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payrolls');
        Schema::dropIfExists('salary_structures');
        Schema::dropIfExists('employees');
        Schema::dropIfExists('designations');
    }
};
