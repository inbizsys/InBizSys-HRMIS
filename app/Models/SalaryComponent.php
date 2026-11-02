<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalaryComponent extends Model
{
    use HasFactory;

    protected $table = 'salary_components';

    protected $fillable = [
        'organisation_id',
        'type',
        'name',
        'name_in_payslip',
        'calculation_type',
        'limit',
        'is_epf_applicable',
        'is_taxable',
        'is_recurring',
        'is_active',
        'is_system',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_system' => 'boolean',
        'limit'     => 'decimal:2',
        'calculation_type' => 'string',
        'is_epf_applicable' => 'boolean',
        'is_taxable' => 'boolean',
        'is_recurring' => 'boolean',
    ];

    // ──────────────────────────────────────────────
    // Accessors
    // ──────────────────────────────────────────────

    /**
     * Get a human-readable type label.
     */
    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'earning'   => 'Earning',
            'deduction' => 'Deduction',
            default     => ucfirst($this->type),
        };
    }

    // ──────────────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────────────

    public function scopeForOrganisation(Builder $query, int $organisationId): Builder
    {
        return $query->where('organisation_id', $organisationId);
    }

    public function scopeEarnings(Builder $query): Builder
    {
        return $query->where('type', 'earning');
    }

    public function scopeDeductions(Builder $query): Builder
    {
        return $query->where('type', 'deduction');
    }

    // ──────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }
}
