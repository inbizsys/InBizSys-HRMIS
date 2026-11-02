<div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 mt-6">
    <h2 class="text-2xl font-semibold text-gray-800 mb-4">Leave Application Management Approval Queue Workspace Panel</h2>

    @if (session()->has('panelErrorFeedback'))
    <div class="mb-4 p-4 bg-red-100 border-l-4 border-red-500 text-red-700 font-medium rounded">
        {{ session('panelErrorFeedback') }}
    </div>
    @endif

    @if (session()->has('panelSuccessFeedback'))
    <div class="mb-4 p-4 bg-green-100 border-l-4 border-green-500 text-green-700 font-medium rounded">
        {{ session('panelSuccessFeedback') }}
    </div>
    @endif

    <div class="flex border-b border-gray-200 mb-4">
        @can('supervisor leave approval')
        <button wire:click="$set('activeTabWorkflowState', 'pending_supervisor')" class="px-4 py-2 font-medium text-sm {{ $activeTabWorkflowState === 'pending_supervisor' ? 'border-b-2 border-blue-500 text-blue-600' : 'text-gray-500 hover:text-gray-700' }}">
            Supervisor Pending
        </button>
        @endcan
        @can('hr leave approval')
        <button wire:click="$set('activeTabWorkflowState', 'pending_hr')" class="px-4 py-2 font-medium text-sm {{ $activeTabWorkflowState === 'pending_hr' ? 'border-b-2 border-blue-500 text-blue-600' : 'text-gray-500 hover:text-gray-700' }}">
            HR Pending
        </button>
        @endcan
        @can('approve leave approval')
        <button wire:click="$set('activeTabWorkflowState', 'approved')" class="px-4 py-2 font-medium text-sm {{ $activeTabWorkflowState === 'approved' ? 'border-b-2 border-blue-500 text-blue-600' : 'text-gray-500 hover:text-gray-700' }}">
            Approved History
        </button>
        @endcan
        @can('reject leave approval')
        <button wire:click="$set('activeTabWorkflowState', 'rejected')" class="px-4 py-2 font-medium text-sm {{ $activeTabWorkflowState === 'rejected' ? 'border-b-2 border-blue-500 text-blue-600' : 'text-gray-500 hover:text-gray-700' }}">
            Rejected Log
        </button>
        @endcan
    </div>

    <div class="bg-white rounded-lg shadow overflow-x-auto">

        <div class="mb-6 flex flex-col md:flex-row justify-between items-stretch md:items-center gap-4 bg-slate-50 p-4 rounded-xl border border-slate-200 shadow-xs">

            <div class="w-full md:w-auto flex-1 max-w-md relative" wire:click.away="$set('isMainDropdownOpen', false)">
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Filter By Name</label>
                <div class="relative">
                    <input wire:model.live="searchQuery"
                        wire:focus="$set('isMainDropdownOpen', true)"
                        type="text"
                        placeholder="Search by Employee Name..."
                        class="rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 w-full pl-9">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>

                @if($isMainDropdownOpen && !empty($uniqueEmployeesCollection))
                <div class="absolute left-0 right-0 top-full mt-1.5 bg-white border border-slate-200 rounded-xl shadow-2xl z-50 divide-y divide-slate-50 max-h-48 overflow-y-auto">
                    @foreach($uniqueEmployeesCollection as $employeeName)
                    <button type="button" wire:click="selectFromMainSearch('{{ addslashes($employeeName) }}')"
                        class="w-full text-left px-4 py-3 hover:bg-indigo-50 transition-colors cursor-pointer flex flex-col justify-center">
                        <span class="text-xs font-bold text-slate-700">{{ $employeeName }}</span>
                        <span class="text-[9px] text-slate-400 font-medium tracking-wide uppercase mt-0.5">Active Employee</span>
                    </button>
                    @endforeach
                </div>
                @endif
            </div>

            <div class="w-full md:w-56"
                wire:ignore
                x-data="{ 
        searchDate: @entangle('searchDate'),
        filterPicker: null,
        initFilterPicker() {
            this.filterPicker = flatpickr(this.$refs.filterDatePicker, {
                altInput: true,
                altFormat: '{{ $globalCalendarFormat ?? 'Y-m-d' }}', 
                dateFormat: 'Y-m-d',
                defaultDate: this.searchDate,
                disableMobile: 'true',
                onReady: (sd, ds, inst) => {
                    inst.altInput.placeholder = '{{ $globalPlaceholderFormat ?? 'Select Date...' }}'; 
                    inst.altInput.className = 'rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 w-full pr-10 cursor-pointer bg-white';
                },
                onChange: (selectedDates, dateStr) => {
                    this.searchDate = dateStr;
                    $wire.set('searchDate', dateStr);
                }
            });
        }
     }"
                x-init="initFilterPicker(); $watch('searchDate', value => { if(!value && filterPicker) filterPicker.clear(false); })">

                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Filter By Date</label>
                <div class="relative flex items-center">

                    <input type="text" x-ref="filterDatePicker" readonly class="hidden">

                    @if(!empty($searchDate))
                    <button type="button"
                        wire:click="$set('searchDate', '')"
                        class="absolute right-2 text-gray-400 hover:text-red-500 transition-colors p-1 z-10"
                        title="Clear Date">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                    @endif

                    @if(empty($searchDate))
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-gray-400">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Req ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee Details</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Leave Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Duration Bounds</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Net Days</th>

                    @if($activeTabWorkflowState === 'rejected')
                    <th class="px-6 py-3 text-left text-xs font-medium text-red-500 uppercase tracking-wider">Rejection Reason</th>
                    @else
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reason Given</th>
                    @endif

                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions Block</th>
                </tr>
            </thead>

            <tbody class="bg-white divide-y divide-gray-200 text-sm">
                @forelse($leaveRequests as $requestItem)

                <tr wire:key="{{ $activeTabWorkflowState === 'rejected' ? 'rejected-log-' . ($requestItem->log_id ?? $requestItem->id) : 'request-row-' . $requestItem->id }}">

                    {{-- Req ID --}}
                    <td class="px-6 py-4 whitespace-nowrap font-mono font-medium text-gray-900">
                        {{ $requestItem->request_number }}
                    </td>

                    {{-- Employee Details --}}
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">
                            {{ $requestItem->employee && $requestItem->employee->user ? $requestItem->employee->user->name : 'Unknown' }}
                        </div>
                    </td>

                    {{-- Leave Type --}}
                    <td class="px-6 py-4 whitespace-nowrap text-gray-500">
                        {{ $requestItem->applyLeaveTypes->whereNotNull('leave_type_id')->first()?->leaveType->name ?? 'No-Pay Only' }}
                    </td>

                    {{-- Duration Bounds --}}
                    <td class="px-6 py-4 whitespace-nowrap text-gray-500">
                        {{ $requestItem->start_date }} to {{ $requestItem->end_date }}
                        @if($requestItem->is_half_day)
                        <span class="ml-1 px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Half-Day ({{ $requestItem->leave_shift }})</span>
                        @endif
                    </td>

                    {{-- Net Days --}}
                    <td class="px-6 py-4 text-center text-gray-500 whitespace-nowrap">
                        {{ $requestItem->total_days }}
                    </td>

                    {{-- Dynamic Reasons Block --}}
                    @if($activeTabWorkflowState === 'rejected')
                    <td class="px-6 py-4 whitespace-nowrap font-medium text-red-600 max-w-xs truncate"
                        title="{{ $requestItem->unique_rejection_remarks ?? 'No reason provided' }}">

                        {{ $requestItem->unique_rejection_remarks ?? 'No reason provided' }}

                        <span class="block text-[10px] text-gray-400 font-normal mt-0.5">
                            ({{ $requestItem->unique_rejection_action ?? 'Rejected' }})
                        </span>
                    </td>
                    @elseif($activeTabWorkflowState === 'approved')

                    <td class="px-6 py-4 whitespace-nowrap text-gray-600 max-w-xs truncate"
                        title="{{ $requestItem->logs->first()?->remarks ?? $requestItem->reason }}">

                        {{ str_replace(
                        ['. Leave text finalized.', '. Leave txn finalized.', 'Split Balances Sync committed.', 'Metrics breakdown ->'], 
                        ['.', '.', '', ''], 
                        $requestItem->logs->first()?->remarks ?? $requestItem->reason
                    ) }}

                        <span class="block text-[10px] text-green-500 font-medium mt-0.5">
                            ({{ $requestItem->logs->first()?->action ?? 'Approved' }})
                        </span>
                    </td>
                    @else
                    <td class="px-6 py-4 text-gray-500 max-w-xs truncate" title="{{ $requestItem->reason }}">
                        {{ $requestItem->reason }}
                    </td>
                    @endif

                    {{-- Actions Block --}}
                    <td class="px-6 py-4 text-center whitespace-nowrap">
                        <div class="flex items-center justify-center space-x-2">
                            @can('open leave approval')
                            <button wire:click="viewLeaveApplicationDetails({{ $requestItem->id }})"
                                title="View Details"
                                class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-lg bg-green-100 text-green-800 hover:bg-green-200 transition focus:outline-none">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </button>
                            @endcan

                            @if(!in_array($activeTabWorkflowState, ['pending_supervisor', 'pending_hr']))
                            <span class="px-2 py-1 text-xs font-semibold rounded-full 
                            {{ ($activeTabWorkflowState === 'rejected' ? ($requestItem->unique_rejection_action ?? 'Rejected') : $requestItem->status) === 'Approved' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $activeTabWorkflowState === 'rejected' ? ($requestItem->unique_rejection_action ?? 'Rejected') : $requestItem->status }}
                            </span>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">No configuration records found mapping this operational state queue.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-5 bg-white p-3 rounded-lg border border-gray-100 shadow-2xs">
            {{ $leaveRequests->links() }}
        </div>
    </div>

    {{-- LEAVE DETAILS SUMMARY MODAL WINDOW OVERLAY --}}
    @if($isDetailsModalOpen && $selectedRequestForDetails)
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50 p-4" role="dialog" aria-modal="true">
        <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full p-6 border border-gray-200">

            {{-- Modal Top Navigation / Header Area --}}
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Leave Request Application Summary Core Overview</h3>

                <button type="button" wire:click="closeDetailsModal" class="text-gray-400 hover:text-gray-500 text-xl font-bold" aria-label="Close modal">&times;</button>
            </div>

            <div class="space-y-4">
                {{-- Metrics Breakdown Block Matrix Overview --}}
                <div class="grid grid-cols-2 gap-4 text-sm bg-gray-50 p-4 rounded-xl border border-gray-100 shadow-xs">
                    <div class="space-y-2">
                        <div>
                            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Applicant Employee</span>
                            <span class="text-sm font-semibold text-gray-800">{{ $selectedRequestForDetails->employee?->user?->name ?? 'N/A' }}</span>
                        </div>
                        <div>
                            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Total Requested Days</span>
                            <span class="px-2.5 py-0.5 bg-indigo-100 text-indigo-800 font-bold rounded-full text-xs inline-block mt-0.5">
                                {{ $selectedRequestForDetails->total_days }} Days Total
                            </span>
                        </div>
                        <div class="text-xs text-gray-500 pt-1">
                            <strong>Duration Bounds:</strong> {{ $selectedRequestForDetails->start_date }} to {{ $selectedRequestForDetails->end_date }}
                        </div>
                    </div>

                    {{-- Splitting Metrics Data Allocations Column --}}
                    <div class="border-l border-gray-200 pl-4 space-y-2">
                        <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Approved Leave Breakdown</span>

                        <div class="space-y-1.5 max-h-28 overflow-y-auto pr-1">
                            @forelse($selectedRequestForDetails->applyLeaveTypes as $splitItemRow)
                            <div class="flex justify-between items-center bg-white p-2 rounded-lg border border-gray-100 shadow-2xs">
                                <span class="text-xs font-medium text-gray-700">
                                    {{ $splitItemRow->leaveType->name ?? 'No-Pay Leave' }}
                                </span>
                                <span class="px-2 py-0.5 text-xs font-bold rounded-md {{ $splitItemRow->leave_type_id ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' }}">
                                    {{ $splitItemRow->days_count }} Days
                                </span>
                            </div>
                            @empty
                            <div class="flex justify-between items-center bg-white p-2 rounded-lg border border-gray-100">
                                <span class="text-xs font-medium text-gray-700">
                                    {{ $selectedRequestForDetails->leaveType->name ?? 'No-Pay Only' }}
                                </span>
                                <span class="px-2 py-0.5 text-xs font-bold bg-gray-100 text-gray-700 rounded-md">
                                    {{ $selectedRequestForDetails->total_days }} Days
                                </span>
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- Entitlement Live Ledger Data Structure View Grid Table --}}
                <div>
                    <h4 class="text-sm font-semibold text-gray-700 mb-2">Current Leave Year Entitlement Status</h4>
                    <table class="min-w-full divide-y divide-gray-200 text-xs border border-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-2 text-left font-medium text-gray-500">Leave Type</th>
                                <th class="px-3 py-2 text-center font-medium text-gray-500">Allocated</th>
                                <th class="px-3 py-2 text-center font-medium text-gray-500">Utilized</th>
                                <th class="px-3 py-2 text-center font-medium text-gray-500">Remaining Balance</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($selectedEmployeeBalances as $balanceRowItem)
                            <tr class="{{ $balanceRowItem['is_shortage'] ? 'bg-red-50 text-red-950 font-medium' : ($balanceRowItem['is_current_type'] ? 'bg-blue-50 font-medium text-blue-900' : '') }}">
                                <td class="px-3 py-2">{{ $balanceRowItem['leave_type_name'] }}</td>
                                <td class="px-3 py-2 text-center">{{ $balanceRowItem['allocated_days'] }}</td>
                                <td class="px-3 py-2 text-center {{ $balanceRowItem['is_shortage'] ? 'text-red-700' : 'text-red-600' }}">{{ $balanceRowItem['used_days'] }}</td>
                                <td class="px-3 py-2 text-center font-bold {{ $balanceRowItem['is_shortage'] ? 'text-red-700' : 'text-green-600' }}">{{ $balanceRowItem['computed_remaining'] }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-3 py-2 text-center text-gray-500 italic">No entitlement ledger entities mapped for this current profile calendar cycle.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>


                @if($noPayDaysCalculated > 0 && in_array($activeTabWorkflowState, ['pending_supervisor', 'pending_hr']))
                <div class="p-3 bg-orange-50 border-l-4 border-orange-500 text-orange-900 text-xs rounded shadow-sm">
                    <div class="font-bold flex items-center mb-1 text-sm text-orange-800">
                        <svg class="w-4 h-4 mr-1 text-orange-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        Attention: Leave Balance Exceeded!
                    </div>
                    This employee has a balance of only <span class="font-bold text-base text-orange-700">{{ $selectedRequestForDetails->total_days - $noPayDaysCalculated }}</span> days. If you approve this request, the excess <span class="font-bold text-base text-red-600 underline">{{ $noPayDaysCalculated }}</span> days will be automatically converted to <strong>No-Pay Leave</strong>.
                </div>
                @endif

                {{-- Initial User Stated Justification Reasons Display Segment --}}
                <div class="text-sm">
                    <strong>Stated Reason / Justification:</strong>
                    <p class="mt-1 p-2 bg-gray-50 border border-gray-100 rounded text-gray-600">{{ $selectedRequestForDetails->reason }}</p>
                </div>

                {{-- Secure Document Proof Auditing Interface Controller Section --}}
                @if($selectedRequestForDetails->attachment_path)
                <div class="p-3.5 bg-slate-50 border border-slate-200 rounded-xl flex items-center justify-between shadow-sm">
                    <div class="flex items-center space-x-2 text-slate-700">
                        <svg class="w-5 h-5 text-indigo-500 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                        </svg>
                        <div class="text-xs font-semibold text-gray-800">Verified Employee Evidence Attachment Available</div>
                    </div>
                    <button type="button" wire:click="openAttachmentPreviewWorkflow" class="px-3.5 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-medium shadow-sm transition-colors flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        Inspect Document
                    </button>
                </div>
                @endif

                {{-- APPLICATION LOG REMARK (APPROVED) SEGMENT --}}
                @if(!empty($modalRejectionRemark))
                <div class="p-4 bg-red-50 border border-red-200 rounded-xl shadow-xs my-3" wire:key="modal-remark-card-{{ $selectedRequestForDetails->id }}">
                    <div class="text-xs font-bold text-red-700 uppercase tracking-wider block mb-1">
                        Application Log Remark ({{ $modalRejectionAction ?? 'Log' }})
                    </div>


                    <div class="text-[11px] text-gray-500 mt-1 flex items-center gap-1">
                        <span class="font-medium text-red-600 bg-red-100 px-1.5 py-0.5 rounded-sm">
                            Status: {{ $modalRejectionAction ?? 'Processed' }}
                        </span>
                        <span>• Date:
                            {{ $modalRejectionDate ? (\Carbon\Carbon::parse($modalRejectionDate)->format('Y-m-d h:i A')) : 'N/A' }}
                        </span>
                    </div>
                </div>
                @endif
            </div>

            @if (session()->has('panelErrorFeedback'))
            <div class="mt-4 p-3 bg-red-50 border-l-4 border-red-500 text-red-700 text-xs font-semibold rounded">
                {{ session('panelErrorFeedback') }}
            </div>
            @endif

            {{-- Modal Interactive Action Button Controls Interface Area Footer --}}
            <div class="mt-6 flex justify-between items-center">
                <button type="button" wire:click="closeDetailsModal" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded shadow-sm">
                    Dismiss Overlay
                </button>

                @if(in_array($activeTabWorkflowState, ['pending_supervisor', 'pending_hr']))
                <div class="flex space-x-2">
                    <button type="button" wire:click="initiateRejectionProcess({{ $selectedRequestForDetails->id }})" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded shadow-sm">
                        Reject Application
                    </button>
                    <button type="button" wire:click="approveLeaveApplicationRequest({{ $selectedRequestForDetails->id }})" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded shadow-sm">
                        Approve Application
                    </button>
                </div>
                @endif
            </div>

        </div>
    </div>
    @endif

    {{-- REJECTION REASON MODAL WINDOW --}}
    @if($isRejectionModalWindowOpen)
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6 border border-gray-200">
            <h3 class="text-lg font-medium text-gray-900 mb-2">State Rejection Justification</h3>
            <p class="text-sm text-gray-500 mb-4">Please input a explicit justification statement below regarding this processing rejection choice.</p>

            <form wire:submit.prevent="executeRejectionSubmission" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Rejection Reason Remarks</label>
                    <textarea wire:model="rejectionReasonInputText" rows="4" placeholder="Input the explicit operational reason here..." class="w-full border-gray-300 rounded shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500"></textarea>
                    @error('rejectionReasonInputText') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div class="flex justify-end space-x-2">
                    <button type="button" wire:click="dismissRejectionModal" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded shadow-sm">
                        Cancel Action
                    </button>
                    <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded shadow-sm">
                        Confirm Rejection
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- SUB-MODAL WINDOW: ATTACHMENT SECURE INLINE PREVIEW OVERLAY --}}
    @if($isAttachmentPreviewModalOpen && $currentActiveAttachmentSecureUrl)
    <div class="fixed inset-0 z-[60] overflow-y-auto" wire:key="secure-attachment-preview-submodal-frame">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            {{-- Dimmed background overlay covering the primary layout screen --}}
            <div class="fixed inset-0 transition-opacity bg-slate-900 bg-opacity-60 backdrop-blur-xs" wire:click="closeAttachmentPreviewWorkflow"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            {{-- Main Structural Dialog Box Frame View Wrapper --}}
            <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full border border-slate-100">

                {{-- Header Actions Control Deck Panel --}}
                <div class="bg-slate-50 px-6 py-4 border-b border-slate-100 flex justify-between items-center">
                    <div class="flex items-center space-x-2">
                        <span class="p-1.5 bg-indigo-50 rounded-md text-indigo-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 01-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 011.5.124m7.5 10.376A8.965 8.965 0 0012 12.75a8.965 8.965 0 00-3.75 1.125m7.5 3.375c0-.621-.504-1.125-1.125-1.125H5.75m7.438-3.51a11.504 11.504 0 01-7.438 0M15.75 9V5.25c0-.621-.504-1.125-1.125-1.125h-9.75a1.125 1.125 0 00-1.125 1.125v.375m16.5 0h-3.75a1.125 1.125 0 01-1.125-1.125V1.125m3.75 0H18M12 9A3 3 0 1012 3a3 3 0 000 6z" />
                            </svg>
                        </span>
                        <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wide">Evidence Attachment Document Viewer Panel</h3>
                    </div>
                    <button type="button" wire:click="closeAttachmentPreviewWorkflow" class="text-slate-400 hover:text-slate-600 transition-colors text-2xl font-semibold focus:outline-none">&times;</button>
                </div>

                {{-- Interactive Document Container Frame Core Layer --}}
                <div class="bg-white p-4">
                    {{-- Interactive Document Container Frame Core Layer  --}}
                    <div class="bg-white p-4">

                        @if(in_array($currentActiveAttachmentExtension, ['jpg', 'jpeg', 'png', 'webp']))
                        {{-- Graphic Resource Image Preview Node --}}
                        <div class="w-full flex justify-center items-center overflow-y-auto max-h-[550px] bg-slate-50 border border-slate-100 rounded-lg p-2">
                            <img src="{{ $currentActiveAttachmentSecureUrl }}" alt="Leave Application Document Token Preview" class="max-w-full h-auto rounded-md shadow-sm object-contain">
                        </div>
                        @else
                        {{-- Statutory Portable Document Format File Embedded Pipeline Frame --}}
                        <div class="w-full bg-slate-50 border border-slate-100 rounded-lg overflow-hidden shadow-inner">
                            <iframe src="{{ $currentActiveAttachmentSecureUrl }}" class="w-full h-[520px] rounded-lg border-0" shadow-sm></iframe>
                        </div>
                        @endif
                    </div>

                </div>

                {{-- Base Control Footer Deck Workspace Actions --}}
                <div class="bg-slate-50 px-6 py-3 flex justify-end border-t border-slate-100">
                    <button type="button" wire:click="closeAttachmentPreviewWorkflow" class="px-4 py-2 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 text-xs font-semibold rounded-lg shadow-xs transition-all">
                        Return to Requests Details
                    </button>
                </div>

            </div>
        </div>
    </div>
    @endif

</div>