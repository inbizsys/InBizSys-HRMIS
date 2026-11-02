<div class="h-full bg-gray-50">
    <div class="w-full py-6 px-6">

        {{-- Header --}}
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-xl font-semibold text-gray-800">Salary Components</h1>
            @can('create salary component')
                <button type="button" wire:click="openModal"
                    class="inline-flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add Component
                </button>
            @endcan
        </div>

        {{-- Tabs --}}
        <div class="flex gap-0 mb-0 border-b border-gray-200">
            <button type="button" wire:click="switchTab('earning')"
                class="px-5 py-2.5 text-sm font-medium border-b-2 transition-colors
                    {{ $activeTab === 'earning'
                        ? 'border-blue-600 text-blue-600'
                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                Earnings
            </button>
            <button type="button" wire:click="switchTab('deduction')"
                class="px-5 py-2.5 text-sm font-medium border-b-2 transition-colors
                    {{ $activeTab === 'deduction'
                        ? 'border-blue-600 text-blue-600'
                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                Deductions
            </button>
        </div>

        {{-- Table --}}
        <div class="bg-white rounded-b-xl rounded-tr-xl border border-t-0 border-gray-200 overflow-hidden shadow-sm">
            <table class="w-full text-sm text-left">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50">
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Payslip Name
                        </th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Mode & Limit
                        </th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Rules &
                            Frequency</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-xs text-right font-semibold text-gray-500 uppercase tracking-wider">
                            Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($componentList as $component)
                        <tr class="hover:bg-gray-50 transition-colors">
                            {{-- Name --}}
                            <td class="px-6 py-4">
                                <button type="button" wire:click="edit({{ $component->id }})"
                                    class="text-blue-600 hover:text-blue-700 font-medium">
                                    {{ $component->name }}
                                </button>
                            </td>

                            {{-- Type --}}
                            <td class="px-6 py-4 text-gray-600">{{ $component->name_in_payslip }}</td>

                            {{-- Limit: displays % for percentage, or currency amount for fixed --}}
                            <td class="px-6 py-4 text-gray-600 font-medium">
                                @if ($component->limit)
                                    @if ($component->calculation_type === 'percentage')
                                        <span class="text-purple-700 font-bold">{{ $component->limit }}%</span>
                                    @else
                                        <span class="text-xs text-gray-400 mr-0.5">{{ $baseCurrency?->code ?? 'LKR' }}</span>{{ number_format($component->limit, 2) }}
                                    @endif
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>

                            {{-- Rules & Frequency Badges --}}
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap items-center gap-1.5">
                                    {{-- Mode badge --}}
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold {{ $component->calculation_type === 'percentage' ? 'bg-purple-50 text-purple-700 border border-purple-200' : 'bg-slate-50 text-slate-700 border border-slate-200' }}">
                                        {{ $component->calculation_type === 'percentage' ? '% of Basic' : 'Fixed Amount' }}
                                    </span>
                                    {{-- EPF badge (Only for earnings) --}}
                                    @if ($component->type === 'earning' && $component->is_epf_applicable)
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-200"
                                            title="Subject to EPF/ETF">
                                            EPF/ETF
                                        </span>
                                    @endif
                                    {{-- Taxable badge --}}
                                    @if ($component->is_taxable)
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-amber-50 text-amber-700 border border-amber-200"
                                            title="Subject to Income Tax (APIT)">
                                            Taxable
                                        </span>
                                    @endif
                                    {{-- Recurring / One-time badge --}}
                                    @if ($component->is_recurring)
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            Recurring
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-gray-100 text-gray-600 border border-gray-200">
                                            One-time
                                        </span>
                                    @endif
                                </div>
                            </td>

                            {{-- Status --}}
                            <td class="px-6 py-4">
                                @if ($component->is_active)
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                        Active
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500">
                                        Inactive
                                    </span>
                                @endif
                            </td>

                            {{-- Actions (Matching Shift & Policy Settings style) --}}
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                <div class="inline-flex items-center space-x-1 justify-end w-full">
                                    {{-- Edit Button --}}
                                    @can('edit salary component')
                                        <button type="button" wire:click="edit({{ $component->id }})"
                                            title="Edit Component"
                                            class="px-2 py-1 inline-flex text-xs font-semibold rounded-lg bg-blue-100 text-blue-800 hover:bg-blue-200 transition">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                            </svg>
                                        </button>
                                    @endcan
                                    {{-- Delete Button (hidden for system records & "Basic") --}}
                                    @if (!$component->is_system && strtolower($component->name) !== 'basic')
                                        @can('delete salary component')
                                            <span class="text-gray-200 select-none">|</span>
                                            <button type="button" wire:click="confirmDelete({{ $component->id }})"
                                                title="Delete Component"
                                                class="px-2 py-1 inline-flex text-xs font-semibold rounded-lg bg-red-100 text-red-800 hover:bg-red-200 transition">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                    stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                                </svg>
                                            </button>
                                        @endcan
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-400 text-sm">
                                No {{ $activeTab === 'earning' ? 'earnings' : 'deductions' }} added yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{-- Pagination --}}
            @if ($componentList->hasPages())
                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
                    {{ $componentList->links() }}
                </div>
            @endif
        </div>

        {{-- ─── Add / Edit Modal ─────────────────────────────────── --}}
        @if ($showModal)
            @teleport('body')
            <div class="absolute inset-0 z-50 flex items-center justify-center bg-black/50"
                wire:keydown.escape="closeModal">
                <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg mx-4 overflow-hidden">

                    {{-- Modal Header --}}
                    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                        <h2 class="text-base font-semibold text-gray-800">
                            @if ($editingId)
                                {{ $activeTab === 'earning' ? 'Edit Earning' : 'Edit Deduction' }}
                            @else
                                Add Component
                            @endif
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

                        {{-- Type --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Type <span class="text-red-500">*</span>
                            </label>
                            <select wire:model="type" {{ $editingId ? 'disabled' : '' }}
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent
                                    {{ $editingId ? 'bg-gray-100 cursor-not-allowed text-gray-500' : '' }}">
                                <option value="earning">Earning</option>
                                <option value="deduction">Deduction</option>
                            </select>
                            @error('type')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Name --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" wire:model="name"
                                {{ $editingId && strtolower($name) === 'basic' ? 'disabled' : '' }}
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent
                                    {{ $editingId && strtolower($name) === 'basic' ? 'bg-gray-100 cursor-not-allowed text-gray-500' : '' }}"
                                placeholder="e.g. House Rent Allowance" autofocus>
                            @if ($editingId && strtolower($name) === 'basic')
                                <p class="text-xs text-gray-400 mt-1">Name cannot be changed for system components.</p>
                            @endif
                            @error('name')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Name in Payslip --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Name in Payslip <span class="text-red-500">*</span>
                            </label>
                            <input type="text" wire:model="nameInPayslip"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="e.g. HRA">
                            @error('nameInPayslip')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- 1. Calculation Mode --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Calculation Mode <span class="text-red-500">*</span>
                            </label>
                            <div class="grid grid-cols-2 gap-3">
                                <label
                                    class="flex items-center gap-2 p-3 border rounded-xl cursor-pointer transition {{ $calculationType === 'fixed' ? 'border-blue-500 bg-blue-50/50 text-blue-900 font-semibold' : 'border-gray-200 hover:bg-gray-50 text-gray-700' }}">
                                    <input type="radio" value="fixed" wire:model.live="calculationType"
                                        class="text-blue-600 focus:ring-blue-500">
                                    <span class="text-xs font-semibold">Fixed Flat Amount</span>
                                </label>
                                <label
                                    class="flex items-center gap-2 p-3 border rounded-xl cursor-pointer transition {{ $calculationType === 'percentage' ? 'border-blue-500 bg-blue-50/50 text-blue-900 font-semibold' : 'border-gray-200 hover:bg-gray-50 text-gray-700' }}">
                                    <input type="radio" value="percentage" wire:model.live="calculationType"
                                        class="text-blue-600 focus:ring-blue-500">
                                    <span class="text-xs font-semibold">% of Basic Salary</span>
                                </label>
                            </div>
                        </div>

                        {{-- 2. Dynamic Limit (depends on calculation mode) --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                {{ $calculationType === 'percentage' ? 'Limit (Max % of Basic)' : 'Limit (Max Amount)' }}
                            </label>
                            <div
                                class="flex rounded-lg border border-gray-300 overflow-hidden focus-within:ring-2 focus-within:ring-blue-500 focus-within:border-transparent">
                                <span
                                    class="inline-flex items-center px-3 py-2 bg-gray-100 border-r border-gray-300 text-sm text-gray-600 font-bold select-none whitespace-nowrap">
                                    {{ $calculationType === 'percentage' ? '%' : ($baseCurrency?->code ?? 'LKR') }}
                                </span>
                                <input type="number" wire:model="limit" min="0" {{ $calculationType === 'percentage' ? 'max=100' : '' }} step="0.01"
                                    class="flex-1 px-3 py-2 text-sm focus:outline-none border-0"
                                    placeholder="{{ $calculationType === 'percentage' ? 'Optional — e.g. 20 (for max 20%)' : 'Optional — leave blank for no limit' }}">
                            </div>
                            @error('limit')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        {{-- Policy & Legal Compliance Card (Matching EmployeeCategoryManager style) --}}
                        <div class="p-4 bg-slate-50 border border-slate-200/80 rounded-xl space-y-3">
                            <h4 class="text-xs font-bold text-slate-600 uppercase tracking-wider">Payroll & Legal Rules
                            </h4>
                            {{-- EPF/ETF Checkbox (Visible only for earnings) --}}
                            @if ($type === 'earning')
                                <div class="flex items-center gap-2.5">
                                    <input type="checkbox" id="isEpfApplicable" wire:model="isEpfApplicable"
                                        class="rounded text-blue-600 focus:ring-blue-500 border-gray-300">
                                    <label for="isEpfApplicable"
                                        class="text-xs font-semibold text-gray-700 cursor-pointer">
                                        Eligible for EPF / ETF
                                    </label>
                                </div>
                            @endif
                            {{-- Taxable Checkbox --}}
                            <div class="flex items-center gap-2.5">
                                <input type="checkbox" id="isTaxable" wire:model="isTaxable"
                                    class="rounded text-blue-600 focus:ring-blue-500 border-gray-300">
                                <label for="isTaxable" class="text-xs font-semibold text-gray-700 cursor-pointer">
                                    Subject to Income Tax
                                </label>
                            </div>
                            {{-- Recurring Checkbox --}}
                            <div class="flex items-center gap-2.5">
                                <input type="checkbox" id="isRecurring" wire:model="isRecurring"
                                    class="rounded text-blue-600 focus:ring-blue-500 border-gray-300">
                                <label for="isRecurring" class="text-xs font-semibold text-gray-700 cursor-pointer">
                                    Recurring Monthly
                                </label>
                            </div>
                        </div>

                        {{-- Mark as Active --}}
                        <div class="flex items-center gap-2">
                            <input type="checkbox" id="isActive" wire:model="isActive"
                                {{ strtolower($name) === 'basic' ? 'disabled' : '' }}
                                class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500
            {{ strtolower($name) === 'basic' ? 'cursor-not-allowed opacity-50' : 'cursor-pointer' }}">
                            <label for="isActive"
                                class="text-sm cursor-pointer select-none
            {{ strtolower($name) === 'basic' ? 'text-gray-400' : 'text-gray-700' }}">
                                Mark this as Active
                            </label>
                        </div>
                        @if (strtolower($name) === 'basic')
                            <p class="text-xs text-gray-400 -mt-2">Basic component must always remain active.</p>
                        @endif

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
            @endteleport
        @endif

        {{-- ─── Delete Confirm Modal ──────────────────────────────── --}}
        @if ($confirmDeleteId)
        @teleport('body')
            <div class="absolute inset-0 z-50 flex items-center justify-center bg-black/50">
                <div class="bg-white rounded-xl shadow-2xl max-w-lg mx-4 p-6">
                    <h3 class="text-base font-semibold text-gray-800 mb-2">Delete Component?</h3>
                    <p class="text-sm text-gray-500 mb-5">This action cannot be undone. Are you sure?</p>
                    <div class="flex gap-3 justify-end">
                        <button type="button" wire:click="cancelDelete"
                            class="border border-gray-300 text-gray-700 text-sm px-4 py-2 rounded-lg hover:bg-gray-100 transition-colors">
                            Cancel
                        </button>
                        <button type="button" wire:click="delete" wire:loading.attr="disabled"
                            class="bg-red-600 hover:bg-red-700 text-white text-sm px-4 py-2 rounded-lg transition-colors">
                            Delete
                        </button>
                    </div>
                </div>
            </div>
            @endteleport
        @endif

    </div>
</div>
