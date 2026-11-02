<div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">

    <!-- Header Section with Filters and Actions -->
    <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm space-y-4">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Monthly Payroll Processing</h1>
                <p class="text-xs text-gray-500 mt-1">
                    Calculate monthly salaries, Overtime, No-Pay, EPF/ETF statutory contributions, and net pay.
                </p>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-wrap items-center gap-3">
                <!-- Process / Calculate Payroll -->
                <button type="button" wire:click="processMonthlyPayroll" wire:loading.attr="disabled"
                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-xl shadow-xs transition disabled:opacity-50">
                    <span wire:loading.remove wire:target="processMonthlyPayroll">⚡ Process / Calculate Payroll</span>
                    <span wire:loading wire:target="processMonthlyPayroll">Calculating...</span>
                </button>

                <!-- Approve Selected: Only visible if Draft (processed) records are selected -->
                @if ($selectedProcessedCount > 0)
                    <button type="button" wire:click="openApproveModal"
                        class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold rounded-xl shadow-xs transition">
                        ✓ Approve Selected ({{ $selectedProcessedCount }})
                    </button>
                @endif

                <!-- Reverse Selected: Only visible if Approved records are selected -->
                @if ($selectedApprovedCount > 0)
                    <button type="button" wire:click="openSelectiveReverseModal"
                        class="inline-flex items-center gap-2 px-4 py-2.5 bg-amber-600 hover:bg-amber-700 text-white text-sm font-bold rounded-xl shadow-xs transition">
                        ↩ Reverse Selected ({{ $selectedApprovedCount }})
                    </button>
                @endif

                <!-- Disabled button when nothing is selected -->
                @if ($selectedProcessedCount === 0 && $selectedApprovedCount === 0)
                    <button type="button" disabled
                        class="inline-flex items-center gap-2 px-4 py-2.5 bg-gray-200 text-gray-400 text-sm font-bold rounded-xl cursor-not-allowed opacity-60">
                        ✓ Approve Selected (0)
                    </button>
                @endif

                <!-- In-page Bulk Print (Self-contained) -->
                @if ($hasApprovedRecords)
                    <button type="button" onclick="printBulkPayslips()"
                        class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl shadow-xs transition">
                        🖨️ Bulk Print Approved Slips
                    </button>
                @endif
            </div>
        </div>

        <!-- Filter Controls -->
        <div class="flex flex-wrap items-center gap-3 pt-3 border-t border-gray-100">
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Target Month</label>
                <input type="month" wire:model.live="selectedMonth"
                    class="text-sm border-gray-200 rounded-xl focus:ring-blue-500 font-semibold text-gray-800">
            </div>

            <!-- Employee Search Input -->
            <div class="flex-1 min-w-[180px]">
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Search Employee</label>
                <div class="relative">
                    <input type="text" wire:model.live.debounce.300ms="searchEmployee"
                        placeholder="Search name or ID..."
                        class="w-full text-sm border-gray-200 rounded-xl focus:ring-blue-500 font-medium pl-8">
                    <svg class="w-4 h-4 text-gray-400 absolute left-2.5 top-3" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Department</label>
                <select wire:model.live="selectedDepartmentId"
                    class="text-sm border-gray-200 rounded-xl focus:ring-blue-500 font-medium">
                    <option value="">All Departments</option>
                    @foreach ($departments as $department)
                        <option value="{{ $department->id }}">{{ $department->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Category</label>
                <select wire:model.live="selectedCategoryId"
                    class="text-sm border-gray-200 rounded-xl focus:ring-blue-500 font-medium">
                    <option value="">All Categories</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Status</label>
                <select wire:model.live="selectedStatus"
                    class="text-sm border-gray-200 rounded-xl focus:ring-blue-500 font-medium">
                    <option value="all">All Statuses</option>
                    <option value="processed">Processed (Draft)</option>
                    <option value="approved">Approved</option>
                    <option value="paid">Paid</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Alert Notifications -->
    @if ($feedbackMessage)
        <div
            class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl text-sm flex items-center justify-between">
            <span class="font-medium">✓ {{ $feedbackMessage }}</span>
            <button type="button" wire:click="$set('feedbackMessage', '')"
                class="text-emerald-500 hover:text-emerald-700 font-bold">&times;</button>
        </div>
    @endif

    @if ($errorMessage)
        <div
            class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl text-sm flex items-center justify-between">
            <span class="font-medium">⚠ {{ $errorMessage }}</span>
            <button type="button" wire:click="$set('errorMessage', '')"
                class="text-rose-500 hover:text-rose-700 font-bold">&times;</button>
        </div>
    @endif

    <!-- Summary Stats Cards (Dynamic according to active filters) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <!-- 1. Total Net Payable -->
        <div class="bg-white border border-gray-200 p-4 rounded-2xl shadow-xs">
            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block mb-1">Total Net Payable</span>
            <span class="text-xl font-extrabold text-emerald-700">LKR {{ number_format($totalNetPayable, 2) }}</span>
            <span class="text-[11px] text-gray-500 block mt-1">Take-home for {{ $selectedMonth }}</span>
        </div>

        <!-- 2. Gross Salary Total -->
        <div class="bg-white border border-gray-200 p-4 rounded-2xl shadow-xs">
            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block mb-1">Gross Salary Total</span>
            <span class="text-xl font-extrabold text-blue-700">LKR {{ number_format($totalGrossCost, 2) }}</span>
            <span class="text-[11px] text-gray-500 block mt-1">Earnings before deductions</span>
        </div>

        <!-- 3. Total EPF (20% = 12% Employer + 8% Employee) -->
        <div class="bg-white border border-gray-200 p-4 rounded-2xl shadow-xs">
            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block mb-1">Total EPF</span>
            <span class="text-xl font-extrabold text-purple-700">LKR {{ number_format($totalEpfEmployer + $totalEpfEmployee, 2) }}</span>
            <span class="text-[11px] text-gray-500 block mt-1">Co. : {{ number_format($totalEpfEmployer, 2) }} | Emp.: {{ number_format($totalEpfEmployee, 2) }}</span>
        </div>

        <!-- 4. Total ETF (3% Employer) -->
        <div class="bg-white border border-gray-200 p-4 rounded-2xl shadow-xs">
            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block mb-1">Total ETF </span>
            <span class="text-xl font-extrabold text-indigo-700">LKR {{ number_format($totalEtfEmployer, 2) }}</span>
            <span class="text-[11px] text-gray-500 block mt-1">Employer to ETF Board</span>
        </div>

        <!-- 5. Total Cost to Company (CTC) -->
        <div class="bg-white border border-gray-200 p-4 rounded-2xl shadow-xs">
            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block mb-1">Total CTC</span>
            <span class="text-xl font-extrabold text-slate-800">LKR {{ number_format($totalCompanyCost, 2) }}</span>
            <span class="text-[11px] text-gray-500 block mt-1">Gross (-NoPay) + Co. EPF/ETF</span>
        </div>
    </div>

    <!-- Payroll Data Table with Checkboxes -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-left">
                <thead class="bg-gray-50 text-xs font-bold text-gray-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-3.5 text-center">
                            <input type="checkbox" wire:model.live="selectAll"
                                class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                        </th>
                        <th class="px-5 py-3.5 whitespace-nowrap">Employee</th>
                        <th class="px-5 py-3.5 whitespace-nowrap">Basic Salary</th>
                        <th class="px-5 py-3.5 whitespace-nowrap">Allowances</th>
                        <th class="px-5 py-3.5 whitespace-nowrap">Overtime</th>
                        <th class="px-5 py-3.5 whitespace-nowrap">No-Pay</th>
                        <th class="px-5 py-3.5 whitespace-nowrap">EPF</th>
                        <th class="px-5 py-3.5 whitespace-nowrap">Net Salary</th>
                        <th class="px-5 py-3.5 whitespace-nowrap text-center">Status</th>
                        <th class="px-5 py-3.5 whitespace-nowrap text-center">Payslip</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white text-sm">
                    @forelse($payrollsList as $payrollRecord)
                        <tr
                            class="hover:bg-gray-50/70 transition {{ in_array((string) $payrollRecord->id, $selectedPayrollIds) ? 'bg-blue-50/30' : '' }}">
                            <td class="px-4 py-4 text-center">
                                @if (in_array($payrollRecord->status, ['processed', 'approved']))
                                    <input type="checkbox" wire:model.live="selectedPayrollIds"
                                        value="{{ $payrollRecord->id }}"
                                        class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                                @else
                                    <span class="text-blue-600 text-xs font-bold">Paid</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap">
                                <div class="font-bold text-gray-900">{{ $payrollRecord->employee?->first_name }}
                                    {{ $payrollRecord->employee?->last_name }}</div>
                                <div class="text-xs text-gray-400 font-mono">
                                    {{ $payrollRecord->employee?->employee_id }} •
                                    {{ $payrollRecord->employee?->department?->name ?? 'General' }}</div>
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap font-medium text-gray-700">
                                {{ number_format($payrollRecord->basic_salary, 2) }}
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap text-gray-600">
                                {{ number_format($payrollRecord->epf_applicable_allowances + $payrollRecord->other_allowances, 2) }}
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap text-blue-600 font-semibold">
                                +{{ number_format($payrollRecord->total_ot_amount, 2) }}
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap text-rose-600 font-medium">
                                @if ($payrollRecord->no_pay_amount > 0)
                                    -{{ number_format($payrollRecord->no_pay_amount, 2) }}
                                    <span
                                        class="text-xs text-gray-400 block font-normal">({{ $payrollRecord->absent_days }}
                                        days)</span>
                                @else
                                    <span class="text-gray-400 font-normal">-</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap text-amber-700 font-medium">
                                -{{ number_format($payrollRecord->epf_employee_amount, 2) }}
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap font-extrabold text-emerald-700 text-base">
                                LKR {{ number_format($payrollRecord->net_salary, 2) }}
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap text-center">
                                @if ($payrollRecord->status === 'approved')
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        Approved
                                    </span>
                                @elseif ($payrollRecord->status === 'paid')
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-800 border border-blue-200">
                                        Paid
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 border border-amber-200">
                                        Processed (Draft)
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap text-center">
                                <button type="button" wire:click="viewPayslip({{ $payrollRecord->id }})"
                                    class="inline-flex items-center px-3 py-1.5 text-xs font-bold text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-lg transition">
                                    View Slip
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-6 py-10 text-center text-gray-400 text-sm">
                                No payroll records found for <span
                                    class="font-semibold text-gray-600">{{ $selectedMonth }}</span>. Click
                                <strong>"Process / Calculate Payroll"</strong> above to generate salaries.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-gray-100">
            {{ $payrollsList->links() }}
        </div>
    </div>

    <!-- APPROVE CONFIRMATION MODAL -->
    @if ($showApproveConfirmModal)
        @teleport('body')
            <div
                class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/60 backdrop-blur-xs flex items-center justify-center p-4">
                <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-gray-100 space-y-4">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center font-bold text-lg">
                            ✓
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-gray-900">Approve Selected Payrolls?</h3>
                            <p class="text-xs text-gray-500">Selected Count: <span
                                    class="font-bold text-gray-800">{{ count($selectedPayrollIds) }} Employees</span></p>
                        </div>
                    </div>

                    <p class="text-xs text-gray-600 leading-relaxed">
                        Are you sure you want to approve and finalize the selected payroll records for <strong
                            class="text-gray-900">{{ $selectedMonth }}</strong>? Once approved, these records will be
                        locked.
                    </p>

                    <div class="flex justify-end gap-3 pt-3 border-t border-gray-100">
                        <button type="button" wire:click="closeApproveModal"
                            class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-xl transition">
                            Cancel
                        </button>
                        <button type="button" wire:click="approveSelectedPayroll"
                            class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-xs transition">
                            Yes, Approve Selected
                        </button>
                    </div>
                </div>
            </div>
        @endteleport
    @endif

    <!-- SELECTIVE REVERSE CONFIRMATION MODAL -->
    @if ($showSelectiveReverseConfirmModal)
        @teleport('body')
            <div
                class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/60 backdrop-blur-xs flex items-center justify-center p-4">
                <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-gray-100 space-y-4">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-10 h-10 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center font-bold text-lg">
                            ↩
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-gray-900">Reverse Selected Records?</h3>
                            <p class="text-xs text-gray-500">Selected Count: <span
                                    class="font-bold text-gray-800">{{ $selectedApprovedCount }} Approved Employees</span></p>
                        </div>
                    </div>

                    <p class="text-xs text-gray-600 leading-relaxed">
                        Are you sure you want to reverse the selected <strong class="text-amber-700">{{ $selectedApprovedCount }} approved employee record(s)</strong> back to <strong class="text-gray-900">Draft (Processed)</strong>?
                        Only these selected records will be unlocked for re-calculation, leaving all other employees approved.
                    </p>

                    <div class="flex justify-end gap-3 pt-3 border-t border-gray-100">
                        <button type="button" wire:click="closeSelectiveReverseModal"
                            class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-xl transition">
                            Cancel
                        </button>
                        <button type="button" wire:click="reverseSelectedPayroll"
                            class="px-5 py-2 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold rounded-xl shadow-xs transition">
                            Yes, Reverse Selected
                        </button>
                    </div>
                </div>
            </div>
        @endteleport
    @endif

    <!-- INDIVIDUAL PAYSLIP MODAL (EXCEL CORPORATE GRID FORMAT) -->
    @if ($showPayslipModal && $selectedPayrollForModal)
        @teleport('body')
            <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-xs">
                <div class="relative bg-white rounded-lg max-w-4xl w-full max-h-[92vh] flex flex-col shadow-2xl border border-gray-400 overflow-hidden font-sans">
                    
                    <!-- Scrollable Payslip Body -->
                    <div class="p-6 overflow-y-auto flex-1 bg-white text-gray-900 text-xs">
                        
                        <!-- Outer Bordered Grid Wrapper -->
                        <div id="printablePayslip" class="payslip-sheet w-full border-2 border-black divide-y-2 divide-black text-xs text-black bg-white">
                            
                            <!-- 1. Header Section -->
                            <div class="text-center py-2 px-4 bg-gray-50/50">
                                <h1 class="text-2xl font-black tracking-wide text-black uppercase">
                                    {{ $organisation->name ?? 'InBizSys (Pvt) Ltd' }}
                                </h1>
                                <p class="text-xs font-semibold text-gray-700 mt-0.5">
                                    {{ implode(' ', array_filter([$organisation->address_line_1, $organisation->address_line_2, $organisation->city, $organisation->state])) }}
                                </p>
                            </div>
                            <!-- 2. Pay Period Title Bar -->
                            <div class="grid grid-cols-4 divide-x divide-black font-bold text-center bg-gray-100/70">
                                <div class="col-span-3 py-1.5 text-sm uppercase">Salary Slip For the month of</div>
                                <div class="py-1.5 text-sm text-black">
                                    {{ date('M-y', strtotime($selectedPayrollForModal->payroll_month . '-01')) }}
                                </div>
                            </div>
                            <!-- 3. Employee Information Header -->
                            <div class="text-center py-1 font-bold bg-gray-200/80 uppercase tracking-wider text-xs">
                                Employee Information
                            </div>
                            <!-- 4. Employee Information Grid -->
                            <div class="divide-y divide-black">
                                <div class="grid grid-cols-4 divide-x divide-black">
                                    <div class="px-2 py-1 font-bold bg-gray-50">UID:</div>
                                    <div class="px-2 py-1 font-semibold">{{ $selectedPayrollForModal->employee?->employee_id }}</div>
                                    <div class="px-2 py-1 font-bold bg-gray-50">Designation:</div>
                                    <div class="px-2 py-1 font-semibold">{{ $selectedPayrollForModal->employee?->job_position ?? 'Employee' }}</div>
                                </div>
                                <div class="grid grid-cols-4 divide-x divide-black">
                                    <div class="px-2 py-1 font-bold bg-gray-50">Name:</div>
                                    <div class="px-2 py-1 font-semibold">
                                        {{ $selectedPayrollForModal->employee?->first_name }} {{ $selectedPayrollForModal->employee?->last_name }}
                                    </div>
                                    <div class="px-2 py-1 font-bold bg-gray-50">EPF No:</div>
                                    <div class="px-2 py-1 font-semibold">
                                        {{ $selectedPayrollForModal->employee?->epf_number ?? $selectedPayrollForModal->employee?->employee_id }}
                                    </div>
                                </div>
                            </div>
                            <!-- 5. Attendance & Bank Details Header -->
                            <div class="grid grid-cols-2 divide-x-2 divide-black">
                                <div class="text-center py-1 font-bold bg-gray-200/80 uppercase">Employee Attendance</div>
                                <div class="text-center py-1 font-bold bg-gray-200/80 uppercase">Salary Transferred To</div>
                            </div>
                            <!-- 6. Attendance & Bank Details Data -->
                            <div class="grid grid-cols-2 divide-x-2 divide-black">
                                <!-- Attendance Sub-grid -->
                                <div class="divide-y divide-black">
                                    <div class="grid grid-cols-4 divide-x divide-black">
                                        <div class="px-2 py-1 font-bold bg-gray-50">Working Days:</div>
                                        <div class="px-2 py-1 text-center font-semibold">
                                            {{ $selectedPayrollForModal->employee?->employeeCategory?->standard_monthly_days ?? 26 }}
                                        </div>
                                        <div class="px-2 py-1 font-bold bg-gray-50">Overtime Hours:</div>
                                        <div class="px-2 py-1 text-center font-semibold">
                                            {{ number_format($selectedPayrollForModal->normal_ot_hours + $selectedPayrollForModal->weekend_ot_hours + $selectedPayrollForModal->holiday_ot_hours, 1) }}
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-4 divide-x divide-black">
                                        <div class="px-2 py-1 font-bold bg-gray-50">Leave Allowed:</div>
                                        <div class="px-2 py-1 text-center font-semibold">{{ $payslipLeaveAllowed }}</div>
                                        <div class="px-2 py-1 font-bold bg-gray-50">No Pay Leave:</div>
                                        <div class="px-2 py-1 text-center font-semibold text-rose-700">
                                            {{ (int) $selectedPayrollForModal->absent_days }}
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-4 divide-x divide-black">
                                        <div class="px-2 py-1 font-bold bg-gray-50">Leave Taken:</div>
                                        <div class="px-2 py-1 text-center font-semibold">{{ $payslipLeaveTaken }}</div>
                                        <div class="col-span-2 bg-gray-50"></div>
                                    </div>
                                </div>
                                <!-- Bank Details Sub-grid -->
                                <div class="divide-y divide-black">
                                    <div class="grid grid-cols-3 divide-x divide-black">
                                        <div class="px-2 py-1 font-bold bg-gray-50">Bank Name:</div>
                                        <div class="col-span-2 px-2 py-1 font-semibold">{{ $selectedPayrollForModal->bank_name ?? 'N/A' }}</div>
                                    </div>
                                    <div class="grid grid-cols-3 divide-x divide-black">
                                        <div class="px-2 py-1 font-bold bg-gray-50">Account No:</div>
                                        <div class="col-span-2 px-2 py-1 font-semibold">{{ $selectedPayrollForModal->account_number ?? 'N/A' }}</div>
                                    </div>
                                    <div class="grid grid-cols-3 divide-x divide-black">
                                        <div class="px-2 py-1 font-bold bg-gray-50">Branch Name:</div>
                                        <div class="col-span-2 px-2 py-1 font-semibold">{{ $selectedPayrollForModal->branch_name ?? 'N/A' }}</div>
                                    </div>
                                </div>
                            </div>
                            <!-- 7. Salary Calculations Title Header -->
                            <div class="text-center py-1 font-bold bg-gray-200/80 uppercase tracking-wider text-xs">
                                Salary Calculations
                            </div>
                            <!-- 8. Calculations Main Two-Column Box -->
                            <div class="grid grid-cols-2 divide-x-2 divide-black">
                                
                                <!-- LEFT COLUMN: Earnings & Perks -->
                                <div class="divide-y divide-black flex flex-col justify-between">
                                    <div>
                                        <div class="flex justify-between px-2 py-1 font-bold bg-gray-50">
                                            <span>Basic Salary</span>
                                            <span>LKR {{ number_format($selectedPayrollForModal->basic_salary, 2) }}</span>
                                        </div>
                                        <div class="px-2 py-0.5 font-bold text-gray-600 bg-gray-100/50">Allowances:</div>
                                        
                                        <!-- Itemized Allowances -->
                                        @if (!empty($selectedPayrollForModal->allowance_breakdown))
                                            @foreach ($selectedPayrollForModal->allowance_breakdown as $item)
                                                <div class="flex justify-between px-4 py-0.5 border-b border-gray-200">
                                                    <span>{{ $item['name'] }}</span>
                                                    <span>LKR {{ number_format($item['amount'], 2) }}</span>
                                                </div>
                                            @endforeach
                                        @else
                                            <div class="px-4 py-0.5 text-gray-400">None</div>
                                        @endif
                                        <div class="flex justify-between px-2 py-1 font-bold bg-gray-50 border-t border-black">
                                            <span>Total Allowances</span>
                                            <span>LKR {{ number_format($selectedPayrollForModal->gross_salary - $selectedPayrollForModal->basic_salary - $selectedPayrollForModal->total_ot_amount, 2) }}</span>
                                        </div>
                                        <div class="flex justify-between px-2 py-1 font-black bg-gray-100 border-t border-black">
                                            <span>Gross Salary</span>
                                            <span>LKR {{ number_format($selectedPayrollForModal->gross_salary - $selectedPayrollForModal->total_ot_amount, 2) }}</span>
                                        </div>
                                        <div class="px-2 py-0.5 font-bold text-gray-600 bg-gray-100/50 border-t border-black">Additional Perks:</div>
                                        <div class="flex justify-between px-4 py-0.5">
                                            <span>Overtime</span>
                                            <span>LKR {{ number_format($selectedPayrollForModal->total_ot_amount, 2) }}</span>
                                        </div>
                                        
                                    </div>
                                    <div class="flex justify-between px-2 py-1.5 font-black bg-gray-200 border-t-2 border-black">
                                        <span>Salary Before Deduction</span>
                                        <span>LKR {{ number_format($selectedPayrollForModal->gross_salary, 2) }}</span>
                                    </div>
                                </div>
                                <!-- RIGHT COLUMN: Deductions & Employer Shares -->
                                <div class="divide-y divide-black flex flex-col justify-between">
                                    <div>
                                        <div class="px-2 py-1 font-bold bg-gray-50">Deductions</div>
                                        
                                        <div class="flex justify-between px-4 py-0.5 border-b border-gray-200 text-rose-700 font-medium">
                                            <span>No Pay Days Deductions</span>
                                            <span>LKR {{ number_format($selectedPayrollForModal->no_pay_amount, 2) }}</span>
                                        </div>
                                        
                                        <div class="flex justify-between px-4 py-0.5 border-b border-gray-200 font-medium">
                                            <span>EPF Employee Contribution ({{ $selectedPayrollForModal->epf_employee_rate }}%)</span>
                                            <span>LKR {{ number_format($selectedPayrollForModal->epf_employee_amount, 2) }}</span>
                                        </div>
                                        <!-- Other deductions if any -->
                                        @if (!empty($selectedPayrollForModal->deduction_breakdown))
                                            @foreach ($selectedPayrollForModal->deduction_breakdown as $dItem)
                                                <div class="flex justify-between px-4 py-0.5 border-b border-gray-200">
                                                    <span>{{ $dItem['name'] }}</span>
                                                    <span>LKR {{ number_format($dItem['amount'], 2) }}</span>
                                                </div>
                                            @endforeach
                                        @endif
                                        <div class="flex justify-between px-2 py-1 font-bold bg-gray-50 border-t border-black">
                                            <span>Total Deductions</span>
                                            <span>LKR {{ number_format($selectedPayrollForModal->total_deductions, 2) }}</span>
                                        </div>
                                        <!-- Employer Contributions -->
                                        <div class="flex justify-between px-4 py-1 border-t border-black bg-purple-50/50">
                                            <span class="font-bold">EPF Employer Contribution ({{ $selectedPayrollForModal->epf_employer_rate }}%)</span>
                                            <span class="font-bold">LKR {{ number_format($selectedPayrollForModal->epf_employer_amount, 2) }}</span>
                                        </div>
                                        <div class="flex justify-between px-4 py-1 border-t border-gray-300 bg-indigo-50/50">
                                            <span class="font-bold">ETF Employer Contribution ({{ $selectedPayrollForModal->etf_employer_rate }}%)</span>
                                            <span class="font-bold">LKR {{ number_format($selectedPayrollForModal->etf_employer_amount, 2) }}</span>
                                        </div>
                                    </div>
                                    <div class="flex justify-between px-2 py-1.5 font-black bg-emerald-100 text-emerald-950 border-t-2 border-black text-sm">
                                        <span>Net Payable Salary</span>
                                        <span>LKR {{ number_format($selectedPayrollForModal->net_salary, 2) }}</span>
                                    </div>
                                </div>
                            </div>
                            <!-- 9. Amount in Words -->
                            <div class="grid grid-cols-4 divide-black bg-gray-50 py-1.5 px-2 font-bold">
                                <div class="col-span-1">Amount in Words:</div>
                                <div class="col-span-3 font-semibold text-gray-800 italic">
                                    {{ $payslipAmountInWords }} 
                                </div>
                            </div>

                        </div>
                    </div>
                    <!-- Modal Actions Footer -->
                    <div class="px-6 py-3 bg-gray-100 border-t border-gray-300 flex justify-end gap-3">
                        <button type="button" onclick="printPayslipDocument()" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg text-xs shadow-sm transition">
                            🖨️ Print Payslip
                        </button>
                        <button type="button" wire:click="closePayslipModal" class="px-4 py-2 bg-gray-700 hover:bg-gray-800 text-white font-bold rounded-lg text-xs shadow-sm transition">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        @endteleport
    @endif

    <!-- HIDDEN CONTAINER FOR BULK PRINTING (EXACT SAME CORPORATE EXCEL FORMAT) -->
    <div id="bulkPrintContainer" style="display: none;">
        @foreach ($approvedPayrollsForPrint as $bPayroll)
            <div class="payslip-sheet w-full border-2 border-black divide-y-2 divide-black text-xs text-black bg-white mb-6">
                <!-- 1. Header Section -->
                <div class="text-center py-2 px-4 bg-gray-50/50">
                    <h1 class="text-2xl font-black tracking-wide text-black uppercase">
                        {{ $organisation->name ?? 'InBizSys (Pvt) Ltd' }}
                    </h1>
                    <p class="text-xs font-semibold text-gray-700 mt-0.5">
                        {{ implode(' ', array_filter([$organisation->address_line_1, $organisation->address_line_2, $organisation->city, $organisation->state])) }}
                    </p>
                </div>
                <!-- 2. Pay Period Title Bar -->
                <div class="grid grid-cols-4 divide-x divide-black font-bold text-center bg-gray-100/70">
                    <div class="col-span-3 py-1.5 text-sm uppercase">Salary Slip For the month of</div>
                    <div class="py-1.5 text-sm text-black">
                        {{ date('M-y', strtotime($bPayroll->payroll_month . '-01')) }}
                    </div>
                </div>
                <!-- 3. Employee Information Header -->
                <div class="text-center py-1 font-bold bg-gray-200/80 uppercase tracking-wider text-xs">
                    Employee Information
                </div>
                <!-- 4. Employee Information Grid -->
                <div class="divide-y divide-black">
                    <div class="grid grid-cols-4 divide-x divide-black">
                        <div class="px-2 py-1 font-bold bg-gray-50">UID:</div>
                        <div class="px-2 py-1 font-semibold">{{ $bPayroll->employee?->employee_id }}</div>
                        <div class="px-2 py-1 font-bold bg-gray-50">Designation:</div>
                        <div class="px-2 py-1 font-semibold">{{ $bPayroll->employee?->job_position ?? 'Employee' }}</div>
                    </div>
                    <div class="grid grid-cols-4 divide-x divide-black">
                        <div class="px-2 py-1 font-bold bg-gray-50">Name:</div>
                        <div class="px-2 py-1 font-semibold">
                            {{ $bPayroll->employee?->first_name }} {{ $bPayroll->employee?->last_name }}
                        </div>
                        <div class="px-2 py-1 font-bold bg-gray-50">EPF No:</div>
                        <div class="px-2 py-1 font-semibold">
                            {{ $bPayroll->employee?->epf_number ?? $bPayroll->employee?->employee_id }}
                        </div>
                    </div>
                </div>
                <!-- 5. Attendance & Bank Details Header -->
                <div class="grid grid-cols-2 divide-x-2 divide-black">
                    <div class="text-center py-1 font-bold bg-gray-200/80 uppercase">Employee Attendance</div>
                    <div class="text-center py-1 font-bold bg-gray-200/80 uppercase">Salary Transferred To</div>
                </div>
                <!-- 6. Attendance & Bank Details Data -->
                <div class="grid grid-cols-2 divide-x-2 divide-black">
                    <!-- Attendance Sub-grid -->
                    <div class="divide-y divide-black">
                        <div class="grid grid-cols-4 divide-x divide-black">
                            <div class="px-2 py-1 font-bold bg-gray-50">Working Days:</div>
                            <div class="px-2 py-1 text-center font-semibold">
                                {{ $bPayroll->employee?->employeeCategory?->standard_monthly_days ?? 26 }}
                            </div>
                            <div class="px-2 py-1 font-bold bg-gray-50">Overtime Hours:</div>
                            <div class="px-2 py-1 text-center font-semibold">
                                {{ number_format($bPayroll->normal_ot_hours + $bPayroll->weekend_ot_hours + $bPayroll->holiday_ot_hours, 1) }}
                            </div>
                        </div>
                        <div class="grid grid-cols-4 divide-x divide-black">
                            <div class="px-2 py-1 font-bold bg-gray-50">Leave Allowed:</div>
                            <div class="px-2 py-1 text-center font-semibold">{{ $bPayroll->print_leave_allowed }}</div>
                            <div class="px-2 py-1 font-bold bg-gray-50">No Pay Leave:</div>
                            <div class="px-2 py-1 text-center font-semibold text-rose-700">
                                {{ (int) $bPayroll->absent_days }}
                            </div>
                        </div>
                        <div class="grid grid-cols-4 divide-x divide-black">
                            <div class="px-2 py-1 font-bold bg-gray-50">Leave Taken:</div>
                            <div class="px-2 py-1 text-center font-semibold">{{ $bPayroll->print_leave_taken }}</div>
                            <div class="col-span-2 bg-gray-50"></div>
                        </div>
                    </div>
                    <!-- Bank Details Sub-grid -->
                    <div class="divide-y divide-black">
                        <div class="grid grid-cols-3 divide-x divide-black">
                            <div class="px-2 py-1 font-bold bg-gray-50">Bank Name:</div>
                            <div class="col-span-2 px-2 py-1 font-semibold">{{ $bPayroll->bank_name ?? 'N/A' }}</div>
                        </div>
                        <div class="grid grid-cols-3 divide-x divide-black">
                            <div class="px-2 py-1 font-bold bg-gray-50">Account No:</div>
                            <div class="col-span-2 px-2 py-1 font-semibold">{{ $bPayroll->account_number ?? 'N/A' }}</div>
                        </div>
                        <div class="grid grid-cols-3 divide-x divide-black">
                            <div class="px-2 py-1 font-bold bg-gray-50">Branch Name:</div>
                            <div class="col-span-2 px-2 py-1 font-semibold">{{ $bPayroll->branch_name ?? 'N/A' }}</div>
                        </div>
                    </div>
                </div>
                <!-- 7. Salary Calculations Title Header -->
                <div class="text-center py-1 font-bold bg-gray-200/80 uppercase tracking-wider text-xs">
                    Salary Calculations
                </div>
                <!-- 8. Calculations Main Two-Column Box -->
                <div class="grid grid-cols-2 divide-x-2 divide-black">
                    <!-- LEFT COLUMN: Earnings & Perks -->
                    <div class="divide-y divide-black flex flex-col justify-between">
                        <div>
                            <div class="flex justify-between px-2 py-1 font-bold bg-gray-50">
                                <span>Basic Salary</span>
                                <span>LKR {{ number_format($bPayroll->basic_salary, 2) }}</span>
                            </div>
                            <div class="px-2 py-0.5 font-bold text-gray-600 bg-gray-100/50">Allowances:</div>
                            @if (!empty($bPayroll->allowance_breakdown))
                                @foreach ($bPayroll->allowance_breakdown as $item)
                                    <div class="flex justify-between px-4 py-0.5 border-b border-gray-200">
                                        <span>{{ $item['name'] }}</span>
                                        <span>LKR {{ number_format($item['amount'], 2) }}</span>
                                    </div>
                                @endforeach
                            @else
                                <div class="px-4 py-0.5 text-gray-400">None</div>
                            @endif
                            <div class="flex justify-between px-2 py-1 font-bold bg-gray-50 border-t border-black">
                                <span>Total Allowances</span>
                                <span>LKR {{ number_format($bPayroll->gross_salary - $bPayroll->basic_salary - $bPayroll->total_ot_amount, 2) }}</span>
                            </div>
                            <div class="flex justify-between px-2 py-1 font-black bg-gray-100 border-t border-black">
                                <span>Gross Salary</span>
                                <span>LKR {{ number_format($bPayroll->gross_salary - $bPayroll->total_ot_amount, 2) }}</span>
                            </div>
                            <div class="px-2 py-0.5 font-bold text-gray-600 bg-gray-100/50 border-t border-black">Additional Perks:</div>
                            <div class="flex justify-between px-4 py-0.5">
                                <span>Overtime</span>
                                <span>LKR {{ number_format($bPayroll->total_ot_amount, 2) }}</span>
                            </div>
                        </div>
                        <div class="flex justify-between px-2 py-1.5 font-black bg-gray-200 border-t-2 border-black">
                            <span>Salary Before Deduction</span>
                            <span>LKR {{ number_format($bPayroll->gross_salary, 2) }}</span>
                        </div>
                    </div>
                    <!-- RIGHT COLUMN: Deductions & Employer Shares -->
                    <div class="divide-y divide-black flex flex-col justify-between">
                        <div>
                            <div class="px-2 py-1 font-bold bg-gray-50">Deductions</div>
                            <div class="flex justify-between px-4 py-0.5 border-b border-gray-200 text-rose-700 font-medium">
                                <span>No Pay Days Deductions</span>
                                <span>LKR {{ number_format($bPayroll->no_pay_amount, 2) }}</span>
                            </div>
                            <div class="flex justify-between px-4 py-0.5 border-b border-gray-200 font-medium">
                                <span>EPF Employee Contribution ({{ $bPayroll->epf_employee_rate }}%)</span>
                                <span>LKR {{ number_format($bPayroll->epf_employee_amount, 2) }}</span>
                            </div>
                            @if (!empty($bPayroll->deduction_breakdown))
                                @foreach ($bPayroll->deduction_breakdown as $dItem)
                                    <div class="flex justify-between px-4 py-0.5 border-b border-gray-200">
                                        <span>{{ $dItem['name'] }}</span>
                                        <span>LKR {{ number_format($dItem['amount'], 2) }}</span>
                                    </div>
                                @endforeach
                            @endif
                            <div class="flex justify-between px-2 py-1 font-bold bg-gray-50 border-t border-black">
                                <span>Total Deductions</span>
                                <span>LKR {{ number_format($bPayroll->total_deductions, 2) }}</span>
                            </div>
                            <div class="flex justify-between px-4 py-1 border-t border-black bg-purple-50/50">
                                <span class="font-bold">EPF Employer Contribution ({{ $bPayroll->epf_employer_rate }}%)</span>
                                <span class="font-bold">LKR {{ number_format($bPayroll->epf_employer_amount, 2) }}</span>
                            </div>
                            <div class="flex justify-between px-4 py-1 border-t border-gray-300 bg-indigo-50/50">
                                <span class="font-bold">ETF Employer Contribution ({{ $bPayroll->etf_employer_rate }}%)</span>
                                <span class="font-bold">LKR {{ number_format($bPayroll->etf_employer_amount, 2) }}</span>
                            </div>
                        </div>
                        <div class="flex justify-between px-2 py-1.5 font-black bg-emerald-100 text-emerald-950 border-t-2 border-black text-sm">
                            <span>Net Payable Salary</span>
                            <span>LKR {{ number_format($bPayroll->net_salary, 2) }}</span>
                        </div>
                    </div>
                </div>
                <!-- 9. Amount in Words -->
                <div class="grid grid-cols-4 divide-black bg-gray-50 py-1.5 px-2 font-bold">
                    <div class="col-span-1">Amount in Words:</div>
                    <div class="col-span-3 font-semibold text-gray-800 italic">
                        {{ $bPayroll->print_words }}
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    @script
    <script>
        // Shared master print helper
        window.printContentToIframe = function(htmlContent) {
            let printIframe = document.getElementById('unifiedPayslipPrintFrame');
            if (printIframe) printIframe.remove();

            printIframe = document.createElement('iframe');
            printIframe.id = 'unifiedPayslipPrintFrame';
            printIframe.style.position = 'fixed';
            printIframe.style.right = '0';
            printIframe.style.bottom = '0';
            printIframe.style.width = '200px';
            printIframe.style.height = '200px';
            printIframe.style.border = 'none';
            printIframe.style.opacity = '0.01';
            printIframe.style.zIndex = '-9999';
            printIframe.style.pointerEvents = 'none';
            document.body.appendChild(printIframe);

            let stylesHtml = '';
            document.querySelectorAll('link[rel="stylesheet"], style').forEach(node => {
                stylesHtml += node.outerHTML;
            });

            const doc = printIframe.contentWindow.document;
            doc.open();
            doc.write('<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Salary Slips</title>' +
                stylesHtml +
                '<style>' +
                '@page { size: A4 portrait; margin: 8mm; }' +
                'body { background-color: #ffffff !important; color: #000000 !important; font-family: ui-sans-serif, system-ui, sans-serif; padding: 0; margin: 0; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }' +
                '.payslip-sheet { page-break-after: always !important; break-after: page !important; margin-bottom: 0 !important; }' +
                '.border-black { border-color: #000000 !important; }' +
                '</style></head><body class="p-0 m-0 bg-white">' +
                htmlContent +
                '<' + '/body><' + '/html>'
            );
            doc.close();

            setTimeout(function() {
                try {
                    printIframe.contentWindow.focus();
                    printIframe.contentWindow.print();
                } catch (err) {
                    console.error('Print Error:', err);
                    window.print();
                }
            }, 300);
        };

        // 1. Single Slip Print
        window.printPayslipDocument = function() {
            const singleEl = document.getElementById('printablePayslip');
            if (!singleEl) {
                alert('Payslip content not found!');
                return;
            }
            printContentToIframe(singleEl.outerHTML);
        };

        // 2. Bulk Slips Print
        window.printBulkPayslips = function() {
            const bulkEl = document.getElementById('bulkPrintContainer');
            if (!bulkEl || bulkEl.children.length === 0) {
                alert('No approved payslips found to print for this selection!');
                return;
            }
            printContentToIframe(bulkEl.innerHTML);
        };
    </script>
    @endscript
</div>