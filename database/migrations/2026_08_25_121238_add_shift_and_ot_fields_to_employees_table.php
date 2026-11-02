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
        Schema::table('employees', function (Blueprint $table) {
            // Shift ID foreign key 
            $table->foreignId('shift_id')
                ->nullable()
                ->after('department_id')
                ->constrained('shifts')
                ->nullOnDelete();

            // OT Eligibility Flag
            $table->boolean('is_ot_eligible')
                ->default(false)
                ->after('shift_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign(['shift_id']);
            $table->dropColumn(['shift_id', 'is_ot_eligible']);
        });
    }
};
