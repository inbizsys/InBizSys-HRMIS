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
        Schema::create('salary_components', function (Blueprint $table) {
            $table->id();

            // Foreign key to the organisation
            $table->foreignId('organisation_id')
                  ->constrained('organisations')
                  ->cascadeOnDelete();

            // Component Type: earning or deduction
            $table->enum('type', ['earning', 'deduction']);

            // Component Details
            $table->string('name');
            $table->string('name_in_payslip');
           $table->decimal('limit', 12, 2)->unsigned()->nullable();

            // Status
            $table->boolean('is_active')->default(true);

            // Protect built-in "Basic" component from deletion
            $table->boolean('is_system')->default(false);

            $table->timestamps();
            $table->softDeletes();

            // Name must be unique per organisation and type
            $table->unique(['organisation_id', 'type', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salary_components');
    }
};
