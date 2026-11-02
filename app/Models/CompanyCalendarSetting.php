<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CompanyCalendarSetting extends Model
{
    use HasFactory;

    protected $table = 'company_calendar_settings';

    protected $fillable = [
        'day_of_week',
        'is_working_day',
        'day_type',
        'half_day_shift',
        'half_day_start_time',   
        'half_day_end_time',
        'present_calculation_rule',
        'absent_calculation_rule',
    ];

    protected $casts = [
        'is_working_day' => 'boolean',
    ];
}
