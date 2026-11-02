<?php

namespace App\Livewire\Payroll;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\Employee;
use App\Models\Department;
use App\Models\EmployeeCategory;
use App\Models\Attendance;
use App\Models\AttendancePolicy;
use App\Models\EpfEtfSetting;
use App\Models\EmployeeSalaryComponent;
use App\Models\Payroll;
use App\Models\Organisation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

#[Layout('layouts.app')]
class PayrollManager extends Component
{
    use WithPagination;

    // Filters & Selected Month
    public string $selectedMonth = '';
    public string $selectedDepartmentId = '';
    public string $selectedCategoryId = '';
    public string $selectedStatus = 'all';
    public string $searchEmployee = '';

    // Multi-select Checkbox state
    public array $selectedPayrollIds = [];
    public bool $selectAll = false;

    // Selective Reverse Modal State
    public bool $showSelectiveReverseConfirmModal = false;

     // Approve Modal State
    public bool $showApproveConfirmModal = false;
    public float $payslipLeaveAllowed = 0.0;
    public float $payslipLeaveTaken = 0.0;
    public string $payslipAmountInWords = '';

    // Modal state for viewing payslip
    public bool $showPayslipModal = false;
    public ?Payroll $selectedPayrollForModal = null;

    // Messages
    public string $feedbackMessage = '';
    public string $errorMessage = '';

    public function mount(): void
    {
        $this->selectedMonth = date('Y-m');
    }

    public function updatingSearchEmployee(): void
    {
        $this->resetPage();
    }
    public function updatingSelectedDepartmentId(): void
    {
        $this->resetPage();
    }
    public function updatingSelectedCategoryId(): void
    {
        $this->resetPage();
    }
    public function updatingSelectedStatus(): void
    {
        $this->resetPage();
    }

      /**
     * Select All or Deselect All Checkbox Handler (Filter Aware)
     */
    public function updatedSelectAll(bool $value): void
    {
        if ($value) {
            $query = Payroll::where('payroll_month', $this->selectedMonth);

            // 1. Department Filter
            if (!empty($this->selectedDepartmentId)) {
                $query->whereHas('employee', fn($empQuery) => $empQuery->where('department_id', $this->selectedDepartmentId));
            }

            // 2. Category Filter
            if (!empty($this->selectedCategoryId)) {
                $query->whereHas('employee', fn($empQuery) => $empQuery->where('employee_category_id', $this->selectedCategoryId));
            }

            // 3. Status Filter
            if ($this->selectedStatus !== 'all') {
                $query->where('status', $this->selectedStatus);
            } else {
                $query->whereIn('status', ['processed', 'approved']);
            }

            // 4. Employee Search Filter
            if (!empty(trim($this->searchEmployee))) {
                $searchTerm = trim($this->searchEmployee);
                $query->whereHas('employee', function ($employeeQuery) use ($searchTerm) {
                    $employeeQuery->where('first_name', 'like', '%' . $searchTerm . '%')
                      ->orWhere('last_name', 'like', '%' . $searchTerm . '%')
                      ->orWhere('employee_id', 'like', '%' . $searchTerm . '%');
                });
            }

            $this->selectedPayrollIds = $query->pluck('id')
                ->map(fn($id) => (string) $id)
                ->toArray();
        } else {
            $this->selectedPayrollIds = [];
        }
    }

    /**
     * Process / Calculate Monthly Payroll for all eligible active staff
     */
    public function processMonthlyPayroll(): void
    {
        $this->feedbackMessage = '';
        $this->errorMessage = '';

        if (empty($this->selectedMonth)) {
            $this->errorMessage = 'Please select a valid target month.';
            return;
        }

        // Active Policy Threshold
        $activePolicy = AttendancePolicy::where('is_active', true)->first();
        $policyThresholdMinutes = (int) ($activePolicy->overtime_minimum_minutes ?? 30);

        // EPF & ETF Statutory Rates
        $epfSetting = EpfEtfSetting::where('fund_type', 'epf')->where('is_active', true)->first();
        $etfSetting = EpfEtfSetting::where('fund_type', 'etf')->where('is_active', true)->first();

        $epfEmployeeRate = (float) ($epfSetting->employee_rate ?? 8.00);
        $epfEmployerRate = (float) ($epfSetting->employer_rate ?? 12.00);
        $etfEmployerRate = (float) ($etfSetting->employer_rate ?? 3.00);

        // Fetch active employees
        $employeesQuery = Employee::with([
            'salaryAndBank',
            'employeeCategory',
            'department'
        ])->where('status', 'active');

        if (!empty($this->selectedDepartmentId)) {
            $employeesQuery->where('department_id', $this->selectedDepartmentId);
        }

        if (!empty($this->selectedCategoryId)) {
            $employeesQuery->where('employee_category_id', $this->selectedCategoryId);
        }

        $employees = $employeesQuery->get();

        if ($employees->isEmpty()) {
            $this->errorMessage = 'No active employees found for the selected criteria.';
            return;
        }

        $processedCount = 0;

        DB::transaction(function () use (
            $employees,
            $policyThresholdMinutes,
            $epfEmployeeRate,
            $epfEmployerRate,
            $etfEmployerRate,
            &$processedCount
            
        ) {
            $previousMonth = date('Y-m', strtotime($this->selectedMonth . '-01 -1 month'));
            foreach ($employees as $employee) {
                // Check if already approved or paid (do not overwrite approved payrolls)
                $existingPayroll = Payroll::where('employee_id', $employee->id)
                    ->where('payroll_month', $this->selectedMonth)
                    ->first();

                if ($existingPayroll && in_array($existingPayroll->status, ['approved', 'paid'])) {
                    continue;
                }

                $category = $employee->employeeCategory;
                $salaryBank = $employee->salaryAndBank;

                // 1. Basic Salary
                $basicSalary = (float) ($salaryBank?->basic_salary ?? 0.00);

                // 2. Attendance & No-Pay (Absent Days)
                $attendances = Attendance::where('employee_id', $employee->id)
                    ->where('date', 'like', $this->selectedMonth . '%')
                    ->get();

                $absentDaysCount = 0.00;
                $monthlyNormalOtHours = 0.00;
                $monthlyWeekendOtHours = 0.00;
                $monthlyHolidayOtHours = 0.00;

                foreach ($attendances as $attendanceRecord) {
                    $status = $attendanceRecord->status;

                    // Absent evaluation
                    if ($status === 'absent') {
                        $absentDaysCount += 1.0;
                    } elseif (in_array($status, ['half_day_absent', 'half_day_morning_late'])) {
                        $absentDaysCount += 0.5;
                    }

                    // Overtime evaluation
                    $dayOtHours = (float) $attendanceRecord->ot_hours;
                    $dayOtMinutes = (int) round($dayOtHours * 60);

                    if ($status === 'holiday_present') {
                        $monthlyHolidayOtHours += $dayOtHours;
                    } elseif ($status === 'weekend_present') {
                        $monthlyWeekendOtHours += $dayOtHours;
                    } else {
                        if ($dayOtMinutes >= $policyThresholdMinutes) {
                            $monthlyNormalOtHours += $dayOtHours;
                        }
                    }
                }

                // Standard Working Days for No-Pay Divisor (e.g., 26 or 30 days)
                $standardMonthlyDays = (int) ($category?->standard_monthly_days ?? 26);
                $dailyRate = ($standardMonthlyDays > 0) ? ($basicSalary / $standardMonthlyDays) : 0;
                $noPayAmount = round($absentDaysCount * $dailyRate, 2);

                // 3. Overtime Amounts
                $canEarnCashOt = (!empty($employee->is_ot_eligible) && !empty($category?->is_ot_eligible));
                $otDivisor = (int) ($category?->ot_divisor_hours ?? 200);
                $hourlyRate = ($otDivisor > 0) ? ($basicSalary / $otDivisor) : 0;

                $normalMultiplier  = (float) ($category?->ot_calculation_rate ?? 1.50);
                $weekendMultiplier = (float) ($category?->ot_weekend_rate ?? 1.50);
                $holidayMultiplier = (float) ($category?->ot_holiday_rate ?? 2.00);

                $normalOtAmount = 0.00;
                $weekendOtAmount = 0.00;
                $holidayOtAmount = 0.00;
                $totalOtAmount = 0.00;

                if ($canEarnCashOt) {
                    $normalOtAmount  = round($monthlyNormalOtHours * $hourlyRate * $normalMultiplier, 2);
                    $weekendOtAmount = round($monthlyWeekendOtHours * $hourlyRate * $weekendMultiplier, 2);
                    $holidayOtAmount = round($monthlyHolidayOtHours * $hourlyRate * $holidayMultiplier, 2);
                    $totalOtAmount   = round($normalOtAmount + $weekendOtAmount + $holidayOtAmount, 2);
                }

                // 4. Employee Salary Components (Allowances & Deductions)
                $assignedComponents = EmployeeSalaryComponent::with('salaryComponent')
                    ->where('employee_id', $employee->id)
                    ->where('is_active', true)
                    ->where(function ($query) {
                        $query->whereNull('applicable_month')
                            ->orWhere('applicable_month', '')
                            ->orWhere('applicable_month', $this->selectedMonth);
                    })
                    ->get();
                $epfApplicableAllowances = 0.00;
                $otherAllowances = 0.00;
                $otherDeductions = 0.00;
                $allowanceBreakdownList = [];
                $deductionBreakdownList = [];

                 // Check if previous month had unrecovered shortfall or arrears
                $previousPayroll = Payroll::where('employee_id', $employee->id)
                    ->where('payroll_month', $previousMonth)
                    ->first();
                $arrearsBroughtForward = (float) ($previousPayroll?->unrecovered_amount ?? 0.00);
                if ($arrearsBroughtForward > 0) {
                    $deductionBreakdownList[] = [
                        'name'   => 'Prior Month Arrears (' . date('M Y', strtotime($previousMonth . '-01')) . ')',
                        'amount' => $arrearsBroughtForward,
                    ];
                }

                foreach ($assignedComponents as $assignedItem) {
                    $component = $assignedItem->salaryComponent;
                    if (!$component) {
                        continue;
                    }
                    // Calculate percentage or fixed component amount
                    $computedAmount = 0.00;
                    if (($component->calculation_type ?? 'fixed') === 'percentage') {
                        $computedAmount = round(($basicSalary * (float) $assignedItem->amount) / 100, 2);
                    } else {
                        $computedAmount = (float) $assignedItem->amount;
                    }
                    // Categorize earnings/allowances and prepare snapshot
                    if (in_array(strtolower($component->type), ['earning', 'allowance'])) {
                        if ($component->is_epf_applicable) {
                            $epfApplicableAllowances += $computedAmount;
                        } else {
                            $otherAllowances += $computedAmount;
                        }
                        // Record allowance breakdown details for payslip
                        $allowanceBreakdownList[] = [
                            'name'          => !empty($component->name_in_payslip) ? $component->name_in_payslip : $component->name,
                            'amount'        => $computedAmount,
                            'is_epf'        => (bool) $component->is_epf_applicable,
                            'is_percentage' => (($component->calculation_type ?? 'fixed') === 'percentage'),
                            'rate'          => (float) $assignedItem->amount,
                        ];
                    } elseif (strtolower($component->type) === 'deduction') {
                        $otherDeductions += $computedAmount;
                        $deductionBreakdownList[] = [
                            'name'          => !empty($component->name_in_payslip) ? $component->name_in_payslip : $component->name,
                            'amount'        => $computedAmount,
                        ];
                    }
                }

                // 5. Statutory EPF / ETF Calculations
                $epfQualifyingSalary = max(0.00, ($basicSalary + $epfApplicableAllowances) - $noPayAmount);
                $epfEmployeeAmount  = round($epfQualifyingSalary * ($epfEmployeeRate / 100), 2);
                $epfEmployerAmount  = round($epfQualifyingSalary * ($epfEmployerRate / 100), 2);
                $etfEmployerAmount  = round($epfQualifyingSalary * ($etfEmployerRate / 100), 2);

                 // 6. Gross & Net Salary with Dynamic Category Deduction Limit (e.g. 60% or 50%)
                $grossSalary = round($basicSalary + $epfApplicableAllowances + $otherAllowances + $totalOtAmount, 2);
                $earnedRemuneration = max(0.00, $grossSalary - $noPayAmount);
                // Fetch dynamic limit from category (default 60% if null)
                $maxDeductionPercentage = (float) ($category?->max_deduction_percentage ?? 60.00);
                $maxAllowableDeductions = round($earnedRemuneration * ($maxDeductionPercentage / 100), 2);
                // Available room for voluntary deductions (Loans, Arrears) after mandatory statutory EPF
                $availableRoomForVoluntary = max(0.00, $maxAllowableDeductions - $epfEmployeeAmount);
                $voluntaryDeductionsTotal = round($otherDeductions + $arrearsBroughtForward, 2);
                if ($voluntaryDeductionsTotal > $availableRoomForVoluntary) {
                    $actualVoluntaryDeducted = $availableRoomForVoluntary;
                    $unrecoveredAmount = round($voluntaryDeductionsTotal - $availableRoomForVoluntary, 2);
                } else {
                    $actualVoluntaryDeducted = $voluntaryDeductionsTotal;
                    $unrecoveredAmount = 0.00;
                }
                $totalDeductions = round($noPayAmount + $epfEmployeeAmount + $actualVoluntaryDeducted, 2);
                $netSalary = max(0.00, round($grossSalary - $totalDeductions, 2));
                
                // 7. Store / Update Payroll Record
                Payroll::updateOrCreate(
                    [
                        'employee_id'   => $employee->id,
                        'payroll_month' => $this->selectedMonth,
                    ],
                    [
                        'basic_salary'              => $basicSalary,
                        'epf_applicable_allowances' => $epfApplicableAllowances,
                        'other_allowances'          => $otherAllowances,
                        'normal_ot_hours'           => $monthlyNormalOtHours,
                        'normal_ot_amount'          => $normalOtAmount,
                        'weekend_ot_hours'          => $monthlyWeekendOtHours,
                        'weekend_ot_amount'         => $weekendOtAmount,
                        'holiday_ot_hours'          => $monthlyHolidayOtHours,
                        'holiday_ot_amount'         => $holidayOtAmount,
                        'total_ot_amount'           => $totalOtAmount,
                        'gross_salary'              => $grossSalary,
                        'absent_days'               => $absentDaysCount,
                        'no_pay_amount'             => $noPayAmount,
                        'epf_qualifying_salary'     => $epfQualifyingSalary,
                        'epf_employee_rate'         => $epfEmployeeRate,
                        'epf_employee_amount'       => $epfEmployeeAmount,
                        'epf_employer_rate'         => $epfEmployerRate,
                        'epf_employer_amount'       => $epfEmployerAmount,
                        'etf_employer_rate'         => $etfEmployerRate,
                        'etf_employer_amount'       => $etfEmployerAmount,
                        'other_deductions'          => $otherDeductions,
                        'total_deductions'          => $totalDeductions,
                        'net_salary'                => $netSalary,
                        'allowance_breakdown'       => $allowanceBreakdownList,
                        'deduction_breakdown'       => $deductionBreakdownList,
                        'payment_method'            => $salaryBank->payment_method ?? 'bank_transfer',
                        'bank_name'                 => $salaryBank->bank_name ?? null,
                        'branch_name'               => $salaryBank->branch_name ?? null,
                        'account_number'            => $salaryBank->account_number ?? null,
                        'arrears_brought_forward'   => $arrearsBroughtForward,
                        'unrecovered_amount'        => $unrecoveredAmount,
                        'status'                    => 'processed',
                        'processed_by'              => Auth::id(),
                    ]
                );

                $processedCount++;
            }
        });

        $this->feedbackMessage = "Successfully processed payroll for {$processedCount} employees for {$this->selectedMonth}!";
    }

     public function openApproveModal(): void
    {
        if (empty($this->selectedPayrollIds)) {
            $this->errorMessage = 'Please select at least one employee payroll record to approve.';
            return;
        }
        $this->showApproveConfirmModal = true;
    }
    public function closeApproveModal(): void
    {
        $this->showApproveConfirmModal = false;
    }
    /**
     * Approve only the selected employees
     */
    public function approveSelectedPayroll(): void
    {
        if (empty($this->selectedPayrollIds)) {
            $this->errorMessage = 'No payroll records selected for approval.';
            return;
        }
        $updatedCount = Payroll::whereIn('id', $this->selectedPayrollIds)
            ->where('status', 'processed')
            ->update([
                'status'      => 'approved',
                'approved_by' => Auth::id(),
            ]);
        $this->selectedPayrollIds = [];
        $this->selectAll = false;
        $this->showApproveConfirmModal = false;
        $this->feedbackMessage = "Successfully approved {$updatedCount} selected payroll records for {$this->selectedMonth}!";
    }

    public function openSelectiveReverseModal(): void
    {
        if (empty($this->selectedPayrollIds)) {
            $this->errorMessage = 'Please select at least one approved record to reverse.';
            return;
        }
        $this->showSelectiveReverseConfirmModal = true;
    }

    public function closeSelectiveReverseModal(): void
    {
        $this->showSelectiveReverseConfirmModal = false;
    }

    /**
     * Reverse only the selected employees back to Draft
     */
    public function reverseSelectedPayroll(): void
    {
        if (empty($this->selectedPayrollIds)) {
            $this->errorMessage = 'Please select at least one payroll record to reverse.';
            return;
        }

        $revertedCount = Payroll::whereIn('id', $this->selectedPayrollIds)
            ->whereIn('status', ['approved', 'paid'])
            ->update([
                'status'      => 'processed',
                'approved_by' => null,
            ]);

        $this->selectedPayrollIds = [];
        $this->selectAll = false;
        $this->showSelectiveReverseConfirmModal = false;

        if ($revertedCount > 0) {
            $this->feedbackMessage = "Successfully reversed {$revertedCount} selected record(s) back to Draft!";
        } else {
            $this->errorMessage = 'None of the selected records were in Approved or Paid status to reverse.';
        }
    }

    /**
     * View Individual Payslip in Modal
     */
   public function viewPayslip(int $payrollId): void
{
    $this->selectedPayrollForModal = Payroll::with([
        'employee.department',
        'employee.employeeCategory',
        'employee.salaryAndBank'
    ])->find($payrollId);
    if ($this->selectedPayrollForModal) {
        $employeeId = $this->selectedPayrollForModal->employee_id;
        $payrollYear = (int) substr($this->selectedPayrollForModal->payroll_month, 0, 4);
        // Leave Balances (Allowed & Taken)
        $leaveBalances = \App\Models\LeaveBalance::where('employee_id', $employeeId)
            ->where('year', $payrollYear)
            ->get();
        $this->payslipLeaveAllowed = (float) $leaveBalances->sum('allocated_days');

        $targetMonthString = $this->selectedPayrollForModal->payroll_month;
        $fullDayLeavesCount = \App\Models\Attendance::where('employee_id', $employeeId)
            ->where('date', 'like', $targetMonthString . '%')
            ->where('status', 'leave')
            ->count();

        $halfDayLeavesCount = \App\Models\Attendance::where('employee_id', $employeeId)
            ->where('date', 'like', $targetMonthString . '%')
            ->where('status', 'half_day')
            ->count();

        $this->payslipLeaveTaken = (float) ($fullDayLeavesCount + ($halfDayLeavesCount * 0.5));
        // Convert Net Salary to Words
        $this->payslipAmountInWords = $this->convertNumberToWords($this->selectedPayrollForModal->net_salary);
    }
    $this->showPayslipModal = true;
}

    public function closePayslipModal(): void
    {
        $this->showPayslipModal = false;
        $this->selectedPayrollForModal = null;
    }

      public static function convertNumberToWords(float $number): string
    {
        $dictionary = [
            0 => 'Zero', 1 => 'One', 2 => 'Two', 3 => 'Three', 4 => 'Four', 5 => 'Five',
            6 => 'Six', 7 => 'Seven', 8 => 'Eight', 9 => 'Nine', 10 => 'Ten',
            11 => 'Eleven', 12 => 'Twelve', 13 => 'Thirteen', 14 => 'Fourteen', 15 => 'Fifteen',
            16 => 'Sixteen', 17 => 'Seventeen', 18 => 'Eighteen', 19 => 'Nineteen', 20 => 'Twenty',
            30 => 'Thirty', 40 => 'Forty', 50 => 'Fifty', 60 => 'Sixty', 70 => 'Seventy',
            80 => 'Eighty', 90 => 'Ninety', 100 => 'Hundred', 1000 => 'Thousand', 1000000 => 'Million'
        ];
        if ($number <= 0) return 'Zero Rupees Only';
        
        $rupees = (int) floor($number);
        $cents  = (int) round(($number - $rupees) * 100);
        $words = self::wordsHelper($rupees, $dictionary) . ' Rupees';
    
        if ($cents > 0) {
            $words .= ' and ' . self::wordsHelper($cents, $dictionary) . ' Cents';
        }
        return trim($words) . ' Only';
    }
    private static function wordsHelper(int $number, array $dictionary): string
    {
        $string = '';
        if ($number >= 1000000) {
            $millions = (int) ($number / 1000000);
            $string .= self::wordsHelper($millions, $dictionary) . ' Million ';
            $number %= 1000000;
        }
        if ($number >= 1000) {
            $thousands = (int) ($number / 1000);
            $string .= self::wordsHelper($thousands, $dictionary) . ' Thousand ';
            $number %= 1000;
        }
        if ($number >= 100) {
            $hundreds = (int) ($number / 100);
            $string .= self::wordsHelper($hundreds, $dictionary) . ' Hundred ';
            $number %= 100;
        }
        if ($number > 0) {
            if ($number < 20) {
                $string .= $dictionary[$number];
            } else {
                $tens = ((int) ($number / 10)) * 10;
                $units = $number % 10;
                $string .= $dictionary[$tens] . ($units ? ' ' . $dictionary[$units] : '');
            }
        }
        return trim($string);
    }

    public function render()
    {
        // 1. Base Filter Query
        $payrollQuery = Payroll::with(['employee.department', 'employee.employeeCategory'])
            ->where('payroll_month', $this->selectedMonth);
        // Department Filter
        if (!empty($this->selectedDepartmentId)) {
            $payrollQuery->whereHas('employee', function ($query) {
                $query->where('department_id', $this->selectedDepartmentId);
            });
        }
        // Category Filter
        if (!empty($this->selectedCategoryId)) {
            $payrollQuery->whereHas('employee', function ($query) {
                $query->where('employee_category_id', $this->selectedCategoryId);
            });
        }
        // Status Filter
        if ($this->selectedStatus !== 'all') {
            $payrollQuery->where('status', $this->selectedStatus);
        }
        // Employee Search (Name or Employee ID)
        if (!empty(trim($this->searchEmployee))) {
            $searchTerm = trim($this->searchEmployee);
            $payrollQuery->whereHas('employee', function ($query) use ($searchTerm) {
                $query->where('first_name', 'like', '%' . $searchTerm . '%')
                    ->orWhere('last_name', 'like', '%' . $searchTerm . '%')
                    ->orWhere('employee_id', 'like', '%' . $searchTerm . '%');
            });
        }
        // 2. Summary Totals (Calculated dynamically from the FILTERED Query)
        $totalGrossCost   = (float) (clone $payrollQuery)->sum('gross_salary');
        $totalNetPayable  = (float) (clone $payrollQuery)->sum('net_salary');
        $totalEpfEmployee = (float) (clone $payrollQuery)->sum('epf_employee_amount');
        $totalEpfEmployer = (float) (clone $payrollQuery)->sum('epf_employer_amount');
        $totalEtfEmployer = (float) (clone $payrollQuery)->sum('etf_employer_amount');
        $totalNoPayCost   = (float) (clone $payrollQuery)->sum('no_pay_amount');
        $totalCompanyCost = ($totalGrossCost - $totalNoPayCost) + $totalEpfEmployer + $totalEtfEmployer;

        // 3. Paginated List for Table
        $payrollsList = $payrollQuery->orderBy('id', 'desc')->paginate(15);

        // 4. Count selected Draft vs Approved records for smart button display
        $selectedProcessedCount = 0;
        $selectedApprovedCount  = 0;
        if (!empty($this->selectedPayrollIds)) {
            $selectedStatuses = Payroll::whereIn('id', $this->selectedPayrollIds)
                ->pluck('status')
                ->toArray();
            $selectedProcessedCount = count(array_filter($selectedStatuses, fn($status) => $status === 'processed'));
            $selectedApprovedCount  = count(array_filter($selectedStatuses, fn($status) => in_array($status, ['approved', 'paid'])));
        }

        return view('livewire.payroll.payroll-manager', [
            'departments'            => Department::all(),
            'categories'             => EmployeeCategory::where('is_active', true)->orderBy('name')->get(),
            'payrollsList'           => $payrollsList,
            'totalGrossCost'         => $totalGrossCost,
            'totalEpfEmployee'       => $totalEpfEmployee,
            'totalNetPayable'        => $totalNetPayable,
            'totalEpfEmployer'       => $totalEpfEmployer,
            'totalEtfEmployer'       => $totalEtfEmployer,
            'totalCompanyCost'       => $totalCompanyCost,
            'organisation'           => Organisation::first(),
            'hasApprovedRecords'     => Payroll::where('payroll_month', $this->selectedMonth)->whereIn('status', ['approved', 'paid'])->exists(),
            'hasProcessedRecords'    => Payroll::where('payroll_month', $this->selectedMonth)->where('status', 'processed')->exists(),
            'selectedProcessedCount'   => $selectedProcessedCount,
            'selectedApprovedCount'    => $selectedApprovedCount,
            'approvedPayrollsForPrint' => (clone $payrollQuery)
                ->with(['employee.department', 'employee.employeeCategory', 'employee.salaryAndBank'])
                ->whereIn('status', ['approved', 'paid'])
                ->get()
                ->map(function ($record) {
                    $year = (int) substr($record->payroll_month, 0, 4);
                    $empId = $record->employee_id;

                    $leaveBalances = \App\Models\LeaveBalance::where('employee_id', $empId)
                        ->where('year', $year)
                        ->get();
                    $record->print_leave_allowed = (float) $leaveBalances->sum('allocated_days');

                    $fullDay = \App\Models\Attendance::where('employee_id', $empId)
                        ->where('date', 'like', $record->payroll_month . '%')
                        ->where('status', 'leave')
                        ->count();

                    $halfDay = \App\Models\Attendance::where('employee_id', $empId)
                        ->where('date', 'like', $record->payroll_month . '%')
                        ->where('status', 'half_day')
                        ->count();

                    $record->print_leave_taken = (float) ($fullDay + ($halfDay * 0.5));
                    $record->print_words = self::convertNumberToWords((float) $record->net_salary);

                    return $record;
                }),
        ]);
    }
}