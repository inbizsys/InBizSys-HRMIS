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
        Schema::create('work_locations', function (Blueprint $table) {
            $table->id();

            // Foreign key to the organisation
            $table->foreignId('organisation_id')
                  ->constrained('organisations')
                  ->cascadeOnDelete();

            // Work Location Details
            $table->string('name');

            // Address
            $table->string('address_line_1');
            $table->string('address_line_2')->nullable();
            $table->string('state');
            $table->string('city');
            $table->string('pincode', 20);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_locations');
    }
};
