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
        
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number')->unique(); 
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->foreignId('leave_type_id')->constrained('leave_types')->onDelete('cascade');
            $table->date('start_date'); 
            $table->date('end_date'); 
            $table->boolean('is_half_day')->default(false); 
            $table->string('leave_shift')->nullable(); 
            $table->decimal('total_days', 4, 2); 
            $table->text('reason'); 
            $table->string('attachment_path')->nullable();
            $table->string('status')->default('Pending Supervisor Approval'); 
            $table->integer('current_approval_level')->default(0); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_requests');
    }
};
