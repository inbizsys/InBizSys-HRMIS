<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendancePolicy extends Model
{
    use HasFactory;

    protected $fillable = [
        'policy_name',
        'max_late_allowed_per_month',
        'max_early_leave_allowed_per_month', 
        'late_penalty_type',
        'late_penalty_rate',
        'early_leave_penalty_type',         
        'early_leave_penalty_rate',         
        'deduct_leave_type_id',
        'break_deduction_minutes',
        'overtime_minimum_minutes',
        'is_active',
        'max_unpaid_break_allowed_per_month',
        'unpaid_break_penalty_type',
        'unpaid_break_penalty_rate',
        'unpaid_break_leave_type_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'max_late_allowed_per_month' => 'integer',
        'max_early_leave_allowed_per_month' => 'integer', 
        'late_penalty_rate' => 'decimal:2',
        'early_leave_penalty_rate' => 'decimal:2',         
        'break_deduction_minutes' => 'integer',
        'overtime_minimum_minutes' => 'integer',
    ];

    public function deductLeaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class, 'deduct_leave_type_id');
    }
}