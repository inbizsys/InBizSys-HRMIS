<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveLog extends Model
{
    protected $fillable = ['leave_request_id', 'action', 'performed_by', 'remarks'];

    /**
     * Relationship: A log entry belongs to a specific leave request workflow.
     */
    public function leaveRequest(): BelongsTo
    {
        return $this->belongsTo(LeaveRequest::class);
    }

    /**
     * Relationship: A log entry record belongs to the user who performed the action.
     */
    public function performer(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'performed_by');
    }
}
