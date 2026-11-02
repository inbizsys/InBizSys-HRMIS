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
        
        Schema::create('attendance_policies', function (Blueprint $table) {
            $table->id();
            $table->string('policy_name')->default('Standard Corporate Policy');
            $table->unsignedInteger('max_late_allowed_per_month')->default(3);
            $table->enum('late_penalty_type', ['none', 'deduct_leave', 'salary_deduction'])->default('deduct_leave');
            
            $table->decimal('late_penalty_rate', 3, 2)->default(0.50); 
            
            $table->foreignId('deduct_leave_type_id')->nullable()->constrained('leave_types')->nullOnDelete();
            
            $table->unsignedInteger('break_deduction_minutes')->default(60);
            $table->unsignedInteger('overtime_minimum_minutes')->default(60);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_policies');
    }
};
