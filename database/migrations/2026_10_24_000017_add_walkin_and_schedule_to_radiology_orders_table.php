<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('radiology_orders', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('hospital_id')->constrained('branches')->nullOnDelete();
            $table->boolean('is_walkin')->default(false)->after('status');
            $table->string('token_number', 50)->nullable()->after('order_number');
            $table->string('slot_time', 50)->nullable()->after('scheduled_at');
            $table->string('referring_doctor_name', 150)->nullable()->after('ordering_doctor_id');
            $table->string('patient_contact', 50)->nullable()->after('referring_doctor_name');
            $table->text('notes')->nullable()->after('clinical_indication');
        });
    }

    public function down(): void
    {
        Schema::table('radiology_orders', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropColumn([
                'branch_id',
                'is_walkin',
                'token_number',
                'slot_time',
                'referring_doctor_name',
                'patient_contact',
                'notes',
            ]);
        });
    }
};
