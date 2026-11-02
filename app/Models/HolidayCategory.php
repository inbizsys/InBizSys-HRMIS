<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HolidayCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'color_code',
        'description',
    ];

    public function holidays(): HasMany
    {
        return $this->hasMany(PublicHoliday::class, 'category_id');
    }
}
