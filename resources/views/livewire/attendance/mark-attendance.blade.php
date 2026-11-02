<div class="max-w-8xl mx-auto px-4 sm:px-6 lg:px-8 py-8 font-sans">

    <!-- Top Action Header -->
    <div
        class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6 bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs">
        <div>
            <h1 class="text-xl font-black text-slate-900 tracking-tight">My Attendance & Timesheet Log</h1>
            <p class="text-xs text-slate-500 mt-1">
                {{ $authenticatedEmployee?->first_name }} {{ $authenticatedEmployee?->last_name }} &bull;
                {{ $authenticatedEmployee?->job_position ?? 'Employee' }}
                ({{ $authenticatedEmployee?->employee_id ?? 'EMP' }})
            </p>
        </div>
        <!-- Date Filter Section -->
        <div
            class="bg-white p-4 mb-4 rounded-3xl border border-slate-200/80 shadow-xs flex flex-wrap items-center justify-between gap-4">
            <div class="flex flex-wrap items-center gap-3 text-xs">
                <span class="font-bold text-slate-700 flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    Filter by Date:
                </span>

                <div class="flex items-center gap-2">
                    <label class="text-slate-500 text-[11px] font-semibold">From:</label>
                    <input type="date" wire:model.live="filterDateFrom"
                        class="text-xs border border-slate-200 rounded-xl px-2.5 py-1.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-800">
                </div>

                <div class="flex items-center gap-2">
                    <label class="text-slate-500 text-[11px] font-semibold">To:</label>
                    <input type="date" wire:model.live="filterDateTo"
                        class="text-xs border border-slate-200 rounded-xl px-2.5 py-1.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none text-slate-800">
                </div>

                @if (!empty($filterDateFrom) || !empty($filterDateTo))
                    <button type="button" wire:click="resetDateFilter"
                        class="px-2.5 py-1.5 bg-rose-50 text-rose-600 hover:bg-rose-100 font-bold rounded-xl text-[11px] transition cursor-pointer flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Clear Filter
                    </button>
                @endif
            </div>

        </div>

        <!-- Punch Terminal Button -->
        <div>
            <button type="button" wire:click="openTerminal"
                class="px-5 py-3 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-bold text-sm rounded-2xl shadow-lg shadow-emerald-600/20 flex items-center gap-2 cursor-pointer transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Punch / Check In Terminal</span>
            </button>
        </div>
    </div>

    <!-- Message Alerts -->
    @if (session()->has('successMessage'))
        <div
            class="mb-4 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold rounded-2xl flex items-center gap-2">
            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            <span>{{ session('successMessage') }}</span>
        </div>
    @endif

    @if (session()->has('errorMessage'))
        <div
            class="mb-4 p-4 bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold rounded-2xl flex items-center gap-2">
            <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
            <span>{{ session('errorMessage') }}</span>
        </div>
    @endif

    <!-- Data Records Table -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead
                    class="bg-slate-50/80 text-slate-700 font-bold uppercase tracking-wider text-[11px] border-b border-slate-200">
                    <tr>
                        <th class="p-3.5 whitespace-nowrap">Date</th>
                        <th class="p-3.5 whitespace-nowrap">Shift</th>
                        <th class="p-3.5 whitespace-nowrap">Punch In</th>
                        <th class="p-3.5 whitespace-nowrap">Punch Out</th>
                        <th class="p-3.5 whitespace-nowrap">Total Break</th>
                        <th class="p-3.5 whitespace-nowrap">Work Hours</th>
                        <th class="p-3.5 whitespace-nowrap">Overtime</th>
                        <th class="p-3.5 whitespace-nowrap">Late (Min)</th>
                        <th class="p-3.5 whitespace-nowrap">Early (Min)</th>
                        <th class="p-3.5 whitespace-nowrap">Status</th>
                        <th class="p-3.5 text-center whitespace-nowrap">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($attendances as $record)
                        <tr class="hover:bg-slate-50/60 transition">
                            <!-- Date -->
                            <td class="p-3.5 font-bold text-slate-900 whitespace-nowrap">
                                {{ $record->display_date }}
                            </td>

                            <!-- Shift -->
                            <td class="p-3.5 whitespace-nowrap">
                                <span
                                    class="bg-slate-100 text-slate-700 font-semibold px-2 py-1 rounded-md text-[11px]">
                                    {{ $record->display_shift }}
                                </span>
                            </td>

                            <!-- In Time -->
                            <td class="p-3.5 font-mono font-bold text-emerald-700 whitespace-nowrap">
                                {{ $record->display_in_time }}
                            </td>

                            <!-- Out Time -->
                            <td class="p-3.5 font-mono font-bold text-blue-700 whitespace-nowrap">
                                {{ $record->display_out_time }}
                            </td>

                            <!-- Total Break Time -->
                            <td class="p-3.5 whitespace-nowrap">
                                @if ($record->display_break_mins > 0)
                                    <span
                                        class="inline-flex items-center gap-1 font-semibold text-slate-700 bg-amber-50 border border-amber-200/60 px-2 py-0.5 rounded-lg text-[11px]">
                                        {{ $record->display_break_mins }}m
                                        <span
                                            class="text-[10px] text-amber-700 font-normal">({{ $record->display_break_count }})</span>
                                    </span>
                                @else
                                    <span class="text-slate-400">0m</span>
                                @endif
                            </td>

                            <!-- Work Hours -->
                            <td class="p-3.5 font-bold text-slate-800 whitespace-nowrap">
                                @if ($record->display_work_hours > 0)
                                    {{ $record->display_work_hours }} hrs
                                @else
                                    <span class="text-slate-400">0.00 hrs</span>
                                @endif
                            </td>

                            <!-- Overtime -->
                            <td class="p-3.5 whitespace-nowrap">
                                @if ($record->display_ot_hours > 0)
                                    <span
                                        class="text-indigo-700 font-bold bg-indigo-50 border border-indigo-200/60 px-2 py-0.5 rounded-md text-[11px]">
                                        {{ $record->display_ot_hours }}h
                                    </span>
                                @else
                                    <span class="text-slate-400">0h</span>
                                @endif
                            </td>

                            <!-- Late Minutes -->
                            <td class="p-3.5 whitespace-nowrap">
                                <span class="text-amber-700 bg-amber-100 font-bold px-2 py-0.5 rounded text-[11px]">
                                    {{ $record->late_minutes }}m
                                </span>
                            </td>

                            <!-- Early Minutes -->
                            <td class="p-3.5 whitespace-nowrap">
                                @if (($record->early_minutes ?? 0) > 0)
                                    <span class="text-rose-700 bg-rose-100 font-bold px-2 py-0.5 rounded text-[11px]">
                                        {{ $record->early_minutes }}m
                                    </span>
                                @else
                                    <span class="text-slate-400">0m</span>
                                @endif
                            </td>

                            <!-- Status Column -->
                            <td class="p-3.5 whitespace-nowrap">
                                <div class="flex items-center gap-1.5">

                                    {{-- 1. Main Status Badge (Present, Late, HD, etc.) --}}
                                    @if ($record->status === 'present')
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            Present
                                        </span>
                                    @elseif ($record->status === 'late')
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                            Late In
                                        </span>
                                    @elseif ($record->status === 'half_day_absent')
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-100 text-rose-800">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                            HD Absent
                                        </span>
                                    @elseif (str_starts_with($record->status, 'half_day_morning'))
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-purple-100 text-purple-800">
                                            <span class="w-1.5 h-1.5 rounded-full bg-purple-500"></span>
                                            HD Morning
                                            {{ str_contains($record->status, 'late') ? '(Late)' : '(Present)' }}
                                        </span>
                                    @elseif (str_starts_with($record->status, 'half_day_evening'))
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-indigo-100 text-indigo-800">
                                            <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                                            HD Evening
                                            {{ str_contains($record->status, 'late') ? '(Late)' : '(Present)' }}
                                        </span>
                                    @elseif ($record->status === 'leave')
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-blue-100 text-blue-800">
                                            <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                            Leave (Paid)
                                        </span>
                                    @elseif ($record->status === 'absent')
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-100 text-rose-800">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                            Absent (No Pay)
                                        </span>
                                  @elseif ($record->status === 'holiday_present')
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-teal-100 text-teal-800">
                                            <span class="w-1.5 h-1.5 rounded-full bg-teal-500"></span>
                                            Holiday Worked
                                        </span>
                                    @elseif ($record->status === 'weekend_present')
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-cyan-100 text-cyan-800">
                                            <span class="w-1.5 h-1.5 rounded-full bg-cyan-500"></span>
                                            Weekend Worked
                                        </span>
                                    @else
                                        <span
                                            class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-700">
                                            {{ ucfirst(str_replace('_', ' ', $record->status ?? 'Unknown')) }}
                                        </span>
                                    @endif

                                    {{-- 2. Edited Badge (displayed only if an edit reason exists) --}}
                                    @if ($record->is_edited)
                                        <span
                                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200"
                                            title="Edited via Approved Request">
                                            <svg class="w-3 h-3 text-amber-600" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                            </svg>
                                            Edited
                                        </span>
                                    @endif

                                </div>
                            </td>


                            <!-- Actions -->
                            <td class="p-3.5 whitespace-nowrap text-center">
                                <div class="inline-flex items-center gap-1.5">
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
                                            class="ml-2 px-2.5 py-1 bg-amber-100 text-amber-800 font-bold rounded-lg text-[10px]">
                                            Pending Approval
                                        </span>
                                    @else
                                        <span class="text-gray-200 select-none">|</span>

                                        <button type="button"
                                            wire:click="openAttendanceEditModal({{ $record->id }})"
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
                            <td colspan="11" class="p-8 text-center text-slate-400 font-medium">
                                No attendance records found. Click "Punch / Check In Terminal" to mark attendance.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if (method_exists($attendances, 'hasPages') && $attendances->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $attendances->links() }}
            </div>
        @endif
    </div>

    @if ($showEditModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 overflow-y-auto py-10">
            <div class="bg-white rounded-lg p-6 w-full max-w-lg shadow-xl max-h-[90vh] overflow-y-auto">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Request Attendance & Break Edit</h3>

                <div class="space-y-4">

                    <!-- Date Display Header Card -->
                    <div class="bg-gray-50 p-3.5 rounded-lg border border-gray-200">
                        <label
                            class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Attendance
                            Date</label>
                        <div class="flex items-center gap-2 text-gray-800 font-bold text-sm">
                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                </path>
                            </svg>
                            <span>
                                {{ $editDate ? \Carbon\Carbon::parse($editDate)->format($globalCalendarFormat) : '--' }}
                            </span>
                        </div>
                        <!-- Hidden field -->
                        <input type="hidden" wire:model="editDate">
                    </div>

                    <!-- In Time & Out Time -->
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">In Time</label>
                            <input type="time" step="1" wire:model="editInTime"
                                class="w-full mt-1 border-gray-300 rounded-md shadow-sm">
                            @error('editInTime')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Out Time</label>
                            <input type="time" step="1" wire:model="editOutTime"
                                class="w-full mt-1 border-gray-300 rounded-md shadow-sm">
                            @error('editOutTime')
                                <span class="text-red-500 text-xs">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <!-- Breaks Section (Dynamic break logs) -->
                    <div class="border-t pt-3 mt-3">
                        <!-- Breaks Section Header -->
                        <div class="flex items-center justify-between mb-3">
                            <div>
                                <h4 class="text-sm font-bold text-gray-800">Break Details</h4>
                                <p class="text-[11px] text-gray-500">Add or edit break instances for this attendance
                                    record.</p>
                            </div>

                            <!-- Add Break Button -->
                            <button type="button" wire:click="addBreakToEdit"
                                class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-xs transition cursor-pointer flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4" />
                                </svg>
                                Add Break
                            </button>
                        </div>

                        @if (count($editBreaks) > 0)
                            @foreach ($editBreaks as $index => $breakData)
                                <div class="bg-gray-50 p-3 rounded-xl mb-3 border border-gray-200 relative space-y-2">
                                    <div class="flex items-center justify-between border-b border-gray-200/60 pb-1.5">
                                        <span class="text-xs font-bold text-blue-900 uppercase tracking-wider">Break
                                            #{{ $index + 1 }}</span>
                                        <button type="button" wire:click="removeBreakFromEdit({{ $index }})"
                                            class="text-rose-600 hover:text-rose-800 text-xs font-bold cursor-pointer flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                            Remove
                                        </button>
                                    </div>

                                    <!-- Display active break categories from database in dropdown -->
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-600 mb-1">Select Break
                                            Category</label>
                                        <select wire:model="editBreaks.{{ $index }}.break_type"
                                            class="w-full text-sm border-gray-300 rounded-lg shadow-2xs focus:ring-blue-500 focus:border-blue-500">
                                            @if (!empty($allBreakTypes) && count($allBreakTypes) > 0)
                                                @foreach ($allBreakTypes as $bType)
                                                    <option value="{{ $bType->code }}">
                                                        {{ $bType->name }} ({{ $bType->is_paid ? 'Paid' : 'Unpaid' }}
                                                        - Max {{ $bType->max_minutes }} mins)
                                                    </option>
                                                @endforeach
                                            @else
                                                <option value="">No Break Types Configured</option>
                                            @endif
                                        </select>
                                    </div>

                                    <!-- Start & End Time Inputs -->
                                    <div class="grid grid-cols-2 gap-2">
                                        <div>
                                            <label class="block text-[11px] font-medium text-gray-500">Start
                                                Time</label>
                                            <input type="time" step="1"
                                                wire:model="editBreaks.{{ $index }}.start_time"
                                                class="w-full mt-0.5 text-sm border-gray-300 rounded-lg shadow-2xs">
                                        </div>
                                        <div>
                                            <label class="block text-[11px] font-medium text-gray-500">End Time</label>
                                            <input type="time" step="1"
                                                wire:model="editBreaks.{{ $index }}.end_time"
                                                class="w-full mt-0.5 text-sm border-gray-300 rounded-lg shadow-2xs">
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div
                                class="bg-gray-50 border border-dashed border-gray-300 rounded-xl p-4 text-center text-xs text-gray-500 mb-3">
                                No breaks added. Click "+ Add Break" above to choose from available break categories.
                            </div>
                        @endif
                    </div>

                    <!-- Reason -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Reason for Edit</label>
                        <textarea wire:model="editReason" rows="3" class="w-full mt-1 border-gray-300 rounded-md shadow-sm"
                            placeholder="Provide a reason..."></textarea>
                        @error('editReason')
                            <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <button wire:click="$set('showEditModal', false)"
                        class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md">Cancel</button>
                    <button wire:click="submitAttendanceEditRequest"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md">Submit Request</button>
                </div>
            </div>
        </div>
    @endif

    <!-- ==================== VIEW DETAILS MODAL ==================== -->
    @if ($isViewRecordModalOpen && $selectedRecordForView)
        <div class="fixed inset-0 bg-slate-950/60 backdrop-blur-xs z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl p-6 max-w-lg w-full shadow-2xl border border-slate-200 space-y-4">
                <div class="flex items-center justify-between border-b pb-3">
                    <h3 class="font-bold text-slate-900 text-sm">Attendance Summary Details</h3>
                    <button type="button" wire:click="$set('isViewRecordModalOpen', false)"
                        class="text-slate-400 hover:text-slate-600 font-bold text-lg cursor-pointer">&times;</button>
                </div>

                <div class="grid grid-cols-2 gap-3 text-xs">
                    <div class="bg-slate-50 p-3 rounded-xl border border-slate-100">
                        <span class="text-slate-400 block font-bold text-[10px] uppercase">Date</span>
                        <span class="font-semibold text-slate-800">{{ $selectedRecordForView->date }}</span>
                    </div>
                    <div class="bg-slate-50 p-3 rounded-xl border border-slate-100">
                        <span class="text-slate-400 block font-bold text-[10px] uppercase">Shift</span>
                        <span
                            class="font-semibold text-slate-800">{{ $selectedRecordForView->shift?->name ?? 'Default' }}</span>
                    </div>
                    <div class="bg-emerald-50/70 p-3 rounded-xl border border-emerald-100">
                        <span class="text-emerald-700 block font-bold text-[10px] uppercase">Punch In</span>
                        <span
                            class="font-mono font-bold text-emerald-900">{{ !empty($selectedRecordForView->in_time) ? \Carbon\Carbon::parse($selectedRecordForView->in_time)->format('h:i A') : '--:--' }}</span>
                    </div>
                    <div class="bg-blue-50/70 p-3 rounded-xl border border-blue-100">
                        <span class="text-blue-700 block font-bold text-[10px] uppercase">Punch Out</span>
                        <span
                            class="font-mono font-bold text-blue-900">{{ !empty($selectedRecordForView->out_time) ? \Carbon\Carbon::parse($selectedRecordForView->out_time)->format('h:i A') : '--:--' }}</span>
                    </div>
                </div>

                <!-- OVERALL STATUS CARD -->
                <div
                    class="bg-slate-50 p-3 rounded-2xl border border-slate-200/80 flex items-center justify-between text-xs">
                    <span class="text-slate-500 font-bold uppercase text-[10px]">Attendance Status</span>
                    <div>
                        @if ($selectedRecordForView->status === 'present')
                            <span
                                class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800">Present</span>
                        @elseif ($selectedRecordForView->status === 'late')
                            <span
                                class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800">Late
                                In</span>
                        @elseif ($selectedRecordForView->status === 'half_day_absent')
                            <span
                                class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-100 text-rose-800 border border-rose-200">Half
                                Day Absent</span>
                        @elseif (str_starts_with($selectedRecordForView->status, 'half_day_morning'))
                            <span
                                class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-purple-100 text-purple-800">
                                HD Morning
                                {{ str_contains($selectedRecordForView->status, 'late') ? '(Late)' : '(Present)' }}
                            </span>
                        @elseif (str_starts_with($selectedRecordForView->status, 'half_day_evening'))
                            <span
                                class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-indigo-100 text-indigo-800">
                                HD Evening
                                {{ str_contains($selectedRecordForView->status, 'late') ? '(Late)' : '(Present)' }}
                            </span>
                        @elseif ($selectedRecordForView->status === 'leave')
                            <span
                                class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-blue-100 text-blue-800">Approved
                                Leave</span>
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

                <!-- LEAVE & TIMING METRICS CARD -->
                <div class="bg-blue-50/60 p-4 rounded-2xl border border-blue-200/80 space-y-3">
                    <div class="flex items-center justify-between border-b border-blue-200/60 pb-2">
                        <span
                            class="text-blue-900 font-bold text-xs uppercase tracking-wider flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                            @if ($selectedLeaveDetails || $selectedRecordForView->status === 'leave')
                                Leave & Timing Metrics
                            @else
                                Timing Metrics
                            @endif
                        </span>
                        <span
                            class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800 border border-blue-300/50">
                            @if ($selectedLeaveDetails?->is_half_day)
                                Half Day ({{ ucfirst($selectedLeaveDetails->leave_shift ?? 'N/A') }})
                            @elseif ($selectedRecordForView->status === 'leave' || $selectedLeaveDetails)
                                Full Day Leave
                            @else
                                Regular Shift
                            @endif
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-3 text-xs">
                        {{-- Leave iddare maathra Leave Type mathu Leave Session full aagi thoreesuvudu --}}
                        @if ($selectedLeaveDetails || $selectedRecordForView->status === 'leave')
                            <div>
                                <span class="text-slate-400 text-[10px] uppercase font-bold block">Leave Type</span>
                                <span class="font-bold text-slate-800">
                                    {{ $selectedLeaveDetails?->leave_type_name ?? ($selectedRecordForView->status === 'leave' ? 'Approved Leave' : 'No Leave Applied') }}
                                </span>
                            </div>
                            <div>
                                <span class="text-slate-400 text-[10px] uppercase font-bold block">Leave Session</span>
                                <span class="font-semibold text-slate-700">
                                    @if ($selectedLeaveDetails?->is_half_day)
                                        {{ ucfirst($selectedLeaveDetails->leave_shift) }} Half Leave
                                    @elseif($selectedRecordForView->status === 'leave' || $selectedLeaveDetails)
                                        Full Day Leave
                                    @else
                                        Full Working Day
                                    @endif
                                </span>
                            </div>
                        @endif

                        <!-- Arrival Status / Late Minutes (No On Time for absent) -->
                        <div>
                            <span class="text-slate-400 text-[10px] uppercase font-bold block">Arrival Status</span>
                            @if (!empty($selectedRecordForView->in_time))
                                @if (($selectedRecordForView->late_minutes ?? 0) > 0)
                                    <span
                                        class="font-bold text-amber-700 bg-amber-100/80 px-2 py-0.5 rounded text-[11px] inline-block">
                                        {{ $selectedRecordForView->late_minutes }}m Late
                                    </span>
                                @else
                                    <span
                                        class="font-bold text-emerald-700 bg-emerald-100/80 px-2 py-0.5 rounded text-[11px] inline-block">
                                        On Time
                                    </span>
                                @endif
                            @else
                                <span class="font-semibold text-rose-600 italic">Not Clocked In</span>
                            @endif
                        </div>

                        <!-- Departure Status / Early Minutes (fetched directly from database) -->
                        <div>
                            <span class="text-slate-400 text-[10px] uppercase font-bold block">Departure Status</span>
                            @if (!empty($selectedRecordForView->out_time))
                                @if (($selectedRecordForView->early_minutes ?? 0) > 0)
                                    <span
                                        class="font-bold text-rose-700 bg-rose-100/80 px-2 py-0.5 rounded text-[11px] inline-block">
                                        {{ $selectedRecordForView->early_minutes }}m Early Out
                                    </span>
                                @else
                                    <span
                                        class="font-bold text-emerald-700 bg-emerald-100/80 px-2 py-0.5 rounded text-[11px] inline-block">
                                        Completed Shift
                                    </span>
                                @endif
                            @else
                                @if (!empty($selectedRecordForView->in_time))
                                    <span class="font-semibold text-amber-600 italic">Currently On Duty</span>
                                @else
                                    <span class="font-semibold text-slate-400 italic">N/A (Absent)</span>
                                @endif
                            @endif
                        </div>

                        @if ($selectedLeaveDetails?->reason)
                            <div class="col-span-2 border-t border-blue-200/50 pt-2 mt-1">
                                <span class="text-slate-400 text-[10px] uppercase font-bold block">Leave Reason</span>
                                <span class="font-semibold text-slate-700">{{ $selectedLeaveDetails->reason }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- BREAKS LOG -->
                <div class="border-t pt-3">
                    <h4 class="font-bold text-slate-800 text-xs mb-2">Breaks Log for the Day</h4>
                    @if ($selectedRecordForView->breaks && $selectedRecordForView->breaks->count() > 0)
                        <div class="space-y-1.5 max-h-40 overflow-y-auto">
                            @foreach ($selectedRecordForView->breaks as $brk)
                                <div
                                    class="flex items-center justify-between p-2 rounded-lg bg-slate-50 text-[11px] border border-slate-100">
                                    <span class="font-semibold text-slate-700">{{ ucfirst($brk->break_type) }}
                                        Break</span>
                                    <span class="font-mono text-slate-500">{{ $brk->break_start }} -
                                        {{ $brk->break_end ?? 'Active' }}</span>
                                    <span class="font-bold text-amber-700">{{ $brk->duration_minutes }} mins</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p
                            class="text-slate-400 text-xs italic bg-slate-50 p-2.5 rounded-xl border border-slate-100 text-center">
                            No breaks recorded for this day.
                        </p>
                    @endif
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

                <div class="flex justify-end pt-2">
                    <button type="button" wire:click="$set('isViewRecordModalOpen', false)"
                        class="px-5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs cursor-pointer transition">
                        Close
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- ==================== PUNCH / CHECK IN TERMINAL MODAL ==================== -->
    @if ($isTerminalModalOpen)
        <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs z-50 flex items-center justify-center p-4">
            <div
                class="max-w-md w-full bg-white rounded-3xl shadow-2xl border border-slate-200 overflow-hidden relative animate-in fade-in zoom-in duration-150">

                <!-- Modal Top Bar -->
                <div class="p-5 pb-3 border-b border-slate-100 flex items-center justify-between">
                    <span class="font-bold text-slate-900 text-sm">Attendance Terminal</span>
                    <button type="button" wire:click="closeTerminal"
                        class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center font-bold text-sm cursor-pointer">
                        &times;
                    </button>
                </div>

                <!-- Live Clock Terminal -->
                <div class="px-6 py-4 bg-gradient-to-b from-slate-50 to-white text-center border-b border-slate-100">
                    <span
                        class="inline-flex items-center gap-1.5 text-[10px] font-bold text-slate-500 uppercase tracking-widest bg-white px-3 py-1 rounded-full border border-slate-200/70 shadow-2xs">
                        <svg class="w-3 h-3 text-emerald-500" fill="currentColor" viewBox="0 0 8 8">
                            <circle cx="4" cy="4" r="3" />
                        </svg>
                        Server Realtime Clock
                    </span>
                    <div class="text-3xl font-black text-slate-900 tracking-tight font-mono my-2"
                        wire:poll.1s="updateServerTime">
                        {{ $currentRunningTime }}
                    </div>
                </div>

                <div class="p-5 space-y-4">

                    <!--1. Error / Alert Banner shown if you forgot to Check-Out on a previous day -->
                    @if ($unclosedPreviousRecord)
                        <div class="p-3.5 bg-amber-50 border border-amber-200 rounded-2xl text-xs space-y-2">
                            <div class="flex items-center gap-2 text-amber-800 font-bold">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                <span>Notice: Unclosed Attendance Alert!</span>
                            </div>
                            <p class="text-amber-800 leading-relaxed">
                                You have not checked out on <strong
                                    class="underline">{{ $unclosedPreviousRecord->date }}</strong>. Please submit an
                                edit request from the Timesheet Log with the correct time.
                            </p>
                        </div>
                    @endif

                    <!-- Current Status Pill -->
                    <div
                        class="flex items-center justify-between bg-slate-50 p-3 rounded-2xl border border-slate-100 text-xs">
                        <span class="text-slate-500">Current Status</span>
                        @if (
                            $currentAttendanceRecord &&
                                !empty($currentAttendanceRecord->in_time) &&
                                empty($currentAttendanceRecord->out_time) &&
                                $currentAttendanceRecord->date < \Carbon\Carbon::today()->toDateString())
                            <span
                                class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-100 text-rose-800">Unresolved
                                Past Record</span>
                        @elseif (!$currentAttendanceRecord || !$currentAttendanceRecord->in_time)
                            <span
                                class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-slate-200 text-slate-700">Not
                                Clocked In</span>
                        @elseif($activeBreakRecord !== null)
                            <span
                                class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-ping"></span>
                                On {{ ucfirst($activeBreakRecord->break_type) }} Break
                            </span>
                        @elseif(!$currentAttendanceRecord->out_time)
                            <span
                                class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                On Duty
                            </span>
                        @else
                            <span
                                class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-blue-100 text-blue-800">Finished
                                For Today</span>
                        @endif
                    </div>

                    <!-- On Break Banner (Resume with GPS) -->
                    @if ($activeBreakRecord !== null)
                        <div
                            class="p-3 bg-amber-500/10 border border-amber-300 rounded-2xl flex items-center justify-between">
                            <div>
                                <div class="text-xs font-bold text-amber-900 uppercase">On
                                    {{ ucfirst($activeBreakRecord->break_type) }} Break</div>
                                <div class="text-[10px] text-amber-700">Started:
                                    {{ $activeBreakRecord->break_start }}
                                </div>
                            </div>
                            <button type="button" onclick="triggerEndBreakWithCoordinates()"
                                class="px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs rounded-xl shadow cursor-pointer transition">
                                Resume Work
                            </button>
                        </div>
                    @endif

                    <!-- Punch Timeline Card -->
                    <div class="grid grid-cols-2 gap-3">
                        <div class="p-3 rounded-2xl border bg-slate-50/70 border-slate-200/60">
                            <span class="text-[10px] font-bold uppercase text-slate-400 block">Punch In</span>
                            <span class="text-base font-extrabold text-slate-800 font-mono mt-0.5 block">
                                {{ $currentAttendanceRecord?->in_time ?? '--:--' }}
                            </span>
                        </div>
                        <div class="p-3 rounded-2xl border bg-slate-50/70 border-slate-200/60">
                            <span class="text-[10px] font-bold uppercase text-slate-400 block">Punch Out</span>
                            <span class="text-base font-extrabold text-slate-800 font-mono mt-0.5 block">
                                {{ $currentAttendanceRecord?->out_time ?? '--:--' }}
                            </span>
                        </div>
                    </div>


                    <!-- 3. Action Buttons -->
                    <div>
                        @if (!$currentAttendanceRecord || !$currentAttendanceRecord->in_time)

                            @if ($isShiftExpired)
                                <!-- Shift Expired Alert -->
                                <div
                                    class="p-3.5 bg-rose-50 border border-rose-200 rounded-2xl text-xs space-y-2 mb-3">
                                    <div class="flex items-center gap-2 text-rose-800 font-bold">
                                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span>Shift Time Expired!</span>
                                    </div>
                                    <p class="text-rose-700 leading-relaxed">
                                        Your shift time has expired. You cannot clock in for this shift anymore. Please
                                        contact your supervisor if you believe this is an error.
                                    </p>
                                </div>

                                <!-- Disabled / Blocked Clock In Button -->
                                <button type="button" disabled
                                    class="w-full py-3.5 bg-slate-300 text-slate-500 font-bold text-sm rounded-2xl cursor-not-allowed flex items-center justify-center gap-2">
                                    Clock In Disabled (Shift Expired)
                                </button>
                            @else
                                <!-- Shift Not Expired -->
                                <button type="button" onclick="triggerClockInWithCoordinates()"
                                    wire:loading.attr="disabled" wire:target="clockIn"
                                    class="w-full py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm rounded-2xl shadow-lg shadow-emerald-500/20 flex items-center justify-center gap-2 cursor-pointer">
                                    <span wire:loading.remove wire:target="clockIn">Clock In Shift</span>
                                    <span wire:loading wire:target="clockIn">Verifying Location...</span>
                                </button>
                            @endif
                        @elseif(!$currentAttendanceRecord->out_time)
                            <div class="grid grid-cols-2 gap-3">
                                <button type="button" wire:click="openBreakModal"
                                    @if ($activeBreakRecord !== null) disabled @endif
                                    class="py-3 bg-amber-500 hover:bg-amber-600 disabled:opacity-40 text-white font-bold text-xs rounded-2xl flex items-center justify-center gap-1 cursor-pointer">
                                    Take Break
                                </button>
                                <button type="button" wire:click="clockOut" wire:loading.attr="disabled"
                                    @if ($activeBreakRecord !== null) disabled @endif
                                    class="py-3 bg-rose-600 hover:bg-rose-700 disabled:opacity-40 text-white font-bold text-xs rounded-2xl flex items-center justify-center gap-1 cursor-pointer">
                                    Clock Out
                                </button>
                            </div>
                        @else
                            <div
                                class="w-full py-3 bg-emerald-50 border border-emerald-200 text-emerald-800 text-center font-bold text-xs rounded-2xl">
                                Attendance Completed for Today
                            </div>
                        @endif
                    </div>



                </div>
            </div>
        </div>
    @endif
    <!-- ==================== WORK ON LEAVE CONFIRMATION MODAL ==================== -->
    @if ($isLeaveConfirmModalOpen)
        <div class="fixed inset-0 bg-slate-950/60 backdrop-blur-xs z-[80] flex items-center justify-center p-4">
            <div
                class="bg-white rounded-3xl p-6 max-w-md w-full shadow-2xl border border-slate-200 space-y-4 animate-in fade-in zoom-in-95 duration-150">
                <div class="flex items-center gap-3 border-b border-slate-100 pb-3">
                    <div
                        class="w-10 h-10 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900 text-sm">Approved Leave Detected</h3>
                        <p class="text-[11px] text-slate-500">Notice for Work on Leave Day</p>
                    </div>
                </div>

                {{-- Dynamic conditional section --}}
                <div
                    class="bg-amber-50/70 p-3.5 rounded-2xl border border-amber-200/80 text-xs text-amber-900 leading-relaxed">
                    @if (($detectedLeaveRecord?->leave_mode ?? '') === 'half_day')
                        You have an approved <strong>Half Day Leave
                            ({{ ucfirst($detectedLeaveRecord->leave_shift ?? 'Shift') }} -
                            {{ $detectedLeaveRecord->leave_type_name ?? 'Leave' }})</strong> for today.
                    @else
                        You have an approved leave for today
                        (<strong>{{ $detectedFullDayLeave?->leave_type_name ?? 'Full Day Leave' }}</strong>).
                    @endif
                    <br><br>
                    Are you reporting to work today?
                    <br>
                    If you <strong>confirm</strong>, your attendance will be recorded and an automated request will be
                    submitted to the Admin to adjust/re-credit your leave balance.
                </div>

                <div class="flex gap-2 pt-2">
                    <button type="button" wire:click="cancelLeaveClockIn"
                        class="flex-1 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs cursor-pointer transition">
                        Cancel (Accidental Click)
                    </button>
                    <button type="button" wire:click="confirmClockInOnLeave" wire:loading.attr="disabled"
                        class="flex-1 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs shadow-md shadow-emerald-600/20 cursor-pointer transition">
                        Yes, Clock In & Send Request
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- ==================== TAKE BREAK MODAL ==================== -->
    @if ($isBreakModalOpen)
        <div class="fixed inset-0 bg-slate-20/10 backdrop-blur-[1px] z-[70] flex items-center justify-center p-4">
            <div
                class="bg-white rounded-3xl p-6 max-w-sm w-full shadow-2xl border border-slate-100 space-y-4 animate-in fade-in zoom-in-95 duration-150">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="font-bold text-slate-900 text-sm">Select Break Category</h3>
                    <button type="button" wire:click="$set('isBreakModalOpen', false)"
                        class="w-7 h-7 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center text-sm font-bold cursor-pointer">&times;</button>
                </div>

                <div class="space-y-3 text-xs">
                    <div>
                        <label class="font-bold text-slate-700 block mb-1">Break Type</label>
                        <select wire:model="selectedBreakType"
                            class="w-full border border-slate-200 rounded-xl p-2.5 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 outline-none">
                            @foreach (\App\Models\BreakType::where('is_active', true)->get() as $bType)
                                <option value="{{ $bType->code }}">
                                    {{ $bType->name }} ({{ $bType->is_paid ? 'Paid' : 'Unpaid' }} - Max
                                    {{ $bType->max_minutes }}m)
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="font-bold text-slate-700 block mb-1">Reason (Optional)</label>
                        <input type="text" wire:model="breakReason" placeholder="e.g. Bank visit or lunch"
                            class="w-full border border-slate-200 rounded-xl p-2.5 focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 outline-none">
                    </div>
                </div>

                <div class="flex gap-2 pt-2">
                    <button type="button" wire:click="$set('isBreakModalOpen', false)"
                        class="flex-1 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs cursor-pointer">
                        Cancel
                    </button>
                    <button type="button" onclick="triggerStartBreakWithCoordinates()"
                        class="flex-1 py-2.5 bg-amber-500 hover:bg-amber-600 active:scale-95 text-white font-bold rounded-xl text-xs shadow-md shadow-amber-500/20 cursor-pointer">
                        Start Break
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- ==================== PUBLIC HOLIDAY & WEEKEND WORK CONFIRMATION MODAL ==================== -->
    @if ($isHolidayConfirmModalOpen)
        <div class="fixed inset-0 bg-slate-950/60 backdrop-blur-xs z-[80] flex items-center justify-center p-4">
            <div
                class="bg-white rounded-3xl p-6 max-w-md w-full shadow-2xl border border-slate-200 space-y-4 animate-in fade-in zoom-in-95 duration-150">
                <div class="flex items-center gap-3 border-b border-slate-100 pb-3">
                    <div
                        class="w-10 h-10 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900 text-sm">Non-Working Day Detected</h3>
                        <p class="text-[11px] text-slate-500">Notice for Working on a Holiday or Weekend</p>
                    </div>
                </div>

                <div
                    class="bg-amber-50/70 p-3.5 rounded-2xl border border-amber-200/80 text-xs text-amber-900 leading-relaxed">
                    Today is:
                    <strong>{{ $detectedPublicHoliday?->description ?? 'Public Holiday / Weekend' }}</strong>.
                    <br><br>
                    Are you reporting to work today?
                    <br>
                    If you <strong>confirm</strong>, your attendance will be recorded as <strong>Holiday Worked</strong>.
                </div>

                <div class="flex gap-2 pt-2">
                    <button type="button" wire:click="cancelHolidayClockIn"
                        class="flex-1 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs cursor-pointer transition">
                        Cancel
                    </button>
                    <button type="button" wire:click="confirmClockInOnHoliday" wire:loading.attr="disabled"
                        class="flex-1 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs shadow-md shadow-emerald-600/20 cursor-pointer transition">
                        Yes, Clock In
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Geolocation Scripts for Attendance and Breaks -->
    <script>
        function triggerClockInWithCoordinates() {
            if (!navigator.geolocation) {
                alert('Geolocation is not supported by your browser.');
                return;
            }
            navigator.geolocation.getCurrentPosition(
                function(pos) {
                    @this.clockIn(pos.coords.latitude, pos.coords.longitude);
                },
                function(err) {
                    alert('Location permission is strictly required to clock in.');
                }, {
                    timeout: 10000,
                    enableHighAccuracy: true
                }
            );
        }

        function triggerStartBreakWithCoordinates() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    function(pos) {
                        @this.startBreak(pos.coords.latitude, pos.coords.longitude);
                    },
                    function(err) {
                        @this.startBreak(null, null);
                    }, {
                        timeout: 8000,
                        enableHighAccuracy: true
                    }
                );
            } else {
                @this.startBreak(null, null);
            }
        }

        function triggerEndBreakWithCoordinates() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    function(pos) {
                        @this.endBreak(pos.coords.latitude, pos.coords.longitude);
                    },
                    function(err) {
                        @this.endBreak(null, null);
                    }, {
                        timeout: 8000,
                        enableHighAccuracy: true
                    }
                );
            } else {
                @this.endBreak(null, null);
            }
        }
    </script>
</div>
