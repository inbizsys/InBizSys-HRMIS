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
        Schema::table('salary_components', function (Blueprint $table) {
            // Calculation type: 'fixed' (fixed amount) or 'percentage' (% of basic)
            $table->string('calculation_type', 20)->default('fixed')->after('type');
            // Subject to EPF/ETF statutory calculations (true for qualifying allowances)
            $table->boolean('is_epf_applicable')->default(false)->after('limit');
            // Subject to Income Tax / APIT
            $table->boolean('is_taxable')->default(true)->after('is_epf_applicable');
            // Recurring monthly allowance or one-time temporary component
            $table->boolean('is_recurring')->default(true)->after('is_taxable');
        });
    
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('salary_components', function (Blueprint $table) {
            $table->dropColumn([
                'calculation_type',
                'is_epf_applicable',
                'is_taxable',
                'is_recurring',
            ]);
        });
    
    }
};
