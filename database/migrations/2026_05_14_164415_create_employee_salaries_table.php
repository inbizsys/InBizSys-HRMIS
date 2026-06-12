<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_salaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('salary_component_id')->constrained('salary_components')->cascadeOnDelete();

            // Calculation type: 'percentage' or 'fixed'
            $table->enum('calculation_type', ['percentage', 'fixed'])->default('percentage');

            // For percentage type (e.g., 50 for 50%)
            $table->decimal('percentage_value', 5, 2)->nullable();

            // For fixed amount type
            $table->decimal('fixed_amount', 15, 2)->nullable();

            // Calculated values
            $table->decimal('monthly_amount', 15, 2)->default(0);
            $table->decimal('annual_amount', 15, 2)->default(0);

            // Type: earning or deduction
            $table->boolean('is_earning')->default(true);

            $table->timestamps();

            // Unique constraint to avoid duplicate components per employee
            $table->unique(['employee_id', 'salary_component_id'], 'unique_employee_component');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_salaries');
    }
};
