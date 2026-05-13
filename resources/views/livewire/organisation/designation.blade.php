<div class="h-full bg-gray-50">
    <div class="w-full py-6 px-6">

        {{-- Header --}}
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-xl font-semibold text-gray-800">Designations</h1>
            <button type="button" wire:click="openModal"
                class="inline-flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                New Designation
            </button>
        </div>

        {{-- Table --}}
        {{-- Table --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
            <table class="w-full text-sm text-left">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50">
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            Designation Name</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            Total Employees</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($designationList as $designation)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">
                                <button type="button" wire:click="edit({{ $designation->id }})"
                                    class="text-blue-600 hover:text-blue-700 font-medium">
                                    {{ $designation->name }}
                                </button>
                            </td>
                            <td class="px-6 py-4 text-gray-600">0</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-4">
                                    <button type="button" wire:click="edit({{ $designation->id }})"
                                        class="text-blue-600 hover:text-blue-800 transition-colors"
                                        title="Edit Designation">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>

                                    <button type="button" wire:click="confirmDelete({{ $designation->id }})"
                                        class="text-red-600 hover:text-red-800 transition-colors"
                                        title="Delete Designation">
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
                            <td colspan="3" class="px-6 py-12 text-center text-gray-400 text-sm">
                                No designations added yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{-- Pagination Links render kirima --}}
            @if ($designationList->hasPages())
                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
                    {{ $designationList->links() }}
                </div>
            @endif
        </div>

        {{-- Add / Edit Modal --}}
        @if ($showModal)
            <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 "
                wire:keydown.escape="closeModal">
                <div class="bg-white rounded-xl shadow-2xl w-full max-w-md mx-4 overflow-hidden">

                    {{-- Modal Header --}}
                    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                        <h2 class="text-base font-semibold text-gray-800">
                            {{ $editingId ? 'Edit Designation' : 'New Designation' }}
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
                    <div class="px-6 py-5">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Designation Name <span class="text-red-600">*</span>
                        </label>
                        <input type="text" wire:model="name"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            autofocus>
                        @error('name')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="pt-2"></div>

                    {{-- Modal Footer --}}
                    <div class="flex items-center justify-between px-6 py-4 border-t border-gray-200 bg-gray-50">
                        <div class="flex items-center gap-3">
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

        {{-- Delete Confirm Modal --}}
        @if ($confirmDeleteId)
            <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
                <div class="bg-white rounded-xl shadow-2xl  max-w-sm mx-4 p-6">
                    <h3 class="text-base font-semibold text-gray-800 mb-2">Delete Designation?</h3>
                    <p class="text-sm text-gray-500 mb-5">This action cannot be undone. Are you sure?</p>
                    <div class="flex gap-3 justify-end">
                        <button type="button" wire:click="cancelDelete"
                            class="border border-gray-300 text-gray-700 text-sm px-4 py-2 rounded-lg hover:bg-gray-100 transition-colors">
                            Cancel
                        </button>
                        <button type="button" wire:click="delete"
                            class="bg-red-600 hover:bg-red-700 text-white text-sm px-4 py-2 rounded-lg transition-colors">
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        @endif

    </div>
</div>
