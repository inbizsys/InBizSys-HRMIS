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
        
        Schema::create('leave_types', function (Blueprint $table) {
            $table->id();
            $table->string('name'); 
            $table->integer('max_days_per_year'); 
            $table->integer('required_approval_levels')->default(1); 
            $table->integer('auto_escalate_above_days')->nullable(); 
            $table->boolean('requires_attachment')->default(false); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_types');
    }
};
