<?php

namespace App\Livewire\Payroll;

use Livewire\Component;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\Department;
use App\Models\EmployeeCategory;
use App\Models\AttendancePolicy;
use Carbon\Carbon;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class OvertimeManager extends Component
{
    public string $selectedMonth;
    public ?int $selectedDepartmentId = null;
    public ?int $selectedCategoryId = null;
    public string $filterEligibility = 'all'; // 'all', 'eligible', 'non_eligible'

    // Daily Drill-down Modal State
    public bool $showDailyModal = false;
    public ?Employee $selectedEmployeeForModal = null;
    public array $dailyBreakdownRecords = [];
    public int $policyThresholdMinutes = 60;

    public function mount(): void
    {
        $this->selectedMonth = Carbon::now()->format('Y-m');
        
        $activePolicy = AttendancePolicy::where('is_active', true)->first();
        $this->policyThresholdMinutes = (int) ($activePolicy->overtime_minimum_minutes ?? 60);
    }

    public function viewDailyDetails(int $employeeId): void
    {
        $this->selectedEmployeeForModal = Employee::find($employeeId);
        $startDate = Carbon::parse($this->selectedMonth . '-01')->startOfMonth()->toDateString();
        $endDate   = Carbon::parse($this->selectedMonth . '-01')->endOfMonth()->toDateString();

        $attendances = Attendance::where('employee_id', $employeeId)
            ->whereBetween('date', [$startDate, $endDate])
            ->where(function ($query) {
                $query->where('ot_hours', '>', 0)
                      ->orWhere('total_worked_hours', '>', 8)
                      ->orWhereIn('status', ['holiday_present', 'weekend_present']);
            })
            ->orderBy('date', 'asc')
            ->get();

        $this->dailyBreakdownRecords = [];

        foreach ($attendances as $record) {
            $rawOtHours = (float) $record->ot_hours;
            $rawOtMinutes = (int) round($rawOtHours * 60);
            $status = $record->status;

            $qualificationBadge = 'Below Threshold (0 OT)';
            $badgeColor = 'bg-gray-100 text-gray-500';
            $finalApprovedOtHours = 0.00;

            if ($status === 'holiday_present') {
                $qualificationBadge = 'Holiday Double OT';
                $badgeColor = 'bg-purple-100 text-purple-700 font-semibold';
                $finalApprovedOtHours = $rawOtHours;
            } elseif ($status === 'weekend_present') {
                $qualificationBadge = 'Weekend OT';
                $badgeColor = 'bg-amber-100 text-amber-700 font-semibold';
                $finalApprovedOtHours = $rawOtHours;
            } else {
                // Check daily policy threshold for regular working days
                if ($rawOtMinutes >= $this->policyThresholdMinutes) {
                    $qualificationBadge = 'Threshold Passed (Qualified)';
                    $badgeColor = 'bg-emerald-100 text-emerald-700 font-bold';
                    $finalApprovedOtHours = $rawOtHours;
                } else {
                    $qualificationBadge = 'Below Threshold (' . $rawOtMinutes . 'm < ' . $this->policyThresholdMinutes . 'm)';
                    $badgeColor = 'bg-rose-50 text-rose-600 border border-rose-200';
                    $finalApprovedOtHours = 0.00;
                }
            }

            $this->dailyBreakdownRecords[] = [
                'date'           => $record->date,
                'status'         => $status,
                'in_time'        => $record->in_time ? Carbon::parse($record->in_time)->format('h:i A') : '--:--',
                'out_time'       => $record->out_time ? Carbon::parse($record->out_time)->format('h:i A') : '--:--',
                'worked_hours'   => (float) $record->total_worked_hours,
                'raw_ot_hours'   => $rawOtHours,
                'final_ot_hours' => $finalApprovedOtHours,
                'badge_label'    => $qualificationBadge,
                'badge_color'    => $badgeColor,
            ];
        }

        $this->showDailyModal = true;
    }

    public function closeDailyModal(): void
    {
        $this->showDailyModal = false;
        $this->selectedEmployeeForModal = null;
        $this->dailyBreakdownRecords = [];
    }

    public function render()
    {
        $startDate = Carbon::parse($this->selectedMonth . '-01')->startOfMonth()->toDateString();
        $endDate   = Carbon::parse($this->selectedMonth . '-01')->endOfMonth()->toDateString();

        $employees = Employee::query()
            ->with(['department', 'employeeCategory', 'salaryAndBank'])
            ->where('status', 'active')
            ->when($this->selectedDepartmentId, fn($query) => $query->where('department_id', $this->selectedDepartmentId))
            ->when($this->selectedCategoryId, fn($query) => $query->where('employee_category_id', $this->selectedCategoryId))
            ->get();

        $totalPayableAmountAll = 0.00;
        $totalEligibleStaffCount = 0;
        $totalExecutiveExtraHours = 0.00;

        $overtimeSummaryList = $employees->map(function ($employee) use ($startDate, $endDate, &$totalPayableAmountAll, &$totalEligibleStaffCount, &$totalExecutiveExtraHours) {
            $category = $employee->employeeCategory;
            
            // Overtime is payable only when enabled on both Category and Employee profile
            $isCategoryEligible = $category && (bool) $category->is_ot_eligible;
            $isEmployeeEligible = (bool) $employee->is_ot_eligible;
            $canEarnCashOt = $isCategoryEligible && $isEmployeeEligible;

            $attendances = Attendance::where('employee_id', $employee->id)
                ->whereBetween('date', [$startDate, $endDate])
                ->get();

            $monthlyNormalOtHours = 0.00;
            $monthlyWeekendOtHours = 0.00;
            $monthlyHolidayOtHours = 0.00;

            // Daily threshold check and monthly accrual
            foreach ($attendances as $attRecord) {
                $status = $attRecord->status;
                $dayOtHours = (float) $attRecord->ot_hours;
                $dayOtMinutes = (int) round($dayOtHours * 60);

                if ($status === 'holiday_present') {
                    $monthlyHolidayOtHours += $dayOtHours;
                } elseif ($status === 'weekend_present') {
                    $monthlyWeekendOtHours += $dayOtHours;
                } else {
                    // Accrue normal OT only if daily time meets or exceeds the threshold
                    if ($dayOtMinutes >= $this->policyThresholdMinutes) {
                        $monthlyNormalOtHours += $dayOtHours;
                    }
                }
            }

            $totalQualifiedExtraHours = round($monthlyNormalOtHours + $monthlyWeekendOtHours + $monthlyHolidayOtHours, 2);

            $basicSalary = (float) ($employee->salaryAndBank?->basic_salary ?? 0);
            $divisor     = (int) ($category?->ot_divisor_hours ?? 200);
            $hourlyRate  = ($divisor > 0) ? ($basicSalary / $divisor) : 0;

            // Multipliers from Category
            $normalMultiplier  = (float) ($category?->ot_calculation_rate ?? 1.50);
            $weekendMultiplier = (float) ($category?->ot_weekend_rate ?? 1.50);
            $holidayMultiplier = (float) ($category?->ot_holiday_rate ?? 2.00);

            $calculatedAmount = 0.00;
            if ($canEarnCashOt) {
                $totalEligibleStaffCount++;
                $normalAmount  = $monthlyNormalOtHours * $hourlyRate * $normalMultiplier;
                $weekendAmount = $monthlyWeekendOtHours * $hourlyRate * $weekendMultiplier;
                $holidayAmount = $monthlyHolidayOtHours * $hourlyRate * $holidayMultiplier;

                $calculatedAmount = round($normalAmount + $weekendAmount + $holidayAmount, 2);
                $totalPayableAmountAll += $calculatedAmount;
            } else {
                $totalExecutiveExtraHours += $totalQualifiedExtraHours;
            }

            return [
                'employee_id'         => $employee->id,
                'name'                => $employee->first_name . ' ' . $employee->last_name,
                'emp_no'              => $employee->employee_id,
                'department'          => $employee->department?->name ?? 'General',
                'category'            => $category?->name ?? 'None',
                'basic_salary'        => $basicSalary,
                'hourly_rate'         => $hourlyRate,
                'can_earn_ot'         => $canEarnCashOt,
                'normal_ot_hours'     => $monthlyNormalOtHours,
                'weekend_ot_hours'    => $monthlyWeekendOtHours,
                'holiday_ot_hours'    => $monthlyHolidayOtHours,
                'total_extra_hours'   => $totalQualifiedExtraHours,
                'calculated_amount'   => $calculatedAmount,
            ];
        });

        // Rule 1: Exclude employees who have 0 extra hours (Display only staff with OT > 0)
        $overtimeSummaryList = $overtimeSummaryList->filter(function ($item) {
            return $item['total_extra_hours'] > 0;
        });

        // Filter by Eligibility
        if ($this->filterEligibility === 'eligible') {
            $overtimeSummaryList = $overtimeSummaryList->where('can_earn_ot', true);
        } elseif ($this->filterEligibility === 'non_eligible') {
            $overtimeSummaryList = $overtimeSummaryList->where('can_earn_ot', false);
        }

        return view('livewire.payroll.overtime-manager', [
            'departments'              => Department::all(),
            'categories'               => EmployeeCategory::where('is_active', true)->orderBy('name')->get(),
            'otSummaryList'            => $overtimeSummaryList,
            'totalPayableAll'          => $totalPayableAmountAll,
            'totalEligibleCount'       => $totalEligibleStaffCount,
            'totalExecutiveExtraHours' => $totalExecutiveExtraHours,
        ]);
    }
}