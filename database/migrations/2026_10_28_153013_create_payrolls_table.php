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
         Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('payroll_month', 7)->index(); // Format: YYYY-MM (e.g. 2026-10)
            // 1. Basic & Earnings
            $table->decimal('basic_salary', 12, 2)->default(0.00);
            $table->decimal('epf_applicable_allowances', 12, 2)->default(0.00); // BRA / Cost of Living
            $table->decimal('other_allowances', 12, 2)->default(0.00);          // Travel, Attendance, etc.
            
            // 2. Overtime Earnings (From Overtime Module)
            $table->decimal('normal_ot_hours', 8, 2)->default(0.00);
            $table->decimal('normal_ot_amount', 12, 2)->default(0.00);
            $table->decimal('weekend_ot_hours', 8, 2)->default(0.00);
            $table->decimal('weekend_ot_amount', 12, 2)->default(0.00);
            $table->decimal('holiday_ot_hours', 8, 2)->default(0.00);
            $table->decimal('holiday_ot_amount', 12, 2)->default(0.00);
            $table->decimal('total_ot_amount', 12, 2)->default(0.00);
            // 3. Gross Earnings
            $table->decimal('gross_salary', 12, 2)->default(0.00);
            // 4. Attendance & No-Pay Deductions
            $table->decimal('absent_days', 5, 2)->default(0.00);
            $table->decimal('no_pay_amount', 12, 2)->default(0.00);
            // 5. Statutory EPF / ETF
            $table->decimal('epf_qualifying_salary', 12, 2)->default(0.00); // (Basic + EPF Allowances - No Pay)
            $table->decimal('epf_employee_rate', 5, 2)->default(8.00);
            $table->decimal('epf_employee_amount', 12, 2)->default(0.00);   // Deducted from salary
            $table->decimal('epf_employer_rate', 5, 2)->default(12.00);
            $table->decimal('epf_employer_amount', 12, 2)->default(0.00);   // Company contribution
            $table->decimal('etf_employer_rate', 5, 2)->default(3.00);
            $table->decimal('etf_employer_amount', 12, 2)->default(0.00);   // Company contribution
            // 6. Other Deductions & Net Pay
            $table->decimal('other_deductions', 12, 2)->default(0.00);       // Advances, Loans, etc.
            $table->decimal('total_deductions', 12, 2)->default(0.00);
            $table->decimal('net_salary', 12, 2)->default(0.00);
            // 7. Payment & Bank Information Snapshot
            $table->string('payment_method', 50)->default('bank_transfer');
            $table->string('bank_name')->nullable();
            $table->string('branch_name')->nullable();
            $table->string('account_number')->nullable();
            // 8. Workflow Status & Audit
            $table->enum('status', ['draft', 'processed', 'approved', 'paid'])->default('draft');
            $table->text('remarks')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            // Ensure an employee has only one payroll record per month
            $table->unique(['employee_id', 'payroll_month']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};
