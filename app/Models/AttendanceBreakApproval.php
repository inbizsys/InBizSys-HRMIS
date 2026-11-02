<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceBreakApproval extends Model
{
    protected $fillable = [
        'employee_id',
        'attendance_approval_id',
        'attendance_break_id',
        'requested_start_time',
        'requested_end_time',
        'reason',
        'status',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function attendanceBreak()
    {
        return $this->belongsTo(AttendanceBreak::class);
    }
}