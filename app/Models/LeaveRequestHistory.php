<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class LeaveRequestHistory extends Model
{
    use HasFactory;

    protected $table = 'leave_request_histories';
    protected $guarded = [];

    protected $casts = [
        'start_date'         => 'date',
        'end_date'           => 'date',
        'is_half_day'        => 'boolean',
        'total_days'         => 'decimal:2',
        'paid_days'          => 'decimal:2',
        'no_pay_days'        => 'decimal:2',
        'split_details_json' => 'array', 
    ];

  
    public function leaveRequest(): BelongsTo
    {
        return $this->belongsTo(LeaveRequest::class, 'leave_request_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class, 'leave_type_id');
    }

    public function actionedBy(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'actioned_by_employee_id');
    }
}
