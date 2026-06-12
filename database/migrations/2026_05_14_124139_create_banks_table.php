<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('banks', function (Blueprint $table) {
            $table->id();

            $table->string('bank_code', 20)->unique();   // e.g. BOC, HNB, COMM
            $table->string('bank_name');                 // e.g. Bank of Ceylon
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('bank_branches', function (Blueprint $table) {
            $table->id();

            $table->foreignId('bank_id')->constrained('banks')->cascadeOnDelete();
            $table->string('branch_code', 20);           // e.g. BOC-001
            $table->string('branch_name');               // e.g. Colombo Main Branch
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Duplicate branch code cannot be entered for the same bank
            $table->unique(['bank_id', 'branch_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_branches');
        Schema::dropIfExists('banks');
    }
};
