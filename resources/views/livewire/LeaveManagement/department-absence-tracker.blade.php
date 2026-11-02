<div class="max-w-90% mx-auto px-4 sm:px-6 lg:px-8 mt-6 space-y-6">

    <!-- Header Title & Filter Panel -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between border-b border-gray-100 pb-4 gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 tracking-tight">Department Absence & Workload Planner</h2>
            <p class="text-sm text-gray-500 font-medium mt-1">Track approved team leaves to manage daily operational tasks and capacities.</p>
        </div>

        <!-- Date Selector Input Form Component -->
        <div class="bg-white p-2 rounded-xl shadow-xs border border-gray-200 flex items-center gap-2">
            <label class="text-xs font-bold text-gray-500 uppercase tracking-wider pl-2">Target Date:</label>
            <input type="date" wire:model.live="searchDate" class="text-sm border-gray-200 rounded-lg focus:border-blue-500 focus:ring-blue-500 font-medium text-slate-700">
        </div>
    </div>

    <!-- MAIN DATA DISPLAY COMPONENT TABLE -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        
        <div class="p-6 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-sm font-bold text-gray-400 uppercase tracking-wider">
                Approved Absences Mapped on: <span class="text-blue-600 font-semibold">{{ \Carbon\Carbon::parse($searchDate)->format('F d, Y') }}</span>
            </h3>
            <span class="text-xs font-bold text-slate-500 bg-slate-50 border border-slate-200 px-3 py-1 rounded-lg">
                {{ $absentEmployeesCollection->count() }} Total Absences
            </span>
        </div>

        @if($absentEmployeesCollection->isEmpty())
        <!-- Empty State Display Element -->
        <div class="text-center py-12 bg-gray-50">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <h3 class="mt-2 text-sm font-bold text-gray-700">Full Capacity Restored</h3>
            <p class="mt-1 text-sm text-gray-500 font-medium">No approved employee absences documented inside this department boundaries for selected date.</p>
        </div>
        @else
        <!-- Responsive Data Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-left">
                <thead class="bg-gray-50/80">
                    <tr>
                        <th scope="col" class="px-6 py-3.5 text-xs font-bold text-gray-500 uppercase tracking-wider">Employee</th>
                        <th scope="col" class="px-6 py-3.5 text-xs font-bold text-gray-500 uppercase tracking-wider">Leave Type</th>
                        <th scope="col" class="px-6 py-3.5 text-xs font-bold text-gray-500 uppercase tracking-wider">Leave Period</th>
                        <th scope="col" class="px-6 py-3.5 text-xs font-bold text-gray-500 uppercase tracking-wider">Return Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @foreach($absentEmployeesCollection as $absenceRequestItem)
                    <tr class="hover:bg-gray-50/60 transition-colors">
                        
                        <!-- Employee Info -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 border border-blue-100 flex items-center justify-center font-bold text-xs">
                                    {{ mb_substr($absenceRequestItem->employee->user->name ?? 'E', 0, 1) }}
                                </div>
                                <div>
                                    <div class="text-sm font-bold text-gray-900">{{ $absenceRequestItem->employee->user->name }}</div>
                                    <div class="text-xs text-gray-400 font-mono">{{ $absenceRequestItem->employee->employee_id ?? 'EMP-Record' }}</div>
                                </div>
                            </div>
                        </td>

                        <!-- Leave Type Badge -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-orange-50 text-orange-700 border border-orange-100">
                                {{ $absenceRequestItem->applyLeaveTypes->first()?->leaveType?->name ?? 'Standard Leave' }}
                            </span>
                        </td>

                        <!-- Leave From Date -->
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-700">
                            {{ \Carbon\Carbon::parse($absenceRequestItem->start_date)->format('Y-m-d') }}
                        </td>

                        <!-- Return Date -->
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-700">
                            {{ \Carbon\Carbon::parse($absenceRequestItem->end_date)->format('Y-m-d') }}
                        </td>

                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

    </div>

</div>