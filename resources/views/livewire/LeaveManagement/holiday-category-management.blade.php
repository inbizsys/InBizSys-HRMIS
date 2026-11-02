<div class="p-6 bg-gray-50 min-h-screen">
    {{-- Header Content --}}
    <div class="max-w-8xl mx-auto mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        {{-- Title and Search Bar side by side --}}
        <div class="flex flex-col lg:flex-row lg:items-center gap-4 flex-1">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Holiday Categories</h1>
                <p class="text-sm text-gray-500 mt-0.5">Manage holiday types, color codes, and print legends.</p>
            </div>

            {{-- Search Bar name eka lagatama --}}
            <div class="relative w-full sm:w-72">
                <input type="text" wire:model.live="search" placeholder="Search categories..."
                    class="w-full pl-10 pr-4 py-2 bg-white border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none transition-all text-gray-800 shadow-sm">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>
        </div>

        {{-- Add New Category Button on the Right --}}
        <div>
            <button wire:click="openCreateModal"
                class="inline-flex items-center justify-center bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2.5 rounded-xl shadow-sm text-sm transition-all whitespace-nowrap">
                <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add New Category
            </button>
        </div>
    </div>

    {{-- Session Flash Alert --}}
    @if (session()->has('message'))
        <div
            class="max-w-7xl mx-auto mb-4 p-4 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm font-medium">
            {{ session('message') }}
        </div>
    @endif

    {{-- Main Table Box --}}
    <div class="max-w-8xl mx-auto bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

        {{-- Table --}}
        <div class="overflow-x-auto bg-white">
            <table class="w-full text-left border-collapse bg-white">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wide">Category Name</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wide">Color Badge</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wide">Description</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wide text-center">
                            Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 font-medium text-sm text-gray-700 bg-white">
                    @forelse($categories as $cat)
                        <tr wire:key="cat-{{ $cat->id }}" class="hover:bg-gray-50/70 transition-colors">
                            <td class="px-6 py-4 text-gray-900 font-semibold">{{ $cat->name }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-block w-6 h-6 rounded-full border border-gray-200 shadow-sm"
                                    style="background-color: {{ $cat->color_code }};" title="{{ $cat->color_code }}">
                                </span>
                            </td>
                            <td class="px-6 py-4 text-gray-500 text-xs">{{ $cat->description ?: '-' }}</td>
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                <div class="inline-flex items-center space-x-1">
                                    {{-- EDIT BUTTON --}}
                                    <button type="button" wire:click="openEditModal({{ $cat->id }})"
                                        title="Edit Category"
                                        class="px-2 py-1 inline-flex text-xs font-semibold rounded-lg bg-blue-100 text-blue-800 hover:bg-blue-200">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                        </svg>
                                    </button>

                                    <span class="text-gray-200 select-none">|</span>

                                    {{-- DELETE BUTTON --}}
                                    <button type="button"
                                        wire:click="confirmDelete({{ $cat->id }}, '{{ addslashes($cat->name) }}')"
                                        title="Delete"
                                        class="px-2 py-1 inline-flex text-xs font-semibold rounded-lg bg-red-100 text-red-800 hover:bg-red-200">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-10 text-center text-gray-400 italic bg-white">No holiday
                                categories found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 bg-gray-50 border-t border-gray-100">{{ $categories->links() }}</div>
    </div>

    {{-- CREATE / EDIT MODAL --}}
    @if ($showModal)
        <div wire:key="category-modal-wrapper"
            class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-gray-600/50 backdrop-blur-sm" wire:click="resetForm"></div>

            <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-lg p-6 border border-gray-100">
                <div class="flex items-center justify-between border-b border-gray-100 pb-4 mb-4">
                    <h3 class="text-lg font-bold text-gray-900">
                        {{ $isEditMode ? 'Edit Holiday Category' : 'Add Holiday Category' }}
                    </h3>
                    <button wire:click="resetForm" type="button" class="text-gray-400 hover:text-gray-500">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form wire:submit.prevent="saveCategory" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide mb-1">
                            Category Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" wire:model="name" placeholder="e.g., Poya Day, Public Holiday"
                            class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none text-gray-800">
                        @error('name')
                            <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide mb-1">
                            Color Code / Badge <span class="text-red-500">*</span>
                        </label>
                        <div class="flex items-center gap-3">
                            <input type="color" wire:model="color_code"
                                class="w-12 h-10 rounded-xl border border-gray-300 p-1 cursor-pointer bg-white">
                            <input type="text" wire:model="color_code" placeholder="#e11d48"
                                class="flex-1 border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none text-gray-800 uppercase">
                        </div>
                        @error('color_code')
                            <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label
                            class="block text-xs font-bold text-gray-600 uppercase tracking-wide mb-1">Description</label>
                        <textarea wire:model="description" rows="3" placeholder="Optional description..."
                            class="w-full border border-gray-300 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none text-gray-800"></textarea>
                        @error('description')
                            <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-100">
                        <button type="button" wire:click="resetForm"
                            class="px-4 py-2.5 rounded-xl border border-gray-200 text-gray-700 font-semibold text-sm hover:bg-gray-50">Cancel</button>
                        <button type="submit"
                            class="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm shadow-sm">
                            {{ $isEditMode ? 'Update Category' : 'Save Category' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- DELETE CONFIRMATION MODAL --}}
    @if ($showDeleteModal)
        <div wire:key="delete-modal-wrapper"
            class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-gray-600/50 backdrop-blur-sm" wire:click="$set('showDeleteModal', false)">
            </div>
            <div
                class="relative bg-white rounded-2xl shadow-xl w-full max-w-md p-6 text-center border border-gray-100">
                <div
                    class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 text-red-600 mb-4">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Delete Holiday Category</h3>
                <p class="text-sm text-gray-500 mb-6">Are you sure you want to delete category <span
                        class="font-semibold text-gray-900">"{{ $categoryNameToDelete }}"</span>?</p>
                <div class="flex items-center justify-center space-x-3">
                    <button type="button" wire:click="$set('showDeleteModal', false)"
                        class="px-4 py-2.5 rounded-xl border border-gray-200 text-gray-700 font-semibold text-sm hover:bg-gray-50">Cancel</button>
                    <button type="button" wire:click="deleteCategory"
                        class="px-4 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white font-semibold text-sm">Yes,
                        Delete It</button>
                </div>
            </div>
        </div>
    @endif
</div>
