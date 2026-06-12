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
        Schema::create('epf_etf_settings', function (Blueprint $table) {
            $table->id();

            // Foreign key to the organisation
            $table->foreignId('organisation_id')
                  ->constrained('organisations')
                  ->cascadeOnDelete();

            // Fund type: 'epf' or 'etf'
            $table->enum('fund_type', ['epf', 'etf']);

            // EPF Registration Number / ETF Registration Number
            $table->string('registration_number')->nullable();

            // Deduction cycle: monthly, bi-monthly, etc.
            $table->enum('deduction_cycle', ['monthly', 'bi_monthly', 'quarterly'])->default('monthly');

            // Contribution Rates (stored as percentage, e.g. 8.00 = 8%)
            $table->decimal('employee_rate', 5, 2)->unsigned()->default(0.00);
            $table->decimal('employer_rate', 5, 2)->unsigned()->default(0.00);

            // Status
            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes();

            // One setting per fund type per organisation
            $table->unique(['organisation_id', 'fund_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('epf_etf_settings');
    }
};
