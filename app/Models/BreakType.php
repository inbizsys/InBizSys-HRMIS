<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BreakType extends Model
{
    protected $fillable = [
        'name',
        'code',
        'max_minutes',
        'is_paid',
        'is_active',
    ];

    protected $casts = [
        'is_paid' => 'boolean',
        'is_active' => 'boolean',
        'max_minutes' => 'integer',
    ];
}