<?php

namespace App\Livewire\Employee;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Spatie\Permission\Models\Role;
use App\Models\Bank;
use App\Models\BankBranch;
use App\Models\Organisation;
use App\Models\Department;
use App\Models\Shift;
use App\Models\WorkLocation;
use Illuminate\Support\Str;
use App\Models\EmployeeCategory;

/**
 * EmployeeManagement Livewire Component
 *
 * 4-step wizard modal
 *   Step 1 → Personal Info & System Access
 *   Step 2 → Address & Emergency Contacts
 *   Step 3 → Family Details (Spouse + Children)
 *   Step 4 → Salary & Bank Details
 */
#[Layout('layouts.app')]
class EmployeeManagement extends Component
{
    use WithPagination;

    // ==========================================
    // --- UI STATE MANAGEMENT & CONTROL MODALS ---
    // ==========================================

    public $isModalOpen = false;
    public $isEditMode = false;
    public $currentStep = 1;
    public $totalSteps = 4;
    public $selectedEmployeeId;
    public $isDropdownOpen = false;

    // ==========================================
    // --- GLOBAL SEARCH & FILTER PROPERTIES ----
    // ==========================================

    public $search = '';
    public $filterPosition = '';
    public $isFilterDropdownOpen = false;
    public $isPositionDropdownOpen = false;

    // ==========================================
    // --- STEP 1: PERSONAL INFO & ACCESS -------
    // ==========================================

    public $employee_id, $epf_number, $first_name, $last_name, $date_of_birth, $gender;
    public $nic_number, $passport_number, $marital_status, $phone_number;
    public $job_position, $work_location, $date_of_join;

    public $status = 'Active';
    public $system_access = true;
    public $email;
    public string $password = ''; // Custom Password Property
    public $isGenderDropdownOpen = false;
    public $isJobDropdownOpen = false;
    public $isMaritalDropdownOpen = false;
    public $isStatusDropdownOpen = false;

    // --- DEPARTMENT DROPDOWN DATA FIELDS ---
    public $department_id = null;
    public string $selectedDepartmentName = '';
    public bool $isDepartmentDropdownOpen = false;

    // --- SHIFT & OT DATA FIELDS (FIX FOR THE ERROR) ---
    public $shift_id = null;
    public string $selectedShiftName = '';
    public bool $isShiftDropdownOpen = false;
    public bool $is_ot_eligible = false;
    public bool $isCategoryOtEnabled = false;
    // ==========================================
    // --- STEP 2: ADDRESS & EMERGENCY ----------
    // ==========================================

    public $address_line_1, $address_line_2, $city, $postal_code;
    public $emergency_contacts = [];

    // ==========================================
    // --- STEP 3: FAMILY DETAILS ---------------
    // ==========================================

    public $spouse_name, $spouse_dob, $spouse_nic, $spouse_passport;
    public $children = [];

    public $openChildGenderIndex = null;
    public $openChildMaritalIndex = null;

    // ==========================================
    // --- STEP 4: SALARY & BANK DETAILS --------
    // ==========================================

    public $basic_salary, $allowances = 0, $deductions = 0, $bonus = 0;
    public $currency = 'LKR';
    public $payment_method = 'Bank Transfer';
    public $bank_name, $branch_name, $account_holder_name, $account_number;
    public $account_type = 'Savings';
    public $isPaymentDropdownOpen = false;

    // ==========================================
    // --- SEARCHABLE BANK DROPDOWN COMPONENT ---
    // ==========================================

    public $searchBank = '';
    public $searchBranch = '';
    public $isBankDropdownOpen = false;
    public $isBranchDropdownOpen = false;
    public $banks = [];
    public $branches = [];
    public $filterStatus = '';
    public $isLocationDropdownOpen = false;

    // --- EMPLOYEE CATEGORY DATA FIELDS ---
public $employee_category_id = null;
public string $selectedCategoryName = '';
public bool $isCategoryDropdownOpen = false;

public function toggleCategoryDropdown(): void
{
    $this->isCategoryDropdownOpen = !$this->isCategoryDropdownOpen;
}

public function selectCategory($categoryId, string $categoryName): void
    {
        $this->employee_category_id = $categoryId ?: null;
        $this->selectedCategoryName = $categoryName;
        $this->isCategoryDropdownOpen = false;
        if ($this->employee_category_id) {
            $category = EmployeeCategory::find($this->employee_category_id);
            if ($category) {
                // Set OT eligibility directly based on employee category
                $this->isCategoryOtEnabled = (bool) $category->is_ot_eligible;
                $this->is_ot_eligible = $this->isCategoryOtEnabled;
            }
        } else {
            $this->isCategoryOtEnabled = false;
            $this->is_ot_eligible = false;
        }
    }
    // ==================================
    // -- LIVEWIRE BRIDGE METHODS -------
    // ==================================

    public function selectGender($value)
    {
        $this->gender = $value;
        $this->isGenderDropdownOpen = false;
    }

    public function selectJobPosition($value)
    {
        $this->job_position = $value;
        $this->isJobDropdownOpen = false;
    }

    public function selectWorkLocation($value)
    {
        $this->work_location = $value;
        $this->isLocationDropdownOpen = false;
    }

    public function selectMaritalStatus($value)
    {
        $this->marital_status = $value;
        $this->isMaritalDropdownOpen = false;
    }

    public function selectStatus($value)
    {
        $this->status = $value;
        $this->isStatusDropdownOpen = false;
    }

    public function selectChildGender($index, $value)
    {
        $this->children[$index]['gender'] = $value;
        $this->openChildGenderIndex = null;
    }

    public function selectChildMaritalStatus($index, $value)
    {
        $this->children[$index]['marital_status'] = $value;
        $this->openChildMaritalIndex = null;
    }

    public function selectPaymentMethod($value)
    {
        $this->payment_method = $value;
        $this->isPaymentDropdownOpen = false;
    }

    public function toggleDepartmentDropdown(): void
    {
        $this->isDepartmentDropdownOpen = !$this->isDepartmentDropdownOpen;
    }

    public function selectDepartment($departmentId, string $departmentName): void
    {
        $this->department_id = $departmentId ?: null;
        $this->selectedDepartmentName = $departmentName;
        $this->isDepartmentDropdownOpen = false;
    }

    // --- SHIFT DROPDOWN METHODS ---
    public function toggleShiftDropdown(): void
    {
        $this->isShiftDropdownOpen = !$this->isShiftDropdownOpen;
    }

    public function selectShift($shiftId, string $shiftName): void
    {
        $this->shift_id = $shiftId ?: null;
        $this->selectedShiftName = $shiftName;
        $this->isShiftDropdownOpen = false;
    }

    // ==========================================
    // --- LIFECYCLE HOOKS ----------------------
    // ==========================================

    public function mount()
    {
        $this->emergency_contacts = [
            ['name' => '', 'relationship' => '', 'phone' => '']
        ];
    }

    // ==========================================
    // --- EMERGENCY CONTACT MANAGEMENT ---------
    // ==========================================

    public function addEmergencyContact()
    {
        $this->emergency_contacts[] = [
            'name' => '',
            'relationship' => '',
            'phone' => ''
        ];
    }

    public function removeEmergencyContact($index)
    {
        unset($this->emergency_contacts[$index]);
        $this->emergency_contacts = array_values($this->emergency_contacts);
    }

    // ==========================================
    // --- REAL-TIME LIVEWIRE SEARCH RESETS -----
    // ==========================================

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function selectFilterStatus($value)
    {
        $this->filterStatus = $value;
        $this->isFilterDropdownOpen = false;
    }

    public function selectFilterPosition($value)
    {
        $this->filterPosition = $value;
        $this->isPositionDropdownOpen = false;
    }

    // ==========================================
    // --- MODAL OPEN / CLOSE -------------------
    // ==========================================

    public function openModal()
    {
        $this->resetForm();
        $this->resetValidation();

        $this->isEditMode = false;
        $this->isModalOpen = true;
        $this->currentStep = 1;

        $this->banks = Bank::orderBy('bank_name', 'asc')->get()->toArray();
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
    }

    // ==========================================
    // --- STEP NAVIGATION ----------------------
    // ==========================================

    public function nextStep()
    {
        $this->validateCurrentStep();

        if ($this->currentStep == 3) {
            session([
                'wizard_spouse_name'     => $this->spouse_name,
                'wizard_spouse_dob'      => $this->spouse_dob,
                'wizard_spouse_nic'      => $this->spouse_nic,
                'wizard_spouse_passport' => $this->spouse_passport,
                'wizard_children'        => $this->children,
            ]);
        }

        if ($this->currentStep < $this->totalSteps) {
            $this->currentStep++;
        }
    }

    public function previousStep()
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }

    public function setStep($step)
    {
        if ($step > $this->currentStep) {
            $this->validateCurrentStep();
        }

        if ($this->currentStep == 3) {
            session([
                'wizard_spouse_name'     => $this->spouse_name,
                'wizard_spouse_dob'      => $this->spouse_dob,
                'wizard_spouse_nic'      => $this->spouse_nic,
                'wizard_spouse_passport' => $this->spouse_passport,
                'wizard_children'        => $this->children,
            ]);
        }

        $this->currentStep = $step;
    }

    // ==========================================
    // --- STEP-BY-STEP FORM VALIDATION ---------
    // ==========================================

    private function validateCurrentStep()
    {
        if ($this->currentStep == 1) {

            $userIdToIgnore = 'NULL';
            if ($this->selectedEmployeeId) {
                $emp = Employee::find($this->selectedEmployeeId);
                $userIdToIgnore = $emp ? $emp->user_id : 'NULL';
            }

            $this->validate([
                'employee_id'     => 'required|unique:employees,employee_id,' . $this->selectedEmployeeId,
                'epf_number'  => 'nullable|string|max:50',
                'first_name'      => 'required|string|max:255',
                'last_name'       => 'required|string|max:255',
                'date_of_birth'   => 'required|date',
                'gender'          => 'required|in:Male,Female,Other',
                'marital_status'  => 'required|in:Single,Married,Divorced',
                'nic_number'      => 'required_without:passport_number|nullable|string|unique:employees,nic_number,' . $this->selectedEmployeeId,
                'passport_number' => 'required_without:nic_number|nullable|string|unique:employees,passport_number,' . $this->selectedEmployeeId,
                'phone_number'    => 'required|string',
                'job_position'    => 'required|string',
                'work_location'   => 'required|string',
                'shift_id'        => 'nullable|exists:shifts,id',
                'is_ot_eligible'  => 'boolean',
                'status'          => 'nullable|in:Active,Inactive,Suspended',
                'email'           => 'required|email|unique:users,email,' . $userIdToIgnore,
                'password'        => 'nullable|string|min:6',
                'employee_category_id' => 'required|exists:employee_categories,id',
            ], [
                'nic_number.required_without'      => 'Either NIC or Passport number must be provided.',
                'passport_number.required_without' => 'Either NIC or Passport number must be provided.',
                'nic_number.unique'                => 'This NIC number has already been taken.',
                'passport_number.unique'           => 'This Passport number has already been taken.',
            ]);
        } elseif ($this->currentStep == 2) {

            $this->validate([
                'address_line_1'                    => 'required|string',
                'city'                              => 'required|string',
                'emergency_contacts'                => 'required|array|min:1',
                'emergency_contacts.*.name'         => 'required|string',
                'emergency_contacts.*.relationship' => 'required|string',
                'emergency_contacts.*.phone'        => 'required|string',
            ], [
                'emergency_contacts.*.name.required'         => 'The contact name is required.',
                'emergency_contacts.*.relationship.required' => 'The relationship is required.',
                'emergency_contacts.*.phone.required'        => 'The phone number is required.',
            ]);
        } elseif ($this->currentStep == 3) {

            if ($this->marital_status === 'Married') {
                $this->validate([
                    'spouse_name'     => 'required|string|min:3|max:255',
                    'spouse_dob'      => 'required|date',
                    'spouse_nic'      => 'required_without:spouse_passport|nullable|string',
                    'spouse_passport' => 'required_without:spouse_nic|nullable|string',
                ], [
                    'spouse_name.required'             => 'The spouse name is required.',
                    'spouse_name.min'                  => 'The spouse name must be at least 3 characters.',
                    'spouse_dob.required'              => 'The spouse date of birth is required.',
                    'spouse_nic.required_without'      => 'Either spouse NIC or Passport number must be provided.',
                    'spouse_passport.required_without' => 'Either spouse NIC or Passport number must be provided.',
                ]);
            }

            if (!empty($this->children)) {
                $this->validate([
                    'children.*.name'          => 'required|string|min:3',
                    'children.*.date_of_birth' => 'nullable|date',
                    'children.*.gender'        => 'required|in:Male,Female',
                ]);
            }
        } elseif ($this->currentStep == 4) {

            $minSalary = 0;
            $maxSalary = null;

            if ($this->employee_category_id) {
                $category = EmployeeCategory::find($this->employee_category_id);
                if ($category) {
                    $minSalary = (float) $category->min_basic_salary;
                    if ($category->max_basic_salary !== null && (float) $category->max_basic_salary > 0) {
                        $maxSalary = (float) $category->max_basic_salary;
                    }
                }
            }

            $salaryRules = ['required', 'numeric', "min:{$minSalary}"];
            $salaryMessages = [
                'basic_salary.min' => 'Basic Salary must be at least LKR ' . number_format($minSalary, 2) . ' for this category.',
            ];

            if ($maxSalary !== null) {
                $salaryRules[] = "max:{$maxSalary}";
                $salaryMessages['basic_salary.max'] = 'Basic Salary cannot exceed LKR ' . number_format($maxSalary, 2) . ' for this category.';
            }

            $this->validate([
                'basic_salary'   => $salaryRules,
                'currency'       => 'required|string|max:10',
                'payment_method' => 'required|string',
                'bank_name'      => 'required|string',
                'branch_name'    => 'required|string',
                'account_number' => 'nullable|string',
            ], $salaryMessages);
        }
    }

    // ==========================================
    // --- BANK SEARCHABLE DROPDOWN HANDLERS ----
    // ==========================================

    public function selectBank($bankName, $bankId)
    {
        $this->bank_name = $bankName;
        $this->searchBank = $bankName;
        $this->isBankDropdownOpen = false;

        $this->branch_name = '';
        $this->searchBranch = '';
        $this->branches = BankBranch::where('bank_id', $bankId)->orderBy('branch_name', 'asc')->get()->toArray();
    }

    public function updatedSearchBank($value)
    {
        if (empty(trim($value))) {
            $this->bank_name = null;
            $this->searchBranch = '';
            $this->isBankDropdownOpen = true;
            $this->isBranchDropdownOpen = false;
        }
    }

    public function selectBranch($branchName)
    {
        $this->branch_name = $branchName;
        $this->searchBranch = $branchName;
        $this->isBranchDropdownOpen = false;
    }

    // ==========================================
    // --- CHILDREN MANAGEMENT ------------------
    // ==========================================

    public function addChild()
    {
        $this->children[] = [
            'name'           => '',
            'date_of_birth'  => '',
            'gender'         => 'Male',
            'marital_status' => 'Single'
        ];
    }

    public function removeChild($index)
    {
        unset($this->children[$index]);
        $this->children = array_values($this->children);
    }

    // ==========================================
    // --- EDIT MODE: DATA LOAD -----------------
    // ==========================================

    public function editEmployee($id)
    {
        $this->resetForm();
        $this->isEditMode = true;
        $this->selectedEmployeeId = $id;
        $this->resetValidation();

        $this->banks = Bank::orderBy('bank_name', 'asc')->get()->toArray();
        $employee = Employee::with(['user', 'emergencyContacts', 'relations', 'salaryAndBank', 'shift'])->findOrFail($id);
        $this->email = $employee->user ? trim($employee->user->email) : null;

        // --- Load Assigned Department & Shift Details ---
        $this->department_id = $employee->department_id;
        $this->selectedDepartmentName = $employee->department ? $employee->department->name : '';
        $this->shift_id = $employee->shift_id;
        $this->selectedShiftName = $employee->shift ? $employee->shift->name : '';
        $this->is_ot_eligible = (bool) $employee->is_ot_eligible;
        $this->employee_category_id = $employee->employee_category_id;
        $this->selectedCategoryName = $employee->category ? $employee->category->name : '';

        // --- Step 1: Personal Info ---
        $this->employee_id    = $employee->employee_id;
        $this->epf_number     = $employee->epf_number; 
        $this->first_name     = $employee->first_name;
        $this->last_name      = $employee->last_name;
        $this->date_of_birth  = $employee->date_of_birth ? \Carbon\Carbon::parse($employee->date_of_birth)->format('Y-m-d') : null;
        $this->gender         = $employee->gender;
        $this->nic_number     = $employee->nic_number;
        $this->passport_number = $employee->passport_number;
        $this->marital_status = $employee->marital_status;
        $this->phone_number   = $employee->phone_number;
        $this->job_position   = $employee->job_position;
        $this->work_location  = $employee->work_location;
        $this->date_of_join   = $employee->date_of_join ? \Carbon\Carbon::parse($employee->date_of_join)->format('Y-m-d') : null;
        $this->status         = $employee->status;
        $this->isCategoryOtEnabled = $employee->employeeCategory ? (bool) $employee->employeeCategory->is_ot_eligible : false;
        $this->is_ot_eligible = (bool) $employee->is_ot_eligible;
        $this->email          = $employee->user ? trim($employee->user->email) : null;
        $this->password       = '';
        


        if ($employee->user && $employee->user->roles->count() > 0) {
            $this->job_position = $employee->user->roles->first()->name;
        } else {
            $this->job_position = $employee->job_position;
        }

        // --- Step 2: Address ---
        $this->address_line_1 = $employee->address_line_1;
        $this->address_line_2 = $employee->address_line_2;
        $this->city           = $employee->city;
        $this->postal_code    = $employee->postal_code;

        $this->emergency_contacts = [];
        if ($employee->emergencyContacts && $employee->emergencyContacts->count() > 0) {
            foreach ($employee->emergencyContacts as $contact) {
                $this->emergency_contacts[] = [
                    'name'         => $contact->contact_name,
                    'relationship' => $contact->relationship,
                    'phone'        => $contact->phone_number,
                ];
            }
        } else {
            $this->emergency_contacts = [
                ['name' => '', 'relationship' => '', 'phone' => '']
            ];
        }

        // --- Step 3: Spouse / Parent / Guardian ---
        $familyRelation = $employee->getRelation('relations')
            ->whereIn('relation_type', ['Spouse', 'Parent', 'Guardian'])
            ->first();

        if ($familyRelation) {
            $this->spouse_name     = $familyRelation->name;
            $this->spouse_dob      = $familyRelation->date_of_birth ? \Carbon\Carbon::parse($familyRelation->date_of_birth)->format('Y-m-d') : null;
            $this->spouse_nic      = $familyRelation->nic_number;
            $this->spouse_passport = $familyRelation->passport_number;
        }

        $this->children = $employee->getRelation('relations')
            ->where('relation_type', 'Child')
            ->values()
            ->map(function ($child) {
                return [
                    'name'           => $child->name,
                    'date_of_birth'  => $child->date_of_birth ? \Carbon\Carbon::parse($child->date_of_birth)->format('Y-m-d') : null,
                    'gender'         => $child->gender,
                    'marital_status' => $child->marital_status ?? 'Single'
                ];
            })->toArray();

        // --- Step 4: Salary & Bank ---
        if ($employee->salaryAndBank) {
            $this->basic_salary        = $employee->salaryAndBank->basic_salary;
            $this->currency            = $employee->salaryAndBank->currency;
            $this->allowances          = $employee->salaryAndBank->allowances;
            $this->deductions          = $employee->salaryAndBank->deductions;
            $this->bonus               = $employee->salaryAndBank->bonus;
            $this->payment_method      = $employee->salaryAndBank->payment_method;
            $this->bank_name           = $employee->salaryAndBank->bank_name;
            $this->searchBank          = $employee->salaryAndBank->bank_name;
            $this->branch_name         = $employee->salaryAndBank->branch_name;
            $this->searchBranch        = $employee->salaryAndBank->branch_name;
            $this->account_holder_name = $employee->salaryAndBank->account_holder_name;
            $this->account_number      = $employee->salaryAndBank->account_number;
            $this->account_type        = $employee->salaryAndBank->account_type;

            $selectedBankModel = Bank::where('bank_name', $this->bank_name)->first();
            if ($selectedBankModel) {
                $this->branches = BankBranch::where('bank_id', $selectedBankModel->id)->orderBy('branch_name', 'asc')->get()->toArray();
            }
        }

        $this->currentStep = 1;
        $this->isModalOpen = true;

        $this->dispatch('employee-loaded');
    }

    // ==================================
    // --- SAVE EMPLOYEE CREATE / UPDATE-
    // ==================================

    public function saveEmployee()
    {
        $this->validateCurrentStep();

        if ($this->currentStep < $this->totalSteps) {
            $this->currentStep++;
            return;
        }

        $savedSpouseName     = $this->spouse_name ?: session('wizard_spouse_name');
        $savedSpouseDob      = $this->spouse_dob ?: session('wizard_spouse_dob');
        $savedSpouseNic      = $this->spouse_nic ?: session('wizard_spouse_nic');
        $savedSpousePassport = $this->spouse_passport ?: session('wizard_spouse_passport');
        $savedChildren       = !empty($this->children) ? $this->children : session('wizard_children', []);
        

        $currentOrgId = Auth::user()->organisation_id ?? null;

        DB::transaction(function () use ($savedSpouseName, $savedSpouseDob, $savedSpouseNic, $savedSpousePassport, $savedChildren, $currentOrgId) {

            $userId = null;

            // --- 1. User Account: Create or Update ---
            if ($this->isEditMode) {
                $employeeRecord = Employee::findOrFail($this->selectedEmployeeId);
                $userId = $employeeRecord->user_id;

                if ($userId) {
                    $user = User::find($userId);
                    if ($user) {
                        $updateData = [
                            'name'  => $this->first_name . ' ' . $this->last_name,
                            'email' => $this->email,
                        ];

                        if (!empty($this->password)) {
                            $updateData['password'] = Hash::make($this->password);
                        }

                        $user->update($updateData);

                        if (!empty($this->job_position)) {
                            $user->syncRoles([$this->job_position]);
                        }
                    }
                }
            } else {
                $finalPassword = !empty($this->password) ? $this->password : 'password123';

                $user = User::create([
                    'name'            => $this->first_name . ' ' . $this->last_name,
                    'email'           => $this->email,
                    'password'        => Hash::make($finalPassword),
                    'organisation_id' => $currentOrgId,
                ]);

                $userId = $user->id;

                if (!empty($this->job_position)) {
                    $user->syncRoles([$this->job_position]);
                }
            }

            // --- 2. Employee Core Profile: Create or Update ---
            $employee = Employee::updateOrCreate(
                ['id' => $this->selectedEmployeeId],
                [
                    'organization_id' => $currentOrgId,
                    'user_id'         => $userId,
                    'employee_id'     => $this->employee_id,
                    'epf_number'      => $this->epf_number,
                    'first_name'      => $this->first_name,
                    'last_name'       => $this->last_name,
                    'date_of_birth'   => $this->date_of_birth,
                    'gender'          => $this->gender,
                    'nic_number'      => $this->nic_number,
                    'passport_number' => $this->passport_number,
                    'department_id'   => $this->department_id,
                    'shift_id'        => $this->shift_id,
                    'employee_category_id' => $this->employee_category_id,
                    'is_ot_eligible'  => $this->is_ot_eligible,
                    'marital_status'  => $this->marital_status,
                    'phone_number'    => $this->phone_number,
                    'job_position'    => $this->job_position,
                    'work_location'   => $this->work_location,
                    'date_of_join'    => $this->date_of_join,
                    'status'          => $this->status ?: 'Active',
                    'address_line_1'  => $this->address_line_1,
                    'address_line_2'  => $this->address_line_2,
                    'city'            => $this->city,
                    'postal_code'     => $this->postal_code,
                ]
            );

            // --- 3. Emergency Contacts: Delete & Re-insert ---
            $employee->emergencyContacts()->delete();
            foreach ($this->emergency_contacts as $contact) {
                if (!empty($contact['name']) && !empty($contact['phone'])) {
                    $employee->emergencyContacts()->create([
                        'contact_name' => $contact['name'],
                        'relationship' => $contact['relationship'],
                        'phone_number' => $contact['phone'],
                    ]);
                }
            }

            // --- 4. Family Relations: Delete & Re-insert ---
            $employee->relations()->delete();

            if (!empty(trim($savedSpouseName))) {
                $employee->relations()->create([
                    'relation_type'   => 'Spouse',
                    'name'            => trim($savedSpouseName),
                    'date_of_birth'   => $savedSpouseDob ?: null,
                    'nic_number'      => $savedSpouseNic ?: null,
                    'passport_number' => $savedSpousePassport ?: null,
                    'marital_status'  => 'Married'
                ]);
            }

            if (!empty($savedChildren)) {
                foreach ($savedChildren as $child) {
                    if (!empty($child['name']) && !empty(trim($child['name']))) {
                        $employee->relations()->create([
                            'relation_type'  => 'Child',
                            'name'           => trim($child['name']),
                            'date_of_birth'  => !empty($child['date_of_birth']) ? \Carbon\Carbon::parse($child['date_of_birth'])->format('Y-m-d') : null,
                            'gender'         => $child['gender'] ?: 'Male',
                            'marital_status' => $child['marital_status'] ?: 'Single',
                        ]);
                    }
                }
            }

            // --- 5. Salary & Bank: Update or Create ---
            $employee->salaryAndBank()->updateOrCreate(
                ['employee_id' => $employee->id],
                [
                    'basic_salary'        => $this->basic_salary,
                    'currency'            => $this->currency,
                    'allowances'          => $this->allowances ?: 0,
                    'deductions'          => $this->deductions ?: 0,
                    'bonus'               => $this->bonus ?: 0,
                    'payment_method'      => $this->payment_method,
                    'bank_name'           => $this->bank_name,
                    'branch_name'         => $this->branch_name,
                    'account_holder_name' => $this->account_holder_name,
                    'account_number'      => $this->account_number,
                    'account_type'        => $this->account_type,
                ]
            );
        });

        session()->forget(['wizard_spouse_name', 'wizard_spouse_dob', 'wizard_spouse_nic', 'wizard_spouse_passport', 'wizard_children']);
        session()->flash('message', $this->isEditMode ? 'Employee updated successfully!' : 'Employee registered successfully!');

        $this->closeModal();
        $this->resetForm();
    }

    // ==========================================
    // --- FORM STATE RESET ---------------------
    // ==========================================

    private function resetForm()
    {
        $this->reset([
            'selectedEmployeeId',
            'employee_id',
            'first_name',
            'last_name',
            'date_of_birth',
            'gender',
            'nic_number',
            'passport_number',
            'marital_status',
            'phone_number',
            'job_position',
            'work_location',
            'date_of_join',
            'address_line_1',
            'address_line_2',
            'city',
            'postal_code',
            'spouse_name',
            'spouse_dob',
            'spouse_nic',
            'spouse_passport',
            'basic_salary',
            'bank_name',
            'branch_name',
            'account_holder_name',
            'account_number',
            'searchBank',
            'searchBranch',
            'isBankDropdownOpen',
            'isBranchDropdownOpen',
            'banks',
            'branches',
            'email',
            'password',
            'department_id',
            'selectedDepartmentName',
            'isDepartmentDropdownOpen',
            'shift_id',
            'selectedShiftName',
            'isShiftDropdownOpen',
            'is_ot_eligible',
            'employee_category_id',
            'selectedCategoryName',
            'isCategoryDropdownOpen',
        ]);

        $this->isDropdownOpen = false;
        $this->children = [];
        $this->emergency_contacts = [
            ['name' => '', 'relationship' => '', 'phone' => '']
        ];

        $this->status         = 'Active';
        $this->isCategoryOtEnabled = false;
        $this->system_access  = true;
        $this->epf_number = null;
        $this->currency       = 'LKR';
        $this->payment_method = 'Bank Transfer';
        $this->account_type   = 'Savings';
        $this->allowances     = 0;
        $this->deductions     = 0;
        $this->bonus          = 0;
        $this->is_ot_eligible = false;
    }

    // ==========================================
    // --- WIZARD STEP CONFIGURATION ------------
    // ==========================================

    private function getStepsConfiguration()
    {
        return [
            1 => ['label' => 'Personal', 'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
            2 => ['label' => 'Address',  'icon' => 'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z M15 11a3 3 0 11-6 0 3 3 0 016 0z'],
            3 => ['label' => 'Family',   'icon' => 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z'],
            4 => ['label' => 'Salary',   'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
        ];
    }

    // ==========================================
    // --- HELPER METHODS FOR RENDER ------------
    // ==========================================

    private function getDesignations()
    {
        return Role::where('name', '!=', 'Super Admin')
            ->orderBy('name', 'asc')
            ->get();
    }

    private function getWorkLocations()
    {
        return WorkLocation::orderBy('name', 'asc')->get();
    }

    private function getFilteredEmployees()
    {
        $query = Employee::query();

        if (!empty($this->search)) {
            $query->where(function ($searchQuery) {
                $searchQuery->where('first_name',    'like', '%' . $this->search . '%')
                    ->orWhere('last_name',   'like', '%' . $this->search . '%')
                    ->orWhere('employee_id', 'like', '%' . $this->search . '%')
                    ->orWhere('nic_number',  'like', '%' . $this->search . '%')
                    ->orWhere('phone_number', 'like', '%' . $this->search . '%');
            });
        }

        if (!empty($this->filterPosition)) {
            $query->where('job_position', $this->filterPosition);
        }

        if (!empty($this->filterStatus)) {
            $query->where('status', $this->filterStatus);
        }

        return $query->with(['user', 'department', 'shift'])->latest()->paginate(10);
    }

    private function getFilteredBanksList()
    {
        return collect($this->banks)->filter(function ($bank) {
            return empty($this->searchBank) || Str::contains(strtolower($bank['bank_name']), strtolower($this->searchBank));
        })->take(20);
    }

    private function getFilteredBranchesList()
    {
        return collect($this->branches)->filter(function ($branch) {
            return empty($this->searchBranch) || Str::contains(strtolower($branch['branch_name']), strtolower($this->searchBranch));
        })->take(20);
    }

    private function getAvailableDepartmentsList()
    {
        return Department::orderBy('name', 'asc')->get();
    }

    // ==========================================
    // --- RENDER ------------------------------
    // ==========================================

    public function render()
    {
        return view('livewire.employee.employee-management', [
            'employees'        => $this->getFilteredEmployees(),
            'designations'     => $this->getDesignations(),
            'workLocations'    => $this->getWorkLocations(),
            'filteredBanks'    => $this->getFilteredBanksList(),
            'filteredBranches' => $this->getFilteredBranchesList(),
            'departments'      => $this->getAvailableDepartmentsList(),
            'shifts'           => Shift::where('is_active', true)->get(),
            'steps'            => $this->getStepsConfiguration(),
            'categories' => EmployeeCategory::where('is_active', true)->orderBy('name', 'asc')->get(),
        ]);
    }
}
