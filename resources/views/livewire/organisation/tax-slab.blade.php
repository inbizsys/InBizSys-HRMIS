<div class="h-full bg-gray-50">
    <div class="w-full py-6 px-6">

        {{-- ── Toast Notifications ── --}}
        @if (session()->has('success'))
            <div
                x-data="{ show: true }"
                x-init="setTimeout(() => show = false, 4000)"
                x-show="show"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-2"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 translate-y-2"
                class="fixed bottom-5 right-5 z-[100] flex items-center gap-3 bg-white border border-green-200 text-green-800 text-sm font-medium px-4 py-3 rounded-xl shadow-lg"
            >
                <span class="flex items-center justify-center w-7 h-7 rounded-full bg-green-100 shrink-0">
                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </span>
                {{ session('success') }}
                <button @click="show = false" class="ml-2 text-green-400 hover:text-green-600 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        @endif

        @if (session()->has('error'))
            <div
                x-data="{ show: true }"
                x-init="setTimeout(() => show = false, 5000)"
                x-show="show"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-2"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 translate-y-2"
                class="fixed bottom-5 right-5 z-[100] flex items-center gap-3 bg-white border border-red-200 text-red-800 text-sm font-medium px-4 py-3 rounded-xl shadow-lg"
            >
                <span class="flex items-center justify-center w-7 h-7 rounded-full bg-red-100 shrink-0">
                    <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </span>
                {{ session('error') }}
                <button @click="show = false" class="ml-2 text-red-400 hover:text-red-600 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        @endif

        {{-- ── Page Header ── --}}
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-xl font-semibold text-gray-800">Tax Slabs</h1>
                <p class="text-xs text-gray-500 mt-0.5">Summarized Tax Table – Regular Profits from Employment</p>
            </div>
            <button type="button" wire:click="openModal"
                class="inline-flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                New Slab
            </button>
        </div>

        {{-- ── Tax Table ── --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
            <table class="w-full text-sm text-left">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50">
                        <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider w-10">#</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            Monthly Regular Profits from Employment (Taxable)
                        </th>
                        {{-- Dynamic currency symbol — table header --}}
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            Min Amount ({{ $currencySymbol }})
                        </th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            Max Amount ({{ $currencySymbol }})
                        </th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Tax %</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Tax</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($taxSlabList as $slab)
                        <tr class="hover:bg-gray-50 transition-colors {{ is_null($slab->tax_percentage) ? 'bg-green-50/60' : '' }}">

                            {{-- Slab Order --}}
                            <td class="px-4 py-4">
                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-blue-100 text-blue-700 text-xs font-bold">
                                    {{ $slab->slab_order }}
                                </span>
                            </td>

                            {{-- Range Description — Model accessor එකෙන් dynamic currency සමග ගන්නවා --}}
                            <td class="px-6 py-4 text-gray-800 font-medium">
                                <button type="button" wire:click="edit({{ $slab->id }})"
                                    class="text-left text-blue-600 hover:text-blue-700">
                                    {{ $slab->range_label }}
                                </button>
                            </td>

                            {{-- Min Amount — dynamic currency symbol --}}
                            <td class="px-6 py-4 text-gray-600">
                                {{ is_null($slab->min_amount) ? '—' : $currencySymbol . ' ' . number_format($slab->min_amount, 0) }}
                            </td>

                            {{-- Max Amount — dynamic currency symbol --}}
                            <td class="px-6 py-4 text-gray-600">
                                {{ is_null($slab->max_amount) ? 'No Limit' : $currencySymbol . ' ' . number_format($slab->max_amount, 0)}}
                            </td>

                            {{-- Tax Percentage --}}
                            <td class="px-6 py-4">
                                @if(is_null($slab->tax_percentage))
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-green-100 text-green-700 text-xs font-medium">
                                        Relief
                                    </span>
                                @else
                                    <span class="font-semibold text-gray-800">{{ number_format($slab->tax_percentage, 0) }}%</span>
                                @endif
                            </td>

                            {{-- Tax Label — Model accessor එකෙන් dynamic currency සමග ගන්නවා --}}
                            <td class="px-6 py-4 text-gray-600 text-xs max-w-xs">
                                {{ $slab->tax_label }}
                            </td>

                            {{-- Actions --}}
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-3">
                                    {{-- Move Up --}}
                                    <button type="button" wire:click="moveUp({{ $slab->id }})"
                                        class="text-gray-400 hover:text-gray-600 transition-colors disabled:opacity-30"
                                        title="Move Up"
                                        @if($loop->first) disabled @endif>
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                                        </svg>
                                    </button>

                                    {{-- Move Down --}}
                                    <button type="button" wire:click="moveDown({{ $slab->id }})"
                                        class="text-gray-400 hover:text-gray-600 transition-colors disabled:opacity-30"
                                        title="Move Down"
                                        @if($loop->last) disabled @endif>
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </button>

                                    {{-- Edit --}}
                                    <button type="button" wire:click="edit({{ $slab->id }})"
                                        class="text-blue-600 hover:text-blue-800 transition-colors"
                                        title="Edit Slab">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </button>

                                    {{-- Delete --}}
                                    <button type="button" wire:click="confirmDelete({{ $slab->id }})"
                                        class="text-red-600 hover:text-red-800 transition-colors"
                                        title="Delete Slab">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-gray-400 text-sm">
                                No tax slabs configured yet. Click <strong>New Slab</strong> to get started.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{-- Pagination --}}
            @if($taxSlabList->hasPages())
                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
                    {{ $taxSlabList->links() }}
                </div>
            @endif
        </div>

        {{-- ── Add / Edit Modal ── --}}
        @if($showModal)
            <div class="absolute inset-0 z-50 flex items-center justify-center bg-black/50"
                wire:keydown.escape="closeModal">
                <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg mx-4 overflow-hidden">

                    {{-- Modal Header --}}
                    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                        <h2 class="text-base font-semibold text-gray-800">
                            {{ $editingId ? 'Edit Tax Slab' : 'New Tax Slab' }}
                        </h2>
                        <button type="button" wire:click="closeModal"
                            class="text-gray-400 hover:text-gray-600 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    {{-- Modal Body --}}
                    <div class="px-6 py-5 space-y-4">

                        {{-- Relief Toggle --}}
                        <div class="flex items-center gap-3 p-3 rounded-lg bg-green-50 border border-green-200">
                            <input type="checkbox" id="is_relief" wire:model.live="is_relief"
                                class="w-4 h-4 rounded text-green-600 border-gray-300 focus:ring-green-500 cursor-pointer">
                            <label for="is_relief" class="text-sm font-medium text-green-800 cursor-pointer select-none">
                                This is a <strong>Relief from Tax</strong> slab (no tax applies)
                            </label>
                        </div>

                        {{-- Min / Max Amount — dynamic currency symbol labels --}}
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Minimum Amount ({{ $currencySymbol }})
                                </label>
                                <input type="number" wire:model="min_amount" min="0" step="1"
                                    placeholder="e.g. 150000"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <p class="text-xs text-gray-400 mt-0.5">Leave blank if slab starts from 0</p>
                                @error('min_amount') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Maximum Amount ({{ $currencySymbol }})
                                </label>
                                <input type="number" wire:model="max_amount" min="0" step="1"
                                    placeholder="e.g. 233333"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <p class="text-xs text-gray-400 mt-0.5">Leave blank for the top slab (no limit)</p>
                                @error('max_amount') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        {{-- Tax % — hidden for relief slab --}}
                        @if(!$is_relief)
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Tax Percentage (%) <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <input type="number" wire:model="tax_percentage" min="0" max="100" step="0.01"
                                            placeholder="e.g. 6"
                                            class="w-full border border-gray-300 rounded-lg px-3 py-2 pr-8 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm font-medium">%</span>
                                    </div>
                                    @error('tax_percentage') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            {{-- Live Preview --}}
                            @if($tax_percentage)
                                <div class="p-3 rounded-lg bg-blue-50 border border-blue-200 text-xs text-blue-800">
                                    <span class="font-semibold">Formula Preview: </span>
                                    {{ $tax_percentage }}% of monthly regular profits from employment
                                </div>
                            @endif
                        @else
                            <div class="p-3 rounded-lg bg-green-50 border border-green-200 text-xs text-green-800">
                                <span class="font-semibold">Tax: </span>Relief from Tax — no tax applicable for this income band.
                            </div>
                        @endif

                        {{-- Description --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description
                                <span class="text-gray-400 font-normal">(optional)</span>
                            </label>
                            <textarea wire:model="description" rows="2"
                                placeholder="Additional notes about this slab..."
                                maxlength="500"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"></textarea>
                            @error('description') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- Modal Footer --}}
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

        {{-- ── Delete Confirm Modal ── --}}
        @if($confirmDeleteId)
            <div class="absolute inset-0 z-50 flex items-center justify-center bg-black/50">
                <div class="bg-white rounded-xl shadow-2xl max-w-lg mx-4 p-6">
                    <div class="flex items-start gap-4 mb-5">
                        <span class="flex items-center justify-center w-10 h-10 rounded-full bg-red-100 shrink-0">
                            <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </span>
                        <div>
                            <h3 class="text-base font-semibold text-gray-800 mb-1">Delete Tax Slab?</h3>
                            <p class="text-sm text-gray-500">
                                This slab will be permanently removed and the remaining slabs will be re-sequenced. This action cannot be undone.
                            </p>
                        </div>
                    </div>
                    <div class="flex gap-3 justify-end">
                        <button type="button" wire:click="cancelDelete"
                            class="border border-gray-300 text-gray-700 text-sm px-4 py-2 rounded-lg hover:bg-gray-100 transition-colors">
                            Cancel
                        </button>
                        <button type="button" wire:click="delete" wire:loading.attr="disabled"
                            class="bg-red-600 hover:bg-red-700 text-white text-sm px-4 py-2 rounded-lg transition-colors flex items-center gap-2">
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
