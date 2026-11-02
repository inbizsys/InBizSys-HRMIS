<div class="p-6 space-y-6 bg-slate-50 min-h-screen">

    @if (session()->has('errorMessage'))
        <div class="p-4 bg-rose-50 border border-rose-200 text-rose-700 rounded-2xl text-xs font-bold flex items-center justify-between">
            <span>⚠ {{ session('errorMessage') }}</span>
        </div>
    @endif

    <!-- 1. FILTERS & SEARCH BAR -->
    <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-200 space-y-4">
        <div class="flex flex-wrap gap-3 items-center justify-between">

            <h1 class="text-xl font-black text-slate-900 tracking-tight">All Attendance Records</h1>

            <div class="flex flex-wrap gap-3 items-center w-full lg:w-auto">
                <!-- Employee Search -->
                <input type="text" wire:model.live.debounce.300ms="searchQuery" placeholder="Search Employee / ID..."
                    class="px-3.5 py-2 border border-slate-300 rounded-xl text-xs w-full sm:w-80 focus:ring-2 focus:ring-blue-500 outline-none">

                <!-- Department Filter -->
                <select wire:model.live="selectedDepartmentId"
                    class=" w-full sm:w-64 px-3.5 py-2 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="">All Departments</option>
                    @foreach ($departmentList as $department)
                        <option value="{{ $department->id }}">{{ $department->name }}</option>
                    @endforeach
                </select>

                <!-- Status Filter -->
                <select wire:model.live="selectedStatus"
                    class="px-3.5 py-2 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="">All Statuses</option>
                    <option value="present">Present (Full Day)</option>
                    <option value="late">Late Arrivals</option>
                    <option value="half_day">Half Day Present (Morning / Evening)</option>
                    <option value="half_day_absent">Half Day Absent</option>
                    <option value="holiday_present">Holiday Work</option>
                    <option value="weekend_present">Weekend Work</option>
                    <option value="absent">Full Day Absent</option>
                    <option value="leave">Approved Leave</option>
                    <option value="pending_approval">Pending Approval</option>
                </select>
            </div>

            <!-- Date Range Pickers -->
            <div class="flex items-center gap-2 w-full lg:w-auto">
                <input type="date" wire:model.live="startDate"
                    class="px-3 py-1.5 border border-slate-300 rounded-xl text-xs font-semibold text-slate-700 outline-none">
                <span class="text-xs text-slate-400 font-bold">to</span>
                <input type="date" wire:model.live="endDate"
                    class="px-3 py-1.5 border border-slate-300 rounded-xl text-xs font-semibold text-slate-700 outline-none">
            </div>

        </div>
    </div>

    <!-- 2. SUMMARY METRICS CARDS -->
    <div class="grid grid-cols-2 md:grid-cols-7 gap-3">
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Total Staff</span>
            <span class="text-xl font-extrabold text-slate-800">{{ $attendanceMetrics['total_employees'] }}</span>
        </div>

        <div class="bg-emerald-50/60 p-4 rounded-2xl border border-emerald-200 shadow-sm">
            <span class="text-[10px] font-bold text-emerald-600 uppercase tracking-wider block">Present</span>
            <span class="text-xl font-extrabold text-emerald-700">{{ $attendanceMetrics['present_count'] }}</span>
        </div>

        <div class="bg-rose-50/60 p-4 rounded-2xl border border-slate-200 shadow-sm">
            <span class="text-[10px] font-bold text-rose-600 uppercase tracking-wider block">Full Day Absent</span>
            <span class="text-xl font-extrabold text-rose-700">{{ $attendanceMetrics['absent_count'] }}</span>
        </div>

        <div class="bg-orange-50/60 p-4 rounded-2xl border border-orange-200 shadow-sm">
            <span class="text-[10px] font-bold text-orange-600 uppercase tracking-wider block">Half Day Absent</span>
            <span
                class="text-xl font-extrabold text-orange-700">{{ $attendanceMetrics['half_day_absent_count'] ?? 0 }}</span>
        </div>

        <div class="bg-blue-50/60 p-4 rounded-2xl border border-blue-200 shadow-sm">
            <span class="text-[10px] font-bold text-blue-600 uppercase tracking-wider block">On Leave</span>
            <span class="text-xl font-extrabold text-blue-700">{{ $attendanceMetrics['leave_count'] }}</span>
        </div>

        <div class="bg-amber-50/60 p-4 rounded-2xl border border-amber-200 shadow-sm">
            <span class="text-[10px] font-bold text-amber-600 uppercase tracking-wider block">Late Arrivals</span>
            <span class="text-xl font-extrabold text-amber-700">{{ $attendanceMetrics['late_count'] }}</span>
        </div>

        <div class="bg-purple-50/60 p-4 rounded-2xl border border-purple-200 shadow-sm">
            <span class="text-[10px] font-bold text-purple-600 uppercase tracking-wider block">Pending Edits</span>
            <span class="text-xl font-extrabold text-purple-700">{{ $attendanceMetrics['pending_edits'] }}</span>
        </div>
    </div>

    <!-- 3. MAIN ATTENDANCE TABLE STRUCTURE -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200 uppercase text-[10px] text-slate-400 font-bold">
                    <tr>
                        <th class="p-4">Employee Details</th>
                        <th class="p-4">Date</th>
                        <th class="p-4">Shift</th>
                        <th class="p-4">Punch In / Out</th>
                        <th class="p-4">Worked / OT Hours</th>
                        <th class="p-4">Status</th>
                        <th class="p-4 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($attendanceRecords as $record)
                        <tr class="hover:bg-slate-50/80 transition-colors">

                            <td class="p-4">
                                <span class="font-bold text-slate-800 block">{{ $record->employee?->first_name }}
                                    {{ $record->employee?->last_name }}</span>
                                <div class="flex items-center gap-2 mt-0.5">
                                    <span class="text-[10px] font-bold text-slate-400">ID:
                                        {{ $record->employee?->employee_id }}</span>
                                    <span
                                        class="text-[10px] px-2 py-0.5 bg-slate-100 rounded-md text-slate-600 font-semibold">{{ $record->employee?->department?->name ?? 'N/A' }}</span>
                                </div>
                            </td>

                            <td class="p-4 font-semibold text-slate-700">{{ $record->display_date }}</td>

                            <td class="p-4 font-semibold text-slate-600">
                                <span
                                    class="px-2 py-1 bg-slate-100 border border-slate-200 rounded-lg text-[10px] font-bold text-slate-700">
                                    {{ $record->display_shift }}
                                </span>
                            </td>

                            <td class="p-4">
                                <div class="space-y-0.5">
                                    <div class="flex items-center gap-1">
                                        <span class="text-slate-400 text-[10px]">In:</span>
                                        <span class="font-bold text-slate-800">{{ $record->display_in_time }}</span>
                                        @if ($record->display_late_mins > 0)
                                            <span
                                                class="ml-1 px-1.5 py-0.5 bg-amber-100 text-amber-800 rounded-md text-[9px] font-bold">Late
                                                {{ $record->display_late_mins }}m</span>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <span class="text-slate-400 text-[10px]">Out:</span>
                                        <span class="font-bold text-slate-800">{{ $record->display_out_time }}</span>
                                    </div>
                                </div>
                            </td>

                            <td class="p-4">
                                <span class="font-bold text-slate-800 block">{{ $record->display_work_hours }}
                                    hrs</span>
                                @if ($record->display_ot_hours > 0)
                                    <span class="text-[10px] font-bold text-emerald-600">OT:
                                        +{{ $record->display_ot_hours }} hrs</span>
                                @else
                                    <span class="text-[10px] text-slate-400">OT: 0.00</span>
                                @endif
                            </td>

                            <!-- Status Badge Column -->
                            <td class="p-4">
                                <div class="flex flex-wrap items-center gap-1.5">
                                    @if ($record->status === 'holiday_present')
                                        <span
                                            class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-teal-100 text-teal-800 border border-teal-200">Holiday
                                            Work</span>
                                    @elseif ($record->status === 'weekend_present')
                                        <span
                                            class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-cyan-100 text-cyan-800 border border-cyan-200">Weekend
                                            Work</span>
                                    @elseif ($record->status === 'leave')
                                        <span
                                            class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800 border border-blue-200">Leave</span>
                                    @elseif ($record->status === 'present')
                                        <span
                                            class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">Present</span>
                                    @elseif ($record->status === 'late')
                                        <span
                                            class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">Late
                                            In</span>
                                    @elseif ($record->status === 'absent')
                                        <span
                                            class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-200">Absent</span>
                                    @else
                                        <span
                                            class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-800 border border-slate-200">
                                            {{ ucfirst(str_replace('_', ' ', $record->status ?? 'Unknown')) }}
                                        </span>
                                    @endif

                                    @if ($record->is_edited)
                                        <span
                                            class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                            Edited
                                        </span>
                                    @endif

                                    @if ($record->has_pending)
                                        <span
                                            class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-purple-100 text-purple-800 border border-purple-200">Pending
                                            Edit</span>
                                    @endif
                                </div>
                            </td>

                            <td class="p-3.5 whitespace-nowrap text-center">
                                <div class="inline-flex items-center justify-center gap-1.5">
                                    <button type="button" wire:click="viewAttendanceRecord({{ $record->id }})"
                                        title="View Details"
                                        class="px-2 py-1 inline-flex text-xs font-semibold rounded-lg bg-green-100 text-green-800 hover:bg-green-200 transition cursor-pointer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178z" />
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                    </button>

                                    @if ($record->has_pending)
                                        <span
                                            class="px-2.5 py-1 bg-amber-100 text-amber-800 font-bold rounded-lg text-[10px]">
                                            Pending Approval
                                        </span>
                                    @else
                                        <span class="text-slate-300 select-none">|</span>
                                        <button type="button" wire:click="openEditModal({{ $record->id }})"
                                            title="Edit Attendance"
                                            class="px-2 py-1 inline-flex text-xs font-semibold rounded-lg bg-blue-100 text-blue-800 hover:bg-blue-200 transition cursor-pointer">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                                class="w-4 h-4">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-slate-400 font-semibold">No attendance
                                records found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-100">
            {{ $attendanceRecords->links() }}
        </div>
    </div>

    <!-- ==================== VIEW DETAILS MODAL ==================== -->
    @if ($isViewRecordModalOpen && $selectedRecordForView)
        @teleport('body')
            <div class="fixed inset-0 bg-slate-950/60 backdrop-blur-xs z-50 flex items-center justify-center p-4">
                <div
                    class="bg-white rounded-3xl p-6 max-w-lg w-full shadow-2xl border border-slate-200 space-y-4 max-h-[90vh] overflow-y-auto">

                    <div class="flex items-center justify-between border-b pb-3 border-slate-100">
                        <div>
                            <h3 class="font-bold text-slate-900 text-sm">Attendance Summary Details</h3>
                            <p class="text-[11px] text-slate-400 font-semibold mt-0.5">
                                {{ $selectedRecordForView->employee?->first_name }}
                                {{ $selectedRecordForView->employee?->last_name }}
                                <span
                                    class="text-slate-500 font-bold">({{ $selectedRecordForView->employee?->employee_id ?? 'EMP' }})</span>
                            </p>
                        </div>
                        <button type="button" wire:click="$set('isViewRecordModalOpen', false)"
                            class="text-slate-400 hover:text-slate-600 font-bold text-lg cursor-pointer">&times;</button>
                    </div>

                    <div class="grid grid-cols-2 gap-3 text-xs">
                        <div class="bg-slate-50 p-3 rounded-xl border border-slate-100">
                            <span class="text-slate-400 block font-bold text-[10px] uppercase">Attendance Date</span>
                            <span class="font-bold text-slate-800">
                                {{ \Carbon\Carbon::parse($selectedRecordForView->date)->format($globalCalendarFormat ?? 'd/m/Y') }}
                            </span>
                        </div>
                        <div class="bg-slate-50 p-3 rounded-xl border border-slate-100">
                            <span class="text-slate-400 block font-bold text-[10px] uppercase">Assigned Shift</span>
                            <span class="font-bold text-slate-800">
                                {{ $selectedRecordForView->shift?->name ?? 'Default Shift' }}
                            </span>
                        </div>
                        <div class="bg-emerald-50/70 p-3 rounded-xl border border-emerald-100">
                            <span class="text-emerald-700 block font-bold text-[10px] uppercase">Punch In</span>
                            <span class="font-mono font-bold text-emerald-900">
                                {{ !empty($selectedRecordForView->in_time) ? \Carbon\Carbon::parse($selectedRecordForView->in_time)->format('h:i A') : '--:--' }}
                            </span>
                        </div>
                        <div class="bg-blue-50/70 p-3 rounded-xl border border-blue-100">
                            <span class="text-blue-700 block font-bold text-[10px] uppercase">Punch Out</span>
                            <span class="font-mono font-bold text-blue-900">
                                {{ !empty($selectedRecordForView->out_time) ? \Carbon\Carbon::parse($selectedRecordForView->out_time)->format('h:i A') : '--:--' }}
                            </span>
                        </div>
                    </div>

                    <div
                        class="bg-slate-50 p-3 rounded-2xl border border-slate-200/80 flex items-center justify-between text-xs">
                        <span class="text-slate-500 font-bold uppercase text-[10px]">Current Status</span>
                        <div>
                            @if ($selectedRecordForView->status === 'holiday_present')
                                <span
                                    class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-teal-100 text-teal-800">Holiday
                                    Work</span>
                            @elseif ($selectedRecordForView->status === 'weekend_present')
                                <span
                                    class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-cyan-100 text-cyan-800">Weekend
                                    Work</span>
                            @elseif ($selectedRecordForView->status === 'present')
                                <span
                                    class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800">Present</span>
                            @elseif ($selectedRecordForView->status === 'late')
                                <span
                                    class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800">Late
                                    In</span>
                            @elseif ($selectedRecordForView->status === 'absent')
                                <span
                                    class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-100 text-rose-800">Absent
                                    (No Pay)</span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-slate-200 text-slate-700">
                                    {{ ucfirst(str_replace('_', ' ', $selectedRecordForView->status ?? 'Unknown')) }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- ADMIN EDIT & REASON CARD -->
                    @if (!empty($selectedRecordForView->reason))
                        <div class="bg-amber-50/80 p-3.5 rounded-2xl border border-amber-200/80 space-y-1">
                            <div class="flex items-center justify-between">
                                <span
                                    class="text-amber-900 font-bold text-xs uppercase tracking-wider flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                                    Manual Adjustment Notice
                                </span>
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-200/60 text-amber-900">
                                    Admin Edited
                                </span>
                            </div>
                            <div class="pt-1 text-xs">
                                <span class="text-slate-400 font-bold text-[10px] uppercase block">Reason for
                                    Adjustment</span>
                                <p
                                    class="text-slate-800 font-medium mt-0.5 leading-relaxed bg-white/70 p-2 rounded-xl border border-amber-100">
                                    {{ $selectedRecordForView->reason }}
                                </p>
                            </div>
                        </div>
                    @endif

                    <div class="flex justify-end pt-2 border-t border-slate-100">
                        <button type="button" wire:click="$set('isViewRecordModalOpen', false)"
                            class="px-5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs cursor-pointer transition">
                            Close
                        </button>
                    </div>

                </div>
            </div>
        @endteleport
    @endif

    <!-- ==================== EDIT MODAL ==================== -->
    @if ($isEditModalOpen)
        @teleport('body')
            <div class="fixed inset-0 bg-slate-950/60 backdrop-blur-xs z-40 flex items-center justify-center p-4">
                <div
                    class="bg-white rounded-3xl p-6 max-w-lg w-full shadow-2xl border border-slate-200 space-y-4 max-h-[90vh] overflow-y-auto">

                    <div class="flex items-center justify-between border-b pb-3 border-slate-100">
                        <div>
                            <h3 class="font-bold text-slate-900 text-sm">Direct Attendance & Break Edit</h3>
                            <p class="text-[11px] text-slate-400 font-semibold">{{ $editEmployeeName }}</p>
                        </div>
                        <button type="button" wire:click="closeModals"
                            class="text-slate-400 hover:text-slate-600 font-bold text-xl cursor-pointer">&times;</button>
                    </div>

                    <div
                        class="bg-slate-50 p-2.5 rounded-xl border border-slate-200 text-xs flex justify-between items-center">
                        <span class="text-slate-400 font-bold uppercase text-[10px]">Attendance Date</span>
                        <span
                            class="font-bold text-slate-800">{{ \Carbon\Carbon::parse($editDate)->format($globalCalendarFormat ?? 'd/m/Y') }}</span>
                    </div>

                    <div class="grid grid-cols-2 gap-3 text-xs">
                        <div>
                            <label class="block font-bold text-slate-600 mb-1">In Time</label>
                            <input type="time" step="1" wire:model="editInTime"
                                class="w-full p-2 border border-slate-300 rounded-xl outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-600 mb-1">Out Time</label>
                            <input type="time" step="1" wire:model="editOutTime"
                                class="w-full p-2 border border-slate-300 rounded-xl outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    <div class="border-t border-slate-100 pt-3 space-y-2">
                        <div class="flex items-center justify-between">
                            <h4 class="font-bold text-slate-800 text-xs">Manage Breaks</h4>
                            <button type="button" wire:click="addBreakRow"
                                class="px-3 py-1 bg-blue-50 text-blue-600 hover:bg-blue-100 font-bold rounded-lg text-xs transition flex items-center gap-1 cursor-pointer">
                                + Add Break
                            </button>
                        </div>

                        @if (count($editBreaks) > 0)
                            <div class="space-y-2 max-h-40 overflow-y-auto pr-1">
                                @foreach ($editBreaks as $index => $brk)
                                    <div
                                        class="grid grid-cols-12 gap-2 items-center bg-slate-50 p-2 rounded-xl border border-slate-200 text-xs">
                                        <div class="col-span-4">
                                            <select wire:model="editBreaks.{{ $index }}.break_type"
                                                class="w-full p-1.5 border border-slate-300 rounded-lg text-[11px] outline-none focus:ring-2 focus:ring-blue-500">
                                                <option value="">Select Type</option>
                                                @foreach ($breakTypes as $bType)
                                                    <option value="{{ $bType->code ?? $bType->name }}">
                                                        {{ ucfirst($bType->name) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-span-3">
                                            <input type="time" step="1"
                                                wire:model="editBreaks.{{ $index }}.break_start"
                                                class="w-full p-1.5 border border-slate-300 rounded-lg text-[11px] outline-none focus:ring-2 focus:ring-blue-500">
                                        </div>
                                        <div class="col-span-3">
                                            <input type="time" step="1"
                                                wire:model="editBreaks.{{ $index }}.break_end"
                                                class="w-full p-1.5 border border-slate-300 rounded-lg text-[11px] outline-none focus:ring-2 focus:ring-blue-500">
                                        </div>
                                        <div class="col-span-2 text-center">
                                            <button type="button" wire:click="removeBreakRow({{ $index }})"
                                                class="p-1 text-rose-500 hover:bg-rose-50 rounded-lg cursor-pointer"
                                                title="Remove Break">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="p-3 bg-slate-50 rounded-xl text-center border border-dashed border-slate-200">
                                <p class="text-slate-400 text-xs italic">No breaks recorded for this day.</p>
                            </div>
                        @endif
                    </div>

                    <div class="border-t border-slate-100 pt-2">
                        <label class="block font-bold text-slate-600 mb-1 text-xs">Reason for Manual Edit <span
                                class="text-rose-500">*</span></label>
                        <textarea wire:model="editReason" rows="2"
                            class="w-full p-2 border border-slate-300 rounded-xl text-xs outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="E.g., Adjusted punch timings..."></textarea>
                        @error('editReason')
                            <span class="text-rose-500 text-[10px] font-bold">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="pt-2 flex justify-end gap-2 border-t border-slate-100">
                        <button type="button" wire:click="closeModals"
                            class="px-4 py-2 bg-slate-100 text-slate-700 font-bold rounded-xl text-xs hover:bg-slate-200 transition">Cancel</button>
                        <button type="button" wire:click="prepareUpdateConfirmation"
                            class="px-4 py-2 bg-blue-600 text-white font-bold rounded-xl text-xs hover:bg-blue-700 transition">Update
                            Record</button>
                    </div>

                </div>
            </div>
        @endteleport
    @endif

    <!-- ==================== CONFIRMATION MODAL ==================== -->
    @if ($isConfirmModalOpen)
        @teleport('body')
            <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs z-50 flex items-center justify-center p-4">
                <div
                    class="bg-white rounded-3xl p-6 max-w-sm w-full shadow-2xl border border-slate-200 space-y-4 text-center">
                    <div
                        class="w-12 h-12 bg-amber-100 text-amber-600 rounded-full flex items-center justify-center mx-auto">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div>
                        <h4 class="font-bold text-slate-800 text-base">Confirm Attendance Update</h4>
                        <p class="text-xs text-slate-500 mt-1">Are you sure you want to directly update attendance for
                            <span class="font-bold text-slate-800">{{ $editEmployeeName }}</span>?</p>
                    </div>
                    <div class="flex items-center gap-2 pt-2">
                        <button type="button" wire:click="$set('isConfirmModalOpen', false)"
                            class="w-1/2 py-2.5 bg-slate-100 text-slate-700 font-bold rounded-xl text-xs hover:bg-slate-200 transition">Go
                            Back</button>
                        <button type="button" wire:click="confirmAndUpdateAttendance"
                            class="w-1/2 py-2.5 bg-emerald-600 text-white font-bold rounded-xl text-xs hover:bg-emerald-700 transition">Yes,
                            Confirm</button>
                    </div>
                </div>
            </div>
        @endteleport
    @endif
</div>
