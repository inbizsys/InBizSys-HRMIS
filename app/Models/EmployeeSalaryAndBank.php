<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeSalaryAndBank extends Model
{
    // Explicitly defines the custom database table name associated with this model
    protected $table = 'employee_salaries_and_banks';

   
    protected $fillable = [
        'employee_id',
        'basic_salary',
        'currency',
        'allowances',
        'deductions',
        'bonus',
        'payment_method',
        'bank_name',
        'branch_name',
        'account_holder_name',
        'account_number',
        'account_type'
    ];


    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}