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

        Schema::create('employees', function (Blueprint $table) {
            $table->id(); // Primary Key
            $table->string('employee_id')->unique(); 
            $table->string('first_name');
            $table->string('last_name');
            $table->date('date_of_birth');
            $table->string('gender');
            $table->string('nic_number')->unique()->nullable();
            $table->string('passport_number')->unique()->nullable();
            $table->string('marital_status'); // Single, Married, etc.
            $table->string('phone_number');
            $table->string('job_position'); 
            $table->string('country');
            $table->date('date_of_join')->nullable();
            $table->string('status')->default('Active'); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
