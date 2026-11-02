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
        // Convert ENUM to VARCHAR(50) with default 'present'
        DB::statement("ALTER TABLE `attendances` MODIFY `status` VARCHAR(50) NOT NULL DEFAULT 'present'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE `attendances` MODIFY `status` ENUM('present', 'late', 'half_day', 'absent') NOT NULL DEFAULT 'present'");
    }
};
