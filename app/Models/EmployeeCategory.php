<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class EmployeeCategory extends Model
{
    use HasFactory;

    protected $table = 'employee_categories';

    protected $fillable = [
        'name',
        'code',
        'min_basic_salary',
        'max_basic_salary',
        'is_ot_eligible',
        'ot_divisor_hours',
        'ot_calculation_rate',
        'ot_weekend_rate',    
        'ot_holiday_rate', 
        'standard_monthly_days',
        'max_deduction_percentage',
        'is_active',
    ];

    protected $casts = [
        'min_basic_salary' => 'decimal:2',
        'max_basic_salary' => 'decimal:2',
        'is_ot_eligible' => 'boolean',
        'ot_divisor_hours' => 'integer',
        'ot_calculation_rate' => 'decimal:2',
        'ot_weekend_rate'     => 'decimal:2', 
        'ot_holiday_rate'     => 'decimal:2',
        'standard_monthly_days' => 'integer',
        'max_deduction_percentage' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function setMaxBasicSalaryAttribute($value): void
    {
        $this->attributes['max_basic_salary'] = ($value !== null && trim((string) $value) !== '') ? $value : null;
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class, 'employee_category_id');
    }
}