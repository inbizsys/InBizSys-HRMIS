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
        Schema::table('attendance_policies', function (Blueprint $table) {
            // 1. Allowed free break overuse occurrences per month
            $table->unsignedInteger('max_unpaid_break_allowed_per_month')
                  ->default(3)
                  ->after('break_deduction_minutes');

            // 2. Penalty type when break limit is exceeded (None, Deduct Leave, Salary Deduction)
            $table->enum('unpaid_break_penalty_type', ['none', 'deduct_leave', 'salary_deduction'])
                  ->default('none')
                  ->after('max_unpaid_break_allowed_per_month');

            // 3. Deduction rate in days (0.50 Half Day or 1.00 Full Day)
            $table->decimal('unpaid_break_penalty_rate', 3, 2)
                  ->default(0.50)
                  ->after('unpaid_break_penalty_type');

            // 4. Leave type ID to deduct from if penalty is leave deduction
            $table->foreignId('unpaid_break_leave_type_id')
                  ->nullable()
                  ->after('unpaid_break_penalty_rate')
                  ->constrained('leave_types')
                  ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_policies', function (Blueprint $table) {
            // Drop foreign key constraint
            $table->dropForeign(['unpaid_break_leave_type_id']);

            // Drop columns
            $table->dropColumn([
                'max_unpaid_break_allowed_per_month',
                'unpaid_break_penalty_type',
                'unpaid_break_penalty_rate',
                'unpaid_break_leave_type_id',
            ]);
        });
    }
};
