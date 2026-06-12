<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkLocation extends Model
{
    use HasFactory;

    protected $table = 'work_locations';

    protected $fillable = [
        'organisation_id',
        'name',
        'address_line_1',
        'address_line_2',
        'state',
        'city',
        'pincode',
    ];

    // ──────────────────────────────────────────────
    // Accessors
    // ──────────────────────────────────────────────

    /**
     * Get the full address as a formatted string.
     */
    public function getFullAddressAttribute(): string
    {
        return collect([
            $this->address_line_1,
            $this->address_line_2,
            trim("{$this->city}, {$this->state} {$this->pincode}"),
        ])->filter()->implode(', ');
    }

    /**
     * Get the total number of employees assigned to this work location.
     * Uncomment once the Employee model and relationship are set up.
     */
    // public function getTotalEmployeesAttribute(): int
    // {
    //     return $this->employees()->count();
    // }

    // ──────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    // public function employees(): HasMany
    // {
    //     return $this->hasMany(Employee::class);
    // }
}
