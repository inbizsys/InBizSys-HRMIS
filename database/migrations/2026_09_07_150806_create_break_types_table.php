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
        Schema::create('break_types', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g. Tea Break, Lunch Break
            $table->string('code')->unique(); // e.g. tea, lunch, official, personal
            $table->integer('max_minutes')->default(15); // e.g. 15, 60
            $table->boolean('is_paid')->default(true); // true = Paid, false = Unpaid
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('break_types');
    }
};
