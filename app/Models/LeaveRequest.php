<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeaveRequest extends Model
{
    protected $fillable = [
        'request_number',
        'employee_id',
        'start_date',
        'end_date',
        'is_half_day',
        'leave_shift',
        'total_days',
        'reason',
        'attachment_path',
        'status',
        'current_approval_level',
        'paid_days',   
        'no_pay_days',
    ];

    /**
     * Relationship: A leave request belongs to an employee (user).
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }

    /**
     * Relationship: A leave request belongs to a specific leave type.
     */
    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }

    /**
     * Relationship: A leave request generates multiple workflow audit logs.
     */
    public function logs(): HasMany
    {
        return $this->hasMany(LeaveLog::class);
    }

    
    public function applyLeaveTypes(): HasMany
    {
        return $this->hasMany(ApplyLeaveType::class, 'leave_request_id');
    }
}
