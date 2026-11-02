<div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 mt-6">

    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-xl font-semibold text-gray-800">My Leave Management</h2>
            <p class="text-sm text-gray-500">View your history and request new leaves.</p>
        </div>

        <button wire:click="toggleApplicationModal" class="flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium shadow-sm transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Apply for Leave
        </button>
    </div>

    {{-- Leave Type Cards Layout Section --}}
    <div class="flex overflow-x-auto gap-4 mb-6 pb-2 scrollbar-thin">
        @foreach($leaveBalances as $balance)
        <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm flex justify-between items-center min-w-[220px] w-[calc(20%-1rem)] flex-shrink-0">
            <div>
                <h4 class="text-sm font-semibold text-gray-700">{{ $balance->leaveType->name ?? 'Leave' }}</h4>
                <p class="text-xs text-gray-400">Total Allocated: {{ $balance->allocated_days }} Days</p>
            </div>
            <div class="text-right">
                <span class="text-xl font-bold text-indigo-600 block">{{ $balance->allocated_days - $balance->used_days }}</span>
                <span class="text-[10px] text-gray-500 uppercase font-medium">Remaining</span>
            </div>
        </div>
        @endforeach
    </div>

    @if (session()->has('formSuccessFeedback'))
    <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg text-sm flex items-center gap-2">
        <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        {{ session('formSuccessFeedback') }}
    </div>
    @endif

    <div class="overflow-x-auto rounded-xl border border-gray-100 shadow-sm bg-white">
        <table class="w-full text-left border-collapse text-sm">
            <thead class="bg-gray-50 text-gray-600 font-medium border-b border-gray-100">
                <tr>
                    <th class="p-4">Req Number</th>
                    <th class="p-4">Leave Type</th>
                    <th class="p-4">Duration</th>
                    <th class="p-4 text-center">Days</th>
                    <th class="p-4">Status</th>
                    <th class="p-4 text-center">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 text-gray-600">
                @forelse($leaveHistoryCollection as $leaveHistoryItem)
                <tr class="hover:bg-gray-50/70 transition-colors">
                    <td class="p-4 font-mono text-xs font-semibold text-gray-700">{{ $leaveHistoryItem->request_number }}</td>
                    <td class="p-4 font-medium text-gray-900">
                        {{ $leaveHistoryItem->applyLeaveTypes->whereNotNull('leave_type_id')->first()?->leaveType->name ?? 'No-Pay' }}
                    </td>
                    <td class="p-4 text-xs text-gray-500">
                        {{ \Carbon\Carbon::parse($leaveHistoryItem->start_date)->format('Y-m-d') }} to
                        {{ \Carbon\Carbon::parse($leaveHistoryItem->end_date)->format('Y-m-d') }}
                    </td>
                    <td class="p-4 text-center font-semibold text-gray-700">{{ $leaveHistoryItem->total_days }}</td>
                    <td class="p-4">
                        @if($leaveHistoryItem->status === 'Approved')
                        <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 rounded-full font-medium text-xs border border-emerald-100">Approved</span>
                        @elseif($leaveHistoryItem->status === 'Rejected')
                        <span class="px-2.5 py-1 bg-rose-50 text-rose-700 rounded-full font-medium text-xs border border-rose-100">Rejected</span>
                        @elseif($leaveHistoryItem->status === 'Pending HR Approval' || $leaveHistoryItem->status === 'Pending Supervisor Approval')
                        <span class="px-2.5 py-1 bg-amber-50 text-amber-700 rounded-full font-medium text-xs border border-amber-100">Pending</span>
                        @else
                        <span class="px-2.5 py-1 bg-gray-50 text-gray-600 rounded-full font-medium text-xs border border-gray-100">{{ $leaveHistoryItem->status }}</span>
                        @endif
                    </td>
                    <td class="p-4 text-center flex items-center justify-center gap-2">
                        <button wire:click="openViewModal({{ $leaveHistoryItem->id }})" title="View" class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-lg bg-green-100 text-green-800 hover:bg-green-200">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </button>

                        @if($leaveHistoryItem->status === 'Rejected')
                        <span class="text-gray-200 select-none">|</span>

                        <button wire:click="openReapplyModal({{ $leaveHistoryItem->id }})" title="Edit" class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-lg bg-indigo-100 text-indigo-800 hover:bg-indigo-200">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                            </svg>
                        </button>
                        @endif

                        @if(str_contains($leaveHistoryItem->status, 'Pending'))
                        <span class="text-gray-200 select-none">|</span>
                        <button wire:click="openCancelConfirmationModal({{ $leaveHistoryItem->id }})" title="Cancel Request" class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-lg bg-red-100 text-red-800 hover:bg-red-200">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                            </svg>
                        </button>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="p-8 text-center text-gray-400">No leave history records found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <div wire:key="history-pagination" class="mt-4 px-2">
            @if(method_exists($leaveHistoryCollection, 'links'))
            {{ $leaveHistoryCollection->links() }}
            @endif
        </div>
    </div>

    {{-- ========================================== --}}
    {{-- 1. REQUEST A NEW LEAVE MAIN MODAL OVERLAY --}}
    {{-- ========================================== --}}
    @if($isApplicationModalOpen)
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-45"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-100">
                <div class="bg-white px-6 pt-6 pb-4 border-b border-gray-50">
                    <div class="flex justify-between items-center">

                        <h3 class="text-lg font-semibold text-gray-900">
                            @if($isEditMode)
                            Review & Re-apply Leave Request
                            @else
                            Request a New Leave
                            @endif
                        </h3>
                        <button type="button" wire:click="toggleApplicationModal" class="text-gray-400 hover:text-gray-500">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <form wire:submit.prevent="{{ $isEditMode ? 'resubmitLeaveRequest' : 'submitLeaveApplication' }}">
                    <div class="bg-white px-6 py-4 space-y-4">
                        @if ($errors->has('start_date'))
                        <div class="p-3 bg-red-50 border-l-4 border-red-500 text-red-700 text-xs font-semibold rounded">
                            {{ $errors->first('start_date') }}
                        </div>
                        @endif
                        {{-- Leave Type Selection --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1.5 tracking-wide">Find & Select Leave Type:</label>
                            <div class="relative">
                                <input type="text"
                                    wire:model.live.debounce.300ms="individualLeaveTypeSearch"
                                    wire:focus="$set('isLeaveTypeDropdownOpen', true)"
                                    placeholder="Search leave type by name..."
                                    autocomplete="off"
                                    class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 focus:outline-none transition-all font-medium text-slate-700">

                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.604 10.604Z" />
                                    </svg>
                                </div>

                                @if($isLeaveTypeDropdownOpen)
                                <div class="fixed inset-0 z-40" wire:click="$set('isLeaveTypeDropdownOpen', false)"></div>
                                <div class="absolute z-50 w-full mt-1.5 max-h-48 overflow-y-auto bg-white border border-slate-100 rounded-xl shadow-lg divide-y divide-slate-50 p-1">
                                    @forelse($filteredLeaveTypes as $leaveType)
                                    <button type="button"
                                        wire:click="selectLeaveType({{ $leaveType->id }}, '{{ $leaveType->name }}')"
                                        class="w-full flex items-center justify-between p-2.5 hover:bg-slate-50 text-left rounded-lg text-xs text-slate-700 font-medium transition-colors">
                                        <span>{{ $leaveType->name }}</span>
                                    </button>
                                    @empty
                                    <span class="text-xs text-slate-400 p-3 block text-center">No matching leave types discovered.</span>
                                    @endforelse
                                </div>
                                @endif
                            </div>
                            @error('leave_type_id') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        @if($availableLeaveBalance !== null)
                        <div class="p-2.5 bg-gray-50 rounded-lg text-xs flex justify-between items-center border border-gray-100">
                            <span class="text-gray-500">Available Balance:</span>
                            <span class="font-bold text-indigo-600">{{ $availableLeaveBalance }} Days Remaining</span>
                        </div>
                        @endif

                        {{-- Half-Day Selector --}}
                        <div class="grid grid-cols-2 gap-4 items-center p-3 bg-slate-50 rounded-xl border border-slate-100">
                            <div class="flex items-center h-full">
                                <label class="inline-flex items-center cursor-pointer select-none">
                                    <input type="checkbox" wire:model.live="is_half_day" x-on:change="$dispatch('half-day-toggled')" class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                                    <span class="ml-2 text-xs font-semibold text-gray-700 uppercase">Apply Half-Day Leave</span>
                                </label>
                            </div>

                            @if($is_half_day)
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Select Shift</label>
                                <div class="relative">
                                    <details class="w-full relative block [&_summary::-webkit-details-marker]:hidden group">
                                        <summary class="w-full bg-purple-50 border border-purple-200 rounded-md shadow-sm pl-3 pr-8 py-1.5 text-left cursor-pointer text-xs font-semibold text-purple-900 h-[34px] flex items-center justify-between list-none">
                                            <span>{{ $leave_shift ?? 'Morning' }} Shift</span>
                                            <svg class="h-4 w-4 text-purple-400 transition-transform group-open:rotate-180" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />
                                            </svg>
                                        </summary>
                                        <button type="button" class="fixed inset-0 z-30 cursor-default bg-transparent w-full h-full block" onclick="this.closest('details').removeAttribute('open')"></button>
                                        <div class="absolute left-0 z-40 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg py-1">
                                            <button type="button" wire:click="$set('leave_shift', 'Morning')" onclick="this.closest('details').removeAttribute('open')" class="w-full text-left cursor-pointer px-4 py-2 text-xs hover:bg-purple-50 text-gray-700 block transition-colors">Morning Shift</button>
                                            <button type="button" wire:click="$set('leave_shift', 'Evening')" onclick="this.closest('details').removeAttribute('open')" class="w-full text-left cursor-pointer px-4 py-2 text-xs hover:bg-purple-50 text-gray-700 block transition-colors">Evening Shift</button>
                                        </div>
                                    </details>
                                </div>
                                @error('leave_shift') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                            @endif
                        </div>

                        {{-- Date Pickers Section --}}
                        <div class="grid grid-cols-2 gap-4"
                            x-data="{ 
                                startDate: @entangle('start_date'), 
                                endDate: @entangle('end_date'),
                                isHalfDay: @entangle('is_half_day'),
                                startPicker: null,
                                endPicker: null,
                                initPickers() {
                                    this.startPicker = flatpickr(this.$refs.startDatePicker, {
                                        altInput: true,
                                        altFormat: '{{ $globalCalendarFormat }}',
                                        dateFormat: 'Y-m-d',
                                        defaultDate: this.startDate,
                                        onReady: (sd, ds, inst) => {
                                            inst.altInput.placeholder = '{{ $globalPlaceholderFormat }}';
                                            inst.altInput.className = 'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm cursor-pointer bg-white';
                                        },
                                        onChange: (selectedDates, dateStr) => {
                                            this.startDate = dateStr;
                                            $wire.set('start_date', dateStr);
                                            if (this.isHalfDay) {
                                                this.endDate = dateStr;
                                                $wire.set('end_date', dateStr);
                                            }
                                        }
                                    });

                                    this.endPicker = flatpickr(this.$refs.endDatePicker, {
                                        altInput: true,
                                        altFormat: '{{ $globalCalendarFormat }}',
                                        dateFormat: 'Y-m-d',
                                        defaultDate: this.endDate,
                                        onReady: (sd, ds, inst) => {
                                            inst.altInput.placeholder = '{{ $globalPlaceholderFormat }}';
                                            inst.altInput.className = 'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm cursor-pointer bg-white';
                                        },
                                        onChange: (selectedDates, dateStr) => {
                                            this.endDate = dateStr;
                                            $wire.set('end_date', dateStr);
                                        }
                                    });
                                },
                                syncConstraints() {
                                    if (this.isHalfDay && this.startDate) {
                                        this.endDate = this.startDate;
                                        $wire.set('end_date', this.startDate);
                                        if(this.endPicker) this.endPicker.destroy();
                                    } else {
                                        if(!this.endPicker || !document.body.contains(this.endPicker.element)) {
                                            this.initPickers();
                                        }
                                    }
                                }
                            }"
                            x-init="initPickers(); $watch('isHalfDay', value => syncConstraints())"
                            @half-day-toggled.window="syncConstraints()">

                            {{-- Start Date Picker --}}
                            <div class="relative">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                                <div class="relative mt-1" wire:ignore>
                                    <input type="text" x-ref="startDatePicker" readonly class="hidden">
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none mt-1">
                                        <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                </div>
                                @error('start_date') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            {{-- End Date Picker --}}
                            <div class="relative" x-show="!isHalfDay" x-transition>
                                <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                                <div class="relative mt-1" wire:ignore>
                                    <input type="text" x-ref="endDatePicker" readonly class="hidden">
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none mt-1">
                                        <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                </div>
                                @error('end_date') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            {{-- Explicit Single Day Visual Placeholder for Half-Day --}}
                            <div class="relative" x-show="isHalfDay" x-transition>
                                <label class="block text-sm font-medium text-gray-400 mb-1">End Date</label>
                                <div class="mt-2 px-3 py-2 bg-slate-100 border border-slate-200 rounded-md text-slate-500 text-xs font-medium">
                                    Locked to Start Date
                                </div>
                            </div>
                        </div>

                        {{-- Dynamic Summary Calculation Panels --}}
                        @if($start_date && $end_date)
                        <div class="mt-4 space-y-2 animate-fadeIn">
                            <div class="p-2.5 bg-indigo-50/70 text-indigo-900 rounded-lg text-xs flex justify-between items-center font-medium">
                                <span>Total Requested Business Days:</span>
                                <span class="font-bold text-sm">{{ $total_days }} {{ $total_days == 1 ? 'Day' : 'Days' }}</span>
                            </div>

                            @if($total_days <= 0)
                                <div class="p-2.5 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-xs font-medium flex items-center gap-1.5 shadow-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 text-rose-600 shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                                </svg>
                                <span>The selected date range contains no working days. Please choose valid working days.</span>
                        </div>
                        @endif
                    </div>
                    @endif

                    {{-- Reason Area --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Reason for Leave</label>
                        <textarea wire:model="reason" rows="3" placeholder="Provide a brief reason..." class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"></textarea>
                        @error('reason') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- Document Attachment Layer --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase mb-1 flex items-center gap-1">
                            <span>Supporting Document / Attachment</span>
                            <span class="text-gray-400 text-[10px] lowercase">(Optional)</span>
                        </label>


                        @if($existing_attachment_path)
                        <div class="mt-1 p-3 bg-blue-50 border border-blue-200 rounded-xl flex items-center justify-between shadow-sm">
                            <span class="text-xs font-semibold text-blue-800">📄 Previously Attached File</span>
                            <div class="flex gap-2 items-center">

                                <button type="button" wire:click="openAttachmentPreview('{{ addslashes($existing_attachment_path) }}')" title="View" class="px-2 py-1 inline-flex items-center text-xs leading-5 font-semibold rounded-lg bg-green-100 text-green-800 hover:bg-green-200 transition-colors">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    View
                                </button>
                                <button type="button" wire:click="removeExistingFile" class="px-2 py-1 inline-flex items-center text-xs leading-5 font-semibold rounded-lg bg-rose-100 text-rose-800 hover:bg-rose-200 transition-colors">
                                    Remove
                                </button>
                            </div>
                        </div>

                        @else

                        <div onclick="document.getElementById('file-upload').click()" class="mt-1 flex justify-center px-6 py-5 border-2 border-gray-300 border-dashed rounded-xl hover:border-indigo-500 hover:bg-slate-50 text-center cursor-pointer transition-all bg-white relative">
                            <input id="file-upload" name="file-upload" type="file" wire:model="attachment" class="sr-only">
                            <div class="space-y-1">
                                <svg class="mx-auto h-8 w-8 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                </svg>
                                <div class="text-xs text-gray-700 font-medium">
                                    <span class="text-indigo-600 hover:text-indigo-700 underline underline-offset-2">Click here to upload a file</span>
                                </div>
                                <p class="text-[10px] text-gray-400">Supported formats: PDF, JPG, PNG (Max 2MB)</p>
                            </div>
                        </div>

                        <div wire:loading wire:target="attachment" class="text-xs text-indigo-600 mt-1 font-medium animate-pulse">
                            ⏳ Uploading document, please wait...
                        </div>

                        @if ($attachment)
                        <div class="mt-2 p-2 bg-emerald-50 border border-emerald-200 rounded-lg flex items-center justify-between text-xs text-emerald-800">
                            <span class="font-medium truncate max-w-[250px]">📄 {{ $attachment->getClientOriginalName() }}</span>
                            <button type="button" wire:click="$set('attachment', null)" class="text-rose-500 hover:text-rose-700 font-bold ml-2 transition-colors">Remove</button>
                        </div>
                        @endif
                        @endif

                        @error('attachment') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                    </div>
            </div>

            {{-- Action Bar --}}
            <div class="bg-gray-50 px-6 py-3 flex justify-end gap-2 border-t border-gray-100">
                <button type="button" wire:click="toggleApplicationModal" class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
                    Cancel
                </button>

                <button type="button"
                    wire:click="checkLeaveBalanceAndRoute"
                    class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium shadow-sm">
                    @if($isEditMode)
                    Review & Update
                    @else
                    Submit Request
                    @endif
                </button>
            </div>
            </form>
        </div>
    </div>
</div>
@endif

{{-- ======================================================== --}}
{{-- 2. AUTOMATIC MULTI-SPLIT CONFIRMATION MODAL (REAL-TIME)  --}}
{{-- ======================================================== --}}
@if($isSplitConfirmationModalOpen)
<div class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" wire:key="split-confirmation-modal">
    <div class="w-full max-w-lg bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden transform transition-all animate-fade-in relative z-[110]">
        <div class="p-4 bg-amber-50 border-b border-amber-200/60 flex items-center gap-2 font-bold text-amber-900">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-amber-600">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
            </svg>
            <span class="text-sm">Advanced Multi-Type Leave Split Matrix</span>
        </div>

        <div class="p-5 text-xs font-medium space-y-4">
            <p class="text-slate-600 leading-relaxed">
                Your available balance for this specific leave type is <strong class="text-slate-900">{{ $availableLeaveBalance ?? 0 }} day(s)</strong>.
                Please allocate the remaining overflow pool of <strong class="text-rose-600 font-bold">{{ $total_days - ($availableLeaveBalance ?? 0) }} day(s)</strong> across alternative buckets below.
            </p>

            @if($unassignedFallbackDaysRemaining > 0)
            <div class="p-2.5 rounded-xl font-bold bg-amber-100 text-amber-800 border border-amber-300 text-center shadow-inner">
                ⚠️ Required Assignment Left: <span class="underline font-extrabold">{{ $unassignedFallbackDaysRemaining }} day(s)</span>
            </div>
            @elseif($unassignedFallbackDaysRemaining < 0)
                <div class="p-2.5 rounded-xl font-bold bg-rose-100 text-rose-800 border border-rose-300 text-center shadow-inner">
                ❌ Over-allocated Pool Error: Exceeded bounds by {{ abs($unassignedFallbackDaysRemaining) }} day(s)
        </div>
        @else
        <div class="p-2.5 rounded-xl font-bold bg-emerald-100 text-emerald-800 border border-emerald-300 text-center shadow-inner">
            ✅ Success Matrix: All structural parameters aligned. Ready for validation.
        </div>
        @endif

        @error('matrix_error')
        <div class="text-rose-600 font-semibold text-[11px] bg-rose-50 p-2.5 rounded-lg border border-rose-200">
            {{ $message }}
        </div>
        @enderror

        @error('database_error')
        <div class="text-rose-600 font-semibold text-[11px] bg-rose-50 p-2.5 rounded-lg border border-rose-200">
            {{ $message }}
        </div>
        @enderror

        <div class="space-y-2.5 max-h-60 overflow-y-auto pr-1">
            <span class="block text-[10px] font-bold uppercase text-gray-400 tracking-wider">Configure Allocation Splits:</span>

            @foreach($alternativePaidLeaveTypes ?? [] as $altType)
            <div class="flex items-center justify-between bg-gray-50 p-3 rounded-xl border border-gray-200/80 hover:bg-gray-100/50 transition">
                <div class="flex flex-col">
                    <span class="text-slate-700 font-bold text-xs">{{ $altType['name'] }}</span>
                    <span class="text-[10px] text-gray-400 font-medium">Available Balance: {{ $altType['balance'] }} Days</span>
                </div>
                <input type="number" step="0.5" min="0" max="{{ $altType['balance'] }}"
                    wire:model.live.number="allocatedFallbackDaysArray.{{ $altType['id'] }}"
                    wire:change="calculateUnassignedSplitDaysPool"
                    class="w-24 p-1.5 text-center bg-white border border-gray-300 rounded-lg focus:ring-amber-500 focus:border-amber-500 text-xs text-slate-800 font-bold shadow-xs">
            </div>
            @endforeach

            <div class="flex items-center justify-between bg-gray-50 p-3 rounded-xl border border-gray-200/80 hover:bg-gray-100/50 transition">
                <div class="flex flex-col">
                    <span class="text-rose-700 font-bold text-xs">No-Pay Leave</span>
                    <span class="text-[10px] text-gray-400 font-medium">Salary Deduction Rule Baseline</span>
                </div>
                <input type="number" step="0.5" min="0"
                    wire:model.live.number="allocatedFallbackDaysArray.nopay"
                    wire:change="calculateUnassignedSplitDaysPool"
                    class="w-24 p-1.5 text-center bg-white border border-gray-300 rounded-lg focus:ring-amber-500 focus:border-amber-500 text-xs text-slate-800 font-bold shadow-xs">
            </div>
        </div>
    </div>

    <div class="p-4 bg-gray-50 border-t border-gray-100 flex justify-end gap-2.5">

        <button type="button"
            wire:click="$set('isSplitConfirmationModalOpen', false)"
            class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-xl font-bold text-xs hover:bg-gray-50 transition shadow-xs">
            Cancel
        </button>

        <button type="button"
            wire:click="{{ $isEditMode ? 'resubmitLeaveRequest' : 'submitLeaveApplication' }}"
            @if($unassignedFallbackDaysRemaining !==0.0 || $errors->has('matrix_error') || $errors->has('database_error')) disabled @endif
            class="px-4 py-2 bg-indigo-600 text-white rounded-xl font-bold text-xs hover:bg-indigo-700 transition disabled:opacity-40 disabled:cursor-not-allowed shadow-md">
            Confirm & Submit
        </button>

    </div>
</div>
@endif

{{-- ========================================== --}}
{{-- 3. VIEW LEAVE DETAILS MODAL OVERLAY SECTION --}}
{{-- ========================================== --}}
@if($showViewModal && $selectedLeaveDetails)
<div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-gray-900 bg-opacity-50">
    <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6 m-4 relative">
        <div class="flex justify-between items-center pb-3 border-b border-gray-100">
            <div>
                <h3 class="text-base font-bold text-gray-800">Leave Application Details</h3>
                <span class="text-xs text-indigo-600 font-semibold uppercase tracking-wider">
                    Req: {{ $selectedLeaveDetails->request_number ?? 'N/A' }}
                </span>
            </div>
            <button wire:click="closeViewModal" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
        </div>

        <div class="mt-4 space-y-4">
            <div class="grid grid-cols-2 gap-4 bg-slate-50 p-2.5 rounded-lg border border-slate-100">
                <div>
                    <span class="text-[10px] font-bold text-gray-400 block uppercase tracking-wider">Leave Type</span>
                    <span class="text-xs font-semibold text-gray-800">
                        {{ $selectedLeaveDetails->applyLeaveTypes->whereNotNull('leave_type_id')->first()?->leaveType->name ?? 'No-Pay' }}
                    </span>
                </div>
                <div class="text-right">
                    <span class="text-[10px] font-bold text-gray-400 block uppercase tracking-wider">Total Duration</span>
                    <span class="text-xs font-bold text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded border border-indigo-100 inline-block">
                        {{ $selectedLeaveDetails->total_days }} {{ $selectedLeaveDetails->total_days == 1 ? 'Day' : 'Days' }}
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <span class="text-[10px] font-bold text-gray-400 block uppercase tracking-wider">Start Date</span>
                    <span class="text-xs font-medium text-gray-800 flex items-center gap-1 mt-0.5">
                        {{ \Carbon\Carbon::parse($selectedLeaveDetails->start_date)->format($globalCalendarFormat) }}
                    </span>
                </div>
                <div>
                    <span class="text-[10px] font-bold text-gray-400 block uppercase tracking-wider">End Date</span>
                    <span class="text-xs font-medium text-gray-800 flex items-center gap-1 mt-0.5">
                        {{ \Carbon\Carbon::parse($selectedLeaveDetails->end_date)->format($globalCalendarFormat) }}
                    </span>
                </div>
            </div>

            @if($selectedLeaveDetails->is_half_day)
            <div class="p-2.5 bg-purple-50 text-purple-900 rounded-lg text-xs flex justify-between items-center border border-purple-100">
                <span class="font-semibold flex items-center gap-1"> Half-Day Leave Applied</span>
                <span class="font-bold uppercase bg-purple-200 px-2 py-0.5 rounded text-[10px] text-purple-800">{{ $selectedLeaveDetails->leave_shift }} Shift</span>
            </div>
            @endif

            <div>
                <span class="text-[10px] font-bold text-gray-400 block uppercase tracking-wider">Reason</span>
                <p class="text-xs text-gray-700 bg-gray-50 p-2.5 rounded-lg border border-gray-100 mt-1 italic">
                    "{{ $selectedLeaveDetails->reason ?? 'No reason provided' }}"
                </p>
            </div>

            @if($selectedLeaveDetails->attachment_path)
            <div>
                <span class="text-[10px] font-bold text-gray-400 block uppercase tracking-wider mb-1">Attachment</span>
                <button type="button" wire:click="openAttachmentPreview('{{ addslashes($selectedLeaveDetails->attachment_path) }}')" class="inline-flex items-center gap-1.5 text-xs font-medium text-indigo-600 hover:text-indigo-700 hover:underline bg-indigo-50/50 px-3 py-1.5 rounded-lg border border-indigo-100 transition-colors w-full justify-between group">
                    <span class="flex items-center gap-1.5 truncate max-w-[280px]">
                        📄 Click to Preview Document Inside Modal
                    </span>
                    <svg class="w-4 h-4 text-indigo-500 group-hover:translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178z" />
                    </svg>
                </button>
            </div>
            @endif

            <div class="pt-3 border-t border-gray-100">
                <span class="text-[10px] font-bold text-gray-400 block uppercase tracking-wider">Status</span>
                @if(str_contains(strtolower($selectedLeaveDetails->status), 'pending'))
                <span class="inline-block mt-1 px-2.5 py-0.5 text-[11px] font-bold rounded-full bg-amber-100 text-amber-800 border border-amber-200">
                    {{ $selectedLeaveDetails->status }}
                </span>
                @elseif(str_contains(strtolower($selectedLeaveDetails->status), 'approve'))
                <span class="inline-block mt-1 px-2.5 py-0.5 text-[11px] font-bold rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200">
                    {{ $selectedLeaveDetails->status }}
                </span>
                @elseif(str_contains(strtolower($selectedLeaveDetails->status), 'reject'))
                <span class="inline-block mt-1 px-2.5 py-0.5 text-[11px] font-bold rounded-full bg-rose-100 text-rose-800 border border-rose-200">
                    {{ $selectedLeaveDetails->status }}
                </span>
                @endif
            </div>

            @if(str_contains(strtolower($selectedLeaveDetails->status), 'reject') && $latestRejectionRemark)
            <div class="p-2.5 bg-rose-50 border border-rose-100 rounded-lg">
                <span class="text-[10px] font-bold text-rose-800 block uppercase tracking-wider">Rejection Reason / Remark</span>
                <p class="text-xs text-rose-900 mt-1 italic">
                    "{{ $latestRejectionRemark }}"
                </p>
            </div>
            @endif
        </div>

        <div class="mt-6 flex justify-end">
            <button wire:click="closeViewModal" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-lg transition-colors border border-gray-200 shadow-sm">
                Close
            </button>
        </div>
    </div>
</div>
@endif

{{-- ======================================================== --}}
{{-- 4. SUB-MODAL FOR ATTACHMENT PREVIEW (ON TOP OF DETAILS)  --}}
{{-- ======================================================== --}}
@if($showAttachmentModal && $attachmentUrl)
<div class="fixed inset-0 z-[150] flex items-center justify-center bg-gray-950 bg-opacity-60 overflow-hidden">
    <div class="bg-white rounded-xl shadow-2xl max-w-2xl w-full h-[85vh] p-5 m-4 flex flex-col">
        <div class="flex justify-between items-center pb-2 border-b border-gray-100 shrink-0">
            <h4 class="text-sm font-bold text-gray-800 flex items-center gap-1.5">
                📄 Attachment Document Viewer
            </h4>
            <button type="button" wire:click="closeAttachmentPreview" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
        </div>

        <div class="mt-4 flex-1 bg-slate-100 rounded-lg overflow-hidden relative border border-gray-200">
            @if(str_ends_with(strtolower($attachmentUrl), '.pdf'))
            <iframe src="{{ $attachmentUrl }}" class="w-full h-full border-none rounded-lg" loading="lazy"></iframe>
            @else
            <div class="w-full h-full flex items-center justify-center p-2 bg-slate-900 overflow-auto">
                <img src="{{ $attachmentUrl }}" alt="Uploaded Supporting Document" class="max-w-full max-h-full object-contain rounded shadow-md">
            </div>
            @endif
        </div>

        <div class="mt-4 flex justify-between items-center pt-2 border-t border-gray-100 shrink-0">
            <a href="{{ $attachmentUrl }}" download class="text-xs font-semibold text-indigo-600 hover:text-indigo-700 hover:underline">
                ⬇️ Download Original File
            </a>
            <button type="button" wire:click="closeAttachmentPreview" class="px-3 py-1.5 bg-gray-800 hover:bg-gray-900 text-white text-xs font-semibold rounded-md transition-colors shadow-sm">
                Return to Details
            </button>
        </div>
    </div>
</div>
@endif

{{-- ========================================== --}}
{{-- 5. CANCEL LEAVE REQUEST CONFIRMATION MODAL --}}
{{-- ========================================== --}}
@if($isCancelModalOpen)
<div class="fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-45"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-gray-100">
            <div class="bg-white p-6">
                <div class="sm:flex sm:items-start">
                    <div class="flex items-center justify-center flex-shrink-0 w-12 h-12 mx-auto bg-rose-50 rounded-full sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="w-6 h-6 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-base font-semibold text-gray-900">Cancel Leave Request</h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">
                                Are you sure you want to cancel this leave request? This action cannot be undone.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-gray-50 px-6 py-3 flex justify-end gap-2 border-t border-gray-100">
                <button type="button" wire:click="closeCancelModal" class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
                    No, Keep It
                </button>
                <button type="button" wire:click="confirmCancelLeaveRequest" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-sm font-medium shadow-sm">
                    Yes, Cancel Request
                </button>
            </div>
        </div>
    </div>
</div>
@endif

</div>