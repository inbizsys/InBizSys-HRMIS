<div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">

    <!-- Header Section (Pure Tailwind CSS) -->
    <div
        class="bg-white border border-gray-200 rounded-2xl p-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4 shadow-sm">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Shift & Attendance Settings</h1>
            <p class="text-sm text-gray-500 mt-1">Manage company work rosters, grace periods, auto break deductions, and
                late penalty rules.</p>
        </div>

        @if ($activeTab === 'shifts')
            <button type="button" wire:click="openCreateShiftModal"
                class="inline-flex items-center px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold uppercase tracking-wider rounded-xl shadow-sm transition">
                + Add New Shift
            </button>
        @elseif($activeTab === 'breaks')
            <button type="button" wire:click="openCreateBreakTypeModal"
                class="inline-flex items-center px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold uppercase tracking-wider rounded-xl shadow-sm transition">
                + Add New Break Type
            </button>
        @endif
    </div>

    <!-- Flash Message -->
    @if (session()->has('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm font-medium">
            {{ session('success') }}
        </div>
    @endif

    <!-- Navigation Tabs -->
    <div class="flex border-b border-gray-200 gap-6">
        <button type="button" wire:click="$set('activeTab', 'shifts')"
            class="pb-3 text-xs font-bold uppercase tracking-wider border-b-2 transition {{ $activeTab === 'shifts' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-400 hover:text-gray-600' }}">
            Work Shifts Roster
        </button>
        <button type="button" wire:click="$set('activeTab', 'policy')"
            class="pb-3 text-xs font-bold uppercase tracking-wider border-b-2 transition {{ $activeTab === 'policy' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-400 hover:text-gray-600' }}">
            Rules & Late Policies
        </button>
        <button type="button" wire:click="$set('activeTab', 'breaks')"
            class="pb-3 text-xs font-bold uppercase tracking-wider border-b-2 transition {{ $activeTab === 'breaks' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-400 hover:text-gray-600' }}">
            Break Categories
        </button>
    </div>

    <!-- TAB 1: WORK SHIFTS ROSTER TABLE -->
    @if ($activeTab === 'shifts')
        <div class="w-full bg-white rounded-2xl border border-gray-200 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-left">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3.5 text-xs font-bold text-gray-500 uppercase tracking-wider">Shift Name
                            </th>
                            <th class="px-6 py-3.5 text-xs font-bold text-gray-500 uppercase tracking-wider">Schedule
                                Time</th>
                            <th class="px-6 py-3.5 text-xs font-bold text-gray-500 uppercase tracking-wider">Grace
                                Periods</th>
                            <th class="px-6 py-3.5 text-xs font-bold text-gray-500 uppercase tracking-wider">Required
                                Hours</th>
                            <th class="px-6 py-3.5 text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                            <th
                                class="px-6 py-3.5 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse($shifts as $shift)
                            <tr class="hover:bg-gray-50/70 transition">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-800">
                                    {{ $shift->name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-600">
                                    <span class="text-blue-600 font-semibold">{{ $shift->formatted_start_time }}</span>
                                    <span class="text-gray-400 mx-1">-</span>
                                    <span class="text-slate-700 font-semibold">{{ $shift->formatted_end_time }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500">
                                    <div>Late: <span class="font-bold text-amber-600">{{ $shift->late_grace_minutes }}
                                            mins</span></div>
                                    <div>Early Leave: <span
                                            class="font-bold text-slate-700">{{ $shift->early_leave_grace_minutes }}
                                            mins</span></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500">
                                    <div>Full Day: <span
                                            class="font-bold text-gray-800">{{ $shift->standard_working_hours }}
                                            hrs</span></div>
                                    <div>Half Day: <span
                                            class="font-bold text-gray-800">{{ $shift->half_day_min_hours }} hrs</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if ($shift->is_active)
                                        <button type="button" wire:click="toggleShiftStatus({{ $shift->id }})"
                                            class="px-2.5 py-1 text-xs font-bold rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            Active
                                        </button>
                                    @else
                                        <button type="button" wire:click="toggleShiftStatus({{ $shift->id }})"
                                            class="px-2.5 py-1 text-xs font-bold rounded-full bg-gray-100 text-gray-500 border border-gray-200">
                                            Disabled
                                        </button>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    <div class="inline-flex items-center space-x-1">

                                        {{-- VIEW BUTTON --}}
                                        <button type="button" wire:click="viewShift({{ $shift->id }})"
                                            title="View Shift"
                                            class="px-2 py-1 inline-flex text-xs font-semibold rounded-lg bg-green-100 text-green-800 hover:bg-green-200 transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178z" />
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                        </button>

                                        <span class="text-gray-200 select-none">|</span>

                                        {{-- EDIT BUTTON --}}
                                        <button type="button" wire:click="openEditModal({{ $shift->id }})"
                                            title="Edit Shift"
                                            class="px-2 py-1 inline-flex text-xs font-semibold rounded-lg bg-blue-100 text-blue-800 hover:bg-blue-200 transition">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                            </svg>
                                        </button>

                                        <span class="text-gray-200 select-none">|</span>

                                        {{-- DELETE BUTTON --}}
                                        <button type="button"
                                            wire:click="confirmDelete({{ $shift->id }}, '{{ addslashes($shift->name) }}')"
                                            title="Delete Shift"
                                            class="px-2 py-1 inline-flex text-xs font-semibold rounded-lg bg-red-100 text-red-800 hover:bg-red-200 transition">
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
                                <td colspan="6" class="px-6 py-8 text-center text-gray-400 text-sm">
                                    No shift schedules configured yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- TAB 2: FULL WIDTH RULES & LATE POLICIES FORM -->
    @if ($activeTab === 'policy')
        <div class="w-full bg-white rounded-2xl border border-gray-200 p-6 sm:p-8 shadow-xs">
            <form wire:submit="savePolicy" class="space-y-6">

                <div class="border-b border-gray-100 pb-4">
                    <h2 class="text-base font-bold text-gray-900">Attendance Penalty & Rule Engine</h2>
                    <p class="text-xs text-gray-500 mt-0.5">Define automated salary deductions, leave reductions, and
                        break calculations across the company.</p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                    <!-- Left Section: Basic Info & Late Limits -->
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Policy
                                Name</label>
                            <input type="text" wire:model="policy_name" disabled
                                class="w-full text-sm border-gray-200 bg-gray-100 text-gray-500 rounded-xl cursor-not-allowed font-medium focus:ring-0 focus:border-gray-200">
                            @error('policy_name')
                                <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label
                                    class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Allowed
                                    Late Count / Month</label>
                                <input type="number" wire:model="max_late_allowed_per_month"
                                    class="w-full text-sm border-gray-200 rounded-xl focus:border-blue-500 focus:ring-blue-500 font-medium">
                                <p class="text-[11px] text-gray-400 mt-1">Free late arrivals before penalty applies.
                                </p>
                                @error('max_late_allowed_per_month')
                                    <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Late
                                    Penalty Action</label>
                                <select wire:model.live="late_penalty_type"
                                    class="w-full text-sm border-gray-200 rounded-xl focus:border-blue-500 focus:ring-blue-500 font-medium">
                                    <option value="none">No Penalty</option>
                                    <option value="deduct_leave">Deduct From Leave Balance</option>
                                    <option value="salary_deduction">Payroll Salary Deduction</option>
                                </select>
                                @error('late_penalty_type')
                                    <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 border-t border-gray-100">
                            <div>
                                <label
                                    class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Allowed
                                    Early Leaves / Month</label>
                                <input type="number" wire:model="max_early_leave_allowed_per_month"
                                    class="w-full text-sm border-gray-200 rounded-xl focus:border-blue-500 focus:ring-blue-500 font-medium">
                                <p class="text-[11px] text-gray-400 mt-1">Free early departures allowed.</p>
                                @error('max_early_leave_allowed_per_month')
                                    <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label
                                    class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Early
                                    Leave Penalty Action</label>
                                <select wire:model.live="early_leave_penalty_type"
                                    class="w-full text-sm border-gray-200 rounded-xl focus:border-blue-500 focus:ring-blue-500 font-medium">
                                    <option value="none">No Penalty</option>
                                    <option value="deduct_leave">Deduct From Leave Balance</option>
                                    <option value="salary_deduction">Payroll Salary Deduction</option>
                                </select>
                                @error('early_leave_penalty_type')
                                    <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <!-- Unpaid Break Overuse Settings -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 border-t border-gray-100">
                            <div>
                                <label
                                    class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Allowed
                                    Break Overuse / Month</label>
                                <input type="number" wire:model="max_unpaid_break_allowed_per_month"
                                    class="w-full text-sm border-gray-200 rounded-xl focus:border-blue-500 focus:ring-blue-500 font-medium">
                                <p class="text-[11px] text-gray-400 mt-1">Free excess break days allowed before penalty.</p>
                                @error('max_unpaid_break_allowed_per_month')
                                    <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label
                                    class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Break
                                    Penalty Action</label>
                                <select wire:model.live="unpaid_break_penalty_type"
                                    class="w-full text-sm border-gray-200 rounded-xl focus:border-blue-500 focus:ring-blue-500 font-medium">
                                    <option value="none">No Penalty</option>
                                    <option value="deduct_leave">Deduct From Leave Balance</option>
                                    <option value="salary_deduction">Payroll Salary Deduction</option>
                                </select>
                                @error('unpaid_break_penalty_type')
                                    <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Right Section: Deductions & Overtime Conditions -->
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label
                                    class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Break
                                    Deduction (Minutes)</label>
                                <input type="number" wire:model="break_deduction_minutes"
                                    class="w-full text-sm border-gray-200 rounded-xl focus:border-blue-500 focus:ring-blue-500 font-medium">
                                <p class="text-[11px] text-gray-400 mt-1">Auto-deducted daily lunch duration.</p>
                                @error('break_deduction_minutes')
                                    <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label
                                    class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Overtime
                                    Min Threshold (Mins)</label>
                                <input type="number" wire:model="overtime_minimum_minutes"
                                    class="w-full text-sm border-gray-200 rounded-xl focus:border-blue-500 focus:ring-blue-500 font-medium">
                                <p class="text-[11px] text-gray-400 mt-1">Minimum extra minutes to qualify for OT.</p>
                                @error('overtime_minimum_minutes')
                                    <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <!-- Late Penalty Conditional Boxes -->
                        @if ($late_penalty_type === 'deduct_leave')
                            <div
                                class="grid grid-cols-1 sm:grid-cols-2 gap-4 p-4 bg-blue-50/60 border border-blue-100 rounded-xl">
                                <div>
                                    <label
                                        class="block text-xs font-bold text-blue-900 uppercase tracking-wider mb-2">Deduct
                                        From Leave Type (Late)</label>
                                    <select wire:model="deduct_leave_type_id"
                                        class="w-full text-sm border-gray-200 rounded-xl focus:border-blue-500 focus:ring-blue-500 font-medium">
                                        <option value="">-- Select Leave Type --</option>
                                        @foreach ($leaveTypes as $leaveType)
                                            <option value="{{ $leaveType->id }}">{{ $leaveType->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('deduct_leave_type_id')
                                        <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div>
                                    <label
                                        class="block text-xs font-bold text-blue-900 uppercase tracking-wider mb-2">Late
                                        Penalty Rate (Days)</label>
                                    <input type="number" step="0.25" wire:model="late_penalty_rate"
                                        class="w-full text-sm border-gray-200 rounded-xl focus:border-blue-500 focus:ring-blue-500 font-medium">
                                    <p class="text-[11px] text-blue-500 mt-1">Default 0.50 means half a day deducted.
                                    </p>
                                    @error('late_penalty_rate')
                                        <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        @endif

                        @if ($late_penalty_type === 'salary_deduction')
                            <div class="p-4 bg-amber-50/60 border border-amber-100 rounded-xl">
                                <label
                                    class="block text-xs font-bold text-amber-900 uppercase tracking-wider mb-2">Late
                                    Salary Deduction Day Equivalent</label>
                                <input type="number" step="0.25" wire:model="late_penalty_rate"
                                    class="w-full text-sm border-gray-200 rounded-xl focus:border-blue-500 focus:ring-blue-500 font-medium">
                                <p class="text-[11px] text-amber-600 mt-1">Day rate equivalent to calculate salary
                                    deduction.</p>
                                @error('late_penalty_rate')
                                    <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>
                        @endif

                        <!-- Early Leave Penalty Conditional Boxes -->
                        @if ($early_leave_penalty_type === 'deduct_leave')
                            <div
                                class="grid grid-cols-1 sm:grid-cols-2 gap-4 p-4 bg-purple-50/60 border border-purple-100 rounded-xl">
                                <div>
                                    <label
                                        class="block text-xs font-bold text-purple-900 uppercase tracking-wider mb-2">Deduct
                                        From Leave Type (Early)</label>
                                    <select wire:model="deduct_leave_type_id"
                                        class="w-full text-sm border-gray-200 rounded-xl focus:border-blue-500 focus:ring-blue-500 font-medium">
                                        <option value="">-- Select Leave Type --</option>
                                        @foreach ($leaveTypes as $leaveType)
                                            <option value="{{ $leaveType->id }}">{{ $leaveType->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('deduct_leave_type_id')
                                        <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div>
                                    <label
                                        class="block text-xs font-bold text-purple-900 uppercase tracking-wider mb-2">Early
                                        Leave Penalty Rate (Days)</label>
                                    <input type="number" step="0.25" wire:model="early_leave_penalty_rate"
                                        class="w-full text-sm border-gray-200 rounded-xl focus:border-blue-500 focus:ring-blue-500 font-medium">
                                    <p class="text-[11px] text-purple-600 mt-1">Default 0.50 means half a day deducted.
                                    </p>
                                    @error('early_leave_penalty_rate')
                                        <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        @endif

                        @if ($early_leave_penalty_type === 'salary_deduction')
                            <div class="p-4 bg-purple-50/60 border border-purple-100 rounded-xl">
                                <label
                                    class="block text-xs font-bold text-purple-900 uppercase tracking-wider mb-2">Early
                                    Leave Salary Deduction Day Equivalent</label>
                                <input type="number" step="0.25" wire:model="early_leave_penalty_rate"
                                    class="w-full text-sm border-gray-200 rounded-xl focus:border-blue-500 focus:ring-blue-500 font-medium">
                                <p class="text-[11px] text-purple-600 mt-1">Day rate equivalent to calculate salary
                                    deduction for early departure.</p>
                                @error('early_leave_penalty_rate')
                                    <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>
                        @endif

                        <!-- Break Overuse Penalty Conditional Boxes -->
                        @if ($unpaid_break_penalty_type === 'deduct_leave')
                            <div
                                class="grid grid-cols-1 sm:grid-cols-2 gap-4 p-4 bg-emerald-50/60 border border-emerald-100 rounded-xl">
                                <div>
                                    <label
                                        class="block text-xs font-bold text-emerald-900 uppercase tracking-wider mb-2">Deduct
                                        From Leave Type (Break)</label>
                                    <select wire:model="unpaid_break_leave_type_id"
                                        class="w-full text-sm border-gray-200 rounded-xl focus:border-emerald-500 focus:ring-emerald-500 font-medium">
                                        <option value="">-- Select Leave Type --</option>
                                        @foreach ($leaveTypes as $leaveType)
                                            <option value="{{ $leaveType->id }}">{{ $leaveType->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('unpaid_break_leave_type_id')
                                        <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div>
                                    <label
                                        class="block text-xs font-bold text-emerald-900 uppercase tracking-wider mb-2">Break
                                        Penalty Rate (Days)</label>
                                    <input type="number" step="0.25" wire:model="unpaid_break_penalty_rate"
                                        class="w-full text-sm border-gray-200 rounded-xl focus:border-emerald-500 focus:ring-emerald-500 font-medium">
                                    <p class="text-[11px] text-emerald-600 mt-1">Default 0.50 means half a day deducted.</p>
                                    @error('unpaid_break_penalty_rate')
                                        <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        @endif

                        @if ($unpaid_break_penalty_type === 'salary_deduction')
                            <div class="p-4 bg-rose-50/60 border border-rose-100 rounded-xl">
                                <label
                                    class="block text-xs font-bold text-rose-900 uppercase tracking-wider mb-2">Break
                                    Salary Deduction Day Equivalent</label>
                                <input type="number" step="0.25" wire:model="unpaid_break_penalty_rate"
                                    class="w-full text-sm border-gray-200 rounded-xl focus:border-rose-500 focus:ring-rose-500 font-medium">
                                <p class="text-[11px] text-rose-600 mt-1">Day rate equivalent to calculate salary deduction for break overuse.</p>
                                @error('unpaid_break_penalty_rate')
                                    <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>
                        @endif
                    </div>

                </div>

                <div class="pt-5 border-t border-gray-100 flex justify-end">
                    <button type="submit"
                        class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs uppercase tracking-wider rounded-xl shadow-xs transition">
                        Save Policy Configuration
                    </button>
                </div>
            </form>
        </div>
    @endif

    <!-- TAB 3: BREAK TYPES CONFIGURATION -->
    @if ($activeTab === 'breaks')
        <div class="w-full bg-white rounded-2xl border border-gray-200 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-left">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3.5 text-xs font-bold text-gray-500 uppercase tracking-wider">Break Name
                            </th>
                            <th class="px-6 py-3.5 text-xs font-bold text-gray-500 uppercase tracking-wider">Code / Key
                            </th>
                            <th class="px-6 py-3.5 text-xs font-bold text-gray-500 uppercase tracking-wider">Max
                                Allowed Duration</th>
                            <th class="px-6 py-3.5 text-xs font-bold text-gray-500 uppercase tracking-wider">Pay Status
                            </th>
                            <th class="px-6 py-3.5 text-xs font-bold text-gray-500 uppercase tracking-wider">Status
                            </th>
                            <th
                                class="px-6 py-3.5 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse($breakTypes as $type)
                            <tr class="hover:bg-gray-50/70 transition">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-800">
                                    {{ $type->name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-xs font-mono font-bold text-slate-500">
                                    {{ $type->code }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-600 font-bold">
                                    {{ $type->max_minutes }} Minutes
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if ($type->is_paid)
                                        <span
                                            class="px-2.5 py-1 text-[11px] font-bold rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">Paid
                                            Break</span>
                                    @else
                                        <span
                                            class="px-2.5 py-1 text-[11px] font-bold rounded-full bg-rose-50 text-rose-700 border border-rose-200">Unpaid
                                            (Deducted)
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <button type="button" wire:click="toggleBreakTypeStatus({{ $type->id }})"
                                        class="px-2.5 py-1 text-xs font-bold rounded-full {{ $type->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-gray-100 text-gray-500 border border-gray-200' }}">
                                        {{ $type->is_active ? 'Active' : 'Disabled' }}
                                    </button>
                                </td>
                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    <div class="inline-flex items-center space-x-1">
                                        {{-- Edit Button --}}
                                        <button type="button"
                                            wire:click="openEditBreakTypeModal({{ $type->id }})" title="Edit"
                                            class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-lg bg-blue-100 text-blue-800 hover:bg-blue-200 transition">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                                class="w-4 h-4">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                            </svg>
                                        </button>

                                        <span class="text-gray-200 select-none">|</span>

                                        {{-- Delete Button --}}
                                        <button type="button"
                                            wire:click="confirmBreakTypeDelete({{ $type->id }}, '{{ addslashes($type->name) }}')"
                                            title="Delete"
                                            class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-lg bg-red-100 text-red-800 hover:bg-red-200 transition">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                                class="w-4 h-4">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-gray-400 text-sm">
                                    No break categories configured. Click "+ Add New Break Type" to set up.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- MODAL 1: CREATE / EDIT SHIFT -->
    @if ($showShiftModal)
    @teleport('body')
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 overflow-y-auto py-10">
            <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl border border-gray-100">
                <h3 class="text-base font-bold text-gray-800 mb-4">
                    @if ($editingShiftId)
                        Edit Shift Schedule
                    @else
                        Create New Shift Schedule
                    @endif
                </h3>

                <form wire:submit="saveShift" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Shift Name</label>
                        <input type="text" wire:model="shift_name" placeholder="e.g. Regular Office Shift"
                            class="w-full text-sm border-gray-200 rounded-xl focus:ring-blue-500">
                        @error('shift_name')
                            <span class="text-xs text-red-500 block mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Start Time</label>
                            <input type="time" wire:model="start_time"
                                class="w-full text-sm border-gray-200 rounded-xl focus:ring-blue-500">
                            @error('start_time')
                                <span class="text-xs text-red-500 block mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">End Time</label>
                            <input type="time" wire:model="end_time"
                                class="w-full text-sm border-gray-200 rounded-xl focus:ring-blue-500">
                            @error('end_time')
                                <span class="text-xs text-red-500 block mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Late Grace
                                (Mins)</label>
                            <input type="number" wire:model="late_grace_minutes"
                                class="w-full text-sm border-gray-200 rounded-xl focus:ring-blue-500">
                            @error('late_grace_minutes')
                                <span class="text-xs text-red-500 block mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Early Leave Grace
                                (Mins)</label>
                            <input type="number" wire:model="early_leave_grace_minutes"
                                class="w-full text-sm border-gray-200 rounded-xl focus:ring-blue-500">
                            @error('early_leave_grace_minutes')
                                <span class="text-xs text-red-500 block mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Full Day Work
                                (Hrs)</label>
                            <input type="number" step="0.5" wire:model="standard_working_hours"
                                class="w-full text-sm border-gray-200 rounded-xl focus:ring-blue-500">
                            @error('standard_working_hours')
                                <span class="text-xs text-red-500 block mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Half Day Min
                                (Hrs)</label>
                            <input type="number" step="0.5" wire:model="half_day_min_hours"
                                class="w-full text-sm border-gray-200 rounded-xl focus:ring-blue-500">
                            @error('half_day_min_hours')
                                <span class="text-xs text-red-500 block mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="flex items-center gap-2 pt-2">
                        <input type="checkbox" id="is_active_toggle" wire:model="is_active"
                            class="rounded text-blue-600 focus:ring-blue-500">
                        <label for="is_active_toggle" class="text-sm font-semibold text-gray-700">Active Shift
                            Schedule</label>
                    </div>

                    <div class="pt-4 border-t border-gray-100 flex justify-end gap-2">
                        <button type="button" wire:click="closeModals"
                            class="px-4 py-2 bg-gray-100 text-gray-700 font-bold text-xs uppercase rounded-xl hover:bg-gray-200 transition">
                            Cancel
                        </button>
                        <button type="submit"
                            class="px-5 py-2 bg-blue-600 text-white font-bold text-xs uppercase rounded-xl hover:bg-blue-700 transition">
                            Save Shift
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endteleport
    @endif

    <!-- MODAL 2: VIEW SHIFT DETAILS -->
    @if ($showViewShiftModal && $selectedShift)
    @teleport('body')
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 p-4">
            <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-gray-100 space-y-4">
                <div class="flex justify-between items-center border-b border-gray-100 pb-3">
                    <h3 class="text-base font-bold text-gray-900">{{ $selectedShift->name }}</h3>
                    <span
                        class="px-2.5 py-0.5 text-xs font-bold rounded-full {{ $selectedShift->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">
                        {{ $selectedShift->is_active ? 'Active' : 'Disabled' }}
                    </span>
                </div>

                <div class="space-y-3 text-sm">
                    <div class="flex justify-between py-1 border-b border-gray-50">
                        <span class="text-gray-500">Shift Schedule:</span>
                        <span
                            class="font-bold text-gray-800">{{ Carbon\Carbon::parse($selectedShift->start_time)->format('h:i A') }}
                            - {{ Carbon\Carbon::parse($selectedShift->end_time)->format('h:i A') }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-gray-50">
                        <span class="text-gray-500">Late Grace Period:</span>
                        <span class="font-bold text-amber-600">{{ $selectedShift->late_grace_minutes }} Minutes</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-gray-50">
                        <span class="text-gray-500">Early Leave Grace:</span>
                        <span class="font-bold text-slate-700">{{ $selectedShift->early_leave_grace_minutes }}
                            Minutes</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-gray-50">
                        <span class="text-gray-500">Full Day Minimum:</span>
                        <span class="font-bold text-gray-800">{{ $selectedShift->standard_working_hours }}
                            Hours</span>
                    </div>
                    <div class="flex justify-between py-1">
                        <span class="text-gray-500">Half Day Minimum:</span>
                        <span class="font-bold text-gray-800">{{ $selectedShift->half_day_min_hours }} Hours</span>
                    </div>
                </div>

                <div class="pt-4 border-t border-gray-100 flex justify-end">
                    <button type="button" wire:click="closeModals"
                        class="px-4 py-2 bg-gray-100 text-gray-700 font-bold text-xs uppercase rounded-xl hover:bg-gray-200 transition">
                        Close
                    </button>
                </div>
            </div>
        </div>
        @endteleport
    @endif

    <!-- MODAL 3: DELETE CONFIRMATION -->
    @if ($showDeleteModal)
    @teleport('body')
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 p-4">
            <div
                class="bg-white rounded-2xl max-w-sm w-full p-6 shadow-xl border border-gray-100 text-center space-y-4">
                <div class="w-12 h-12 rounded-full bg-red-100 text-red-600 flex items-center justify-center mx-auto">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>

                <div>
                    <h4 class="text-base font-bold text-gray-900">Delete Shift Schedule?</h4>
                    <p class="text-xs text-gray-500 mt-1">Are you sure you want to delete <span
                            class="font-bold text-gray-800">"{{ $deletingShiftName }}"</span>? This action cannot be
                        undone.</p>
                </div>

                <div class="flex justify-center gap-3 pt-2">
                    <button type="button" wire:click="closeModals"
                        class="px-4 py-2 bg-gray-100 text-gray-700 font-bold text-xs uppercase rounded-xl hover:bg-gray-200 transition">
                        Cancel
                    </button>
                    <button type="button" wire:click="deleteShift"
                        class="px-4 py-2 bg-red-600 text-white font-bold text-xs uppercase rounded-xl hover:bg-red-700 transition">
                        Delete
                    </button>
                </div>
            </div>
        </div>
        @endteleport
    @endif

    <!-- MODAL 4: CREATE / EDIT BREAK TYPE -->
    @if ($showBreakTypeModal ?? false)
    @teleport('body')
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 p-4">
            <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-gray-100">
                <h3 class="text-base font-bold text-gray-800 mb-4">
                    @if ($editingBreakTypeId)
                        Edit Break Category
                    @else
                        Add New Break Category
                    @endif
                </h3>

                <form wire:submit="saveBreakType" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Break Name</label>
                        <input type="text" wire:model="break_type_name" placeholder="e.g. Tea Break"
                            class="w-full text-sm border-gray-200 rounded-xl focus:ring-blue-500">
                        @error('break_type_name')
                            <span class="text-xs text-red-500 block mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Code / Key (Unique)</label>
                        <input type="text" wire:model="break_type_code" placeholder="e.g. tea, lunch"
                            class="w-full text-sm border-gray-200 rounded-xl focus:ring-blue-500">
                        @error('break_type_code')
                            <span class="text-xs text-red-500 block mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Max Minutes Allowed</label>
                        <input type="number" wire:model="break_max_minutes"
                            class="w-full text-sm border-gray-200 rounded-xl focus:ring-blue-500">
                        @error('break_max_minutes')
                            <span class="text-xs text-red-500 block mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="space-y-2 pt-2">
                        <div class="flex items-center gap-2">
                            <input type="checkbox" id="break_is_paid_toggle" wire:model="break_is_paid"
                                class="rounded text-blue-600 focus:ring-blue-500">
                            <label for="break_is_paid_toggle" class="text-sm font-semibold text-gray-700">Is Paid
                                Break (Does not deduct work hours)</label>
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="checkbox" id="break_is_active_toggle" wire:model="break_is_active"
                                class="rounded text-blue-600 focus:ring-blue-500">
                            <label for="break_is_active_toggle" class="text-sm font-semibold text-gray-700">Active
                                Break Type</label>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-gray-100 flex justify-end gap-2">
                        <button type="button" wire:click="closeModals"
                            class="px-4 py-2 bg-gray-100 text-gray-700 font-bold text-xs uppercase rounded-xl hover:bg-gray-200 transition">
                            Cancel
                        </button>
                        <button type="submit"
                            class="px-5 py-2 bg-blue-600 text-white font-bold text-xs uppercase rounded-xl hover:bg-blue-700 transition">
                            Save Break Type
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @endteleport
    @endif

    <!-- MODAL: DELETE BREAK TYPE CONFIRMATION -->
    @if ($showDeleteBreakTypeModal ?? false)
    @teleport('body')
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 p-4">
            <div
                class="bg-white rounded-2xl max-w-sm w-full p-6 shadow-xl border border-gray-100 text-center space-y-4">
                <div class="w-12 h-12 rounded-full bg-red-100 text-red-600 flex items-center justify-center mx-auto">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>

                <div>
                    <h4 class="text-base font-bold text-gray-900">Delete Break Category?</h4>
                    <p class="text-xs text-gray-500 mt-1">Are you sure you want to delete <span
                            class="font-bold text-gray-800">"{{ $deletingBreakTypeName }}"</span>? This action cannot
                        be
                        undone.</p>
                </div>

                <div class="flex justify-center gap-3 pt-2">
                    <button type="button" wire:click="closeModals"
                        class="px-4 py-2 bg-gray-100 text-gray-700 font-bold text-xs uppercase rounded-xl hover:bg-gray-200 transition">
                        Cancel
                    </button>
                    <button type="button" wire:click="deleteBreakType"
                        class="px-4 py-2 bg-red-600 text-white font-bold text-xs uppercase rounded-xl hover:bg-red-700 transition">
                        Delete
                    </button>
                </div>
            </div>
        </div>
        @endteleport
    @endif

</div>