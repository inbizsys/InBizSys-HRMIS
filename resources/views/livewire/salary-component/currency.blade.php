<div class="h-full bg-gray-50">
    <div class="w-full py-6 px-6">

        {{-- Header --}}
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-xl font-semibold text-gray-800">Currencies</h1>
            <button type="button" wire:click="openModal"
                class="inline-flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Add Currency
            </button>
        </div>

        {{-- Table --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
            <table class="w-full text-sm text-left">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50">
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Currency Name</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Code</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Base Currency</th>
                        <th class="px-6 py-3 text-xs text-right font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($currencyList as $currency)
                        <tr class="hover:bg-gray-50 transition-colors">

                            {{-- Name --}}
                            <td class="px-6 py-4">
                                <button type="button" wire:click="edit({{ $currency->id }})"
                                    class="text-blue-600 hover:text-blue-700 font-medium">
                                    {{ $currency->name }}
                                </button>
                            </td>

                            {{-- Code --}}
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-semibold bg-blue-50 text-blue-700 tracking-wider">
                                    {{ $currency->code }}
                                </span>
                            </td>

                            {{-- Base Currency --}}
                            <td class="px-6 py-4">
                                @if($currency->is_base_currency)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                        Yes
                                    </span>
                                @else
                                    <span class="text-gray-400 text-sm">—</span>
                                @endif
                            </td>

                            {{-- Actions --}}
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-4">
                                    <button type="button" wire:click="edit({{ $currency->id }})"
                                        class="text-blue-600 hover:text-blue-800 transition-colors" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </button>

                                    {{-- Base currency delete කරන්න බෑ --}}
                                    @unless($currency->is_base_currency)
                                        <button type="button" wire:click="confirmDelete({{ $currency->id }})"
                                            class="text-red-600 hover:text-red-800 transition-colors" title="Delete">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    @endunless
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-gray-400 text-sm">
                                No currencies added yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            @if($currencyList->hasPages())
                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
                    {{ $currencyList->links() }}
                </div>
            @endif
        </div>

        {{-- ─── Add / Edit Modal ──────────────────────────────── --}}
        @if($showModal)
            <div class="absolute inset-0 z-50 flex items-center justify-center bg-black/50"
                wire:keydown.escape="closeModal">
                <div class="bg-white rounded-xl shadow-2xl w-full max-w-md mx-4 overflow-hidden">

                    {{-- Header --}}
                    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                        <h2 class="text-base font-semibold text-gray-800">
                            {{ $editingId ? 'Edit Currency' : 'Add Currency' }}
                        </h2>
                        <button type="button" wire:click="closeModal"
                            class="text-gray-400 hover:text-gray-600 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    {{-- Body --}}
                    <div class="px-6 py-5 space-y-4">

                        {{-- Currency Name --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Currency Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" wire:model="name"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="e.g. Sri Lankan Rupee"
                                autofocus>
                            @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        {{-- Currency Code --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Code <span class="text-red-500">*</span>
                            </label>
                            <input type="text" wire:model="code"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent uppercase"
                                placeholder="e.g. LKR"
                                maxlength="10">
                            @error('code') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        {{-- Is Base Currency --}}
                        <div class="flex items-start gap-3 p-3 rounded-lg border border-gray-200 bg-gray-50">
                            <input type="checkbox" id="isBaseCurrency" wire:model="isBaseCurrency"
                                class="w-4 h-4 mt-0.5 text-blue-600 border-gray-300 rounded focus:ring-blue-500 cursor-pointer">
                            <div>
                                <label for="isBaseCurrency" class="text-sm font-medium text-gray-700 cursor-pointer select-none">
                                    Set as Base Currency
                                </label>
                                <p class="text-xs text-gray-400 mt-0.5">
                                    Base currency is used as the primary currency for salary component limits. Only one currency can be the base at a time.
                                </p>
                            </div>
                        </div>

                    </div>

                    {{-- Footer --}}
                    <div class="flex items-center justify-between px-6 py-4 border-t border-gray-200 bg-gray-50">
                        <div class="flex items-center gap-4">
                            <button type="button" wire:click="save"
                                wire:loading.attr="disabled"
                                class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-8 py-2 rounded-lg transition-colors disabled:opacity-60 flex items-center gap-2">
                                <span wire:loading wire:target="save">
                                    <svg class="animate-spin w-3.5 h-3.5" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
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

        {{-- ─── Delete Confirm Modal ──────────────────────────── --}}
        @if($confirmDeleteId)
            <div class="absolute inset-0 z-50 flex items-center justify-center bg-black/50">
                <div class="bg-white rounded-xl shadow-2xl max-w-lg mx-4 p-6">
                    <h3 class="text-base font-semibold text-gray-800 mb-2">Delete Currency?</h3>
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
