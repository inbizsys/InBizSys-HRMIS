<div class="h-full bg-gray-50">
    <div class="w-full py-6 px-6">

        {{-- ─── Toast Notifications ─────────────────────────────── --}}
        @if (session()->has('success'))
            <div
                x-data="{ show: true }"
                x-show="show"
                x-init="setTimeout(() => show = false, 4000)"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-2"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 translate-y-2"
                class="fixed bottom-6 right-6 z-[100] flex items-center gap-3 bg-white border border-green-200 text-green-800 text-sm font-medium px-4 py-3 rounded-xl shadow-lg"
            >
                <svg class="w-5 h-5 text-green-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span>{{ session('success') }}</span>
                <button @click="show = false" class="ml-2 text-green-400 hover:text-green-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        @endif

        @if (session()->has('error'))
            <div
                x-data="{ show: true }"
                x-show="show"
                x-init="setTimeout(() => show = false, 5000)"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-2"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 translate-y-2"
                class="fixed bottom-6 right-6 z-[100] flex items-center gap-3 bg-white border border-red-200 text-red-800 text-sm font-medium px-4 py-3 rounded-xl shadow-lg"
            >
                <svg class="w-5 h-5 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                <span>{{ session('error') }}</span>
                <button @click="show = false" class="ml-2 text-red-400 hover:text-red-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        @endif

        {{-- Header --}}
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-xl font-semibold text-gray-800">EPF / ETF Settings</h1>
                <p class="text-sm text-gray-500 mt-0.5">Manage Employees' Provident Fund and Trust Fund contribution rates.</p>
            </div>
            <button type="button" wire:click="openModal"
                class="inline-flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add Setting
            </button>
        </div>

        {{-- Tabs --}}
        <div class="flex gap-0 mb-0 border-b border-gray-200">
            <button type="button" wire:click="switchTab('epf')"
                class="px-5 py-2.5 text-sm font-medium border-b-2 transition-colors
                    {{ $activeTab === 'epf'
                        ? 'border-blue-600 text-blue-600'
                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                EPF
            </button>
            <button type="button" wire:click="switchTab('etf')"
                class="px-5 py-2.5 text-sm font-medium border-b-2 transition-colors
                    {{ $activeTab === 'etf'
                        ? 'border-blue-600 text-blue-600'
                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                ETF
            </button>
        </div>

        {{-- Info Banner --}}
        {{-- <div class="bg-blue-50 border border-blue-100 border-t-0 rounded-b-lg px-5 py-3 mb-0">
            @if ($activeTab === 'epf')
                <p class="text-xs text-blue-700">
                    <span class="font-semibold">EPF (Employees' Provident Fund):</span>
                    Statutory defaults — Employee <span class="font-semibold">8%</span>, Employer <span class="font-semibold">12%</span>.
                    Governed by the EPF Act No. 15 of 1958 (Sri Lanka).
                </p>
            @else
                <p class="text-xs text-blue-700">
                    <span class="font-semibold">ETF (Employees' Trust Fund):</span>
                    Employer-only contribution — Employer <span class="font-semibold">3%</span>.
                    Governed by the ETF Act No. 46 of 1980 (Sri Lanka).
                </p>
            @endif
        </div> --}}

        {{-- Table --}}
        <div class="bg-white rounded-b-xl border border-t-0 border-gray-200 overflow-hidden shadow-sm">
            <table class="w-full text-sm text-left">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50">
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            {{ $activeTab === 'epf' ? 'EPF' : 'ETF' }} Reg. Number
                        </th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Deduction Cycle</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Employee Rate</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Employer Rate</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-xs text-right font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($settingsList as $setting)
                        <tr class="hover:bg-gray-50 transition-colors">

                            {{-- Registration Number --}}
                            <td class="px-6 py-4">
                                <button type="button" wire:click="edit({{ $setting->id }})"
                                    class="text-blue-600 hover:text-blue-700 font-medium">
                                    {{ $setting->registration_number ?: '—' }}
                                </button>
                            </td>

                            {{-- Deduction Cycle --}}
                            <td class="px-6 py-4 text-gray-600">{{ $setting->deduction_cycle_label }}</td>

                            {{-- Employee Rate --}}
                            <td class="px-6 py-4 text-gray-600">
                                @if ($activeTab === 'etf')
                                    <span class="text-xs text-gray-400 italic">N/A</span>
                                @else
                                    <span class="font-medium text-gray-800">{{ number_format($setting->employee_rate, 2) }}</span>
                                    <span class="text-gray-400 text-xs">%</span>
                                @endif
                            </td>

                            {{-- Employer Rate --}}
                            <td class="px-6 py-4 text-gray-600">
                                <span class="font-medium text-gray-800">{{ number_format($setting->employer_rate, 2) }}</span>
                                <span class="text-gray-400 text-xs">%</span>
                            </td>

                            {{-- Status --}}
                            <td class="px-6 py-4">
                                @if ($setting->is_active)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500">
                                        Inactive
                                    </span>
                                @endif
                            </td>

                            {{-- Actions --}}
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-4">
                                    {{-- Edit --}}
                                    <button type="button" wire:click="edit({{ $setting->id }})"
                                        class="text-blue-600 hover:text-blue-800 transition-colors" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>

                                    {{-- Delete --}}
                                    <button type="button" wire:click="confirmDelete({{ $setting->id }})"
                                        class="text-red-600 hover:text-red-800 transition-colors" title="Delete">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-400 text-sm">
                                No {{ strtoupper($activeTab) }} settings configured yet.
                                <button type="button" wire:click="openModal"
                                    class="ml-1 text-blue-500 hover:text-blue-700 underline underline-offset-2">
                                    Add one now.
                                </button>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- ─── Add / Edit Modal ─────────────────────────────────── --}}
        @if ($showModal)
            <div class="absolute inset-0 z-50 flex items-center justify-center bg-black/50"
                wire:keydown.escape="closeModal">
                <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg mx-4 overflow-hidden">

                    {{-- Modal Header --}}
                    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                        <h2 class="text-base font-semibold text-gray-800">
                            {{ $editingId
                                ? 'Edit ' . strtoupper($fundType) . ' Settings'
                                : 'Add ' . strtoupper($activeTab) . ' Settings' }}
                        </h2>
                        <button type="button" wire:click="closeModal"
                            class="text-gray-400 hover:text-gray-600 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    {{-- Modal Body --}}
                    <div class="px-6 py-5 space-y-4">

                        {{-- Fund Type — read-only, locked to the active tab --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Fund Type</label>
                            <div class="flex items-center gap-2.5 px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold tracking-wide
                                    {{ ($editingId ? $fundType : $activeTab) === 'epf'
                                        ? 'bg-blue-100 text-blue-700'
                                        : 'bg-purple-100 text-purple-700' }}">
                                    {{ strtoupper($editingId ? $fundType : $activeTab) }}
                                </span>
                                <span class="text-sm text-gray-600">
                                    {{ ($editingId ? $fundType : $activeTab) === 'epf'
                                        ? "Employees' Provident Fund"
                                        : "Employees' Trust Fund" }}
                                </span>
                                <svg class="w-3.5 h-3.5 text-gray-400 ml-auto shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m0 0v2m0-2h2m-2 0H10m9-7a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <p class="text-xs text-gray-400 mt-1">Fund type is determined by the selected tab and cannot be changed here.</p>
                        </div>

                        {{-- Registration Number --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                {{ strtoupper($fundType) }} Registration Number
                            </label>
                            <input type="text" wire:model="registrationNumber"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('registrationNumber') border-red-400 @enderror"
                                placeholder="e.g. EPF/12345">
                            @error('registrationNumber')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Deduction Cycle --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Deduction Cycle <span class="text-red-500">*</span>
                            </label>
                            <select wire:model="deductionCycle"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="monthly">Monthly</option>
                                <option value="bi_monthly">Bi-Monthly</option>
                                <option value="quarterly">Quarterly</option>
                            </select>
                            @error('deductionCycle')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Contribution Rates --}}
                        <div class="grid grid-cols-2 gap-4">

                            {{-- Employee Rate --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Employee Rate (%) <span class="text-red-500">*</span>
                                </label>
                                <div class="flex rounded-lg border border-gray-300 overflow-hidden focus-within:ring-2 focus-within:ring-blue-500 focus-within:border-transparent
                                    {{ $fundType === 'etf' ? 'bg-gray-50' : '' }}">
                                    <input type="number" wire:model="employeeRate"
                                        min="0" max="100" step="0.01"
                                        {{ $fundType === 'etf' ? 'readonly' : '' }}
                                        class="flex-1 px-3 py-2 text-sm focus:outline-none border-0 bg-transparent
                                            {{ $fundType === 'etf' ? 'text-gray-400 cursor-not-allowed' : '' }}"
                                        placeholder="e.g. 8.00">
                                    <span class="inline-flex items-center px-3 py-2 bg-gray-100 border-l border-gray-300 text-sm text-gray-500 font-medium">%</span>
                                </div>
                                @if ($fundType === 'etf')
                                    <p class="text-xs text-gray-400 mt-1">ETF has no employee contribution.</p>
                                @endif
                                @error('employeeRate')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Employer Rate --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Employer Rate (%) <span class="text-red-500">*</span>
                                </label>
                                <div class="flex rounded-lg border border-gray-300 overflow-hidden focus-within:ring-2 focus-within:ring-blue-500 focus-within:border-transparent">
                                    <input type="number" wire:model="employerRate"
                                        min="0" max="100" step="0.01"
                                        class="flex-1 px-3 py-2 text-sm focus:outline-none border-0"
                                        placeholder="{{ $fundType === 'epf' ? '12.00' : '3.00' }}">
                                    <span class="inline-flex items-center px-3 py-2 bg-gray-100 border-l border-gray-300 text-sm text-gray-500 font-medium">%</span>
                                </div>
                                @error('employerRate')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        {{-- Statutory hint --}}
                        {{-- <div class="flex items-start gap-2 bg-amber-50 border border-amber-100 rounded-lg px-3 py-2.5">
                            <svg class="w-4 h-4 text-amber-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 110 20A10 10 0 0112 2z"/>
                            </svg>
                            <p class="text-xs text-amber-700">
                                @if ($fundType === 'epf')
                                    Statutory rates: Employee <strong>8%</strong>, Employer <strong>12%</strong>.
                                    Only change if a special rate applies to your organisation.
                                @else
                                    Statutory rate: Employer <strong>3%</strong>.
                                    Only change if a special rate applies to your organisation.
                                @endif
                            </p>
                        </div> --}}

                        {{-- Mark as Active --}}
                        <div class="flex items-center gap-2">
                            <input type="checkbox" id="isActive" wire:model="isActive"
                                class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 cursor-pointer">
                            <label for="isActive" class="text-sm cursor-pointer select-none text-gray-700">
                                Mark this as Active
                            </label>
                        </div>

                    </div>

                    {{-- Modal Footer --}}
                    <div class="flex items-center justify-between px-6 py-4 border-t border-gray-200 bg-gray-50">
                        <div class="flex items-center gap-4">
                            <button type="button" wire:click="save" wire:loading.attr="disabled"
                                class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-8 py-2 rounded-lg transition-colors disabled:opacity-60 flex items-center gap-2">
                                <span wire:loading wire:target="save">
                                    <svg class="animate-spin w-3.5 h-3.5" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                            stroke="currentColor" stroke-width="4" />
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z" />
                                    </svg>
                                </span>
                                Save
                            </button>
                            <button type="button" wire:click="closeModal"
                                class="border border-gray-300 text-gray-700 text-sm font-medium px-8 py-2 rounded-lg hover:bg-gray-100 transition-colors">
                                Cancel
                            </button>
                        </div>
                        <span class="text-xs text-red-600">* indicates mandatory fields</span>
                    </div>

                </div>
            </div>
        @endif

        {{-- ─── Delete Confirm Modal ──────────────────────────────── --}}
        @if ($confirmDeleteId)
            <div class="absolute inset-0 z-50 flex items-center justify-center bg-black/50">
                <div class="bg-white rounded-xl shadow-2xl max-w-lg mx-4 p-6">
                    <h3 class="text-base font-semibold text-gray-800 mb-2">Delete Settings?</h3>
                    <p class="text-sm text-gray-500 mb-5">This action cannot be undone. Are you sure you want to delete these {{ strtoupper($activeTab) }} settings?</p>
                    <div class="flex gap-3 justify-end">
                        <button type="button" wire:click="cancelDelete"
                            class="border border-gray-300 text-gray-700 text-sm px-4 py-2 rounded-lg hover:bg-gray-100 transition-colors">
                            Cancel
                        </button>
                        <button type="button" wire:click="delete" wire:loading.attr="disabled"
                            class="bg-red-600 hover:bg-red-700 text-white text-sm px-4 py-2 rounded-lg transition-colors disabled:opacity-60 flex items-center gap-2">
                            <span wire:loading wire:target="delete">
                                <svg class="animate-spin w-3.5 h-3.5" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                                </svg>
                            </span>
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        @endif

    </div>
</div>
