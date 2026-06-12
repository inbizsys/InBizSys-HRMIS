<div class="h-full bg-gray-50">
    <div class="w-full py-6 px-6">

        {{-- ─── Flash Message ───────────────────────────────────── --}}
        @if (session('success'))
            <div class="mb-4 flex items-center gap-2 bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-lg">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                {{ session('success') }}
            </div>
        @endif

        {{-- ─── Header ──────────────────────────────────────────── --}}
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-xl font-semibold text-gray-800">Banks</h1>
                <p class="text-sm text-gray-500 mt-0.5">Manage banks and their branches</p>
            </div>
            <button type="button" wire:click="openBankModal"
                class="inline-flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add Bank
            </button>
        </div>

        {{-- ─── Filters ─────────────────────────────────────────── --}}
        <div class="flex flex-wrap items-center gap-3 mb-4">
            {{-- Search --}}
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z" />
                </svg>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search banks…"
                    class="pl-9 pr-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent w-64">
            </div>

            {{-- Status Filter --}}
            <div class="flex gap-0 border border-gray-300 rounded-lg overflow-hidden">
                @foreach (['active' => 'Active', 'inactive' => 'Inactive', 'all' => 'All'] as $val => $label)
                    <button type="button" wire:click="$set('filterStatus', '{{ $val }}')"
                        class="px-3 py-2 text-xs font-medium transition-colors
                            {{ $filterStatus === $val ? 'bg-blue-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-50' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </div>
        </div>

        {{-- ─── Table ───────────────────────────────────────────── --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
            <table class="w-full text-sm text-left">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50">
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Bank Code</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Bank Name</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Branches</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-xs text-right font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($bankList as $bank)
                        <tr class="hover:bg-gray-50 transition-colors">
                            {{-- Bank Code --}}
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200 font-mono tracking-wide">
                                    {{ $bank->bank_code }}
                                </span>
                            </td>

                            {{-- Bank Name --}}
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-800">{{ $bank->bank_name }}</div>
                            </td>

                            {{-- Branches Count --}}
                            <td class="px-6 py-4">
                                <a href="{{ route('configurations.banks.branches', $bank->id) }}"
                                    wire:navigate
                                    class="inline-flex items-center gap-1.5 text-sm text-blue-600 hover:text-blue-700 font-medium transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                    {{ $bank->branches_count }}
                                    {{ Str::plural('Branch', $bank->branches_count) }}
                                </a>
                            </td>

                            {{-- Status --}}
                            <td class="px-6 py-4">
                                @if ($bank->is_active)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Active</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500">Inactive</span>
                                @endif
                            </td>

                            {{-- Actions --}}
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-4">
                                    <a href="{{ route('configurations.banks.branches', $bank->id) }}"
                                        wire:navigate
                                        class="text-gray-500 hover:text-blue-600 transition-colors" title="Manage Branches">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                    </a>
                                    <button type="button" wire:click="editBank({{ $bank->id }})"
                                        class="text-blue-600 hover:text-blue-800 transition-colors" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>
                                    <button type="button" wire:click="confirmDeleteBank({{ $bank->id }})"
                                        class="text-red-500 hover:text-red-700 transition-colors" title="Delete">
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
                            <td colspan="5" class="px-6 py-12 text-center text-gray-400 text-sm">
                                No banks found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            @if ($bankList->hasPages())
                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
                    {{ $bankList->links() }}
                </div>
            @endif
        </div>

        {{-- ═══════════════════════════════════════════════════════════
             ADD / EDIT BANK — Modal
        ════════════════════════════════════════════════════════════ --}}
        @if ($showBankModal)
            <div class="absolute inset-0 z-50 flex items-center justify-center bg-black/50 p-4" x-data
                x-on:keydown.escape.window="$wire.closeBankModal()">
                <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md flex flex-col overflow-hidden">

                    {{-- Header --}}
                    <div class="flex items-center justify-between px-6 py-5 border-b border-gray-200">
                        <h2 class="text-base font-semibold text-gray-800">
                            {{ $editingBankId ? 'Edit Bank' : 'Add Bank' }}
                        </h2>
                        <button type="button" wire:click="closeBankModal"
                            class="text-gray-400 hover:text-gray-600 transition-colors p-1 rounded-lg hover:bg-gray-100">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    {{-- Body --}}
                    <div class="px-6 py-5 space-y-4">

                        {{-- Bank Code --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                Bank Code <span class="text-red-500">*</span>
                                <span class="text-xs font-normal text-gray-400 ml-1">e.g. 7010, 7047, 7750</span>
                            </label>
                            <input type="text" wire:model="bankCode"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm uppercase font-mono tracking-wide focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('bankCode') border-red-400 @enderror"
                                placeholder="e.g. 7010"
                                maxlength="20">
                            @error('bankCode')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Bank Name --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                Bank Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" wire:model="bankName"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('bankName') border-red-400 @enderror"
                                placeholder="e.g. Bank of Ceylon">
                            @error('bankName')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Is Active --}}
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                            <div class="flex items-center gap-2.5">
                                <input type="checkbox" id="bankIsActive" wire:model="bankIsActive"
                                    class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 cursor-pointer">
                                <label for="bankIsActive" class="text-sm font-medium text-gray-700 cursor-pointer">
                                    Active
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="flex items-center justify-between px-6 py-4 border-t border-gray-200 bg-gray-50">
                        <span class="text-xs text-red-500">* indicates mandatory fields</span>
                        <div class="flex gap-3">
                            <button type="button" wire:click="closeBankModal"
                                class="border border-gray-300 text-gray-700 text-sm font-medium px-5 py-2 rounded-lg hover:bg-gray-100 transition-colors">
                                Cancel
                            </button>
                            <button type="button" wire:click="saveBank" wire:loading.attr="disabled"
                                class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-5 py-2 rounded-lg transition-colors disabled:opacity-60 inline-flex items-center gap-1.5">
                                <span wire:loading wire:target="saveBank">
                                    <svg class="animate-spin w-3.5 h-3.5" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z" />
                                    </svg>
                                </span>
                                {{ $editingBankId ? 'Update Bank' : 'Save Bank' }}
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        @endif

        {{-- ─── Delete Bank Confirm Modal ──────────────────────────── --}}
        @if ($confirmDeleteBankId)
            <div class="absolute inset-0 z-[60] flex items-center justify-center bg-black/50">
                <div class="bg-white rounded-xl shadow-2xl max-w-md mx-4 p-6">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <h3 class="text-base font-semibold text-gray-800">Delete Bank?</h3>
                    </div>
                    <p class="text-sm text-gray-500 mb-5">This will also delete all branches under this bank. This action cannot be undone.</p>
                    <div class="flex gap-3 justify-end">
                        <button type="button" wire:click="cancelDeleteBank"
                            class="border border-gray-300 text-gray-700 text-sm px-5 py-2 rounded-lg hover:bg-gray-100 transition-colors">
                            Cancel
                        </button>
                        <button type="button" wire:click="deleteBank" wire:loading.attr="disabled"
                            class="bg-red-600 hover:bg-red-700 text-white text-sm px-5 py-2 rounded-lg transition-colors">
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        @endif

    </div>
</div>
