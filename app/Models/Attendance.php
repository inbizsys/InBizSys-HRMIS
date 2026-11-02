<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = [
        'employee_id',
        'shift_id',
        'work_location_id',
        'date',
        'in_time',
        'out_time',
        'in_latitude',
        'in_longitude',
        'device_id',
        'is_location_flagged',
        'late_minutes',
        'early_minutes',
        'total_worked_hours',
        'ot_hours',
        'status',
        'reason',
    ];

    protected $casts = [
        'date' => 'date',
        'is_location_flagged' => 'boolean',
    ];

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function workLocation()
    {
        return $this->belongsTo(WorkLocation::class);
    }

    public function breaks()
    {
        return $this->hasMany(AttendanceBreak::class, 'attendance_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
