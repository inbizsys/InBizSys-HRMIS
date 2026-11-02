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
         Schema::table('employee_categories', function (Blueprint $table) {
            // Add weekend and holiday overtime multiplier rates
            $table->decimal('ot_weekend_rate', 4, 2)->default(1.50)->after('ot_calculation_rate');
            $table->decimal('ot_holiday_rate', 4, 2)->default(2.00)->after('ot_weekend_rate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_categories', function (Blueprint $table) {
            $table->dropColumn(['ot_weekend_rate', 'ot_holiday_rate']);
        });
    }
};
