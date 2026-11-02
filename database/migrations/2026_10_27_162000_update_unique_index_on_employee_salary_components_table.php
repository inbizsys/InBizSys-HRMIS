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
        Schema::table('employee_salary_components', function (Blueprint $table) {
            $table->unique(['employee_id', 'salary_component_id', 'applicable_month'], 'emp_sal_comp_month_unique');
        });
        // 2. Drop the old unique index
        Schema::table('employee_salary_components', function (Blueprint $table) {
            $table->dropUnique('emp_sal_comp_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_salary_components', function (Blueprint $table) {
            $table->unique(['employee_id', 'salary_component_id'], 'emp_sal_comp_unique');
        });
        Schema::table('employee_salary_components', function (Blueprint $table) {
            $table->dropUnique('emp_sal_comp_month_unique');
        });
    }
};
