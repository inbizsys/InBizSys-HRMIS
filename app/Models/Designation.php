<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Designation extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'designations';

    protected $fillable = [
        'organisation_id',
        'name',
    ];

    // ──────────────────────────────────────────────
    // Accessors
    // ──────────────────────────────────────────────

    /**
     * Get the total number of employees holding this designation.
     * Shown as "Total Employees" column in the Designations listing.
     * Uncomment once the Employee model is created.
     */
    // public function getTotalEmployeesAttribute(): int
    // {
    //     return $this->employees()->count();
    // }

    // ──────────────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────────────

    /**
     * Filter designations by organisation.
     */
    public function scopeForOrganisation($query, int $organisationId)
    {
        return $query->where('organisation_id', $organisationId);
    }

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
