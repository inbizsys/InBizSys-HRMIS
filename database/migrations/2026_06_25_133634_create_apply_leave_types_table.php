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
        
        Schema::create('apply_leave_types', function (Blueprint $table) {
            $table->id();
            
           
            $table->foreignId('leave_request_id') ->constrained('leave_requests') ->onDelete('cascade'); 
            $table->foreignId('leave_type_id') ->nullable() ->constrained('leave_types') ->onDelete('set null');
            $table->decimal('days_count', 3, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('apply_leave_types');
    }
};
