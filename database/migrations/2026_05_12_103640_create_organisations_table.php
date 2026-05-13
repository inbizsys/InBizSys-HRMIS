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
        Schema::create('organisations', function (Blueprint $table) {
            $table->id();

            // Organisation Identity
            $table->string('organisation_id')->unique();
            $table->string('name');
            $table->string('logo_path')->nullable();

            // Location & Industry
            $table->string('business_location');
            $table->string('industry');

            // Regional Settings
            $table->string('date_format')->default('dd/mm/yyyy');
            $table->string('field_separator', 10)->default('/');

            // Organisation Address (Primary / Head Office)
            $table->string('address_line_1');
            $table->string('address_line_2')->nullable();
            $table->string('city');
            $table->string('state');
            $table->string('pincode', 20);
            $table->string('country')->default('India');

            // Filing Address (may differ from org address)
            $table->boolean('filing_address_same_as_org')->default(true);
            $table->string('filing_address_line_1')->nullable();
            $table->string('filing_address_line_2')->nullable();
            $table->string('filing_city')->nullable();
            $table->string('filing_state')->nullable();
            $table->string('filing_pincode', 20)->nullable();
            $table->string('filing_country')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organisations');
    }
};
