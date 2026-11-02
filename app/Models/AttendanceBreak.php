<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceBreak extends Model
{
    protected $fillable = [
        'attendance_id',
        'break_type',
        'break_start',
        'break_end',
        'duration_minutes',
        'reason',
        'is_paid',
        'start_latitude',
        'start_longitude',
        'end_latitude',
        'end_longitude',
    ];

    protected $casts = [
        'is_paid' => 'boolean',
        'duration_minutes' => 'integer',
        'start_latitude' => 'float',
        'start_longitude' => 'float',
        'end_latitude' => 'float',
        'end_longitude' => 'float',
    ];

    public function breakType()
    {
        return $this->belongsTo(BreakType::class, 'break_type');
    }

    public function attendance()
    {
        return $this->belongsTo(Attendance::class, 'attendance_id');
    }
}
