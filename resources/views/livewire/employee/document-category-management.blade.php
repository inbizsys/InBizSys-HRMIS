<div class="p-6 bg-gray-50 min-h-screen">
    {{-- Header Content --}}
    <div class="max-w-7xl mx-auto mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Document Categories</h1>
            <p class="text-sm text-gray-500 mt-1">Manage categories for employee document uploads (e.g., CV, NIC, Certificates).</p>
        </div>

        @can('create document category')
        <button wire:click="openAddModal" class="inline-flex items-center justify-center bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2.5 rounded-xl shadow-sm text-sm transition-colors duration-200">
            <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Add New Category
        </button>
        @endcan
    </div>

    {{-- Alert Messages --}}
    @if (session()->has('message'))
    <div class="max-w-7xl mx-auto mb-4 p-4 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm font-medium flex items-center">
        <svg class="w-5 h-5 mr-2 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        {{ session('message') }}
    </div>
    @endif

    {{-- Main Table Area --}}
    <div class="max-w-7xl mx-auto bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

        {{-- Search Bar --}}
        <div class="p-5 border-b border-gray-100 bg-white">
            <div class="max-w-md relative">
                <input type="text" wire:model.live="search" placeholder="Search categories by name or description..." class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:outline-none transition-all placeholder-gray-400">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>
        </div>

        {{-- Table Content --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wide">Category Name</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wide">Description</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wide text-center">Status</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-500 uppercase tracking-wide text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 font-medium text-sm text-gray-700">
                    @forelse($categories as $category)
                    <tr class="hover:bg-gray-50/70 transition-colors">
                        <td class="px-6 py-4 font-semibold text-gray-900">{{ $category->name }}</td>
                        <td class="px-6 py-4 text-gray-500 max-w-xs truncate">{{ $category->description ?? '-' }}</td>
                        <td class="px-6 py-4 text-center">
                            @if($category->is_required)
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-red-50 text-red-600">Mandatory</span>
                            @else
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-600">Optional</span>
                            @endif
                        </td>
                        {{-- Table Actions --}}
                        <td class="px-6 py-4 text-center whitespace-nowrap">
                            <div class="inline-flex items-center space-x-1">
                                {{-- Edit Button --}}
                                @can('edit document category')
                                <button type="button" wire:click="editCategory({{ $category->id }})"
                                    title="Edit"
                                    class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-lg bg-indigo-100 text-indigo-800 hover:bg-indigo-200">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                    </svg>
                                </button>
                                @endcan

                                <span class="text-gray-200 select-none">|</span>

                                {{-- Delete Button --}}
                                @can('delete document category')
                                <button type="button" wire:click="confirmDelete({{ $category->id }}, '{{ addslashes($category->name) }}')"
                                    title="Delete"
                                    class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-lg bg-red-100 text-red-800 hover:bg-red-200">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                    </svg>
                                </button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-10 text-center text-gray-400 italic">No document categories found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="p-4 border-t border-gray-50 bg-gray-50/50">
            {{ $categories->links() }}
        </div>
    </div>

    {{-- =================================--}}
    {{-- 1. SINGLE REUSABLE MODAL ------- --}}
    {{-- =================================--}}
    @if($showModal)
    <div class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 sm:p-6">
        <div class="fixed inset-0 bg-gray-600/50 backdrop-blur-sm transition-opacity" wire:click="closeModal"></div>

        <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md p-6 overflow-hidden border border-gray-100">
            <div class="flex items-center justify-between border-b border-gray-100 pb-4 mb-4">

                <h3 class="text-lg font-bold text-gray-900">
                    {{ $isEditMode ? 'Edit Document Category' : 'Add Document Category' }}
                </h3>
                <button wire:click="closeModal" class="text-gray-400 hover:text-gray-500 transition-colors">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>


            <form wire:submit.prevent="{{ $isEditMode ? 'updateCategory' : 'saveCategory' }}" class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Category Name *</label>
                    <input type="text" wire:model="name" placeholder="e.g., CV / NIC / Educational Certificate" class="w-full border border-gray-200 bg-white rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:outline-none transition-all placeholder-gray-400">
                    @error('name') <span class="text-xs text-red-500 mt-1 block font-medium">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Description</label>
                    <textarea wire:model="description" rows="3" placeholder="Brief details about what files belong here..." class="w-full border border-gray-200 bg-white rounded-xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent focus:outline-none transition-all placeholder-gray-400 resize-none"></textarea>
                    @error('description') <span class="text-xs text-red-500 mt-1 block font-medium">{{ $message }}</span> @enderror
                </div>

                <div class="flex items-center p-1">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" wire:model="is_required" class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        <span class="ml-3 text-sm font-semibold text-gray-700">Is this document Mandatory?</span>
                    </label>
                </div>

                <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-100 mt-6">
                    <button type="button" wire:click="closeModal" class="px-4 py-2.5 rounded-xl border border-gray-200 text-gray-700 font-semibold text-sm hover:bg-gray-50 transition-colors">Cancel</button>

                    @if($isEditMode)
                    <button type="submit" class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm shadow-sm transition-colors">Update Category</button>
                    @else
                    <button type="submit" class="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm shadow-sm transition-colors">Save Category</button>
                    @endif
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- =================================== --}}
    {{-- 2. DELETE CONFIRMATION MODAL        --}}
    {{-- =================================== --}}
    @if($showDeleteModal)
    <div class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 sm:p-6">
        <div class="fixed inset-0 bg-gray-600/50 backdrop-blur-sm transition-opacity" wire:click="$set('showDeleteModal', false)"></div>

        <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md p-6 overflow-hidden border border-gray-100 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 text-red-600 mb-4">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>

            <h3 class="text-lg font-bold text-gray-900 mb-2">Delete Category</h3>

            {{-- Conditional Description based on usage --}}
            @if($isCategoryUsed)
            <p class="text-sm text-red-600 mb-6 font-medium bg-red-50 p-3 rounded-xl border border-red-100">
                Cannot delete <span class="font-bold">"{{ $categoryNameToDelete }}"</span> because it is currently being used in Employee Documents.
            </p>
            @else
            <p class="text-sm text-gray-500 mb-6">
                Are you sure you want to delete <span class="font-semibold text-gray-900">"{{ $categoryNameToDelete }}"</span>? This action cannot be undone.
            </p>
            @endif

            <div class="flex items-center justify-center space-x-3">
                <button type="button" wire:click="$set('showDeleteModal', false)"
                    class="px-4 py-2.5 rounded-xl border border-gray-200 text-gray-700 font-semibold text-sm hover:bg-gray-50 transition-colors">
                    {{ $isCategoryUsed ? 'Close' : 'No, Cancel' }}
                </button>

                {{-- CHANGED: Only show the delete button if the category is NOT used. If used, no delete button will be rendered at all. --}}
                @if(!$isCategoryUsed)
                <button type="button" wire:click="deleteCategory"
                    class="px-4 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white font-semibold text-sm shadow-sm transition-colors">
                    Yes, Delete It
                </button>
                @endif
            </div>
        </div>
    </div>
    @endif
</div>