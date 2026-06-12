<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaxSlab extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tax_slabs';

    protected $fillable = [
        'organisation_id',
        'slab_order',
        'min_amount',
        'max_amount',
        'tax_percentage',
        'description',
    ];

    protected $casts = [
        'min_amount'       => 'decimal:2',
        'max_amount'       => 'decimal:2',
        'tax_percentage'   => 'decimal:2',
        'slab_order'       => 'integer',
    ];

    // ──────────────────────────────────────────────
    // Accessors
    // ──────────────────────────────────────────────

    /**
     * Organisation එකේ Base Currency code එක return කරනවා.
     * Base currency set කර නැත්නම් fallback විදිහට 'Rs.' return කරනවා.
     */
    protected function getBaseCurrencySymbol(): string
    {
        return Currency::baseCurrencyFor($this->organisation_id)?->code ?? 'Rs.';
    }

    /**
     * Human-readable range label shown in the table.
     * Base currency code dynamically ගන්නවා.
     * e.g. "Up to LKR 150,000/-"  or  "LKR 150,001/- – LKR 233,333/-"
     */
    public function getRangeLabelAttribute(): string
    {
        $symbol = $this->getBaseCurrencySymbol();

        $min = $this->min_amount;
        $max = $this->max_amount;

        if (is_null($min) || $min == 0) {
            return 'Up to ' . $symbol . ' ' . number_format($max, 0);
        }

        if (is_null($max)) {
            return 'Exceeding ' . $symbol . ' ' . number_format($min, 0);
        }

        return $symbol . ' ' . number_format($min, 0) . ' – ' . $symbol . ' ' . number_format($max, 0);
    }

    /**
     * Human-readable tax label.
     * Base currency code dynamically ගන්නවා.
     * e.g. "6% of monthly regular profits from employment less LKR 9,000/-"
     */
    public function getTaxLabelAttribute(): string
    {
        if (is_null($this->tax_percentage)) {
            return 'Relief from Tax';
        }

        $label = number_format($this->tax_percentage, 0) . '% of monthly regular profits from employment';

        if (!is_null($this->deduction_amount)) {
            $symbol = $this->getBaseCurrencySymbol();
            $label .= ' less ' . $symbol . ' ' . number_format($this->deduction_amount, 0) . '/-';
        }

        return $label;
    }

    // ──────────────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────────────

    public function scopeForOrganisation(Builder $query, int $organisationId): Builder
    {
        return $query->where('organisation_id', $organisationId);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('slab_order');
    }

    // ──────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    // ──────────────────────────────────────────────
    // Helper
    // ──────────────────────────────────────────────

    /**
     * Calculate the tax for a given monthly income using this slab's formula.
     * Returns null if the income doesn't fall in this slab.
     */
    public function calculateTax(float $monthlyIncome): ?float
    {
        $min = (float) ($this->min_amount ?? 0);
        $max = $this->max_amount ? (float) $this->max_amount : PHP_FLOAT_MAX;

        if ($monthlyIncome < $min || $monthlyIncome > $max) {
            return null;
        }

        if (is_null($this->tax_percentage)) {
            return 0.0; // Relief slab
        }

        $tax = ($this->tax_percentage / 100) * $monthlyIncome;

        if (!is_null($this->deduction_amount)) {
            $tax -= (float) $this->deduction_amount;
        }

        return max(0, $tax);
    }
}
