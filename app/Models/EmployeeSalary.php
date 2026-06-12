<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeSalary extends Model
{
    protected $table = 'employee_salaries';

    protected $fillable = [
        'employee_id',
        'salary_component_id',
        'calculation_type', // 'percentage' or 'fixed'
        'percentage_value',
        'fixed_amount',
        'monthly_amount',
        'annual_amount',
        'is_earning', // true = earning, false = deduction
    ];

    protected $casts = [
        'percentage_value' => 'decimal:2',
        'fixed_amount' => 'decimal:2',
        'monthly_amount' => 'decimal:2',
        'annual_amount' => 'decimal:2',
        'is_earning' => 'boolean',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function salaryComponent(): BelongsTo
    {
        return $this->belongsTo(SalaryComponent::class);
    }
}
