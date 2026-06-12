<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organisation_id')->constrained('organisations')->cascadeOnDelete();

            // ── Step 1: Basic Details ──────────────────────────
            $table->string('name');
            $table->string('employee_id', 50);
            $table->date('date_of_joining');
            $table->string('work_email')->unique();
            $table->string('mobile_number', 20)->nullable();
            $table->boolean('is_director')->default(false);
            $table->enum('gender', ['male', 'female', 'other']);
            $table->foreignId('work_location_id')->nullable()->constrained('work_locations')->nullOnDelete();
            $table->foreignId('designation_id')->nullable()->constrained('designations')->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->boolean('enable_portal')->default(false);

            // ── Step 2: Salary Details ─────────────────────────
            $table->decimal('annual_ctc', 15, 2)->nullable();

            // ── Step 3: Personal Details ───────────────────────
            $table->date('date_of_birth')->nullable();
            $table->string('parent_name')->nullable();
            $table->string('emergency_contact_number', 20)->nullable();
            $table->string('differently_abled_type')->nullable();
            $table->string('personal_email')->nullable();
            $table->string('residential_address_1')->nullable();
            $table->string('residential_address_2')->nullable();
            $table->string('residential_city')->nullable();
            $table->string('residential_state')->nullable();
            $table->string('residential_pincode', 10)->nullable();

            // ── Step 4: Payment Information ───────────────────
            $table->string('payment_method')->default('bank_transfer');
            $table->string('account_holder_name')->nullable();
            $table->foreignId('bank_branch_id')
                ->nullable()
                ->constrained('bank_branches')
                ->nullOnDelete();
            $table->string('account_number', 30)->nullable();
            $table->string('ifsc', 20)->nullable();
            $table->enum('account_type', ['savings', 'current'])->default('savings');

            // ── Status ─────────────────────────────────────────
            $table->boolean('is_active')->default(true);
            $table->boolean('is_profile_complete')->default(false);

            $table->softDeletes();
            $table->timestamps();

            // Composite unique: employee_id is unique per organisation
            $table->unique(['organisation_id', 'employee_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
