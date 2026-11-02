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
       
        Schema::create('leave_request_histories', function (Blueprint $table) {
            $table->id();
            
    
            $table->foreignId('leave_request_id')->constrained('leave_requests')->onDelete('cascade');
            
            $table->integer('version_number')->default(1);
        
            $table->unsignedBigInteger('leave_type_id')->nullable();
        
            $table->integer('employee_id');
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_half_day')->default(0);
            $table->string('leave_shift')->nullable();
            $table->decimal('total_days', 5, 2);
            $table->decimal('paid_days', 5, 2)->default(0);
            $table->decimal('no_pay_days', 5, 2)->default(0);
            $table->text('reason')->nullable();
            $table->string('attachment_path')->nullable();
            $table->string('status'); 
           
            $table->json('split_details_json')->nullable();
      
            $table->text('rejection_remarks')->nullable();
            
            $table->unsignedBigInteger('actioned_by_employee_id')->nullable();

            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_request_histories');
    }
};
