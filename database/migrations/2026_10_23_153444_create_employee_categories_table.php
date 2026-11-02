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
        Schema::create('employee_categories', function (Blueprint $table) {
            $table->id();

            // Basic details
            $table->string('name', 100); // e.g., Executive, Junior Developer, Factory Operator
            $table->string('code', 20)->unique(); // e.g., EXEC, DEV-JR, FACT-01 (For payslips and reports)

            // Basic salary range thresholds for HR validation
            $table->decimal('min_basic_salary', 12, 2)->default(0.00);
            $table->decimal('max_basic_salary', 12, 2)->default(0.00);

            // Overtime rules
            $table->boolean('is_ot_eligible')->default(false); // Whether category is overtime eligible
            $table->integer('ot_divisor_hours')->default(200); // Divisor hours to calculate hourly rate (Shop & Office = 200, Wages Board = 200 or 240)
            $table->decimal('ot_calculation_rate', 4, 2)->default(1.50); // Standard normal overtime multiplier (e.g. 1.50x)

            // No-Pay and daily rate rules
            $table->integer('standard_monthly_days')->default(30); // Working days divisor for calculating daily rate for No-Pay (Shop & Office = 30, Wages Board = 26)

            // Status control
            $table->boolean('is_active')->default(true); // Whether category is active for selections

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_categories');
    }
};
