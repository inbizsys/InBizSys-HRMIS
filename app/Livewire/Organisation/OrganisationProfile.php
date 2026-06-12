<?php

namespace App\Livewire\Organisation;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Organisation;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

#[Layout('layouts.app')]
class OrganisationProfile extends Component
{
    use WithFileUploads;

    // Identity
    public $organisationId;
    public $name;
    public $logo;
    public $logo_path;

    // Location & Industry
    public $business_location;
    public $industry;

    // Regional Settings
    public $date_format;
    public $field_separator;

    // Organisation Address
    public $address_line_1;
    public $address_line_2;
    public $city;
    public $locationState;
    public $pincode;
    public $country = 'India';

    // Filing Address
    public bool $filing_address_same_as_org = true;
    public $filing_address_line_1;
    public $filing_address_line_2;
    public $filing_city;
    public $filing_state;
    public $filing_pincode;
    public $filing_country;

    // UI State
    public $successMessage = '';
    public $errorMessage = '';
    public $logoPreview = null;
    public bool $showRemoveLogoConfirm = false;

    // Custom Date Format
    public bool $showCustomDateModal = false;
    public string $customDateInput = '';
    public string $customDatePreview = '';
    public string $appliedCustomFormat = '';

    // Custom format base
    public string $customFormatBase = '';

    // Filing address form toggle
    public bool $showFilingAddressForm = false;

    public $tempImage;
    public bool $showCropModal = false;

    protected $listeners = [
        'open-crop-modal' => 'openCropModal',
    ];

    public function openCropModal()
    {
        $this->showCropModal = true;
    }

    // Edit button — For Re-Crop current logo
    public function openCropModalWithExisting(): void
    {
        //logoPreview (cropped temp) or saved logo_path — whichever available
        $url = $this->logoPreview
            ?? ($this->logo_path ? asset('storage/' . $this->logo_path) : null);

        if (!$url) return;

        $this->showCropModal = true;
        $this->dispatch('crop-modal-opened', url: $url);
    }

    protected function rules(): array
    {
        return [
            'name'              => 'required|string|max:255',
            'business_location' => 'required|string|max:255',
            'industry'          => 'required|string|max:255',
            'date_format'       => 'required|string|max:50',
            'field_separator'   => 'required|string|max:5',
            'address_line_1'    => 'required|string|max:255',
            'address_line_2'    => 'nullable|string|max:255',
            'city'              => 'required|string|max:255',
            'locationState'     => 'required|string|max:255',
            'pincode'           => 'required|string|max:20',
            'country'           => 'nullable|string|max:255',
            'logo'              => 'nullable|image|max:1024|mimes:png,jpg,jpeg',
            'filing_address_same_as_org' => 'boolean',
            'filing_address_line_1' => 'nullable|required_if:filing_address_same_as_org,false|string|max:255',
            'filing_address_line_2' => 'nullable|string|max:255',
            'filing_city'       => 'nullable|required_if:filing_address_same_as_org,false|string|max:255',
            'filing_state'      => 'nullable|required_if:filing_address_same_as_org,false|string|max:255',
            'filing_pincode'    => 'nullable|required_if:filing_address_same_as_org,false|string|max:20',
            'filing_country'    => 'nullable|string|max:255',
        ];
    }

    public function mount(): void
    {
        $org = Organisation::query()->first();

        if ($org) {
            $this->organisationId         = $org->organisation_id;
            $this->name                   = $org->name;
            $this->logo_path              = $org->logo_path;
            $this->business_location      = $org->business_location;
            $this->industry               = $org->industry;
            $this->field_separator        = $org->field_separator ?? '/';
            $this->address_line_1         = $org->address_line_1;
            $this->address_line_2         = $org->address_line_2;
            $this->city                   = $org->city;
            $this->locationState          = $org->state;
            $this->pincode                = $org->pincode;
            $this->country                = $org->country ?? 'India';
            $this->filing_address_same_as_org = $org->filing_address_same_as_org;
            $this->filing_address_line_1  = $org->filing_address_line_1;
            $this->filing_address_line_2  = $org->filing_address_line_2;
            $this->filing_city            = $org->filing_city;
            $this->filing_state           = $org->filing_state;
            $this->filing_pincode         = $org->filing_pincode;
            $this->filing_country         = $org->filing_country;

            // DB value is normalized and compared
            $storedFormat = $this->normalizeFormat($org->date_format);

            $basePatterns = array_keys(Organisation::dateFormatOptions());

            $isKnown = false;
            foreach ($basePatterns as $pattern) {
                $strippedDb      = preg_replace('/[\/\-\._]/', '', $storedFormat);
                $strippedPattern = preg_replace('/[\/\-\._]/', '', $pattern);
                if ($strippedDb === $strippedPattern) {
                    $this->date_format = $this->applySeperatorToFormat($pattern, $this->field_separator);
                    $isKnown = true;
                    break;
                }
            }

            if (!$isKnown) {
                $this->appliedCustomFormat = $storedFormat;
                $this->customFormatBase    = $this->stripSeparatorsFromFormat($storedFormat);
                $this->date_format         = '__custom__';
                $this->customDateInput     = $storedFormat;
                $this->generateCustomPreview();
            }
        } else {
            $this->date_format     = 'dd/mm/yyyy';
            $this->field_separator = '/';
        }
    }

    public function updatedLogo(): void
    {
        $this->validateOnly('logo');
        $tempUrl = $this->logo->temporaryUrl();
        $this->logoPreview = $tempUrl;
        $this->showCropModal = true;

        // URL is passed to Alpine listener — Cropper is init after DOM is ready
        $this->dispatch('crop-modal-opened', url: $tempUrl);
    }

    public function saveCroppedImage(string $base64Image): void
    {
        // Base64 header strip කරලා decode
        $imageData = preg_replace('/^data:image\/\w+;base64,/', '', $base64Image);
        $imageData = base64_decode($imageData);

        // Old cropped logo delete (previous crop replace වෙනවිට)
        if ($this->logo_path && str_starts_with($this->logo_path, 'logos/cropped_')) {
            Storage::disk('public')->delete($this->logo_path);
        }

        // Storage save
        $filename = 'logos/cropped_' . uniqid() . '.png';
        Storage::disk('public')->put($filename, $imageData);

        // Preview — asset('storage/...') pattern use කරනවා (Storage::url() FILESYSTEM_URL config issue හෙව)
        $this->logoPreview = asset('storage/' . $filename);
        $this->logo_path   = $filename;
        $this->showCropModal = false;
    }

    public function removeLogo(): void
    {
        if ($this->logo_path) {
            Storage::disk('public')->delete($this->logo_path);

            $org = Organisation::query()->first();
            if ($org) {
                $org->logo_path = null;
                $org->save();
            }

            $this->logo_path = null;
        }

        $this->logo               = null;
        $this->logoPreview        = null;
        $this->showRemoveLogoConfirm = false;
        $this->successMessage     = 'Logo removed successfully.';
    }

    /**
     * When the Date format dropdown changes
     */
    public function updatedDateFormat(string $value): void
    {
        if ($value === 'custom') {
            $this->customDateInput   = $this->appliedCustomFormat;
            $this->customDatePreview = $this->appliedCustomFormat
                ? $this->generateCustomPreviewText($this->appliedCustomFormat)
                : '';
            $this->showCustomDateModal = true;
        }
    }

    /**
     * When field separator changes — known & custom formats update
     *
     * Custom format — 4 cases:
     *   Case 1: Symbol separators (/ - . _) → replace with new sep
     *            "dd/mm/yyyy" + "-" → "dd-mm-yyyy"
     *
     *   Case 2: Space separators → replace spaces with new sep
     *            "dd mm yyyy" + "-" → "dd-mm-yyyy"
     *
     *   Case 3: No separators at all (eg: "ddmmyyyy", "ddmmmmyyyy") →
     *            tokenize and INSERT separator between tokens
     *            "ddmmmmyyyy" + "/" → "dd/mmmm/yyyy"
     *
     *   Case 4: Mixed (no sep but spaces-as-sep already handled above) — same as Case 3
     */
    public function updatedFieldSeparator(string $value): void
    {
        if ($this->date_format === '__custom__') {
            if (empty($this->appliedCustomFormat)) return;

            $hasSymbolSeparators = (bool) preg_match('/[\/\-\._]/', $this->appliedCustomFormat);
            $hasSpaceSeparators  = str_contains($this->appliedCustomFormat, ' ');

            if ($hasSymbolSeparators) {
                // Case 1: existing symbol separators → swap
                $newFormat = $this->applySeperatorToFormat($this->appliedCustomFormat, $value);
            } elseif ($hasSpaceSeparators) {
                // Case 2: spaces → swap with new separator
                $newFormat = str_replace(' ', $value, $this->appliedCustomFormat);
            } else {
                // Case 3: no separators — tokenize and insert
                $newFormat = $this->insertSeparatorBetweenTokens($this->appliedCustomFormat, $value);
            }

            $this->appliedCustomFormat = $newFormat;
            $this->customDateInput     = $newFormat;
            $this->generateCustomPreview();
            return;
        }

        // Known format
        if ($this->date_format !== 'custom') {
            $this->date_format = $this->applySeperatorToFormat($this->date_format, $value);
        }
    }

    /**
     * A separator is inserted between the tokens of the format string without a separator.
     *
     * Recognized tokens (longest first): mmmm, mmm, mm, m, yyyy, yy, dd, d
     * eg: "ddmmmmyyyy" → tokens: ["dd","mmmm","yyyy"] → "dd/mmmm/yyyy"
     * eg: "dmmyy"      → tokens: ["d","mm","yy"]      → "d/mm/yy"
     * eg: "ddmmmyyyy"  → tokens: ["dd","mmm","yyyy"]  → "dd/mmm/yyyy"
     *
     * Unrecognized chars (literal text) → treated as their own segment.
     */
    public function insertSeparatorBetweenTokens(string $format, string $separator): string
    {
        // Tokens longest-first so greedy match works correctly
        $tokens = ['mmmm', 'mmm', 'mm', 'yyyy', 'yy', 'dd', 'd', 'm'];

        $parts  = [];
        $i      = 0;
        $len    = strlen($format);

        while ($i < $len) {
            $matched = false;
            foreach ($tokens as $token) {
                $tLen = strlen($token);
                if (substr($format, $i, $tLen) === $token) {
                    $parts[] = $token;
                    $i      += $tLen;
                    $matched = true;
                    break;
                }
            }
            if (!$matched) {
                // Unrecognized char — append to last part or start new
                if (!empty($parts)) {
                    $parts[count($parts) - 1] .= $format[$i];
                } else {
                    $parts[] = $format[$i];
                }
                $i++;
            }
        }

        return implode($separator, $parts);
    }

    /**
     * Live preview for custom date modal input.
     * Any input that the user types is normalized (lowercase).
     */
    public function updatedCustomDateInput(): void
    {
        $this->customDateInput = $this->normalizeFormat($this->customDateInput);
        $this->generateCustomPreview();
    }

    // ──────────────────────────────────────────────
    // Format Normalization
    // ──────────────────────────────────────────────

    /**
     * The recognized tokens (M, Y, D) in the format string are normalized to the lowercase-correct form.
     *
     * Rules:
     *   - Month tokens: MMMM / MMM / MM / M  →  mmmm / mmm / mm / m
     *   - Year  tokens: YYYY / YY             →  yyyy / yy
     *   - Day   tokens: DD / D                →  dd / d  (user D/DD ගොඩනැගූ case handle)
     *
     * Separators (/ - . _ space) and lowercase tokens untouched.
     *
     * Strategy — separator tokens are temporarily protected as placeholders,
     *            Remaining alphabetic chars are case-folded and restored.
     *
     * Simpler approach: known token pairs replace කරනවා (longest first):
     */
    public function normalizeFormat(string $format): string
    {
        // Longest tokens first (order matters!)
        $replacements = [
            // Month — user may type any casing: MMMM mmmm MmMm etc.
            'MMMM' => 'mmmm',
            'MMM'  => 'mmm',
            'MM'   => 'mm',

            // Year
            'YYYY' => 'yyyy',
            'YY'   => 'yy',

            // Day (user might type DD instead of dd)
            'DD'   => 'dd',

            // Single-char uppercase variants (after multi-char handled)
            'M'    => 'm',
            'Y'    => 'y', // shouldn't appear normally but handle anyway
            'D'    => 'd',
        ];

        // Case-insensitive replace — preg_replace_callback per token
        foreach ($replacements as $from => $to) {
            $format = preg_replace_callback(
                '/' . preg_quote($from, '/') . '/i',
                fn() => $to,
                $format
            );
        }

        return $format;
    }

    /**
     * Symbol separators (/ - . _) strip — spaces untouched.
     * eg: "dd/mm/yyyy" → "ddmmyyyy"
     */
    public function stripSeparatorsFromFormat(string $format): string
    {
        return preg_replace('/[\/\-\._]/', '', $format);
    }

    /**
     * Symbol separator replace.
     * eg: "dd/mm/yyyy" + "-" → "dd-mm-yyyy"
     */
    public function applySeperatorToFormat(string $format, string $separator): string
    {
        return preg_replace('/[\/\-\._]/', $separator, $format);
    }

    /**
     * Dropdown options — selected separator dynamically applied.
     */
    public function getDateFormatOptionsWithSeparator(): array
    {
        $separator = $this->field_separator ?? '/';
        $options   = [];

        foreach (Organisation::dateFormatOptions() as $baseValue => $label) {
            $newValue    = $this->applySeperatorToFormat($baseValue, $separator);
            $phpFormat   = $this->tokenToPhpFormat($newValue);
            $previewDate = now()->format($phpFormat);
            $options[$newValue] = $newValue . ' [' . $previewDate . ']';
        }

        return $options;
    }

    /**
     * Token-based format string → PHP date() format string.
     *
     * All tokens are now lowercase (after normalizeFormat).
     * Order matters: longer tokens first.
     *
     * eg: "dd-mmm-yyyy" → "d-M-Y"  → "13-May-2026"
     * eg: "dd mm yyyy"  → "d m Y"  → "13 05 2026"
     */
    public function tokenToPhpFormat(string $format): string
    {
        $tokenMap = [
            // Year
            'yyyy' => 'Y',
            'yy'   => 'y',
            // Month — longest first
            'mmmm' => 'F',   // January
            'mmm'  => 'M',   // Jan
            'mm'   => 'm',   // 01
            'm'    => 'n',   // 1
            // Day
            'dd'   => 'd',   // 01
            'd'    => 'j',   // 1
        ];

        foreach ($tokenMap as $token => $phpChar) {
            $format = str_replace($token, $phpChar, $format);
        }

        return $format;
    }

    /**
     * Custom date preview text generate (return — property set නොකරනවා).
     */
    public function generateCustomPreviewText(string $input): string
    {
        if (empty(trim($input))) return '';

        $phpFormat = $this->tokenToPhpFormat($input);

        try {
            return now()->format($phpFormat);
        } catch (\Exception) {
            return 'Invalid format';
        }
    }

    /**
     * Custom date format preview — $customDatePreview set.
     */
    public function generateCustomPreview(): void
    {
        $this->customDatePreview = $this->generateCustomPreviewText($this->customDateInput);
    }

    /**
     * Modal Apply button.
     */
    public function applyCustomFormat(): void
    {
        $this->validate([
            'customDateInput' => 'required|string|max:50',
        ]);

        if (empty(trim($this->customDateInput))) return;

        // Normalize before applying
        $normalized = $this->normalizeFormat($this->customDateInput);

        $this->customDateInput     = $normalized;
        $this->appliedCustomFormat = $normalized;
        $this->customFormatBase    = $this->stripSeparatorsFromFormat($normalized);
        $this->generateCustomPreview();
        $this->date_format         = '__custom__';
        $this->showCustomDateModal = false;
    }

    /**
     * Modal Cancel button.
     */
    public function closeCustomDateModal(): void
    {
        if ($this->appliedCustomFormat) {
            $this->date_format = '__custom__';
        } else {
            $knownFormats      = $this->getDateFormatOptionsWithSeparator();
            $this->date_format = array_key_first($knownFormats);
        }
        $this->showCustomDateModal = false;
        $this->customDateInput     = '';
        $this->customDatePreview   = '';
    }

    public function resetFilingAddress(): void
    {
        $this->filing_address_same_as_org = true;
        $this->filing_address_line_1      = null;
        $this->filing_address_line_2      = null;
        $this->filing_city                = null;
        $this->filing_state               = null;
        $this->filing_pincode             = null;
        $this->filing_country             = null;
        $this->showFilingAddressForm      = false;
    }

    public function save(): void
    {
        $actualDateFormat = $this->date_format === '__custom__'
            ? $this->appliedCustomFormat
            : $this->date_format;

        // Normalize before saving (safety net)
        $actualDateFormat = $this->normalizeFormat($actualDateFormat);

        $originalDateFormat = $this->date_format;
        $this->date_format  = $actualDateFormat;

        $this->validate();

        $this->date_format = $originalDateFormat;

          try {
            DB::transaction(function () use ($actualDateFormat) {
            $org = Organisation::query()->first();

            if (!$org) {
                $org = new Organisation();
            }

            // If you crop and save, logo_path is already set — or direct upload store
            if ($this->logo && !str_starts_with($this->logo_path ?? '', 'logos/cropped_')) {
                $this->logo_path = $this->logo->store('logos', 'public');
            }

            $org->fill([
                'name'                       => $this->name,
                'logo_path'                  => $this->logo_path,
                'business_location'          => $this->business_location,
                'industry'                   => $this->industry,
                'date_format'                => $actualDateFormat,
                'field_separator'            => $this->field_separator,
                'address_line_1'             => $this->address_line_1,
                'address_line_2'             => $this->address_line_2,
                'city'                       => $this->city,
                'state'                      => $this->locationState,
                'pincode'                    => $this->pincode,
                'country'                    => $this->country ?? 'India',
                'filing_address_same_as_org' => $this->filing_address_same_as_org,
                'filing_address_line_1'      => $this->filing_address_same_as_org ? null : $this->filing_address_line_1,
                'filing_address_line_2'      => $this->filing_address_same_as_org ? null : $this->filing_address_line_2,
                'filing_city'                => $this->filing_address_same_as_org ? null : $this->filing_city,
                'filing_state'               => $this->filing_address_same_as_org ? null : $this->filing_state,
                'filing_pincode'             => $this->filing_address_same_as_org ? null : $this->filing_pincode,
                'filing_country'             => $this->filing_address_same_as_org ? null : ($this->filing_country ?? 'India'),
            ]);

            $org->save();

            $this->organisationId = $org->organisation_id;
            });

            $this->successMessage = 'Organisation profile updated successfully.';
            $this->errorMessage   = '';
            $this->showFilingAddressForm = false;

            $this->dispatch('profile-saved');
        } catch (Exception $error) {
            $this->errorMessage   = 'Error saving organisation: ' . $error->getMessage();
            $this->successMessage = '';
            Log::error('Organisation save error: ' . $error->getMessage());
            Log::error($error->getTraceAsString());
        }
    }

    public function render()
    {
        return view('livewire.organisation.organisation-profile', [
            'industryOptions'   => Organisation::industryOptions(),
            'dateFormatOptions' => $this->getDateFormatOptionsWithSeparator(),
            'separatorOptions'  => Organisation::separatorOptions(),
        ]);
    }
}
