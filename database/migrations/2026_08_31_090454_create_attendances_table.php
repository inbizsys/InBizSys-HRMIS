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
        
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shift_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('work_location_id')->nullable()->constrained()->nullOnDelete();
            $table->date('date');
            
            // In / Out Times
            $table->time('in_time')->nullable();
            $table->time('out_time')->nullable();
            
            // Coordinates & Device 
            $table->decimal('in_latitude', 10, 8)->nullable();
            $table->decimal('in_longitude', 11, 8)->nullable();
            $table->string('device_id')->nullable();
            $table->boolean('is_location_flagged')->default(false);
            
            // Calculations
            $table->integer('late_minutes')->default(0);
            $table->decimal('total_worked_hours', 5, 2)->default(0.00);
            $table->decimal('ot_hours', 5, 2)->default(0.00);
            $table->enum('status', ['present', 'late', 'half_day', 'absent'])->default('present');
            
            $table->timestamps();

            // limit one record in a day for the employee
            $table->unique(['employee_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
