<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeRelation extends Model
{
    // Defines the whitelist of attributes/columns that are allowed to be safely mass-assigned 
    // when using methods like EmployeeRelation::create() or $relation->update()
    protected $fillable = [
        'employee_id',
        'relation_type',
        'name',
        'date_of_birth',
        'nic_number',
        'passport_number',
        'gender',
        'marital_status'
    ];

    /**
     * Defines an Inverse One-to-Many relationship (Belongs To).
     * This links the dependent relation record back to its parent Employee record using the 'employee_id' foreign key.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}