<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->nullable()->constrained('hospitals')->nullOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->string('group', 50)->default('general'); // general, frontend, contact, clinical, gateway
            $table->string('key', 100);
            $table->text('value')->nullable();
            $table->string('type', 20)->default('string'); // string, boolean, integer, json, text
            $table->string('label')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_public')->default(true);
            $table->timestamps();

            $table->index(['hospital_id', 'branch_id', 'group']);
            $table->index(['key', 'hospital_id', 'branch_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
