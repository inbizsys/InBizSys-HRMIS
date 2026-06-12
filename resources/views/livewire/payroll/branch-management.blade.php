<div class="p-6 bg-white rounded-lg shadow">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h2 class="text-lg font-bold text-gray-700">Branch Management</h2>
        </div>

        <button wire:click="openModal" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-blue-700 transition flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Add Branch
        </button>
    </div>

    <div class="mb-4">
        <div class="relative max-w-md">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </span>
            <input wire:model.live="search" type="text" placeholder="Search branch name, code or bank..."
                class="w-full pl-9 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-1 focus:ring-blue-500 focus:outline-none">
        </div>
    </div>

    @if (session()->has('message'))
    <div class="p-3 mb-4 text-sm text-green-700 bg-green-100 rounded-lg">
        {{ session('message') }}
    </div>
    @endif

    @if (session()->has('error'))
    <div class="p-3 mb-4 text-sm text-red-700 bg-red-100 rounded-lg">
        {{ session('error') }}
    </div>
    @endif

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-100 text-xs font-semibold uppercase text-gray-600 border-b border-gray-200">
                    <th class="p-3">ID</th>
                    <th class="p-3">Bank Name</th>
                    <th class="p-3">Branch Code</th>
                    <th class="p-3">Branch Name</th>
                    <th class="p-3">Status</th>
                    <th class="p-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="text-sm">
                @forelse($branchList as $branch)
                <tr class="border-b border-gray-100 hover:bg-gray-50">
                    <td class="p-3 text-gray-500">{{ $branch->id }}</td>
                    <td class="p-3 font-medium text-gray-700">{{ $branch->bank?->bank_name ?? 'N/A' }}</td>
                    <td class="p-3 font-semibold text-gray-700">{{ $branch->branch_code }}</td>
                    <td class="p-3 text-gray-700">{{ $branch->branch_name }}</td>
                    <td class="p-3">
                        <span class="px-2 py-1 text-xs rounded-full font-medium {{ $branch->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $branch->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="p-3 text-right space-x-2">
                        <button wire:click="edit({{ $branch->id }})" class="text-blue-600 hover:text-blue-900 transition p-1 inline-flex items-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                            </svg>
                        </button>

                        <button wire:click="confirmDelete({{ $branch->id }})" class="text-red-600 hover:text-red-900 transition p-1 inline-flex items-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14.74 9l-.34 9m-4.72 0L9 9m11.4 5.89c.13.746-.365 1.43-1.114 1.43H5.43c-.749 0-1.244-.684-1.114-1.43l1.1-7.14c.13-.746.8-1.31 1.554-1.31h8.05c.754 0 1.424.564 1.554 1.31l1.1 7.14zM9 3.5V2c0-.552.448-1 1-1h4c.552 0 1 .448 1 1v1.5M4.5 6.75h15" />
                            </svg>
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="p-4 text-center text-gray-400">No branches found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $branchList->links() }}</div>

    @if($showModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeModal"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form wire:submit.prevent="save">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-md font-bold text-gray-900 mb-4">
                            {{ $editingId ? 'Edit Branch Details' : 'Add New Branch' }}
                        </h3>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 uppercase">Select Bank</label>
                                
                                @if($showDropdown)
                                    <div class="fixed inset-0 z-30" wire:click="closeDropdown"></div>
                                @endif

                                <div class="relative mt-1 z-40">
                                    <div class="relative flex items-center">
                                        <input wire:model.live="bank_search" 
                                               wire:focus="$set('showDropdown', true)"
                                               type="text" 
                                               placeholder="Type to search and select bank..."
                                               class="w-full border border-gray-300 bg-white rounded-lg p-2 pr-8 text-sm focus:ring-1 focus:ring-blue-500 focus:outline-none text-gray-900 font-medium placeholder-gray-400">
                                        
                                        <div class="absolute right-2 pointer-events-none text-gray-400">
                                            <svg class="w-4 h-4 transition-transform {{ $showDropdown ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </div>
                                    </div>

                                    @if($showDropdown)
                                    <div class="absolute left-0 right-0 z-50 mt-1 bg-white border border-gray-200 rounded-lg shadow-lg">
                                        <ul class="max-h-52 overflow-y-auto text-sm text-gray-700 divide-y divide-gray-50">
                                            @forelse($banks as $bank)
                                                <li>
                                                    <button type="button" wire:click="selectBank({{ $bank->id }}, '{{ addslashes($bank->bank_name) }}')"
                                                            class="w-full text-left px-3 py-2.5 hover:bg-blue-50 hover:text-blue-600 transition flex items-center justify-between">
                                                        <span class="{{ $bank_id == $bank->id ? 'font-semibold text-blue-600' : '' }}">
                                                            {{ $bank->bank_name }}
                                                        </span>
                                                        @if($bank_id == $bank->id)
                                                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                            </svg>
                                                        @endif
                                                    </button>
                                                </li>
                                            @empty
                                                <li class="px-3 py-3 text-center text-gray-400 text-xs">No banks found.</li>
                                            @endforelse
                                        </ul>
                                    </div>
                                    @endif
                                </div>

                                <input type="hidden" wire:model="bank_id">
                                @error('bank_id') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-600 uppercase">Branch Code</label>
                                <input type="text" wire:model="branch_code" class="mt-1 w-full border border-gray-300 rounded-lg p-2 text-sm focus:ring-1 focus:ring-blue-500 focus:outline-none">
                                @error('branch_code') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-600 uppercase">Branch Name</label>
                                <input type="text" wire:model="branch_name" class="mt-1 w-full border border-gray-300 rounded-lg p-2 text-sm focus:ring-1 focus:ring-blue-500 focus:outline-none">
                                @error('branch_name') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-600 uppercase">Status</label>
                                <select wire:model="is_active" class="mt-1 w-full border border-gray-300 bg-white rounded-lg p-2 text-sm focus:ring-1 focus:ring-blue-500 focus:outline-none">
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                                @error('is_active') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                        <button type="submit" class="w-full sm:w-auto bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-blue-700 transition">
                            {{ $editingId ? 'Update Branch' : 'Save Branch' }}
                        </button>
                        <button type="button" wire:click="closeModal" class="mt-3 sm:mt-0 w-full sm:w-auto bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm font-semibold hover:bg-gray-50 transition">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    @if($confirmDeleteId)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="cancelDelete"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full {{ $isBranchUsed ? 'bg-amber-100' : 'bg-red-100' }} sm:mx-0 sm:h-10 sm:w-10">
                            @if($isBranchUsed)
                            <svg class="h-6 w-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m0-8v4m5.243-4.243a8 8 0 11-11.314 0 8 8 0 0111.314 0z" />
                            </svg>
                            @else
                            <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            @endif
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-md font-bold text-gray-900">
                                {{ $isBranchUsed ? 'Action Restricted' : 'Delete Branch' }}
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    @if($isBranchUsed)
                                    This branch cannot be deleted because this specific bank and branch combination is currently assigned to one or more employee profiles.
                                    @else
                                    Are you sure you want to delete this branch? This action cannot be undone.
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="bg-gray-50 px-4 py-3 sm:px-6 flex flex-col sm:flex-row sm:justify-end gap-2">
                    @if(!$isBranchUsed)
                    <button type="button" wire:click="delete" class="w-full sm:w-auto bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-red-700 transition order-1 sm:order-2">
                        Yes, Delete
                    </button>
                    @endif

                    <button type="button" wire:click="cancelDelete" class="w-full sm:w-auto bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm font-semibold hover:bg-gray-50 transition order-2 sm:order-1">
                        {{ $isBranchUsed ? 'Close' : 'Cancel' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>