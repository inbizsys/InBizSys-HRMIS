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
        Schema::table('payrolls', function (Blueprint $table) {
            $table->decimal('arrears_brought_forward', 12, 2)->default(0.00)->after('other_deductions');
            $table->decimal('unrecovered_amount', 12, 2)->default(0.00)->after('net_salary');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropColumn(['arrears_brought_forward', 'unrecovered_amount']);
        });
    }
};