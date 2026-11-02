<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PublicHoliday extends Model
{
    protected $fillable = [
        'holiday_date', 
        'holiday_type',
        'description',
        'holiday_shift',
        'category_id',
    ];

    public function category()
{
    return $this->belongsTo(HolidayCategory::class, 'category_id');
}

}

