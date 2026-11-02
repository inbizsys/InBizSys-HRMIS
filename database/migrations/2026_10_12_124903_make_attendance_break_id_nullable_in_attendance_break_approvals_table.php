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
       DB::statement('ALTER TABLE attendance_break_approvals MODIFY attendance_break_id BIGINT UNSIGNED NULL;');

        
        Schema::table('attendance_break_approvals', function (Blueprint $table) {
            $table->string('break_type')->nullable()->after('attendance_break_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_break_approvals', function (Blueprint $table) {
            $table->dropColumn('break_type');
        });

        DB::statement('ALTER TABLE attendance_break_approvals MODIFY attendance_break_id BIGINT UNSIGNED NOT NULL;');
    }
};
