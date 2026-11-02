<div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 mt-6">

    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-xl font-semibold text-gray-800">Leave Type Configurations</h2>
            <p class="text-sm text-gray-500">Manage organizational leave types and administrative approval policies.</p>
        </div>

        @can('create leave type')
        <button wire:click="openCreateModal" class="flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium shadow-sm transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Add Leave Type
        </button>
        @endcan
    </div>

    @if (session()->has('operationSuccessFeedback'))
    <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg text-sm flex items-center gap-2">
        <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        {{ session('operationSuccessFeedback') }}
    </div>
    @endif

    @if (session()->has('operationFailureFeedback'))
    <div class="mb-6 p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-lg text-sm flex items-center gap-2">
        <svg class="w-5 h-5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
        {{ session('operationFailureFeedback') }}
    </div>
    @endif

    <div class="overflow-x-auto rounded-xl border border-gray-100 shadow-sm bg-white">
        <table class="w-full text-left border-collapse text-sm">
            <thead class="bg-gray-50 text-gray-600 font-medium border-b border-gray-100">
                <tr>
                    <th class="p-4">Leave Type Name</th>
                    <th class="p-4 text-center">Max Days</th>
                    <th class="p-4 text-center">Approvals</th>
                    <th class="p-4 text-center">Escalation Policy</th>
                    <th class="p-4 text-center">Attachment</th>
                    <th class="p-4 text-center">Status</th>
                    <th class="p-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 text-gray-600">
                @forelse($leaveTypesCollection as $leaveTypeItem)
                <tr class="hover:bg-gray-50/70 transition-colors">
                    <td class="p-4 font-medium text-gray-900">{{ $leaveTypeItem->name }}</td>
                    <td class="p-4 text-center font-semibold text-gray-700">{{ $leaveTypeItem->max_days_per_year }} Days</td>
                    <td class="p-4 text-center">
                        @if($leaveTypeItem->required_approval_levels === 2)
                        <span class="px-2.5 py-1 bg-purple-50 text-purple-700 rounded-full font-medium text-xs border border-purple-100">Dual Level</span>
                        @else
                        <span class="px-2.5 py-1 bg-blue-50 text-blue-700 rounded-full font-medium text-xs border border-blue-100">Single Level</span>
                        @endif
                    </td>
                    <td class="p-4 text-center text-xs text-gray-500">
                        @if($leaveTypeItem->auto_escalate_above_days)
                        <span class="text-gray-700 font-medium">Standard approval up to {{ $leaveTypeItem->auto_escalate_above_days }} consecutive days</span>
                        <span class="block text-[10px] text-amber-600 mt-0.5">Escalates if a single request exceeds this limit</span>
                        @else
                        <span class="text-gray-400">No escalation limit</span>
                        @endif
                    </td>
                    <td class="p-4 text-center">
                        @if($leaveTypeItem->requires_attachment)
                        <span class="text-amber-600 font-medium text-xs">Required</span>
                        @else
                        <span class="text-gray-400 text-xs">Optional</span>
                        @endif
                    </td>
                    <td class="p-4 text-center">
                        @if($leaveTypeItem->is_active)
                        <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 rounded-full font-medium text-xs border border-emerald-100 inline-flex items-center gap-1">
                            <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full"></span> Active
                        </span>
                        @else
                        <span class="px-2.5 py-1 bg-gray-100 text-gray-600 rounded-full font-medium text-xs border border-gray-200 inline-flex items-center gap-1">
                            <span class="w-1.5 h-1.5 bg-gray-400 rounded-full"></span> Disabled
                        </span>
                        @endif
                    </td>
                    <td class="p-4 text-right space-x-1 whitespace-nowrap">

                        {{-- Edit Trigger Component Bindings --}}
                        @can('edit leave type')
                        <button wire:click="openEditModal({{ $leaveTypeItem->id }})"
                            title="Edit"
                            class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-lg bg-indigo-100 text-indigo-800 hover:bg-indigo-200">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                            </svg>
                        </button>
                        @endcan

                        <span class="text-gray-200 select-none">|</span>

                        {{-- Delete Confirmation Trigger Component Hook Setup --}}
                        @can('delete leave type')
                        <button wire:click="openDeleteConfirmationModal({{ $leaveTypeItem->id }})"
                            title="Delete"
                            class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-lg bg-red-100 text-red-800 hover:bg-red-200">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                            </svg>
                        </button>
                        @endcan

                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="p-8 text-center text-gray-400">No active leave type policies defined.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
            {{ $leaveTypesCollection->links() }}
        </div>
    </div>

    @if($isFormModalVisible)
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-45"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>


            <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-gray-100">
                <div class="bg-white px-6 pt-6 pb-4 border-b border-gray-50">
                    <div class="flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-900">{{ $modalOperationTitle }}</h3>
                        <button type="button" wire:click="closeActiveModals" class="text-gray-400 hover:text-gray-500">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <form wire:submit.prevent="saveLeaveTypeConfiguration">
                    <div class="bg-white px-6 py-4 space-y-4">

                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Leave Type Name</label>
                            <input type="text" wire:model="leave_type_name" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            @error('leave_type_name') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Max Allocation (Days)</label>
                            <input type="number" wire:model="maximum_days_per_year" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            @error('maximum_days_per_year') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div class="relative w-full">
                            <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Required Approval</label>
                            <button type="button" wire:click="$toggle('isApprovalDropdownOpen')"
                                class="w-full text-left border border-gray-300 rounded-lg p-2 text-sm bg-white text-gray-800 focus:outline-none focus:ring-1 focus:ring-indigo-500 flex justify-between items-center transition-shadow hover:border-gray-400 font-medium shadow-sm">
                                <span class="flex items-center gap-2">
                                    <span class="text-gray-800">
                                        @if($required_approval_levels_count == 2)
                                        2 Levels (Requires Supervisor & HR Approvals)
                                        @else
                                        1 Level (Supervisor Approval Only)
                                        @endif
                                    </span>
                                </span>
                                <svg class="h-4 w-4 text-gray-400 transition-transform duration-200 {{ $isApprovalDropdownOpen ? 'rotate-180' : '' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            @if($isApprovalDropdownOpen)
                            <div wire:click="$set('isApprovalDropdownOpen', false)" class="fixed inset-0 z-30 cursor-default"></div>
                            <div class="absolute z-40 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg font-medium py-1">
                             
                                <div wire:click="setApprovalLevel(1)" class="cursor-pointer px-4 py-2 text-sm hover:bg-indigo-50 text-gray-700 transition-colors {{ $required_approval_levels_count == 1 ? 'bg-indigo-50 text-indigo-600 font-semibold' : '' }}">
                                    1 Level (Supervisor Approval Only)
                                </div>
                                <div wire:click="setApprovalLevel(2)" class="cursor-pointer px-4 py-2 text-sm hover:bg-indigo-50 text-gray-700 transition-colors {{ $required_approval_levels_count == 2 ? 'bg-indigo-50 text-indigo-600 font-semibold' : '' }}">
                                    2 Levels (Requires Supervisor & HR Approvals)
                                </div>
                            </div>
                            @endif
                            @error('required_approval_levels_count') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        @if($required_approval_levels_count == 1)
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Auto-Escalate Above (Days)</label>
                            <input type="number" placeholder="Optional" wire:model="auto_escalate_above_days" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            @error('auto_escalate_above_days') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        @endif

                        <div class="relative w-full">
                            <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Availability</label>
                            <button type="button" wire:click="$toggle('isAvailabilityDropdownOpen')"
                                class="w-full text-left border border-gray-300 rounded-lg p-2 text-sm bg-white text-gray-800 focus:outline-none focus:ring-1 focus:ring-indigo-500 flex justify-between items-center transition-shadow hover:border-gray-400 font-medium shadow-sm">
                                <span class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full {{ $is_active == 1 ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                                    <span class="text-gray-800">
                                        {{ $is_active == 1 ? 'Active' : 'Disabled' }}
                                    </span>
                                </span>
                                <svg class="h-4 w-4 text-gray-400 transition-transform duration-200 {{ $isAvailabilityDropdownOpen ? 'rotate-180' : '' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            @if($isAvailabilityDropdownOpen)
                            <div wire:click="$set('isAvailabilityDropdownOpen', false)" class="fixed inset-0 z-30 cursor-default"></div>

                            <div class="absolute z-40 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg font-medium py-1">
                                <div wire:click="setAvailability(1)" class="cursor-pointer px-4 py-2 text-sm hover:bg-indigo-50 text-gray-700 transition-colors flex items-center gap-2 {{ $is_active == 1 ? 'bg-indigo-50 text-indigo-600 font-semibold' : '' }}">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Active
                                </div>
                                <div wire:click="setAvailability(0)" class="cursor-pointer px-4 py-2 text-sm hover:bg-indigo-50 text-gray-700 transition-colors flex items-center gap-2 {{ $is_active == 0 ? 'bg-indigo-50 text-indigo-600 font-semibold' : '' }}">
                                    <span class="w-2 h-2 rounded-full bg-rose-500"></span> Disabled
                                </div>
                            </div>
                            @endif
                        </div>

                        <div class="bg-gray-50 px-6 py-3 flex justify-end gap-2 border-t border-gray-100">
                            <button type="button" wire:click="closeActiveModals" class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
                            <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium shadow-sm">
                                {{ $modalActionFormButtonLabel }}
                            </button>
                        </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    @if($isDeleteConfModalVisible)
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-45"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-gray-100">
                <div class="bg-white p-6">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-rose-50 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg font-semibold text-gray-900 border-none pb-0">Confirm Policy Specification Deletion</h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    Are you absolutely certain you want to purge the configuration rule for <span class="font-semibold text-gray-900">"{{ $targetLeaveTypeDeletionDescription }}"</span>? This transactional action cannot be undone.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-6 py-3 flex justify-end gap-2 border-t border-gray-100">
                    <button type="button" wire:click="closeActiveModals" class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button type="button" wire:click="executePolicyPurgeAction" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-sm font-medium shadow-sm">Confirm Delete</button>
                </div>
            </div>
        </div>
    </div>
    @endif

</div>