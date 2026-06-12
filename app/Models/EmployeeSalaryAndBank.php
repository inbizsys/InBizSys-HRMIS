<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeSalaryAndBank extends Model
{
    // Explicitly defines the custom database table name associated with this model
    protected $table = 'employee_salaries_and_banks';

    // Defines the whitelist of attributes/columns that are allowed to be safely mass-assigned 
    // when using methods like EmployeeSalaryAndBank::create() or $salary->update()
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

    /**
     * Defines an Inverse One-to-One / One-to-Many relationship (Belongs To).
     * This links the salary and bank records back to its parent Employee record using the 'employee_id' foreign key.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}