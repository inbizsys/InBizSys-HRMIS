<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceApproval extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'attendance_id',
        'requested_date',
        'requested_in_time',
        'requested_out_time',
        'reason',
        'status',
    ];


    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }
    public function breakApprovals()
    {
        return $this->hasMany(AttendanceBreakApproval::class, 'attendance_approval_id');
    }
}
