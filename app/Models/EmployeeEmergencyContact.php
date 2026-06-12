<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeEmergencyContact extends Model
{
    // Defines the whitelist of attributes/columns that are allowed to be safely mass-assigned 
    // when using methods like EmployeeEmergencyContact::create() or $contact->update()
    protected $fillable = [
        'employee_id', 
        'contact_name', 
        'relationship', 
        'phone_number'
    ];

    /**
     * Defines an Inverse One-to-One / One-to-Many relationship (Belongs To).
     * This links the emergency contact record back to its parent Employee record using the 'employee_id' foreign key.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}