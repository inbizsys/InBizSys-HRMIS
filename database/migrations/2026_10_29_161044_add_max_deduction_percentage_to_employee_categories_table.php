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
            $table->decimal('max_deduction_percentage', 5, 2)->default(60.00)->after('standard_monthly_days');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_categories', function (Blueprint $table) {
            $table->dropColumn('max_deduction_percentage');
        });
    }
};
