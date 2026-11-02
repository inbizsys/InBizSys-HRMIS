<div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">

    <!-- Header Section -->
    <div class="bg-white border border-gray-200 rounded-2xl p-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4 shadow-sm">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Monthly Overtime (OT) Overview</h1>
            
        </div>

        <!-- Filter Controls -->
        <div class="flex flex-wrap items-center gap-3">
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Target Month</label>
                <input type="month" wire:model.live="selectedMonth"
                    class="text-sm border-gray-200 rounded-xl focus:ring-blue-500 font-semibold text-gray-800">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Department</label>
                <select wire:model.live="selectedDepartmentId"
                    class="text-sm border-gray-200 rounded-xl focus:ring-blue-500 font-medium">
                    <option value="">All Departments</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Category</label>
                <select wire:model.live="selectedCategoryId"
                    class="text-sm border-gray-200 rounded-xl focus:ring-blue-500 font-medium">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Eligibility</label>
                <select wire:model.live="filterEligibility"
                    class="text-sm border-gray-200 rounded-xl focus:ring-blue-500 font-medium">
                    <option value="all">All Overtime Staff</option>
                    <option value="eligible">Only OT Payable</option>
                    <option value="non_eligible">Non-Payable (Track Only)</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Summary Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white border border-gray-200 p-5 rounded-2xl shadow-xs">
            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block mb-1">Total Payable OT Cost</span>
            <span class="text-2xl font-extrabold text-blue-700">LKR {{ number_format($totalPayableAll, 2) }}</span>
            <span class="text-xs text-gray-500 block mt-1">Threshold qualified for {{ $selectedMonth }}</span>
        </div>

        <div class="bg-white border border-gray-200 p-5 rounded-2xl shadow-xs">
            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block mb-1">OT Eligible Staff</span>
            <span class="text-2xl font-extrabold text-emerald-700">{{ $totalEligibleCount }} Staff</span>
            <span class="text-xs text-gray-500 block mt-1">Earning cash overtime</span>
        </div>

        <div class="bg-white border border-gray-200 p-5 rounded-2xl shadow-xs">
            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block mb-1">Non-Payable Extra Hours</span>
            <span class="text-2xl font-extrabold text-purple-700">{{ number_format($totalExecutiveExtraHours, 2) }} hrs</span>
            <span class="text-xs text-gray-500 block mt-1">Tracked</span>
        </div>
    </div>

    <!-- Overtime Data Table -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-left">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3.5 text-xs font-bold text-gray-500 uppercase">Employee</th>
                        <th class="px-6 py-3.5 text-xs font-bold text-gray-500 uppercase">Category</th>
                        <th class="px-6 py-3.5 text-xs font-bold text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3.5 text-xs font-bold text-gray-500 uppercase">Normal OT</th>
                        <th class="px-6 py-3.5 text-xs font-bold text-gray-500 uppercase">Weekend OT</th>
                        <th class="px-6 py-3.5 text-xs font-bold text-gray-500 uppercase">Holiday OT</th>
                        <th class="px-6 py-3.5 text-xs font-bold text-gray-500 uppercase">Payable OT (LKR)</th>
                        <th class="px-6 py-3.5 text-center text-xs font-bold text-gray-500 uppercase">Daily Log</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse($otSummaryList as $item)
                        <tr class="hover:bg-gray-50/70 transition">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-bold text-gray-800">{{ $item['name'] }}</div>
                                <div class="text-xs font-mono text-gray-400">{{ $item['emp_no'] }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 font-medium">
                                {{ $item['category'] }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if ($item['can_earn_ot'])
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                                        ✓ OT Eligible
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-600 border border-gray-200">
                                        Non-Payable (Track Only)
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-blue-600">
                                {{ number_format($item['normal_ot_hours'], 2) }} hrs
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-amber-600">
                                {{ number_format($item['weekend_ot_hours'], 2) }} hrs
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-purple-600">
                                {{ number_format($item['holiday_ot_hours'], 2) }} hrs
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold">
                                @if ($item['can_earn_ot'])
                                    <span class="text-emerald-700">LKR {{ number_format($item['calculated_amount'], 2) }}</span>
                                @else
                                    <span class="text-gray-400 font-normal italic">LKR 0.00</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                <button type="button" wire:click="viewDailyDetails({{ $item['employee_id'] }})"
                                    class="inline-flex items-center px-3 py-1.5 text-xs font-bold text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-lg transition">
                                    View Days
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-8 text-center text-gray-400 text-sm">
                                No overtime records found for the selected criteria in this month.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- DAILY ATTENDANCE DRILL-DOWN AUDIT MODAL -->
    @if ($showDailyModal && $selectedEmployeeForModal)
    @teleport('body')
        <div class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            
            <!-- Modal Card (Balanced Width: max-w-4xl with shadow) -->
            <div class="relative bg-white rounded-2xl max-w-4xl w-full shadow-2xl border border-gray-100 overflow-hidden my-6">
                
                <!-- Modal Header -->
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/60">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 tracking-tight">
                            {{ $selectedEmployeeForModal->first_name }} {{ $selectedEmployeeForModal->last_name }}
                        </h3>
                        <p class="text-xs text-gray-500 mt-0.5">
                            Daily Overtime Audit Log for <span class="font-semibold text-gray-700">{{ $selectedMonth }}</span> (Threshold: {{ $policyThresholdMinutes }} mins)
                        </p>
                    </div>
                    <button type="button" wire:click="closeDailyModal" 
                        class="p-1.5 rounded-xl text-gray-400 hover:text-gray-700 hover:bg-gray-200/60 transition text-lg font-bold leading-none">
                        &times;
                    </button>
                </div>
                <!-- Table Container with Controlled Height -->
                <div class="max-h-[28rem] overflow-y-auto">
                    <table class="min-w-full text-left divide-y divide-gray-100">
                        <thead class="bg-gray-50/80 sticky top-0 z-10 border-b border-gray-100">
                            <tr>
                                <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider whitespace-nowrap">Date</th>
                                <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider whitespace-nowrap">Day Type</th>
                                <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider whitespace-nowrap">Punch In / Out</th>
                                <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider whitespace-nowrap">Extra Time</th>
                                <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider whitespace-nowrap">Policy Evaluation</th>
                                <th class="px-5 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider whitespace-nowrap">Accrued OT</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white text-sm">
                            @forelse($dailyBreakdownRecords as $record)
                                <tr class="hover:bg-blue-50/40 transition">
                                    <!-- Clean Date without 00:00:00 -->
                                    <td class="px-5 py-3.5 font-semibold text-gray-800 whitespace-nowrap">
                                        {{ substr($record['date'], 0, 10) }}
                                    </td>
                                    <td class="px-5 py-3.5 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold 
                                            {{ in_array($record['status'], ['holiday_present', 'weekend_present']) ? 'bg-purple-100 text-purple-700 border border-purple-200' : 'bg-gray-100 text-gray-700 border border-gray-200' }}">
                                            {{ $record['status'] }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3.5 text-gray-600 font-mono text-xs whitespace-nowrap">
                                        {{ $record['in_time'] }} - {{ $record['out_time'] }}
                                    </td>
                                    <td class="px-5 py-3.5 font-semibold text-gray-700 whitespace-nowrap">
                                        {{ $record['raw_ot_hours'] }} hrs
                                    </td>
                                    <td class="px-5 py-3.5 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $record['badge_color'] }}">
                                            {{ $record['badge_label'] }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3.5 font-bold text-blue-600 whitespace-nowrap">
                                        {{ $record['final_ot_hours'] > 0 ? $record['final_ot_hours'] . ' hrs' : '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-5 py-8 text-center text-gray-400 text-sm">
                                        No overtime or extra hours recorded for this employee in this month.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <!-- Modal Footer -->
                <div class="px-6 py-3.5 border-t border-gray-100 bg-gray-50/60 flex justify-end">
                    <button type="button" wire:click="closeDailyModal"
                        class="px-5 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-semibold rounded-xl shadow-xs transition">
                        Close
                    </button>
                </div>
            </div>
        </div>
    @endteleport
    @endif

</div>