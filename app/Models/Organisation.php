<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Organisation extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     */
    protected $table = 'organisations';

    /**
     * The primary key associated with the table.
     */
    protected $primaryKey = 'id';

    /**
     * Indicates if the IDs are auto-incrementing.
     */
    public $incrementing = true;

    /**
     * The data type of the primary key.
     */
    protected $keyType = 'int';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        // Identity
        'organisation_id',
        'name',
        'logo_path',

        // Location & Industry
        'business_location',
        'industry',

        // Regional Settings
        'date_format',
        'field_separator',

        // Organisation Address
        'address_line_1',
        'address_line_2',
        'city',
        'state',
        'pincode',
        'country',

        // Filing Address
        'filing_address_same_as_org',
        'filing_address_line_1',
        'filing_address_line_2',
        'filing_city',
        'filing_state',
        'filing_pincode',
        'filing_country',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'filing_address_same_as_org' => 'boolean',
    ];

    /**
     * Default attribute values.
     */
    protected $attributes = [
        'date_format'                => 'dd/mm/yyyy',   // lowercase mm
        'field_separator'            => '/',
        'country'                    => 'India',
        'filing_address_same_as_org' => true,
    ];

    // ──────────────────────────────────────────────
    // Boot
    // ──────────────────────────────────────────────

    protected static function booted(): void
    {
        // Auto-generate a unique organisation_id on creation if not provided
        static::creating(function (Organisation $org) {
            if (empty($org->organisation_id)) {
                $org->organisation_id = static::generateOrganisationId();
            }
        });
    }

    // ──────────────────────────────────────────────
    // Accessors & Mutators
    // ──────────────────────────────────────────────

    /**
     * Get the full organisation address as a formatted string.
     */
    public function getFullAddressAttribute(): string
    {
        return collect([
            $this->address_line_1,
            $this->address_line_2,
            trim("{$this->city}, {$this->state} {$this->pincode}"),
            $this->country,
        ])->filter()->implode("\n");
    }

    /**
     * Get the effective filing address (falls back to org address if same).
     */
    public function getEffectiveFilingAddressAttribute(): array
    {
        if ($this->filing_address_same_as_org) {
            return [
                'address_line_1' => $this->address_line_1,
                'address_line_2' => $this->address_line_2,
                'city'           => $this->city,
                'state'          => $this->state,
                'pincode'        => $this->pincode,
                'country'        => $this->country,
            ];
        }

        return [
            'address_line_1' => $this->filing_address_line_1,
            'address_line_2' => $this->filing_address_line_2,
            'city'           => $this->filing_city,
            'state'          => $this->filing_state,
            'pincode'        => $this->filing_pincode,
            'country'        => $this->filing_country,
        ];
    }

    /**
     * Get the logo URL (returns null if no logo is set).
     */
    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo_path
            ? asset('storage/' . $this->logo_path)
            : null;
    }

    // ──────────────────────────────────────────────
    // Helper Methods
    // ──────────────────────────────────────────────

    /**
     * Generate a unique 9-digit numeric organisation ID.
     */
    public static function generateOrganisationId(): string
    {
        do {
            $id = (string) random_int(100_000_000, 999_999_999);
        } while (static::query()->where('organisation_id', $id)->exists());

        return $id;
    }

    /**
     * Available industry options for dropdowns.
     */
    public static function industryOptions(): array
    {
        return [
            'Web Development',
            'Software Development',
            'Information Technology',
            'Finance & Banking',
            'Healthcare',
            'Education',
            'Manufacturing',
            'Retail',
            'Logistics',
            'Consulting',
            'Media & Entertainment',
            'Real Estate',
            'Other',
        ];
    }

    /**
     * Available date format options for dropdowns.
     * All month tokens use lowercase 'mm' / 'mmm' / 'mmmm' for consistency.
     */
    public static function dateFormatOptions(): array
    {
        return [
            'dd/mm/yyyy' => 'dd/mm/yyyy [' . now()->format('d/m/Y') . ']',
            'mm/dd/yyyy' => 'mm/dd/yyyy [' . now()->format('m/d/Y') . ']',
            'yyyy-mm-dd' => 'yyyy-mm-dd [' . now()->format('Y-m-d') . ']',
            'dd-mm-yyyy' => 'dd-mm-yyyy [' . now()->format('d-m-Y') . ']',
            'custom'     => 'Custom Format...',
        ];
    }

    /**
     * Available field separator options.
     */
    public static function separatorOptions(): array
    {
        return ['/', '-', '.', '_'];
    }

    // ──────────────────────────────────────────────
    // Relationships (extend as the HRMIS grows)
    // ──────────────────────────────────────────────

    // public function employees(): HasMany
    // {
    //     return $this->hasMany(Employee::class);
    // }
}
