<div class="h-full bg-gray-50">
    <div class="w-full py-6 px-6">

        {{-- Header --}}
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-xl font-semibold text-gray-800">Salary Components</h1>
            <button type="button" wire:click="openModal"
                class="inline-flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add Component
            </button>
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
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Limit</th>
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

                            {{-- Limit --}}
                            <td class="px-6 py-4 text-gray-600">
                                @if ($component->limit)
                                    <span
                                        class="text-xs text-gray-400 mr-0.5">{{ $baseCurrency?->code }}</span>{{ number_format($component->limit, 2) }}
                                @else
                                    —
                                @endif
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

                            {{-- Actions --}}
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-4">
                                    {{-- Edit --}}
                                    <button type="button" wire:click="edit({{ $component->id }})"
                                        class="text-blue-600 hover:text-blue-800 transition-colors" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>

                                    {{-- Delete (hidden for system records & "Basic") --}}
                                    @if (!$component->is_system && strtolower($component->name) !== 'basic')
                                        <button type="button" wire:click="confirmDelete({{ $component->id }})"
                                            class="text-red-600 hover:text-red-800 transition-colors" title="Delete">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
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

                        {{-- Limit --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Limit</label>
                            <div
                                class="flex rounded-lg border border-gray-300 overflow-hidden focus-within:ring-2 focus-within:ring-blue-500 focus-within:border-transparent">
                                @if ($baseCurrency)
                                    <span
                                        class="inline-flex items-center px-3 py-2 bg-gray-100 border-r border-gray-300 text-sm text-gray-500 font-medium select-none whitespace-nowrap">
                                        {{ $baseCurrency->code }}
                                    </span>
                                @endif
                                <input type="number" wire:model="limit" min="0" step="0.01"
                                    class="flex-1 px-3 py-2 text-sm focus:outline-none border-0"
                                    placeholder="Optional — leave blank for no limit">
                            </div>
                            @error('limit')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
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
        @endif

        {{-- ─── Delete Confirm Modal ──────────────────────────────── --}}
        @if ($confirmDeleteId)
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
        @endif

    </div>
</div>
