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
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            
            // Company Information
            $table->string('company_name')->nullable();
            $table->string('company_number')->nullable();
            $table->text('company_address')->nullable();
            
            // Media Files
            $table->string('logo')->nullable();
            $table->string('favicon')->nullable();
            
            // Admin Information
            $table->string('admin_name')->nullable();
            
            // Settings
            $table->json('job_types')->nullable(); // For storing multiple job types as JSON
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};