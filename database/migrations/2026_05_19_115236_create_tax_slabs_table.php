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
        Schema::create('tax_slabs', function (Blueprint $table) {
            $table->id();

            // Foreign key to organisations table
            $table->foreignId('organisation_id')->constrained('organisations')->cascadeOnDelete();

            // Slab label — e.g. "Slab 1", "Slab 2" (auto-generated, stored for display)
            $table->unsignedTinyInteger('slab_order');

            // Min & Max monthly income thresholds (in LKR)
            // min_amount = null means "starts from 0"
            // max_amount = null means "no upper limit" (top slab)
            $table->decimal('min_amount', 15, 2)->nullable()->comment('Lower bound of monthly income slab (null = 0)');
            $table->decimal('max_amount', 15, 2)->nullable()->comment('Upper bound of monthly income slab (null = unlimited)');

            // Tax rate for this slab (e.g. 6.00 = 6%)
            // null means relief / exempt slab
            $table->decimal('tax_percentage', 5, 2)->nullable()->comment('Tax rate as percentage; null = Relief from Tax');

            // Human-readable description of the slab shown in the table
            $table->string('description')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tax_slabs');
    }
};
