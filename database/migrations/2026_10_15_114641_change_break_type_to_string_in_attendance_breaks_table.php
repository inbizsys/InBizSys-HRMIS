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
        Schema::table('attendance_breaks', function (Blueprint $table) {
            $table->string('break_type', 50)->default('tea')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_breaks', function (Blueprint $table) {
            $table->enum('break_type', ['lunch', 'tea', 'personal', 'official'])->default('personal')->change();
        });
    }
};
