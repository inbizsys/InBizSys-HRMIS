<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'start_time',
        'end_time',
        'late_grace_minutes',
        'early_leave_grace_minutes',
        'standard_working_hours',
        'half_day_min_hours',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'late_grace_minutes' => 'integer',
        'early_leave_grace_minutes' => 'integer',
        'standard_working_hours' => 'decimal:2',
        'half_day_min_hours' => 'decimal:2',
    ];
}