<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EpfEtfSetting extends Model
{
    use HasFactory;

    protected $table = 'epf_etf_settings';

    protected $fillable = [
        'organisation_id',
        'fund_type',
        'registration_number',
        'deduction_cycle',
        'employee_rate',
        'employer_rate',
        'is_active',
    ];

    protected $casts = [
        'is_active'     => 'boolean',
        'employee_rate' => 'decimal:2',
        'employer_rate' => 'decimal:2',
    ];

    // ──────────────────────────────────────────────
    // Constants
    // ──────────────────────────────────────────────

    // Sri Lanka statutory defaults
    const EPF_EMPLOYEE_RATE = 8.00;
    const EPF_EMPLOYER_RATE = 12.00;
    const ETF_EMPLOYEE_RATE = 0.00;   // ETF is employer-only
    const ETF_EMPLOYER_RATE = 3.00;

    const DEDUCTION_CYCLES = [
        'monthly'    => 'Monthly',
        'bi_monthly' => 'Bi-Monthly',
        'quarterly'  => 'Quarterly',
    ];

    // ──────────────────────────────────────────────
    // Accessors
    // ──────────────────────────────────────────────

    public function getFundTypeLabelAttribute(): string
    {
        return match ($this->fund_type) {
            'epf' => 'EPF',
            'etf' => 'ETF',
            default => strtoupper($this->fund_type),
        };
    }

    public function getDeductionCycleLabelAttribute(): string
    {
        return self::DEDUCTION_CYCLES[$this->deduction_cycle] ?? ucfirst($this->deduction_cycle);
    }

    // ──────────────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────────────

    public function scopeForOrganisation(Builder $query, int $organisationId): Builder
    {
        return $query->where('organisation_id', $organisationId);
    }

    public function scopeEpf(Builder $query): Builder
    {
        return $query->where('fund_type', 'epf');
    }

    public function scopeEtf(Builder $query): Builder
    {
        return $query->where('fund_type', 'etf');
    }

    // ──────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }
}
