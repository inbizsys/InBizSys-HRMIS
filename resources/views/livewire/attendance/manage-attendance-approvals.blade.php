<div class="max-w-8xl mx-auto px-4 sm:px-6 lg:px-8 py-8 font-sans">

    <!-- Header -->
    <div class="mb-6 bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs">
        <h1 class="text-xl font-black text-slate-900 tracking-tight">Attendance & Break Edit Approvals</h1>
        <p class="text-xs text-slate-500 mt-1">Review and manage pending edit and work-on-leave requests in real-time.</p>
    </div>

    <!-- Flash Messages -->
    @if (session()->has('successMessage'))
        <div class="mb-4 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold rounded-2xl flex items-center gap-2">
            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            <span>{{ session('successMessage') }}</span>
        </div>
    @endif

    @if (session()->has('errorMessage'))
        <div class="mb-4 p-4 bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold rounded-2xl flex items-center gap-2">
            <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
            <span>{{ session('errorMessage') }}</span>
        </div>
    @endif

    <!-- Unified Edit Requests Table -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-4 border-b border-slate-100 bg-slate-50/50">
            <h3 class="font-bold text-slate-800 text-sm">Pending Approval Requests</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-700 font-bold uppercase tracking-wider text-[11px] border-b border-slate-200">
                    <tr>
                        <th class="p-3.5">Employee</th>
                        <th class="p-3.5">Details (Date / Times)</th>
                        <th class="p-3.5">Reason & Notice</th>
                        <th class="p-3.5 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">

                    @foreach ($attendanceApprovals as $app)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="p-3.5 font-bold text-slate-900">
                                {{ $app->employee?->first_name }} {{ $app->employee?->last_name }}
                                <span class="block text-[10px] text-slate-400 font-normal">
                                    {{ $app->employee?->job_position ?? 'Employee' }}
                                </span>
                            </td>
                            <td class="p-3.5 space-y-0.5">
                                @if ($app->requested_in_time || $app->requested_out_time || $app->requested_date)
                                    <div class="text-slate-700 font-semibold">Date: <span class="text-blue-600">{{ $app->requested_date }}</span></div>
                                    <div class="font-mono text-[11px]">
                                        In: <span class="text-emerald-700 font-bold">{{ $app->requested_in_time ? \Carbon\Carbon::parse($app->requested_in_time)->format('h:i A') : '-' }}</span>
                                        |
                                        Out: <span class="text-rose-700 font-bold">{{ $app->requested_out_time ? \Carbon\Carbon::parse($app->requested_out_time)->format('h:i A') : ($app->attendance?->out_time ? \Carbon\Carbon::parse($app->attendance->out_time)->format('h:i A') : 'On Duty') }}</span>
                                    </div>
                                @else
                                    <div class="text-slate-700 font-semibold">Break Adjustment Only</div>
                                @endif

                                @if ($app->breakApprovals && $app->breakApprovals->count() > 0)
                                    <div class="text-[10px] text-amber-700 font-bold mt-1 bg-amber-50 px-2 py-0.5 rounded-md inline-block border border-amber-200">
                                         {{ $app->breakApprovals->count() }} Break(s) Modified
                                    </div>
                                @endif
                            </td>
                            <td class="p-3.5">
                                <span class="italic text-slate-700 block">{{ $app->reason }}</span>
                                @if ($app->is_on_duty)
                                    <span class="inline-flex items-center gap-1 text-[10px] font-bold text-amber-700 bg-amber-50 border border-amber-200 px-2 py-0.5 rounded-md mt-1">
                                        <svg class="w-3 h-3 text-amber-500 animate-pulse" fill="currentColor" viewBox="0 0 8 8">
                                            <circle cx="4" cy="4" r="3" />
                                        </svg>
                                        Employee is currently on duty - Approve only after clock-out
                                    </span>
                                @endif
                            </td>
                            <td class="p-3.5 text-center whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5">
                                    <button type="button" wire:click="openApprovalDetails({{ $app->id }})"
    title="View Details"
    class="px-2 py-1 inline-flex text-xs font-semibold rounded-lg bg-green-100 text-green-800 hover:bg-green-200 transition cursor-pointer">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178z" />
        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
    </svg>
</button>

                                    @if ($app->is_on_duty)
                                        <button type="button" disabled
                                            title="Employee is currently on duty. Approval can only be processed after clock-out."
                                            class="px-3 py-1 bg-slate-200 text-slate-400 font-bold rounded-xl text-[11px] cursor-not-allowed">
                                            Approve
                                        </button>
                                    @else
                                        <button type="button"
                                            wire:click="confirmAction({{ $app->id }}, 'attendance', 'approve')"
                                            class="px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-[11px] cursor-pointer shadow-xs transition">
                                            Approve
                                        </button>
                                    @endif

                                    <button type="button"
                                        wire:click="confirmAction({{ $app->id }}, 'attendance', 'reject')"
                                        class="px-3 py-1 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl text-[11px] cursor-pointer shadow-xs transition">
                                        Reject
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach

                    @if ($attendanceApprovals->isEmpty())
                        <tr>
                            <td colspan="4" class="p-8 text-center text-slate-400 font-medium">
                                No pending approval requests found.
                            </td>
                        </tr>
                    @endif

                </tbody>
            </table>
        </div>
    </div>

    <!-- View Details Modal -->
    @if ($isViewModalOpen && $selectedApproval)
        <div class="fixed inset-0 bg-slate-950/60 backdrop-blur-xs z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl p-6 max-w-xl w-full shadow-2xl border border-slate-200 space-y-5 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between border-b pb-3">
                    <h3 class="font-black text-slate-900 text-sm">Review Approval Request Details</h3>
                    <button type="button" wire:click="closeApprovalDetails"
                        class="text-slate-400 hover:text-slate-600 font-bold text-lg cursor-pointer">&times;</button>
                </div>

                <div class="bg-slate-50 p-3.5 rounded-2xl text-xs space-y-1">
                    <div><span class="font-bold text-slate-500">Employee:</span> <span class="font-extrabold text-slate-800">{{ $selectedApproval->employee?->first_name }} {{ $selectedApproval->employee?->last_name }}</span></div>
                    <div><span class="font-bold text-slate-500">Reason Provided:</span> <span class="italic text-slate-700">"{{ $selectedApproval->reason }}"</span></div>
                </div>

                @if ($selectedApproval->requested_in_time || $selectedApproval->requested_out_time || $selectedApproval->requested_date)
                    <div class="space-y-2">
                        <h4 class="font-bold text-slate-700 text-xs uppercase tracking-wider">Attendance Changes</h4>
                        <div class="grid grid-cols-2 gap-4 text-xs">
                            <div class="bg-rose-50/50 border border-rose-100 p-4 rounded-2xl space-y-2">
                                <h5 class="font-bold text-rose-800 uppercase text-[10px] tracking-wider border-b border-rose-200 pb-1">Current Attendance</h5>
                                <div><span class="text-slate-400 block text-[10px]">Date</span> <span class="font-bold text-slate-800">{{ $selectedApproval->attendance?->date }}</span></div>
                                <div><span class="text-slate-400 block text-[10px]">In Time</span> <span class="font-mono font-bold text-slate-800">{{ $selectedApproval->attendance?->in_time ?? '--:--' }}</span></div>
                                <div><span class="text-slate-400 block text-[10px]">Out Time</span> <span class="font-mono font-bold text-slate-800">{{ $selectedApproval->attendance?->out_time ?? 'Currently On Duty' }}</span></div>
                            </div>

                            <div class="bg-emerald-50/50 border border-emerald-100 p-4 rounded-2xl space-y-2">
                                <h5 class="font-bold text-emerald-800 uppercase text-[10px] tracking-wider border-b border-emerald-200 pb-1">Target / Requested</h5>
                                <div><span class="text-slate-400 block text-[10px]">New Date</span> <span class="font-bold text-emerald-700">{{ $selectedApproval->requested_date }}</span></div>
                                <div><span class="text-slate-400 block text-[10px]">In Time</span> <span class="font-mono font-bold text-emerald-700">{{ $selectedApproval->requested_in_time ?? 'Unchanged' }}</span></div>
                                <div><span class="text-slate-400 block text-[10px]">Out Time</span> <span class="font-mono font-bold text-emerald-700">{{ $selectedApproval->requested_out_time ?? ($selectedApproval->attendance?->out_time ?? 'Pending Clock-Out') }}</span></div>
                            </div>
                        </div>
                    </div>
                @endif

                @if ($selectedApproval->breakApprovals && $selectedApproval->breakApprovals->count() > 0)
                    <div class="space-y-2 pt-2 border-t">
                        <h4 class="font-bold text-slate-700 text-xs uppercase tracking-wider">Break Adjustments ({{ $selectedApproval->breakApprovals->count() }})</h4>
                        
                        @foreach ($selectedApproval->breakApprovals as $bApp)
                            <div class="space-y-1">
                                <div class="font-bold text-amber-900 text-[11px]">
                                    👉 {{ ucfirst($bApp->attendanceBreak?->breakType?->name ?? 'Standard') }} Break Modification
                                </div>
                                <div class="grid grid-cols-2 gap-4 text-xs">
                                    <div class="bg-rose-50/50 border border-rose-100 p-3 rounded-2xl space-y-1">
                                        <h5 class="font-bold text-rose-800 uppercase text-[9px] tracking-wider border-b border-rose-200 pb-0.5">Original Break</h5>
                                        <div><span class="text-slate-400 text-[10px]">Start:</span> <span class="font-mono font-bold text-slate-800">{{ $bApp->attendanceBreak?->break_start ?? '--:--' }}</span></div>
                                        <div><span class="text-slate-400 text-[10px]">End:</span> <span class="font-mono font-bold text-slate-800">{{ $bApp->attendanceBreak?->break_end ?? '--:--' }}</span></div>
                                    </div>

                                    <div class="bg-emerald-50/50 border border-emerald-100 p-3 rounded-2xl space-y-1">
                                        <h5 class="font-bold text-emerald-800 uppercase text-[9px] tracking-wider border-b border-emerald-200 pb-0.5">Requested Break</h5>
                                        <div><span class="text-slate-400 text-[10px]">New Start:</span> <span class="font-mono font-bold text-emerald-700">{{ $bApp->requested_start_time ? date('H:i:s', strtotime($bApp->requested_start_time)) : 'Unchanged' }}</span></div>
                                        <div><span class="text-slate-400 text-[10px]">New End:</span> <span class="font-mono font-bold text-emerald-700">{{ $bApp->requested_end_time ? date('H:i:s', strtotime($bApp->requested_end_time)) : 'Unchanged' }}</span></div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" wire:click="closeApprovalDetails"
                        class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs cursor-pointer">
                        Close
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Confirmation Modal -->
    @if ($isConfirmModalOpen)
        <div class="fixed inset-0 bg-slate-950/60 backdrop-blur-xs z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl p-6 max-w-sm w-full shadow-2xl border border-slate-200 space-y-4 text-center">
                <div class="w-12 h-12 rounded-full mx-auto flex items-center justify-center {{ $confirmActionType === 'approve' ? 'bg-emerald-100 text-emerald-600' : 'bg-rose-100 text-rose-600' }}">
                    @if ($confirmActionType === 'approve')
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                    @else
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    @endif
                </div>

                <div>
                    <h3 class="font-black text-slate-900 text-sm capitalize">Are you sure?</h3>
                    <p class="text-xs text-slate-500 mt-1">
                        Do you want to <span class="font-bold {{ $confirmActionType === 'approve' ? 'text-emerald-600' : 'text-rose-600' }}">{{ $confirmActionType }}</span> this request?
                    </p>
                </div>

                <div class="flex justify-center gap-2 pt-2">
                    <button type="button" wire:click="closeConfirmationModal"
                        class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs cursor-pointer">
                        Cancel
                    </button>
                    <button type="button" wire:click="executeAction"
                        class="px-4 py-2 {{ $confirmActionType === 'approve' ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-rose-600 hover:bg-rose-700' }} text-white font-bold rounded-xl text-xs cursor-pointer">
                        Yes, Confirm
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>