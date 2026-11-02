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
        Schema::create('company_calendar_settings', function (Blueprint $table) {
        $table->id();
        $table->string('day_of_week')->unique(); 
        $table->boolean('is_working_day')->default(true);
        $table->string('day_type')->default('Full Day'); 
        $table->string('half_day_shift')->nullable(); 
        
        $table->string('present_calculation_rule')->default('1.0'); 
        $table->string('absent_calculation_rule')->default('1.0'); 
        
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_calendar_settings');
    }
};
