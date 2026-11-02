<?php

namespace App\Livewire\Payroll;

use App\Models\Employee;
use App\Models\EmployeeCategory;
use App\Models\SalaryComponent;
use App\Models\EmployeeSalaryComponent;
use App\Models\Payroll;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\DB;

#[Layout('layouts.app')]
class SalaryAssignmentManager extends Component
{
    use WithPagination;

    public string $search = '';
    public string $selectedCategoryFilter = '';
    public string $selectedMonthFilter = '';

    public function mount(): void
    {
        $this->selectedMonthFilter = date('Y-m');
    }

    public function updatingSelectedMonthFilter(): void
    {
        $this->resetPage();
    }

    // --- Modal 1: Individual Manage States ---
    public bool $showIndividualModal = false;
    public ?Employee $selectedEmployee = null;
    public ?int $selectedComponentId = null;
    public $individualAmount = 0.00;
    public string $individualDurationType = 'permanent'; // 'permanent' or 'single_month'
    public ?string $individualMonth = null;
    public bool $isIndividualComponentRecurring = true;
    public string $individualCalculationType = 'fixed'; // 'fixed' or 'percentage'
    public ?float $individualLimit = null;

    // --- Modal 2: Bulk Assign States ---
    public bool $showBulkModal = false;
    public ?int $bulkComponentId = null;
    public string $bulkTarget = 'category'; // 'all' or 'category'
    public ?int $bulkCategoryId = null;
    public $bulkAmount = 0.00;
    public string $bulkDurationType = 'permanent'; // 'permanent' or 'single_month'
    public ?string $bulkMonth = null;
    public bool $isBulkComponentRecurring = true;
    public string $bulkCalculationType = 'fixed'; // 'fixed' or 'percentage'
    public string $bulkOverwriteMode = 'skip'; // 'skip' or 'overwrite'
    public ?float $bulkLimit = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingSelectedCategoryFilter(): void
    {
        $this->resetPage();
    }

    // Identify calculation mode and recurring status when an individual component is selected
    public function updatedSelectedComponentId($salaryComponentId): void
    {
        if ($salaryComponentId) {
            $salaryComponent = SalaryComponent::find($salaryComponentId);
            if ($salaryComponent) {
                $this->isIndividualComponentRecurring = (bool) $salaryComponent->is_recurring;
                $this->individualCalculationType     = $salaryComponent->calculation_type ?? 'fixed';
                $this->individualDurationType        = $this->isIndividualComponentRecurring ? 'permanent' : 'single_month';
                $this->individualMonth               = $this->isIndividualComponentRecurring ? null : date('Y-m');
                $this->individualLimit               = $salaryComponent->limit ? (float) $salaryComponent->limit : null;
            }
        }
    }

    // Identify calculation mode and recurring status when a bulk component is selected
    public function updatedBulkComponentId($salaryComponentId): void
    {
        if ($salaryComponentId) {
            $salaryComponent = SalaryComponent::find($salaryComponentId);
            if ($salaryComponent) {
                $this->isBulkComponentRecurring = (bool) $salaryComponent->is_recurring;
                $this->bulkCalculationType     = $salaryComponent->calculation_type ?? 'fixed';
                $this->bulkDurationType        = $this->isBulkComponentRecurring ? 'permanent' : 'single_month';
                $this->bulkMonth               = $this->isBulkComponentRecurring ? null : date('Y-m');
                $this->bulkLimit               = $salaryComponent->limit ? (float) $salaryComponent->limit : null;
            }
        }
    }

    // --- INDIVIDUAL MANAGEMENT METHODS ---
    public function openIndividualModal(int $employeeId): void
    {
        $this->selectedEmployee = Employee::with([
            'category',
            'salaryAndBank',
            'salaryComponents.salaryComponent'
        ])->findOrFail($employeeId);

        $this->selectedComponentId             = null;
        $this->individualAmount                = 0.00;
        $this->individualDurationType          = 'permanent';
        $this->individualMonth                 = null;
        $this->isIndividualComponentRecurring  = true;
        $this->individualCalculationType       = 'fixed';
        $this->individualLimit                 = null;
        $this->showIndividualModal             = true;
    }

    public function addComponentToEmployee(): void
    {
        $this->validate([
            'selectedComponentId' => 'required|exists:salary_components,id',
            'individualAmount'    => 'required|numeric|min:0',
            'individualMonth'     => (!$this->isIndividualComponentRecurring) ? 'required' : 'nullable',
        ]);

        $salaryComponent = SalaryComponent::findOrFail($this->selectedComponentId);
        $maximumLimit    = $salaryComponent->limit ? (float) $salaryComponent->limit : null;

        if ($this->individualCalculationType === 'percentage' && (float) $this->individualAmount > 100) {
            $this->addError('individualAmount', 'Percentage cannot exceed 100%.');
            return;
        }

        if ($maximumLimit !== null && (float) $this->individualAmount > $maximumLimit) {
            $formattedLimit = ($this->individualCalculationType === 'percentage')
                ? "{$maximumLimit}%"
                : "LKR " . number_format($maximumLimit, 2);

            $this->addError('individualAmount', "The value cannot exceed the maximum limit of {$formattedLimit}.");
            return;
        }

        // Period lock: Prevent adding allowance/deduction to an approved or paid month
        if (!$this->isIndividualComponentRecurring && !empty($this->individualMonth)) {
            $isLocked = Payroll::where('payroll_month', $this->individualMonth)
                ->whereIn('status', ['approved', 'paid'])
                ->exists();

            if ($isLocked) {
                $this->addError('individualMonth', 'Cannot add component. Payroll for ' . date('F Y', strtotime($this->individualMonth . '-01')) . ' is approved/paid and locked.');
                return;
            }
        }

        $targetMonth = (!$this->isIndividualComponentRecurring) ? $this->individualMonth : null;

        EmployeeSalaryComponent::updateOrCreate(
            [
                'employee_id'         => $this->selectedEmployee->id,
                'salary_component_id' => $this->selectedComponentId,
                'applicable_month'    => $targetMonth,
            ],
            [
                'amount'    => $this->individualAmount,
                'is_active' => true,
            ]
        );
        $this->selectedEmployee->load('salaryComponents.salaryComponent');
        $this->selectedComponentId             = null;
        $this->individualAmount                = 0.00;
        $this->individualMonth                 = null;
        $this->individualDurationType          = 'permanent';
        $this->individualCalculationType       = 'fixed';
        $this->individualLimit                 = null;
        session()->flash('successMessage', 'Salary component assigned successfully.');
    }

    public function removeComponentFromEmployee(int $assignmentId): void
    {
        $assignment = EmployeeSalaryComponent::find($assignmentId);
        if ($assignment && !empty($assignment->applicable_month)) {
            $isLocked = Payroll::where('payroll_month', $assignment->applicable_month)
                ->whereIn('status', ['approved', 'paid'])
                ->exists();

            if ($isLocked) {
                session()->flash('errorMessage', 'Cannot delete component. Payroll for ' . date('F Y', strtotime($assignment->applicable_month . '-01')) . ' is approved/paid and locked.');
                return;
            }
        }

        EmployeeSalaryComponent::where('id', $assignmentId)->delete();
        $this->selectedEmployee->load('salaryComponents.salaryComponent');
        session()->flash('successMessage', 'Salary component removed.');
    }

    public function toggleComponentStatus(int $assignmentId): void
    {
        $assignmentRecord = EmployeeSalaryComponent::findOrFail($assignmentId);
        if (!empty($assignmentRecord->applicable_month)) {
            $isLocked = Payroll::where('payroll_month', $assignmentRecord->applicable_month)
                ->whereIn('status', ['approved', 'paid'])
                ->exists();

            if ($isLocked) {
                session()->flash('errorMessage', 'Cannot change status. Payroll for ' . date('F Y', strtotime($assignmentRecord->applicable_month . '-01')) . ' is approved/paid and locked.');
                return;
            }
        }

        $assignmentRecord->update(['is_active' => !$assignmentRecord->is_active]);
        $this->selectedEmployee->load('salaryComponents.salaryComponent');
    }

    // --- BULK ASSIGNMENT METHODS ---
    public function openBulkModal(): void
    {
        $this->bulkComponentId          = null;
        $this->bulkTarget               = 'category';
        $this->bulkCategoryId           = null;
        $this->bulkAmount               = 0.00;
        $this->bulkDurationType         = 'permanent';
        $this->bulkMonth                = null;
        $this->isBulkComponentRecurring = true;
        $this->bulkCalculationType      = 'fixed';
        $this->bulkLimit                = null;
        $this->bulkOverwriteMode        = 'skip'; // Default skip to protect custom rates
        $this->showBulkModal            = true;
    }

    public function processBulkAssign(): void
    {
        $this->validate([
            'bulkComponentId'   => 'required|exists:salary_components,id',
            'bulkTarget'        => 'required|in:all,category',
            'bulkCategoryId'    => 'required_if:bulkTarget,category',
            'bulkAmount'        => 'required|numeric|min:0',
            'bulkMonth'         => (!$this->isBulkComponentRecurring) ? 'required' : 'nullable',
            'bulkOverwriteMode' => 'required|in:skip,overwrite',
        ]);

        $salaryComponent = SalaryComponent::findOrFail($this->bulkComponentId);
        $maximumLimit    = $salaryComponent->limit ? (float) $salaryComponent->limit : null;

        if ($this->bulkCalculationType === 'percentage' && (float) $this->bulkAmount > 100) {
            $this->addError('bulkAmount', 'Percentage cannot exceed 100%.');
            return;
        }

        if ($maximumLimit !== null && (float) $this->bulkAmount > $maximumLimit) {
            $formattedLimit = ($this->bulkCalculationType === 'percentage')
                ? "{$maximumLimit}%"
                : "LKR " . number_format($maximumLimit, 2);

            $this->addError('bulkAmount', "The value cannot exceed the maximum limit of {$formattedLimit}.");
            return;
        }

        $employeeQuery = Employee::query()->where('status', 'Active');

        if ($this->bulkTarget === 'category' && $this->bulkCategoryId) {
            $employeeQuery->where('employee_category_id', $this->bulkCategoryId);
        }

        $targetEmployeeIds = $employeeQuery->pluck('id');

        if ($targetEmployeeIds->isEmpty()) {
            session()->flash('errorMessage', 'No active employees found matching the selection.');
            return;
        }

        $targetMonth   = (!$this->isBulkComponentRecurring) ? $this->bulkMonth : null;
        $assignedCount = 0;
        $skippedCount  = 0;

        DB::transaction(function () use ($targetEmployeeIds, $targetMonth, &$assignedCount, &$skippedCount) {
            foreach ($targetEmployeeIds as $currentEmployeeId) {
                $existingRecord = EmployeeSalaryComponent::where('employee_id', $currentEmployeeId)
                    ->where('salary_component_id', $this->bulkComponentId)
                    ->where('applicable_month', $targetMonth)
                    ->first();
                if ($existingRecord) {
                    if ($this->bulkOverwriteMode === 'overwrite') {
                        $existingRecord->update([
                            'amount'    => $this->bulkAmount,
                            'is_active' => true,
                        ]);
                        $assignedCount++;
                    } else {
                        $skippedCount++; // Skip duplicate assignment for safety
                    }
                } else {
                    EmployeeSalaryComponent::create([
                        'employee_id'         => $currentEmployeeId,
                        'salary_component_id' => $this->bulkComponentId,
                        'amount'              => $this->bulkAmount,
                        'applicable_month'    => $targetMonth,
                        'is_active'           => true,
                    ]);
                    $assignedCount++;
                }
            }
        });

        $this->showBulkModal = false;
        session()->flash('successMessage', "Bulk assignment complete: {$assignedCount} employees updated." . ($skippedCount > 0 ? " ({$skippedCount} employees skipped as they already have this component)." : ""));
    }

    public function closeModals(): void
    {
        $this->showIndividualModal = false;
        $this->showBulkModal       = false;
        $this->selectedEmployee    = null;
        $this->individualLimit     = null;
        $this->bulkLimit           = null;
        $this->resetValidation();
    }

    // --- CALCULATION HELPER METHODS ---

    /**
     * Calculate percentage allowances into currency amounts based on basic salary
     */
    public function getCalculatedRupeeAmount(EmployeeSalaryComponent $assignedComponent, float $employeeBasicSalary): float
    {
        $salaryComponent = $assignedComponent->salaryComponent;
        if ($salaryComponent && $salaryComponent->calculation_type === 'percentage') {
            return ($employeeBasicSalary * (float) $assignedComponent->amount) / 100;
        }

        return (float) $assignedComponent->amount;
    }

    /**
     * Total calculated amount of active allowances or deductions for employee in the selected month
     */
    public function calculateTotalAmountForEmployee(Employee $employee, string $componentType): float
    {
        $employeeBasicSalary   = (float) ($employee->salaryAndBank?->basic_salary ?? 0.00);
        $totalCalculatedAmount = 0.00;

        foreach ($employee->salaryComponents as $assignedComponent) {
            if (!$assignedComponent->is_active) {
                continue;
            }

            $salaryComponent = $assignedComponent->salaryComponent;
            if (!$salaryComponent || $salaryComponent->type !== $componentType) {
                continue;
            }

            // Monthly Filter rules:
            // 1. Permanent recurring components (applicable_month is null)
            // 2. One-time components applicable only to selected month (applicable_month == selectedMonthFilter)
            $isRecurringComponent = empty($assignedComponent->applicable_month);
            $isForSelectedMonth   = ($assignedComponent->applicable_month === $this->selectedMonthFilter);

            if (!$isRecurringComponent && !$isForSelectedMonth) {
                continue; // Exclude one-time components belonging to other months
            }

            if ($salaryComponent->calculation_type === 'percentage') {
                $totalCalculatedAmount += ($employeeBasicSalary * (float) $assignedComponent->amount) / 100;
            } else {
                $totalCalculatedAmount += (float) $assignedComponent->amount;
            }
        }

        return $totalCalculatedAmount;
    }

    public function render()
    {
        $employees = Employee::with([
            'category',
            'salaryAndBank',
            'salaryComponents.salaryComponent'
        ])
        ->when($this->search, function ($query) {
            $query->where(function ($subQuery) {
                $subQuery->where('first_name', 'like', '%' . $this->search . '%')
                    ->orWhere('last_name', 'like', '%' . $this->search . '%')
                    ->orWhere('employee_id', 'like', '%' . $this->search . '%');
            });
        })
        ->when($this->selectedCategoryFilter, function ($query) {
            $query->where('employee_category_id', $this->selectedCategoryFilter);
        })
        ->paginate(10);

        return view('livewire.payroll.salary-assignment-manager', [
            'employees'        => $employees,
            'categories'       => EmployeeCategory::where('is_active', true)->get(),
            'salaryComponents' => SalaryComponent::where('is_active', true)->where('name', '!=', 'Basic')->get(),
        ]);
    }

}