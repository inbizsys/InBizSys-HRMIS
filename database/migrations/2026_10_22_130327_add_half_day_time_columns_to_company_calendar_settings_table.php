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
        Schema::table('company_calendar_settings', function (Blueprint $table) {
            $table->time('half_day_start_time')->nullable()->after('half_day_shift');
            $table->time('half_day_end_time')->nullable()->after('half_day_start_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('company_calendar_settings', function (Blueprint $table) {
            $table->dropColumn([
                'half_day_start_time',
                'half_day_end_time'
            ]);
        });
    }
};
