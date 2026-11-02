<div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 mt-6">

    <div class="mb-6">
        <h2 class="text-xl font-semibold text-gray-800">HR / Admin: Manage Leave Balances</h2>
        <p class="text-sm text-gray-500">View, search, or assign corporate leave matrix allocations efficiently.</p>
    </div>

    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white p-4 rounded-xl border border-gray-100 shadow-sm mb-6">
        <div class="w-full sm:w-auto flex-1 max-w-md relative" wire:click.away="$set('isMainDropdownOpen', false)">
            <input wire:model.live="searchQuery"
                wire:focus="$set('isMainDropdownOpen', true)"
                type="text"
                placeholder="Search by Employee Name or Leave Type..."
                class="rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 w-full">

            @if($isMainDropdownOpen && count($leaveBalancesCollection) > 0)
            <div class="absolute left-0 right-0 top-full mt-1.5 bg-white border border-slate-200 rounded-xl shadow-2xl z-50 divide-y divide-slate-50 max-h-40 overflow-y-auto">
                @foreach($leaveBalancesCollection as $leaveBalanceItem)
                <button type="button" wire:click="selectFromMainSearch('{{ addslashes($leaveBalanceItem->employee->user->name ?? '') }}')"
                    class="w-full text-left px-4 py-2.5 hover:bg-indigo-50 transition-colors cursor-pointer flex flex-col">
                    <span class="text-xs font-bold text-slate-700">{{ $leaveBalanceItem->employee->user->name ?? 'N/A' }}</span>
                    <span class="text-[10px] text-indigo-600 mt-0.5">{{ $leaveBalanceItem->leaveType->name ?? 'N/A' }} ({{ $leaveBalanceItem->year }})</span>
                </button>
                @endforeach
            </div>
            @endif
        </div>
        <div class="flex items-center gap-3 w-full sm:w-auto justify-end">
            @can('create leave balance')
            <button wire:click="openBulkModal" class="flex items-center justify-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition-colors shadow-sm whitespace-nowrap">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                Mass Allocate Leaves
            </button>
            @endcan
            @can('create leave balance')
            <button wire:click="openIndividualModal" class="flex items-center justify-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-medium transition-colors shadow-sm whitespace-nowrap">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                </svg>
                Individual Assign
            </button>
            @endcan
        </div>
    </div>

    @if (session()->has('successMessage'))
    <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg text-sm flex items-center gap-2">
        <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        {{ session('successMessage') }}
    </div>
    @endif

    <div class="overflow-x-auto rounded-xl border border-gray-100 shadow-sm bg-white">
        <table class="w-full text-left border-collapse text-sm">
            <thead class="bg-gray-50 text-gray-600 font-medium border-b border-gray-100">
                <tr>
                    <th class="p-4">Employee Name</th>
                    <th class="p-4">Leave Type</th>
                    <th class="p-4 text-center">Year</th>
                    <th class="p-4 text-center">Allocated Days</th>
                    <th class="p-4 text-center">Used Days</th>
                    <th class="p-4 text-center">Remaining Days</th>
                    <th class="p-4 text-center">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 text-gray-600">
                @forelse($leaveBalancesCollection as $leaveBalanceItem)
                <tr class="hover:bg-gray-50/70 transition-colors">
                    <td class="p-4 font-medium text-gray-900">{{ $leaveBalanceItem->employee->user->name ?? 'N/A' }}</td>
                    <td class="p-4 font-medium text-gray-700">{{ $leaveBalanceItem->leaveType->name ?? 'N/A' }}</td>
                    <td class="p-4 text-center text-gray-500">{{ $leaveBalanceItem->year }}</td>
                    <td class="p-4 text-center font-semibold text-gray-800">{{ $leaveBalanceItem->allocated_days }}</td>
                    <td class="p-4 text-center text-rose-600 font-medium">{{ $leaveBalanceItem->used_days }}</td>
                    <td class="p-4 text-center font-bold text-emerald-600">{{ $leaveBalanceItem->allocated_days - $leaveBalanceItem->used_days }}</td>
                    <td class="p-4 text-center flex items-center justify-center gap-2">

                        {{-- View --}}
                        @can('edit leave balance')
                        <button type="button" wire:click="openViewModal({{ $leaveBalanceItem->id }})" title="View"
                            class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-lg bg-green-100 text-green-800 hover:bg-green-200 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </button>
                        @endcan

                        <span class="text-gray-200 select-none">|</span>

                        {{-- Delete  --}}
                        @can('delete leave balance')
                        <button type="button" wire:click="confirmDelete({{ $leaveBalanceItem->id }}, '{{ addslashes($leaveBalanceItem->employee->user->name ?? '') }}')" title="Delete"
                            class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-lg bg-red-100 text-red-800 hover:bg-red-200 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                            </svg>
                        </button>
                        @endcan
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="p-8 text-center text-gray-400">No balance records matched your input filter parameters.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $leaveBalancesCollection->links() }}</div>

    @if($isBulkModalVisible)
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-45"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-gray-100">
                <div class="bg-white px-6 pt-5 pb-3 border-b border-gray-50 flex justify-between items-center">
                    <h3 class="text-base font-semibold text-gray-900">Mass Allocate Common Leave Types</h3>
                    <button type="button" wire:click="$set('isBulkModalVisible', false)" class="text-gray-400 hover:text-gray-500">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="p-6 bg-white space-y-4">
                    <p class="text-xs text-gray-400">Select leave types below to assign them to all employees.</p>

                    <div class="space-y-2">
                        <input type="text" wire:model.live.debounce.300ms="leaveTypeSearch" placeholder="Type to search leave type..." class="w-full text-xs rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">

                        <div class="max-h-40 overflow-y-auto border border-slate-200 rounded-xl divide-y divide-slate-50 bg-slate-50/50 p-1">
                            @forelse($bulkLeaveTypesList as $leaveType)
                            <label for="bulk_leave_type_{{ $leaveType->id }}" class="flex items-center gap-3 p-2.5 hover:bg-white rounded-md text-xs text-slate-700 font-medium cursor-pointer transition-colors">

                                <input type="checkbox"
                                    id="bulk_leave_type_{{ $leaveType->id }}"
                                    wire:model="bulkSelectedLeaveTypes.{{ $leaveType->id }}"
                                    class="rounded text-indigo-600 focus:ring-indigo-500 border-gray-300">

                                {{ $leaveType->name }} <span class="text-slate-400 font-normal">({{ $leaveType->max_days_per_year }} Days)</span>
                            </label>
                            @empty
                            <span class="text-xs text-slate-400 p-3 block text-center">No matching leave types discovered.</span>
                            @endforelse
                        </div>
                    </div>
                    @error('bulkSelection') <span class="text-xs text-rose-500 font-medium block">{{ $message }}</span> @enderror
                </div>

                <div class="bg-gray-50 px-6 py-3 flex justify-end gap-2 border-t border-gray-100">
                    <button type="button" wire:click="$set('isBulkModalVisible', false)" class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-xs font-medium text-slate-700 hover:bg-gray-50">Cancel</button>
                    <button type="button" wire:click="allocateBulkLeaves" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-medium shadow-sm">Assign To All</button>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if($isIndividualModalVisible)
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-45"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-xl text-left shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-gray-100 overflow-visible">

                <div class="bg-white px-6 pt-5 pb-3 border-b border-gray-50 flex justify-between items-center">
                    <h3 class="text-base font-semibold text-gray-900">Assign Special Leave Individually</h3>
                    <button type="button" wire:click="$set('isIndividualModalVisible', false)" class="text-gray-400 hover:text-gray-500">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="p-6 bg-white space-y-5 overflow-visible pb-24">
                    @error('individualSelection') <div class="p-2.5 bg-rose-50 border border-rose-100 rounded-lg text-xs text-rose-600 font-medium">{{ $message }}</div> @enderror

                    <!--  FIND & SELECT EMPLOYEE -->
                    <div class="relative z-40" wire:click.away="$set('isEmployeeDropdownOpen', false)">
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1.5 tracking-wide">Find & Select Employee:</label>
                        <div class="relative">
                            <input type="text"
                                wire:model.live.debounce.300ms="individualEmployeeSearch"
                                wire:focus="$set('isEmployeeDropdownOpen', true)"
                                placeholder="Search employee by name..."
                                autocomplete="off"
                                class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 focus:outline-none transition-all font-medium text-slate-700">

                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.604 10.604Z" />
                                </svg>
                            </div>
                        </div>

                        @if($isEmployeeDropdownOpen && count($filteredEmployeesList) > 0)

                        <div class="absolute left-0 right-0 top-full mt-1.5 bg-white border border-slate-200 rounded-xl shadow-2xl z-50 divide-y divide-slate-50 max-h-24 overflow-y-auto">
                            @foreach($filteredEmployeesList as $employee)
                            <button type="button" wire:click="selectEmployee({{ $employee->id }}, '{{ addslashes($employee->user->name ?? '') }}')"
                                class="w-full text-left px-4 py-3 hover:bg-indigo-50 transition-colors cursor-pointer flex flex-col border-l-4 {{ $individualEmployeeId == $employee->id ? 'bg-indigo-50 border-l-indigo-600' : 'border-l-transparent' }}">
                                <span class="text-xs font-bold {{ $individualEmployeeId == $employee->id ? 'text-indigo-800' : 'text-slate-700' }}">{{ $employee->user->name ?? 'N/A' }}</span>
                                <span class="text-[10px] text-slate-400 mt-0.5">{{ $employee->user->email ?? 'N/A' }}</span>
                            </button>
                            @endforeach
                        </div>
                        @endif
                    </div>

                    <!--  FIND & SELECT LEAVE TYPE -->
                    <div class="relative z-30" wire:click.away="$set('isLeaveTypeDropdownOpen', false)">
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1.5 tracking-wide">Find & Select Leave Type:</label>
                        <div class="relative">
                            <input type="text"
                                wire:model.live.debounce.300ms="individualLeaveTypeSearch"
                                wire:focus="$set('isLeaveTypeDropdownOpen', true)"
                                placeholder="Search leave type by name..."
                                autocomplete="off"
                                class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition-all font-medium text-slate-700">

                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg xmlns="http://www.w3.org/2000/xl" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.604 10.604Z" />
                                </svg>
                            </div>
                        </div>

                        @if($isLeaveTypeDropdownOpen && count($filteredLeaveTypesList) > 0)
                        <div class="absolute left-0 right-0 top-full mt-1.5 bg-white border border-slate-200 rounded-xl shadow-2xl z-50 divide-y divide-slate-50 max-h-24 overflow-y-auto">
                            @foreach($filteredLeaveTypesList as $type)
                            <button type="button" wire:click="selectIndividualLeaveType({{ $type->id }}, '{{ addslashes($type->name) }}')"
                                class="w-full text-left px-4 py-3 hover:bg-emerald-50 transition-colors cursor-pointer flex flex-col border-l-4 {{ $individualLeaveTypeId == $type->id ? 'bg-emerald-50 border-l-emerald-600' : 'border-l-transparent' }}">
                                <span class="text-xs font-bold {{ $individualLeaveTypeId == $type->id ? 'text-emerald-800' : 'text-slate-700' }}">{{ $type->name }}</span>
                                <span class="text-[10px] text-slate-400 mt-0.5">Max Allocation: {{ $type->max_days_per_year }} Days</span>
                            </button>
                            @endforeach
                        </div>
                        @endif
                    </div>
                </div>

                <div class="bg-gray-50 px-6 py-3 flex justify-end gap-2 border-t border-gray-100 rounded-b-xl">
                    <button type="button" wire:click="$set('isIndividualModalVisible', false)" class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-xs font-medium text-slate-700 hover:bg-gray-50">Cancel</button>
                    <button type="button" wire:click="allocateIndividualLeave" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-medium shadow-sm">Assign Balance</button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ================= VIEW MODAL ================= --}}
    @if($isViewModalVisible && $selectedLeaveBalance)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">

            <div class="fixed inset-0 bg-slate-900/40 transition-opacity" wire:click="$set('isViewModalVisible', false)"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-slate-200">

                <div class="bg-slate-200 px-6 py-4 flex justify-between items-center">
                    <div class="flex items-center gap-2 text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 text-slate-400">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5a2.25 2.25 0 0 0 2.25 2.25Zm.75-10.5h6.75V16.5H5.25V9Z" />
                        </svg>
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-200" id="modal-title">Leave Balance Overview</h3>
                    </div>
                    <button type="button" wire:click="$set('isViewModalVisible', false)" class="text-slate-400 hover:text-white transition-colors p-1 rounded focus:outline-none">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="px-6 py-5 bg-white divide-y divide-slate-100">

                    <div class="py-3 flex justify-between items-center first:pt-0">
                        <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Employee</span>
                        <span class="text-sm font-bold text-slate-800">{{ $selectedLeaveBalance->employee->user->name ?? 'N/A' }}</span>
                    </div>

                    <div class="py-3 flex justify-between items-center">
                        <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Leave Type</span>
                        <span class="text-sm font-semibold text-slate-700 bg-slate-100 px-2.5 py-0.5 rounded border border-slate-200">{{ $selectedLeaveBalance->leaveType->name ?? 'N/A' }}</span>
                    </div>

                    <div class="py-3 flex justify-between items-center">
                        <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Year</span>
                        <span class="text-sm font-bold text-slate-700">{{ $selectedLeaveBalance->year }}</span>
                    </div>

                    <div class="pt-4 pb-2 grid grid-cols-3 gap-3">

                        <div class="bg-slate-50 p-3 rounded border border-slate-100 text-center">
                            <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">Allocated</span>
                            <span class="text-base font-bold text-slate-800">{{ $selectedLeaveBalance->allocated_days }}</span>
                        </div>

                        <div class="bg-slate-50 p-3 rounded border border-slate-100 text-center">
                            <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">Used</span>
                            <span class="text-base font-bold text-slate-800">{{ $selectedLeaveBalance->used_days }}</span>
                        </div>

                        <div class="bg-slate-50 p-3 rounded border border-slate-100 text-center-border bg-slate-900/5 text-center">
                            <span class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-0.5">Remaining</span>
                            <span class="text-base font-extrabold text-slate-900">{{ $selectedLeaveBalance->allocated_days - $selectedLeaveBalance->used_days }}</span>
                        </div>

                    </div>
                </div>

                <div class="bg-slate-50 px-6 py-3.5 flex justify-end border-t border-slate-100">
                    <button type="button" wire:click="$set('isViewModalVisible', false)"
                        class="px-4 py-1.5 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 rounded text-xs font-semibold transition-all shadow-sm focus:outline-none">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ================= DELETE MODAL ================= --}}
    @if($isDeleteModalVisible)
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-45"></div>
            <div class="inline-block bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:max-w-md sm:w-full border p-6 space-y-4 z-50">
                <div class="flex justify-between items-center border-b pb-3">
                    <h3 class="text-base font-semibold text-rose-600">Confirm Deletion</h3>
                    <button type="button" wire:click="$set('isDeleteModalVisible', false)" class="text-gray-400 hover:text-gray-500">✕</button>
                </div>

                <div class="text-sm text-gray-600">
                    <p>Are you sure you want to delete the leave balance for <strong>{{ $deleteEmployeeName }}</strong>?</p>

                    @if(!$canDelete)
                    <div class="mt-3 p-3 bg-amber-50 border border-amber-200 text-amber-800 rounded-lg text-xs font-medium">
                        ⚠️ This record cannot be deleted because it has active leave requests or related log entries.
                    </div>
                    @endif
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t">
                    <button type="button" wire:click="$set('isDeleteModalVisible', false)" class="px-4 py-2 bg-gray-100 rounded-lg text-xs font-medium text-slate-700 hover:bg-gray-200">Cancel</button>

                    @if($canDelete)
                    <button type="button" wire:click="deleteLeaveBalance" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-xs font-medium shadow-sm">Delete Permanently</button>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif
</div>