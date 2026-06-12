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
        Schema::create('employee_salaries_and_banks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->decimal('basic_salary', 12, 2); 
            $table->string('currency', 3)->default('LKR'); 
            $table->decimal('allowances', 12, 2)->default(0.00); 
            $table->decimal('deductions', 12, 2)->default(0.00); 
            $table->decimal('bonus', 12, 2)->default(0.00); 
            $table->string('payment_method'); 
            $table->string('bank_name')->nullable();
            $table->string('branch_name')->nullable();
            $table->string('account_holder_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('account_type')->nullable(); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_salary_and_banks');
    }
};
