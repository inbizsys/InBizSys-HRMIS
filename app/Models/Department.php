<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'departments';

    protected $fillable = [
        'organisation_id',
        'name',
        'code',
        'description',
    ];

    // ──────────────────────────────────────────────
    // Accessors
    // ──────────────────────────────────────────────

    /**
     * Get the total number of employees in this department.
     * Shown as "Total Employees" column in the Departments listing.
     * Uncomment once the Employee model is created.
     */
    // public function getTotalEmployeesAttribute(): int
    // {
    //     return $this->employees()->count();
    // }

    /**
     * Get a truncated description for table listing display (like "Production Department - Rubber Prod...").
     */
    public function getShortDescriptionAttribute(): string
    {
        return str($this->description ?? '')->limit(40)->value();
    }

    // ──────────────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────────────

    /**
     * Filter departments by organisation.
     */
    public function scopeForOrganisation(Builder $query, int $organisationId)
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
