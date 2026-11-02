<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeaveType extends Model
{
    protected $fillable = [
        'name', 
        'max_days_per_year', 
        'required_approval_levels', 
        'auto_escalate_above_days', 
        'requires_attachment'
        ];

    /**
     * Relationship: A leave type has many leave balances across employees.
     */
    public function leaveBalances(): HasMany
    {
        return $this->hasMany(LeaveBalance::class);
    }

    /**
     * Relationship: A leave type has many leave requests.
     */
    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }
}
