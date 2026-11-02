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
            $table->integer('max_early_leave_allowed_per_month')->default(3)->after('max_late_allowed_per_month');
            $table->string('early_leave_penalty_type')->default('none')->after('late_penalty_rate');
            $table->decimal('early_leave_penalty_rate', 4, 2)->default(0.50)->after('early_leave_penalty_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_policies', function (Blueprint $table) {
            $table->dropColumn([
                'max_early_leave_allowed_per_month',
                'early_leave_penalty_type',
                'early_leave_penalty_rate'
            ]);
        });
    }
};
