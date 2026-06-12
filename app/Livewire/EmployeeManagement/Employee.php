<?php

namespace App\Livewire\EmployeeManagement;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Employee as EmployeeModel;
use App\Models\Organisation;
use App\Models\Department;
use App\Models\Designation;
use App\Models\WorkLocation;
use App\Models\Currency;
use App\Models\SalaryComponent;
use App\Models\EmployeeSalary;
use App\Models\Bank;
use App\Models\BankBranch;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\DB;


#[Layout('layouts.app')]
class Employee extends Component
{
    use WithPagination;

    // ──────────────────────────────────────────────
    // List / Search
    // ──────────────────────────────────────────────

    public string $search        = '';
    public string $filterStatus  = 'active'; // active | inactive | all
    public $filterDepartment     = null;

    // ──────────────────────────────────────────────
    // Modal State
    // ──────────────────────────────────────────────

    public bool $showModal   = false;
    public int  $currentStep = 1;
    public $editingId        = null;

    // ──────────────────────────────────────────────
    // Step 1 – Basic Details
    // ──────────────────────────────────────────────

    public string $firstName        = '';
    public string $middleName       = '';
    public string $lastName         = '';
    public string $employeeId       = '';
    public string $dateOfJoining    = '';
    public string $workEmail        = '';
    public string $mobileNumber     = '';
    public bool   $isDirector       = false;
    public string $gender           = '';
    public $workLocationId          = null;
    public $designationId           = null;
    public $departmentId            = null;
    public bool   $enablePortal     = false;

    // ──────────────────────────────────────────────
    // Step 2 – Salary Details (New Structure)
    // ──────────────────────────────────────────────

    public $basicMonthlyAmount = null;
    public array $addedEarnings = [];
    public array $addedDeductions = [];
    public $selectedEarningComponent = null;
    public $selectedDeductionComponent = null;
    public $tempCalcType = 'percentage';   // 'percentage' or 'fixed'
    public $tempPercentage = null;
    public $tempFixedAmount = null;
    public $showEarningForm = false;
    public $showDeductionForm = false;
    public bool $showSalarySummary = false;

    // ──────────────────────────────────────────────
    // Step 3 – Personal Details
    // ──────────────────────────────────────────────

    public string $dateOfBirth          = '';
    public string $parentName           = '';
    public string $emergencyContactNumber = '';
    public string $differentlyAbledType = '';
    public string $personalEmail        = '';
    public string $residentialAddress1  = '';
    public string $residentialAddress2  = '';
    public string $residentialCity      = '';
    public string $residentialState     = '';
    public string $residentialPincode   = '';

    // ──────────────────────────────────────────────
    // Step 4 – Payment Information
    // ──────────────────────────────────────────────

    public $selectedBankId     = null;   // Bank model id
    public string $bankSearch  = '';
    public bool $showBankDropdown = false;

    public $selectedBranchId   = null;   // BankBranch model id
    public string $branchSearch = '';
    public bool $showBranchDropdown = false;

    public string $paymentMethod     = 'bank_transfer';
    public string $accountHolderName = '';
    public string $accountNumber        = '';
    public string $accountType          = 'savings'; // savings | current

    // Delete
    public $confirmDeleteId = null;

    protected $paginationTheme = 'tailwind';

    // ── Searchable Dropdown Properties ──────────────────────
    public string $earningSearch = '';
    public bool $showEarningDropdown = false;

    public string $deductionSearch = '';
    public bool $showDeductionDropdown = false;

    // ──────────────────────────────────────────────
    // Computed Properties for Salary Summary
    // ──────────────────────────────────────────────

    #[Computed]
    public function totalMonthlyEarnings(): float
    {
        $basicMonthly = (float) ($this->basicMonthlyAmount ?? 0);
        $otherEarnings = collect($this->addedEarnings)->sum('monthly_amount');
        return $basicMonthly + $otherEarnings;
    }

    #[Computed]
    public function totalMonthlyDeductions(): float
    {
        return collect($this->addedDeductions)->sum('monthly_amount');
    }

    #[Computed]
    public function netMonthlySalary(): float
    {
        return $this->totalMonthlyEarnings - $this->totalMonthlyDeductions;
    }

    #[Computed]
    public function totalAnnualEarnings(): float
    {
        $basicAnnual = ((float) ($this->basicMonthlyAmount ?? 0)) * 12;
        $otherEarnings = collect($this->addedEarnings)->sum('annual_amount');
        return $basicAnnual + $otherEarnings;
    }

    #[Computed]
    public function totalAnnualDeductions(): float
    {
        return collect($this->addedDeductions)->sum('annual_amount');
    }

    #[Computed]
    public function netAnnualSalary(): float
    {
        return $this->totalAnnualEarnings - $this->totalAnnualDeductions;
    }

    public function getFilteredBanksProperty()
    {
        $query = Bank::where('is_active', true)->orderBy('bank_name');

        if (!empty(trim($this->bankSearch))) {
            $search = strtolower($this->bankSearch);
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(bank_name) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(bank_code) LIKE ?', ["%{$search}%"]);
            });
        }

        return $query->get();
    }

    public function getFilteredBranchesProperty()
    {
        if (!$this->selectedBankId) return collect();

        $query = BankBranch::where('bank_id', $this->selectedBankId)
            ->where('is_active', true)
            ->orderBy('branch_name');

        if (!empty(trim($this->branchSearch))) {
            $search = strtolower($this->branchSearch);
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(branch_name) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(branch_code) LIKE ?', ["%{$search}%"]);
            });
        }

        return $query->get();
    }

    public function selectBank(int $id): void
    {
        $bank = Bank::find($id);
        $this->selectedBankId     = $id;
        $this->bankSearch         = $bank ? "{$bank->bank_code} – {$bank->bank_name}" : '';
        $this->showBankDropdown   = false;
        // Branch reset when bank changes
        $this->selectedBranchId   = null;
        $this->branchSearch       = '';
        $this->showBranchDropdown = false;
    }

    public function selectBranch(int $id): void
    {
        $branch = BankBranch::find($id);
        $this->selectedBranchId   = $id;
        $this->branchSearch       = $branch ? "{$branch->branch_code} – {$branch->branch_name}" : '';
        $this->showBranchDropdown = false;
    }

    public function openBankDropdown(): void
    {
        // Don't clear bankSearch here — keep the existing label visible.
        // The user can type to search; cross button clears the selection.
        $this->showBankDropdown = true;
    }

    public function openBranchDropdown(): void
    {
        // Don't clear branchSearch here — keep the existing label visible.
        $this->showBranchDropdown = true;
    }

    public function closeBankDropdown(): void
    {
        $this->showBankDropdown = false;
    }

    public function closeBranchDropdown(): void
    {
        $this->showBranchDropdown = false;
    }

    public function clearBank(): void
    {
        $this->selectedBankId     = null;
        $this->bankSearch         = '';
        $this->showBankDropdown   = false;
        $this->selectedBranchId   = null;
        $this->branchSearch       = '';
        $this->showBranchDropdown = false;
    }

    public function clearBranch(): void
    {
        $this->selectedBranchId   = null;
        $this->branchSearch       = '';
        $this->showBranchDropdown = false;
    }

    // ──────────────────────────────────────────────
    // Validation Rules per Step
    // ──────────────────────────────────────────────

    protected function rulesForStep(int $step): array
    {
        return match ($step) {
            1 => [
                'firstName'      => 'required|string|max:100',
                'middleName'     => 'nullable|string|max:100',
                'lastName'       => 'nullable|string|max:100',
                'employeeId'     => 'required|string|max:50',
                'dateOfJoining'  => 'required|date',
                'workEmail'      => 'required|email|max:255',
                'mobileNumber'   => 'nullable|string|max:20',
                'gender'         => 'required|in:male,female,other',
                'workLocationId' => 'required|exists:work_locations,id',
                'designationId'  => 'required|exists:designations,id',
                'departmentId'   => 'required|exists:departments,id',
            ],
            2 => [
                'basicMonthlyAmount' => 'required|numeric|min:0',
                'addedEarnings'      => 'array',
                'addedDeductions'    => 'array',
            ],
            3 => [
                'dateOfBirth'   => 'required|date',
                'parentName'    => 'required|string|max:255',
                'emergencyContactNumber' => 'nullable|string|max:20',
                'personalEmail' => 'nullable|email|max:255',
            ],
            4 => [
                'accountHolderName' => 'required|string|max:255',
                'selectedBankId'    => 'required|exists:banks,id',
                'selectedBranchId'  => 'required|exists:bank_branches,id',
                'accountNumber'     => 'required|string|max:30',
                'accountType'       => 'required|in:savings,current',
            ],
            default => [],
        };
    }

    protected $validationAttributes = [
        'firstName'          => 'First Name',
        'lastName'           => 'Last Name',
        'employeeId'         => 'Employee ID',
        'dateOfJoining'      => 'Date of Joining',
        'workEmail'          => 'Work Email',
        'gender'             => 'Gender',
        'workLocationId'     => 'Work Location',
        'designationId'      => 'Designation',
        'departmentId'       => 'Department',
        'basicMonthlyAmount' => 'Basic Monthly Amount',
        'dateOfBirth'        => 'Date of Birth',
        'parentName'         => "Parent's / Other Name",
        'emergencyContactNumber' => 'Emergency Contact Number',
        'accountHolderName'  => 'Account Holder Name',
        'selectedBankId'   => 'Bank',
        'selectedBranchId' => 'Branch',
        'accountNumber'      => 'Account Number',
        'accountType'        => 'Account Type',
    ];

    // ──────────────────────────────────────────────
    // Lifecycle
    // ──────────────────────────────────────────────

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedBasicMonthlyAmount(): void
    {
        $this->recalculateAllComponents();
    }

    // ──────────────────────────────────────────────
    // Modal Open / Close
    // ──────────────────────────────────────────────

    public function openModal(): void
    {
        $this->resetAll();
        $this->showModal   = true;
        $this->currentStep = 1;
    }

    public function edit(int $id): void
    {
        $this->resetAll();

        $employee = EmployeeModel::with('employeeSalaries.salaryComponent')->findOrFail($id);

        // Step 1
        [$this->firstName, $this->middleName, $this->lastName] = array_pad(
            explode(' ', $employee->name, 3),
            3,
            ''
        );
        $this->employeeId     = $employee->employee_id         ?? '';
        $this->dateOfJoining  = $employee->date_of_joining     ? $employee->date_of_joining->format('Y-m-d') : '';
        $this->workEmail      = $employee->work_email          ?? '';
        $this->mobileNumber   = $employee->mobile_number       ?? '';
        $this->isDirector     = $employee->is_director         ?? false;
        $this->gender         = $employee->gender              ?? '';
        $this->workLocationId = $employee->work_location_id    ?? null;
        $this->designationId  = $employee->designation_id      ?? null;
        $this->departmentId   = $employee->department_id       ?? null;
        $this->enablePortal   = $employee->enable_portal       ?? false;

        // Step 2 - Load saved salary components
        // Basic salary is stored separately in basicMonthlyAmount.
        // We skip the Basic component when loading into addedEarnings
        // to prevent duplicate insert on save.
        foreach ($employee->employeeSalaries as $salaryComp) {

            // ── Skip Basic — it maps to basicMonthlyAmount field ──
            if (strtolower($salaryComp->salaryComponent->name) === 'basic') {
                $this->basicMonthlyAmount = (float) $salaryComp->monthly_amount;
                continue;
            }

            if ($salaryComp->is_earning) {
                $this->addedEarnings[] = [
                    'id'               => $salaryComp->id,
                    'component_id'     => $salaryComp->salary_component_id,
                    'component_name'   => $salaryComp->salaryComponent->name,
                    'name_in_payslip'  => $salaryComp->salaryComponent->name_in_payslip,
                    'calculation_type' => $salaryComp->calculation_type,
                    'percentage_value' => $salaryComp->percentage_value,
                    'fixed_amount'     => $salaryComp->fixed_amount,
                    'monthly_amount'   => (float) $salaryComp->monthly_amount,
                    'annual_amount'    => (float) $salaryComp->annual_amount,
                ];
            } else {
                $this->addedDeductions[] = [
                    'id'               => $salaryComp->id,
                    'component_id'     => $salaryComp->salary_component_id,
                    'component_name'   => $salaryComp->salaryComponent->name,
                    'name_in_payslip'  => $salaryComp->salaryComponent->name_in_payslip,
                    'calculation_type' => $salaryComp->calculation_type,
                    'percentage_value' => $salaryComp->percentage_value,
                    'fixed_amount'     => $salaryComp->fixed_amount,
                    'monthly_amount'   => (float) $salaryComp->monthly_amount,
                    'annual_amount'    => (float) $salaryComp->annual_amount,
                ];
            }
        }

        // Fallback: if Basic was not in employeeSalaries, try the model column
        if ($this->basicMonthlyAmount === null) {
            $this->basicMonthlyAmount = $employee->basic_salary_monthly ?? null;
        }

        // Step 3
        $this->dateOfBirth          = $employee->date_of_birth          ? $employee->date_of_birth->format('Y-m-d') : '';
        $this->parentName           = $employee->parent_name            ?? '';
        $this->emergencyContactNumber = $employee->emergency_contact_number ?? '';
        $this->differentlyAbledType = $employee->differently_abled_type ?? '';
        $this->personalEmail        = $employee->personal_email         ?? '';
        $this->residentialAddress1  = $employee->residential_address_1  ?? '';
        $this->residentialAddress2  = $employee->residential_address_2  ?? '';
        $this->residentialCity      = $employee->residential_city       ?? '';
        $this->residentialState     = $employee->residential_state      ?? '';
        $this->residentialPincode   = $employee->residential_pincode    ?? '';

        // Step 4
        $this->paymentMethod     = $employee->payment_method      ?? 'bank_transfer';
        $this->accountHolderName = $employee->account_holder_name ?? '';
        $this->accountNumber     = $employee->account_number      ?? '';
        $this->accountType       = $employee->account_type        ?? 'savings';

        // Bank & Branch load
        if ($employee->bank_branch_id) {
            $branch = BankBranch::with('bank')->find($employee->bank_branch_id);
            if ($branch) {
                $this->selectedBranchId = $branch->id;
                $this->branchSearch     = "{$branch->branch_code} – {$branch->branch_name}";
                $this->selectedBankId   = $branch->bank_id;
                $this->bankSearch       = "{$branch->bank->bank_code} – {$branch->bank->bank_name}";
            }
        }

        $this->editingId   = $id;
        $this->showModal   = true;
        $this->currentStep = 1;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetAll();
    }

    // ──────────────────────────────────────────────
    // Step Navigation
    // ──────────────────────────────────────────────

    public function nextStep(): void
    {
        $this->validate($this->rulesForStep($this->currentStep));

        if ($this->currentStep === 2) {
            $this->showSalarySummary = true;
        } elseif ($this->currentStep < 4) {
            $this->currentStep++;
        }
    }

    public function prevStep(): void
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }

    public function goToStep(int $step): void
    {
        if ($step < $this->currentStep) {
            $this->currentStep = $step;
        }
    }

    // ── Computed filtered lists ──────────────────────────────

    public function getFilteredEarningComponentsProperty()
    {
        $org = Organisation::first(['id']);

        $query = SalaryComponent::where('organisation_id', $org?->id ?? 0)
            ->where('type', 'earning')
            ->where('is_active', true)
            ->whereRaw('LOWER(name) != ?', ['basic'])
            ->orderBy('name');

        if (!empty(trim($this->earningSearch))) {
            $search = strtolower($this->earningSearch);
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(name_in_payslip) LIKE ?', ["%{$search}%"]);
            });
        }

        return $query->get();
    }

    public function getFilteredDeductionComponentsProperty()
    {
        $org = Organisation::first(['id']);

        $query = SalaryComponent::where('organisation_id', $org?->id ?? 0)
            ->where('type', 'deduction')
            ->where('is_active', true)
            ->orderBy('name');

        if (!empty(trim($this->deductionSearch))) {
            $search = strtolower($this->deductionSearch);
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(name_in_payslip) LIKE ?', ["%{$search}%"]);
            });
        }

        return $query->get();
    }

    // ── Select handlers ──────────────────────────────────────

    public function selectEarningComponent(int $id): void
    {
        $this->selectedEarningComponent = $id;
        $comp = SalaryComponent::find($id);
        $this->earningSearch = $comp ? $comp->name : '';
        $this->showEarningDropdown = false;
    }

    public function selectDeductionComponent(int $id): void
    {
        $this->selectedDeductionComponent = $id;
        $comp = SalaryComponent::find($id);
        $this->deductionSearch = $comp ? $comp->name : '';
        $this->showDeductionDropdown = false;
    }

    // ──────────────────────────────────────────────
    // Salary Summary Methods
    // ──────────────────────────────────────────────

    public function editSalaryFromSummary(): void
    {
        $this->showSalarySummary = false;
        $this->currentStep = 2;
    }

    public function confirmAndContinue(): void
    {
        $this->showSalarySummary = false;
        $this->currentStep = 3;
    }

    public function goBackToStep2(): void
    {
        $this->showSalarySummary = false;
        $this->currentStep = 2;
    }

    // ──────────────────────────────────────────────
    // Save (Step 4 submit)
    // ──────────────────────────────────────────────

    public function save(): void
    {
        $this->validate($this->rulesForStep(4));
        $this->validate($this->rulesForStep(2));

        $orgId = Organisation::first(['id'])?->id;

        $data = [
            'organisation_id'        => $orgId,
            // Step 1
            'name'                   => trim("{$this->firstName} {$this->middleName} {$this->lastName}"),
            'employee_id'            => $this->employeeId,
            'date_of_joining'        => $this->dateOfJoining,
            'work_email'             => $this->workEmail,
            'mobile_number'          => $this->mobileNumber ?: null,
            'is_director'            => $this->isDirector,
            'gender'                 => $this->gender,
            'work_location_id'       => $this->workLocationId,
            'designation_id'         => $this->designationId,
            'department_id'          => $this->departmentId,
            'enable_portal'          => $this->enablePortal,
            // Step 2
            'annual_ctc'             => $this->netAnnualSalary,
            // Step 3
            'date_of_birth'          => $this->dateOfBirth ?: null,
            'parent_name'            => $this->parentName,
            'emergency_contact_number' => $this->emergencyContactNumber ?: null,
            'differently_abled_type' => $this->differentlyAbledType ?: null,
            'personal_email'         => $this->personalEmail ?: null,
            'residential_address_1'  => $this->residentialAddress1 ?: null,
            'residential_address_2'  => $this->residentialAddress2 ?: null,
            'residential_city'       => $this->residentialCity ?: null,
            'residential_state'      => $this->residentialState ?: null,
            'residential_pincode'    => $this->residentialPincode ?: null,
            // Step 4
            'payment_method'         => $this->paymentMethod,
            'account_holder_name'    => $this->accountHolderName,
            'bank_branch_id'      => $this->selectedBranchId,
            'account_number'         => $this->accountNumber,
            'account_type'           => $this->accountType,
            'is_profile_complete'    => true,
        ];


        try {
            DB::transaction(function () use ($data, $orgId) {

                if ($this->editingId) {
                    $employee = EmployeeModel::findOrFail($this->editingId);
                    $employee->update($data);
                    $employee->employeeSalaries()->delete();
                } else {
                    $employee = EmployeeModel::create($data);
                }

                // ── Save Basic component ──────────────────────────────
                $basicComponent = SalaryComponent::where('organisation_id', $orgId)
                    ->whereRaw('LOWER(name) = ?', ['basic'])
                    ->first();

                if ($basicComponent && $this->basicMonthlyAmount) {
                    EmployeeSalary::create([
                        'employee_id'         => $employee->id,
                        'salary_component_id' => $basicComponent->id,
                        'calculation_type'    => 'fixed',
                        'percentage_value'    => null,
                        'fixed_amount'        => $this->basicMonthlyAmount,
                        'monthly_amount'      => $this->basicMonthlyAmount,
                        'annual_amount'       => $this->basicMonthlyAmount * 12,
                        'is_earning'          => true,
                    ]);
                }

                // ── Save added earnings (Basic is already saved above, skip if somehow present) ──
                foreach ($this->addedEarnings as $earning) {
                    // Safety guard: skip if this is the Basic component
                    if ($basicComponent && $earning['component_id'] == $basicComponent->id) {
                        continue;
                    }

                    EmployeeSalary::create([
                        'employee_id'         => $employee->id,
                        'salary_component_id' => $earning['component_id'],
                        'calculation_type'    => $earning['calculation_type'],
                        'percentage_value'    => $earning['percentage_value'],
                        'fixed_amount'        => $earning['fixed_amount'],
                        'monthly_amount'      => $earning['monthly_amount'],
                        'annual_amount'       => $earning['annual_amount'],
                        'is_earning'          => true,
                    ]);
                }

                // ── Save deductions ───────────────────────────────────
                foreach ($this->addedDeductions as $deduction) {
                    EmployeeSalary::create([
                        'employee_id'         => $employee->id,
                        'salary_component_id' => $deduction['component_id'],
                        'calculation_type'    => $deduction['calculation_type'],
                        'percentage_value'    => $deduction['percentage_value'],
                        'fixed_amount'        => $deduction['fixed_amount'],
                        'monthly_amount'      => $deduction['monthly_amount'],
                        'annual_amount'       => $deduction['annual_amount'],
                        'is_earning'          => false,
                    ]);
                }
            });

            $this->closeModal();
            session()->flash('success', 'Employee saved successfully.');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to save employee. Please try again.');
            \Log::error('Employee save error: ' . $e->getMessage());
            // Optionally log full stack trace for debugging
            \Log::error($e->getTraceAsString());
            return;
        }
    }

    // ──────────────────────────────────────────────
    // Step 2 - Salary Component Methods
    // ──────────────────────────────────────────────

    public function recalculateAllComponents(): void
    {
        $basicMonthly = (float) ($this->basicMonthlyAmount ?? 0);

        foreach ($this->addedEarnings as &$earning) {
            if ($earning['calculation_type'] === 'percentage') {
                $pct     = (float) ($earning['percentage_value'] ?? 0);
                $monthly = round($basicMonthly * ($pct / 100), 2);
            } else {
                $monthly = (float) ($earning['fixed_amount'] ?? 0);
            }
            $earning['monthly_amount'] = $monthly;
            $earning['annual_amount']  = round($monthly * 12, 2);
        }
        unset($earning);

        foreach ($this->addedDeductions as &$deduction) {
            if ($deduction['calculation_type'] === 'percentage') {
                $pct     = (float) ($deduction['percentage_value'] ?? 0);
                $monthly = round($basicMonthly * ($pct / 100), 2);
            } else {
                $monthly = (float) ($deduction['fixed_amount'] ?? 0);
            }
            $deduction['monthly_amount'] = $monthly;
            $deduction['annual_amount']  = round($monthly * 12, 2);
        }
        unset($deduction);
    }

    public function addEarning(): void
    {
        $this->validate([
            'selectedEarningComponent' => 'required|exists:salary_components,id',
        ]);

        $component = SalaryComponent::find($this->selectedEarningComponent);

        if ($this->tempCalcType === 'percentage') {
            $this->validate(['tempPercentage' => 'required|numeric|min:0|max:100']);
        } else {
            $this->validate(['tempFixedAmount' => 'required|numeric|min:0']);
        }

        $this->addedEarnings[] = [
            'component_id'     => $component->id,
            'component_name'   => $component->name,
            'name_in_payslip'  => $component->name_in_payslip,
            'calculation_type' => $this->tempCalcType,
            'percentage_value' => $this->tempCalcType === 'percentage' ? ($this->tempPercentage ?? 0) : null,
            'fixed_amount'     => $this->tempCalcType === 'fixed'      ? ($this->tempFixedAmount ?? 0) : null,
            'monthly_amount'   => 0,
            'annual_amount'    => 0,
        ];

        $this->recalculateAllComponents();
        $this->showEarningForm = false;
        $this->resetSalaryForm();
    }

    public function addDeduction(): void
    {
        $this->validate([
            'selectedDeductionComponent' => 'required|exists:salary_components,id',
        ]);

        $component = SalaryComponent::find($this->selectedDeductionComponent);

        if ($this->tempCalcType === 'percentage') {
            $this->validate(['tempPercentage' => 'required|numeric|min:0|max:100']);
        } else {
            $this->validate(['tempFixedAmount' => 'required|numeric|min:0']);
        }

        $this->addedDeductions[] = [
            'component_id'     => $component->id,
            'component_name'   => $component->name,
            'name_in_payslip'  => $component->name_in_payslip,
            'calculation_type' => $this->tempCalcType,
            'percentage_value' => $this->tempCalcType === 'percentage' ? ($this->tempPercentage ?? 0) : null,
            'fixed_amount'     => $this->tempCalcType === 'fixed'      ? ($this->tempFixedAmount ?? 0) : null,
            'monthly_amount'   => 0,
            'annual_amount'    => 0,
        ];

        $this->recalculateAllComponents();
        $this->showDeductionForm = false;
        $this->resetSalaryForm();
    }

    public function removeEarning(int $index): void
    {
        unset($this->addedEarnings[$index]);
        $this->addedEarnings = array_values($this->addedEarnings);
        $this->recalculateAllComponents();
    }

    public function removeDeduction(int $index): void
    {
        unset($this->addedDeductions[$index]);
        $this->addedDeductions = array_values($this->addedDeductions);
        $this->recalculateAllComponents();
    }

    public function updateEarningCalcType(int $index, string $type): void
    {
        $this->addedEarnings[$index]['calculation_type'] = $type;
        if ($type === 'percentage') {
            $this->addedEarnings[$index]['fixed_amount'] = null;
        } else {
            $this->addedEarnings[$index]['percentage_value'] = null;
        }
        $this->recalculateAllComponents();
    }

    public function updateDeductionCalcType(int $index, string $type): void
    {
        $this->addedDeductions[$index]['calculation_type'] = $type;
        if ($type === 'percentage') {
            $this->addedDeductions[$index]['fixed_amount'] = null;
        } else {
            $this->addedDeductions[$index]['percentage_value'] = null;
        }
        $this->recalculateAllComponents();
    }

    public function updateEarningValue(int $index, string $field, $value): void
    {
        $this->addedEarnings[$index][$field] = $value;
        $this->recalculateAllComponents();
    }

    public function updateDeductionValue(int $index, string $field, $value): void
    {
        $this->addedDeductions[$index][$field] = $value;
        $this->recalculateAllComponents();
    }

    // ──────────────────────────────────────────────
    // Delete
    // ──────────────────────────────────────────────

    public function confirmDelete(int $id): void
    {
        $this->confirmDeleteId = $id;
    }

    public function delete(): void
    {
        if ($this->confirmDeleteId) {
            try {
                DB::transaction(function () {
                    $employee = EmployeeModel::findOrFail($this->confirmDeleteId);
                    $employee->employeeSalaries()->delete();
                    $employee->delete();
                });
                session()->flash('success', 'Employee deleted successfully.');
            } catch (\Exception $e) {
                session()->flash('error', 'Failed to delete employee.');
                \Log::error('Employee delete error: ' . $e->getMessage());
            }

            $this->confirmDeleteId = null;
        }
    }

    public function cancelDelete(): void
    {
        $this->confirmDeleteId = null;
    }

    // ──────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────

    private function resetAll(): void
    {
        $this->reset([
            'editingId',
            'currentStep',
            'firstName',
            'middleName',
            'lastName',
            'employeeId',
            'dateOfJoining',
            'workEmail',
            'mobileNumber',
            'isDirector',
            'gender',
            'workLocationId',
            'designationId',
            'departmentId',
            'enablePortal',
            'dateOfBirth',
            'parentName',
            'emergencyContactNumber',
            'differentlyAbledType',
            'personalEmail',
            'residentialAddress1',
            'residentialAddress2',
            'residentialCity',
            'residentialState',
            'residentialPincode',
            'paymentMethod',
            'accountHolderName',
            'selectedBankId',
            'bankSearch',
            'showBankDropdown',
            'selectedBranchId',
            'branchSearch',
            'showBranchDropdown',
            'accountNumber',
            'accountType',
            'basicMonthlyAmount',
            'addedEarnings',
            'addedDeductions',
            'selectedEarningComponent',
            'selectedDeductionComponent',
            'tempCalcType',
            'tempPercentage',
            'tempFixedAmount',
            'showEarningForm',
            'showDeductionForm',
            'showSalarySummary',
            'earningSearch',
            'deductionSearch',
            'showEarningDropdown',
            'showDeductionDropdown',
        ]);
        $this->currentStep = 1;
        $this->resetValidation();
    }

    // ──────────────────────────────────────────────
    // Modal Methods for Salary Components
    // ──────────────────────────────────────────────

    public function openEarningModal(): void
    {
        $this->resetSalaryForm();
        $this->showEarningForm = true;
    }

    public function openDeductionModal(): void
    {
        $this->resetSalaryForm();
        $this->showDeductionForm = true;
    }

    public function closeEarningModal(): void
    {
        $this->showEarningForm = false;
        $this->earningSearch = '';
        $this->showEarningDropdown = false;
        $this->resetSalaryForm();
    }

    public function closeDeductionModal(): void
    {
        $this->showDeductionForm = false;
        $this->deductionSearch = '';
        $this->showDeductionDropdown = false;
        $this->resetSalaryForm();
    }

    private function resetSalaryForm(): void
    {
        $this->selectedEarningComponent   = null;
        $this->selectedDeductionComponent = null;
        $this->tempCalcType               = 'percentage';
        $this->tempPercentage             = null;
        $this->tempFixedAmount            = null;
        $this->resetValidation();
    }

    // ──────────────────────────────────────────────
    // Render
    // ──────────────────────────────────────────────

    public function render()
    {
        $org = Organisation::first(['id']);

        $employees = EmployeeModel::query()
            ->when($org, fn($q) => $q->where('organisation_id', $org->id))
            ->when($this->search, fn($q) => $q->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('work_email', 'like', "%{$this->search}%")
                    ->orWhere('employee_id', 'like', "%{$this->search}%");
            }))
            ->when($this->filterDepartment, fn($q) => $q->where('department_id', $this->filterDepartment))
            ->when($this->filterStatus === 'active',   fn($q) => $q->where('is_active', true))
            ->when($this->filterStatus === 'inactive', fn($q) => $q->where('is_active', false))
            ->with(['department', 'designation', 'workLocation'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $departments   = Department::forOrganisation($org?->id ?? 0)->orderBy('name')->get();
        $designations  = Designation::forOrganisation($org?->id ?? 0)->orderBy('name')->get();
        $workLocations = WorkLocation::where('organisation_id', $org?->id ?? 0)->orderBy('name')->get();
        $baseCurrency  = $org ? Currency::baseCurrencyFor($org->id) : null;

        $earningComponents = SalaryComponent::where('organisation_id', $org?->id ?? 0)
            ->where('type', 'earning')
            ->where('is_active', true)
            ->whereRaw('LOWER(name) != ?', ['basic'])
            ->orderBy('name')
            ->get();

        $deductionComponents = SalaryComponent::where('organisation_id', $org?->id ?? 0)
            ->where('type', 'deduction')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('livewire.employee.employee', [
            'employeeList'        => $employees,
            'departments'         => $departments,
            'designations'        => $designations,
            'workLocations'       => $workLocations,
            'baseCurrency'        => $baseCurrency,
            'earningComponents'   => $earningComponents,
            'deductionComponents' => $deductionComponents,
        ]);
    }
}
