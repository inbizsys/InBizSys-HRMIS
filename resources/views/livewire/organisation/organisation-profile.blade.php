<div class="h-full bg-gray-50">
    <div class="w-full py-6 px-6">

        {{-- Header --}}
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-xl font-semibold text-gray-800">Organisation Profile</h1>
            <span class="text-sm text-gray-400">Organisation ID: {{ $organisationId ?? '—' }}</span>
        </div>

        {{-- Success Message --}}
        @if ($successMessage)
            <div
                class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm flex items-center gap-2">
                <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                        clip-rule="evenodd" />
                </svg>
                {{ $successMessage }}
            </div>
        @endif

        {{-- Error Message --}}
        @if ($errorMessage)
            <div
                class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm flex items-center gap-2">
                <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" />
                </svg>
                {{ $errorMessage }}
            </div>
        @endif

        <div class="bg-white rounded-xl border border-gray-200 divide-y divide-gray-100">

            {{-- ═══ ROW: Organisation Logo ═══ --}}
            <div class="p-6 grid grid-cols-1 md:grid-cols-[0.6fr_1.4fr] gap-6 md:gap-10">
                <div>
                    <h2 class="text-sm font-medium text-gray-700 mb-1">Organisation Logo</h2>
                    <p class="text-xs text-gray-400 leading-relaxed">
                        Displayed on documents such as Payslips and TDS Worksheets.<br>
                        Preferred size: 240 × 240px @ 72 DPI. Max 1MB.<br>
                        Formats: PNG, JPG, JPEG.
                    </p>
                </div>
                <div class="flex flex-col items-start gap-3">
                    {{-- Logo Preview Box --}}
                    <div class="relative w-32 h-32 shrink-0">
                        <label
                            class="flex flex-col items-center justify-center w-full h-full border-2 border-dashed border-gray-300 rounded-lg cursor-pointer hover:border-blue-400 transition-colors bg-gray-50 overflow-hidden">
                            @if ($logoPreview)
                                <img src="{{ $logoPreview }}" class="w-full h-full object-contain p-1"
                                    alt="Logo Preview">
                            @elseif($logo_path)
                                <img src="{{ asset('storage/' . $logo_path) }}" class="w-full h-full object-contain p-1"
                                    alt="Organisation Logo">
                            @else
                                <svg class="w-7 h-7 text-gray-400 mb-0.5" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span class="text-[10px] text-gray-400 mt-0.5">Upload</span>
                            @endif
                            <input type="file" wire:model="logo" class="hidden" accept=".png,.jpg,.jpeg">
                        </label>
                    </div>

                    {{-- Action Buttons — Below the logo --}}
                    @if ($logoPreview || $logo_path)
                        <div class="flex flex-row gap-3 justify-start">

                            {{-- Re-crop / Adjust Button — existing image crop modal open --}}
                            <button type="button" wire:click="openCropModalWithExisting"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-blue-600 border border-blue-300 rounded-lg hover:bg-blue-50 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                </svg>

                            </button>

                            {{-- Select New Logo --}}
                            <label
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-gray-600 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                </svg>

                                <input type="file" wire:model="logo" class="hidden" accept=".png,.jpg,.jpeg">
                            </label>

                            {{-- Remove Button --}}
                            <button type="button" wire:click="$set('showRemoveLogoConfirm', true)"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-red-600 border border-red-300 rounded-lg hover:bg-red-50 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>

                        </div>
                    @endif

                    @error('logo')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- ═══ ROW: Organisation Name ═══ --}}
            <div class="p-6 grid grid-cols-1 md:grid-cols-[0.6fr_1.4fr] gap-6 md:gap-10">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Organisation Name <span class="text-red-500">*</span>
                    </label>
                    <p class="text-xs text-gray-400 leading-relaxed">Your registered business name — appears on all
                        forms and payslips.</p>
                </div>
                <div class="flex flex-col justify-center">
                    <input type="text" wire:model="name"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="e.g. Acme Technologies Pvt Ltd">
                    @error('name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- ═══ ROW: Business Location + Industry ═══ --}}
            <div class="p-6 grid grid-cols-1 md:grid-cols-[0.6fr_1.4fr] gap-6 md:gap-10">
                <div>
                    <h2 class="text-sm font-medium text-gray-700 mb-1">Business Details</h2>
                    <p class="text-xs text-gray-400 leading-relaxed">Your primary business location and the industry
                        your organisation operates in.</p>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">
                            Business Location <span class="text-red-500">*</span>
                        </label>
                        <input type="text" wire:model="business_location"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="e.g. India">
                        @error('business_location')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">
                            Industry <span class="text-red-500">*</span>
                        </label>
                        <select wire:model="industry"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                            <option value="">Select Industry</option>
                            @foreach ($industryOptions as $option)
                                <option value="{{ $option }}">{{ $option }}</option>
                            @endforeach
                        </select>
                        @error('industry')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- ═══ ROW: Date Format + Field Separator ═══ --}}
            <div class="p-6 grid grid-cols-1 md:grid-cols-[0.6fr_1.4fr] gap-6 md:gap-10">
                <div>
                    <h2 class="text-sm font-medium text-gray-700 mb-1">Format Settings</h2>
                    <p class="text-xs text-gray-400 leading-relaxed">
                        Configure how dates and data fields appear across exports and reports.<br>
                        <span class="text-blue-400">Changing the separator updates the date format preview
                            automatically.</span>
                    </p>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                    {{-- Date Format --}}
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">
                            Date Format <span class="text-red-500">*</span>
                        </label>
                        <select wire:model.live="date_format"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">

                            @if ($appliedCustomFormat)
                                <option value="__custom__">
                                    {{ $appliedCustomFormat }}
                                    @if ($customDatePreview && $customDatePreview !== 'Invalid format')
                                        [{{ $customDatePreview }}]
                                    @endif
                                </option>
                            @endif

                            {{-- Known formats — separator dynamically applied --}}
                            @foreach ($dateFormatOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach

                            {{-- Custom Format entry point — BOTTOM --}}
                            <option value="custom">✎ Custom Format...</option>
                        </select>
                        @error('date_format')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Field Separator --}}
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">
                            Field Separator
                        </label>
                        <select wire:model.live="field_separator"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                            @foreach ($separatorOptions as $sep)
                                <option value="{{ $sep }}">{{ $sep }}</option>
                            @endforeach
                        </select>
                    </div>

                </div>
            </div>

            {{-- ═══ ROW: Organisation Address ═══ --}}
            <div class="p-6 grid grid-cols-1 md:grid-cols-[0.6fr_1.4fr] gap-6 md:gap-10">
                <div>
                    <h2 class="text-sm font-medium text-gray-700 mb-1">
                        Organisation Address <span class="text-red-500">*</span>
                    </h2>
                    <p class="text-xs text-gray-400 leading-relaxed">This will be considered as the address of your
                        primary work location.</p>
                </div>
                <div class="space-y-3">
                    <div>
                        <input type="text" wire:model="address_line_1" placeholder="Address Line 1"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('address_line_1')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <input type="text" wire:model="address_line_2" placeholder="Address Line 2 (Optional)"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Country / State</label>
                            <select wire:model="locationState"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                                <option value="">Select a state</option>
                                <option value="Afghanistan">Afghanistan</option>
                                <option value="Australia">Australia</option>
                                <option value="Bangladesh">Bangladesh</option>
                                <option value="Canada">Canada</option>
                                <option value="China">China</option>
                                <option value="France">France</option>
                                <option value="Germany">Germany</option>
                                <option value="India">India</option>
                                <option value="Indonesia">Indonesia</option>
                                <option value="Italy">Italy</option>
                                <option value="Japan">Japan</option>
                                <option value="Malaysia">Malaysia</option>
                                <option value="Maldives">Maldives</option>
                                <option value="Nepal">Nepal</option>
                                <option value="New Zealand">New Zealand</option>
                                <option value="Pakistan">Pakistan</option>
                                <option value="Russia">Russia</option>
                                <option value="Singapore">Singapore</option>
                                <option value="South Africa">South Africa</option>
                                <option value="South Korea">South Korea</option>
                                <option value="Sri Lanka">Sri Lanka</option>
                                <option value="Thailand">Thailand</option>
                                <option value="United Arab Emirates">United Arab Emirates</option>
                                <option value="United Kingdom">United Kingdom</option>
                                <option value="United States">United States</option>
                                <option value="Vietnam">Vietnam</option>
                            </select>
                            @error('locationState')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">City</label>
                            <input type="text" wire:model="city" placeholder="City"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @error('city')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">PIN Code</label>
                            <input type="text" wire:model="pincode" placeholder="PIN Code"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @error('pincode')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- ═══ ROW: Filing Address ═══ --}}
            <div class="p-6 grid grid-cols-1 md:grid-cols-[0.6fr_1.4fr] gap-6 md:gap-10">
                <div>
                    <h2 class="text-sm font-medium text-gray-700 mb-1">Filing Address</h2>
                    <p class="text-xs text-gray-400 leading-relaxed">This registered address will be used across all
                        Forms and Payslips.</p>
                </div>
                <div>

                    @if ($filing_address_same_as_org)
                        {{-- ══ STATE 1: Head Office address is used ══ --}}
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-gray-700">Head Office</span>
                                <button type="button" wire:click="$set('filing_address_same_as_org', false)"
                                    class="text-xs text-blue-600 hover:text-blue-700 flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                    </svg>
                                    Change
                                </button>
                            </div>
                            <p class="text-xs text-gray-500">{{ $address_line_1 }}</p>
                            @if ($address_line_2)
                                <p class="text-xs text-gray-500">{{ $address_line_2 }}</p>
                            @endif
                            <p class="text-xs text-gray-500">{{ $city }}, {{ $locationState }}
                                {{ $pincode }}</p>
                        </div>
                    @else
                        {{-- ══ STATE 2: Separate Filing Address — Preview Card ══ --}}
                        <div class="border border-blue-200 bg-blue-50 rounded-lg p-4 mb-3">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-gray-700">Filing Address</span>
                                <div class="flex items-center gap-3">
                                    <button type="button" wire:click="$toggle('showFilingAddressForm')"
                                        class="text-xs text-blue-600 hover:text-blue-700 flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                        </svg>
                                        {{ $showFilingAddressForm ? 'Cancel' : 'Edit' }}
                                    </button>
                                    <span class="text-gray-300">|</span>
                                    <button type="button" wire:click="$set('filing_address_same_as_org', true)"
                                        class="text-xs text-gray-500 hover:text-gray-700">
                                        Use Head Office Address
                                    </button>
                                </div>
                            </div>
                            {{-- Filing address details --}}
                            @if ($filing_address_line_1)
                                <p class="text-xs text-gray-500">{{ $filing_address_line_1 }}</p>
                            @endif
                            @if ($filing_address_line_2)
                                <p class="text-xs text-gray-500">{{ $filing_address_line_2 }}</p>
                            @endif
                            @if ($filing_city || $filing_state || $filing_pincode)
                                <p class="text-xs text-gray-500">
                                    {{ implode(', ', array_filter([$filing_city, $filing_state, $filing_pincode])) }}
                                </p>
                            @endif
                        </div>

                        {{-- ══ Filing Address Edit Form — When click Edit Button then show ══ --}}
                        @if ($showFilingAddressForm)
                            <div class="space-y-3 p-4 bg-gray-50 rounded-lg border border-gray-200">
                                <span class="text-sm font-medium text-gray-700 block mb-1">Edit Filing Address</span>
                                <div>
                                    <input type="text" wire:model="filing_address_line_1"
                                        placeholder="Address Line 1"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    @error('filing_address_line_1')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <input type="text" wire:model="filing_address_line_2"
                                    placeholder="Address Line 2 (Optional)"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <div class="grid grid-cols-3 gap-3">
                                    <div>
                                        <label class="block text-xs text-gray-500 mb-1">State / Region</label>
                                        {{-- Free-text input field --}}
                                        <input type="text" wire:model="filing_state" placeholder="State / Region"
                                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        @error('filing_state')
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-500 mb-1">City</label>
                                        <input type="text" wire:model="filing_city" placeholder="City"
                                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        @error('filing_city')
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-500 mb-1">PIN Code</label>
                                        <input type="text" wire:model="filing_pincode" placeholder="PIN Code"
                                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        @error('filing_pincode')
                                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endif

                </div>
            </div>

            {{-- ═══ Save Button ═══ --}}
            <div class="p-6 flex justify-end">
                <button type="button" wire:click="save" wire:loading.attr="disabled"
                    class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-6 py-2 rounded-lg transition-colors disabled:opacity-60 flex items-center gap-2">
                    <span wire:loading wire:target="save">
                        <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4" />
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z" />
                        </svg>
                    </span>
                    <span wire:loading.remove wire:target="save">Save Changes</span>
                    <span wire:loading wire:target="save">Saving...</span>
                </button>
            </div>

        </div>
    </div>


    {{-- ═══ Remove Logo Confirm Modal ═══ --}}
    @if ($showRemoveLogoConfirm)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40" x-data
            x-on:keydown.escape.window="$wire.set('showRemoveLogoConfirm', false)">

            <div class="bg-white rounded-xl shadow-xl w-full max-w-sm mx-4 p-6">

                {{-- Icon --}}
                <div class="flex items-center justify-center w-12 h-12 rounded-full bg-red-100 mx-auto mb-4">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </div>

                {{-- Text --}}
                <h3 class="text-base font-semibold text-gray-800 text-center mb-1">Remove Logo?</h3>
                <p class="text-sm text-gray-500 text-center mb-6">
                    The organization logo will be permanently removed. Continue?
                </p>

                {{-- Buttons --}}
                <div class="flex gap-3">
                    <button type="button" wire:click="$set('showRemoveLogoConfirm', false)"
                        class="flex-1 px-4 py-2 text-sm text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                        Cancel
                    </button>
                    <button type="button" wire:click="removeLogo" wire:loading.attr="disabled"
                        class="flex-1 px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition-colors disabled:opacity-60">
                        <span wire:loading wire:target="removeLogo">Removing...</span>
                        <span wire:loading.remove wire:target="removeLogo">Yes, Remove</span>
                    </button>
                </div>

            </div>
        </div>
    @endif

    {{-- ═══════════════════════════════════════════════
         Custom Date Format Modal
    ═══════════════════════════════════════════════ --}}
    @if ($showCustomDateModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" x-data
            x-on:keydown.escape.window="$wire.closeCustomDateModal()">

            <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4 p-6">

                {{-- Modal Header --}}
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-base font-semibold text-gray-800">Custom Date Format</h3>
                        <p class="text-xs text-gray-400 mt-0.5">Define your own date display format</p>
                    </div>
                    <button wire:click="closeCustomDateModal"
                        class="text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Token Reference --}}
                <div class="bg-gray-50 rounded-lg p-3 mb-4">
                    <p class="text-xs font-medium text-gray-500 mb-2">Available tokens</p>
                    <div class="grid grid-cols-2 gap-1 text-xs text-gray-600">
                        <div><code class="bg-white border border-gray-200 px-1.5 py-0.5 rounded text-blue-600">d</code>
                            &nbsp;Day (1–31)</div>
                        <div><code
                                class="bg-white border border-gray-200 px-1.5 py-0.5 rounded text-blue-600">dd</code>
                            &nbsp;Day (01–31)</div>
                        <div><code
                                class="bg-white border border-gray-200 px-1.5 py-0.5 rounded text-blue-600">mmm</code>
                            &nbsp;Month (Jan)</div>
                        <div><code
                                class="bg-white border border-gray-200 px-1.5 py-0.5 rounded text-blue-600">mmmm</code>
                            &nbsp;Month (January)</div>
                        <div><code
                                class="bg-white border border-gray-200 px-1.5 py-0.5 rounded text-blue-600">yy</code>
                            &nbsp;Year (26)</div>
                        <div><code
                                class="bg-white border border-gray-200 px-1.5 py-0.5 rounded text-blue-600">yyyy</code>
                            &nbsp;Year (2026)</div>
                    </div>
                    <p class="text-xs text-gray-400 mt-2">
                        Examples: &nbsp;
                        <span class="font-mono">dd mmmm yyyy</span> &nbsp;·&nbsp;
                        <span class="font-mono">d mmm yy</span> &nbsp;·&nbsp;
                        <span class="font-mono">dd-MM-yyyy</span>
                    </p>
                </div>

                {{-- Format Input --}}
                <label class="block text-sm font-medium text-gray-700 mb-1">Format</label>
                <input type="text" wire:model.live="customDateInput" placeholder="e.g. dd mmmm yyyy"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                @error('customDateInput')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror

                {{-- Live Preview --}}
                @if ($customDatePreview)
                    <div
                        class="mt-3 p-3 rounded-lg {{ $customDatePreview === 'Invalid format' ? 'bg-red-50 border border-red-200' : 'bg-blue-50 border border-blue-200' }}">
                        <p
                            class="text-xs font-medium uppercase tracking-wide {{ $customDatePreview === 'Invalid format' ? 'text-red-400' : 'text-blue-400' }}">
                            Preview
                        </p>
                        <p
                            class="font-semibold mt-0.5 {{ $customDatePreview === 'Invalid format' ? 'text-red-600 text-sm' : 'text-blue-800 text-base' }}">
                            {{ $customDatePreview }}
                        </p>
                    </div>
                @endif

                {{-- Modal Actions --}}
                <div class="flex justify-end gap-3 mt-5">
                    <button type="button" wire:click="closeCustomDateModal"
                        class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                        Cancel
                    </button>
                    <button type="button" wire:click="applyCustomFormat"
                        class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                        Apply Format
                    </button>
                </div>

            </div>
        </div>
    @endif

    {{-- ═══ Profile Logo Crop Modal ═══ --}}
    {{-- Cropper.js CSS/JS — should always be available when the modal opens --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>

    <div
        x-data="{
            cropper: null,
            isApplying: false,

            initCropper() {
                // Previous cropper instance destroy
                if (this.cropper) {
                    this.cropper.destroy();
                    this.cropper = null;
                }

                const img = this.$refs.cropperImg;
                if (!img || !img.src) return;

                // If the image load is complete, directly init, or onload wait
                const setup = () => {
                    this.cropper = new Cropper(img, {
                        aspectRatio: 600 / 240,       // use NaN for Free size — width/height freely resize
                        viewMode: 1,            // Use 0 for Crop box image boundary can also be exceeded
                        dragMode: 'move',
                        autoCropArea: 1,        // Initial crop box — maximum size
                        minCropBoxWidth: 50,
                        minCropBoxHeight: 50,
                        restore: false,
                        guides: true,
                        center: true,
                        highlight: false,
                        cropBoxMovable: true,
                        cropBoxResizable: true,
                        toggleDragModeOnDblclick: false,
                        background: false,
                    });
                };

                if (img.complete && img.naturalWidth > 0) {
                    setup();
                } else {
                    img.onload = setup;
                    img.onerror = () => console.error('Cropper: image load failed');
                }
            },

            destroyCropper() {
                if (this.cropper) {
                    this.cropper.destroy();
                    this.cropper = null;
                }
            },

            zoom(ratio) { this.cropper?.zoom(ratio); },
            rotate(deg) { this.cropper?.rotate(deg); },
            reset() { this.cropper?.reset(); },

            async applyCrop() {
                if (!this.cropper || this.isApplying) return;
                this.isApplying = true;

                try {
                    // Gets the actual dimensions cropped by the user
                    const cropData = this.cropper.getData(true); // true = rounded px values
                    const canvas = this.cropper.getCroppedCanvas({
                        width: 600,
                        height: 240,
                        maxWidth: 4096,       // Too large image limit
                        maxHeight: 4096,
                        imageSmoothingEnabled: true,
                        imageSmoothingQuality: 'high',
                    });

                    await new Promise((resolve, reject) => {
                        canvas.toBlob((blob) => {
                            if (!blob) { reject('Canvas toBlob failed'); return; }
                            const reader = new FileReader();
                            reader.onloadend = () => {
                                $wire.saveCroppedImage(reader.result).then(resolve).catch(reject);
                            };
                            reader.onerror = reject;
                            reader.readAsDataURL(blob);
                        }, 'image/png');
                    });
                } catch (e) {
                    console.error('Crop apply error:', e);
                } finally {
                    this.isApplying = false;
                }
            }
        }"
        x-on:crop-modal-opened.window="
            $nextTick(() => {
                $refs.cropperImg.src = $event.detail.url;
                initCropper();
            })
        "
    >
        @if ($showCropModal)
            <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60"
                x-on:keydown.escape.window="$wire.set('showCropModal', false); destroyCropper();">

                <div class="bg-white rounded-xl shadow-xl w-full max-w-lg mx-4 p-6">

                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-base font-semibold text-gray-800">Adjust Logo</h3>
                        <button
                            @click="destroyCropper(); $wire.set('showCropModal', false)"
                            class="text-gray-400 hover:text-gray-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    {{-- Cropper Container --}}
                    <div class="relative bg-gray-100 rounded-lg overflow-hidden" style="height: 480px;">
                        <img x-ref="cropperImg" class="block max-w-full" alt="Crop preview">
                    </div>

                    {{-- Controls --}}
                    <div class="flex items-center justify-between mt-4">
                        <div class="flex gap-2">
                            {{-- Zoom In --}}
                            <button type="button" @click="zoom(0.1)"
                                class="p-2 border border-gray-300 rounded-lg hover:bg-gray-50" title="Zoom In">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" />
                                </svg>
                            </button>
                            {{-- Zoom Out --}}
                            <button type="button" @click="zoom(-0.1)"
                                class="p-2 border border-gray-300 rounded-lg hover:bg-gray-50" title="Zoom Out">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM13 10H7" />
                                </svg>
                            </button>
                            {{-- Rotate --}}
                            <button type="button" @click="rotate(-90)"
                                class="p-2 border border-gray-300 rounded-lg hover:bg-gray-50" title="Rotate Left">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                            </button>
                            {{-- Reset --}}
                            <button type="button" @click="reset()"
                                class="p-2 border border-gray-300 rounded-lg hover:bg-gray-50 text-base leading-none"
                                title="Reset">↺</button>
                        </div>

                        <div class="flex gap-3">
                            <button type="button"
                                @click="destroyCropper(); $wire.set('showCropModal', false)"
                                class="px-4 py-2 text-sm text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50">
                                Cancel
                            </button>
                            <button type="button" @click="applyCrop()"
                                :disabled="isApplying"
                                class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 disabled:opacity-60 flex items-center gap-2">
                                <svg x-show="isApplying" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                                </svg>
                                <span x-text="isApplying ? 'Applying...' : 'Apply'"></span>
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        @endif
    </div>

</div>
