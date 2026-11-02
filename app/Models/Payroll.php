<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payroll extends Model
{
    use HasFactory;

    protected $table = 'payrolls';

    protected $fillable = [
        'employee_id',
        'payroll_month',
        'basic_salary',
        'epf_applicable_allowances',
        'other_allowances',
        'normal_ot_hours',
        'normal_ot_amount',
        'weekend_ot_hours',
        'weekend_ot_amount',
        'holiday_ot_hours',
        'holiday_ot_amount',
        'total_ot_amount',
        'gross_salary',
        'absent_days',
        'no_pay_amount',
        'epf_qualifying_salary',
        'epf_employee_rate',
        'epf_employee_amount',
        'epf_employer_rate',
        'epf_employer_amount',
        'etf_employer_rate',
        'etf_employer_amount',
        'other_deductions',
        'total_deductions',
        'net_salary',
        'payment_method',
        'bank_name',
        'branch_name',
        'account_number',
        'allowance_breakdown',
        'deduction_breakdown',
        'arrears_brought_forward',
        'unrecovered_amount',
        'status',
        'remarks',
        'processed_by',
        'approved_by',

    ];

    protected $casts = [
        'basic_salary'              => 'decimal:2',
        'epf_applicable_allowances' => 'decimal:2',
        'other_allowances'          => 'decimal:2',
        'normal_ot_hours'           => 'decimal:2',
        'normal_ot_amount'          => 'decimal:2',
        'weekend_ot_hours'          => 'decimal:2',
        'weekend_ot_amount'         => 'decimal:2',
        'holiday_ot_hours'          => 'decimal:2',
        'holiday_ot_amount'         => 'decimal:2',
        'total_ot_amount'           => 'decimal:2',
        'gross_salary'              => 'decimal:2',
        'absent_days'               => 'decimal:2',
        'no_pay_amount'             => 'decimal:2',
        'epf_qualifying_salary'     => 'decimal:2',
        'epf_employee_rate'         => 'decimal:2',
        'epf_employee_amount'       => 'decimal:2',
        'epf_employer_rate'         => 'decimal:2',
        'epf_employer_amount'       => 'decimal:2',
        'etf_employer_rate'         => 'decimal:2',
        'etf_employer_amount'       => 'decimal:2',
        'other_deductions'          => 'decimal:2',
        'total_deductions'          => 'decimal:2',
        'net_salary'                => 'decimal:2',
        'allowance_breakdown' => 'array',
        'deduction_breakdown' => 'array',
    ];

    /**
     * Relationship to Employee
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Relationship to User who processed
     */
    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * Relationship to User who approved
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}