<?php

namespace App\Livewire\Employee;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Carbon\Carbon;
use App\Models\Designation;
use App\Models\Bank;
use App\Models\BankBranch;
use Illuminate\Support\Str;

#[Layout('layouts.app')]
class EmployeeManagement extends Component
{
    use WithPagination;

    // ==========================================
    // --- UI STATE MANAGEMENT & CONTROL MODALS ---
    // ==========================================
    public $isModalOpen = false;         // Controls employee creation/edit modal visibility
    public $isEditMode = false;          // Switch between Form Store (false) and Update (true)
    public $currentStep = 1;             // Tracks the active step in the multi-step form
    public $totalSteps = 4;              // Max steps available in the wizard
    public $selectedEmployeeId;          // Holds ID during edit/update operation
    public $isDropdownOpen = false;      // Generic state toggle for dropdown custom menus

    // ==========================================
    // --- GLOBAL SEARCH & FILTER PROPERTIES ----
    // ==========================================
    public $search = '';                 // Query string for real-time employee search
    public $filterPosition = '';         // Filters list view by job position/designation
    public $filterStatus = '';           // Filters list view by employee status (Active/Inactive)

    // ==========================================
    // --- STEP 1: PERSONAL INFO & ACCESS -------
    // ==========================================
    public $employee_id, $first_name, $last_name, $date_of_birth, $gender;
    public $nic_number, $passport_number, $marital_status, $phone_number;
    public $job_position, $country, $date_of_join;
    public $status = 'Active';           // Default corporate status
    public $system_access = true;        // Controls Employee Portal/Login Generation authorization
    public $email;                       // Corporate email tied directly to system application user access

    // ==========================================
    // --- STEP 2: ADDRESS & EMERGENCY ----------
    // ==========================================
    // NOTE: Structural residential address parameters are now integrated directly into the core employees table
    public $address_line_1, $address_line_2, $city, $postal_code; // Residential info
    public $contact_name, $relationship, $emergency_phone;        // Immediate emergency contact

    // ==========================================
    // --- STEP 3: FAMILY DETAILS ---------------
    // ==========================================
    public $spouse_name, $spouse_dob, $spouse_nic, $spouse_passport; // Dependant Spouse info
    public $children = [];                                           // Dynamic array of child records

    // ==========================================
    // --- STEP 4: SALARY & BANK DETAILS --------
    // ==========================================
    public $basic_salary, $allowances = 0, $deductions = 0, $bonus = 0; // Remuneration properties
    public $currency = 'LKR';                                           // Default currency denomination
    public $payment_method = 'Bank Transfer';                           // Default payroll channel
    public $bank_name, $branch_name, $account_holder_name, $account_number;
    public $account_type = 'Savings';                                   // Default bank account tier

    // ==========================================
    // --- SEARCHABLE BANK DROPDOWN COMPONENT ----
    // ==========================================
    public $searchBank = '';             // Tracks typed input inside custom bank selection
    public $searchBranch = '';           // Tracks typed input inside branch selection
    public $isBankDropdownOpen = false;  // Toggles bank result list UI panel
    public $isBranchDropdownOpen = false; // Toggles branch result list UI panel
    public $banks = [];                  // Holds listed active bank objects
    public $branches = [];               // Dynamic array containing branches matched to chosen bank

    // ==========================================
    // --- REAL-TIME LIVEWIRE SEARCH RESETS -----
    // ==========================================
    public function updatingSearch()
    {
        $this->resetPage(); // Reverts pagination cursor back to page 1 on active typing
    }
    public function updatingFilterPosition()
    {
        $this->resetPage(); // Reverts pagination cursor back to page 1 on position filter change
    }
    public function updatingFilterStatus()
    {
        $this->resetPage(); // Reverts pagination cursor back to page 1 on status filter change
    }

    // ==========================================
    // --- WIZARD / MODAL PANEL TRIGGERS -------
    // ==========================================
    /**
     * Initializes state and displays fresh creation modal layout
     */
    public function openModal()
    {
        $this->resetForm();

        // 🌟 [මෙන්න මෙතනට මේක එකතු කරන්න] 
        // පරණ රතු පාට වැදුණු වැරදි (Validation Errors) ඔක්කොම ක්ලියර් වෙලා යනවා
        $this->resetValidation();

        $this->isEditMode = false;
        $this->isModalOpen = true;
        $this->currentStep = 1;

        // Fetches operational banks alphabetically to bind dropdown view cache
        $this->banks = Bank::orderBy('bank_name', 'asc')->get()->toArray();
    }

    /**
     * Hides the active registration modal
     */
    public function closeModal()
    {
        $this->isModalOpen = false;
    }

    // ==========================================
    // --- STEP NAVIGATION STEERING CONTROL -----
    // ==========================================
    /**
     * Validates current step metrics and advances workflow forward
     */
    public function nextStep()
    {
        $this->validateCurrentStep();
        if ($this->currentStep < $this->totalSteps) {
            $this->currentStep++;
        }
    }

    /**
     * Reverts form view wizard back one step level
     */
    public function previousStep()
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }

    /**
     * Direct Navigation Jump Steering (via clicking top stepper numbers directly)
     */
    public function setStep($step)
    {
        // Enforces forward movement checks before jumping forward prematurely
        if ($step > $this->currentStep) {
            $this->validateCurrentStep();
        }
        $this->currentStep = $step;
    }

    // ==========================================
    // --- COMPARTMENTALIZED FORM VALIDATION ----
    // ==========================================
    /**
     * Executes localized parameter validation dependent on currently viewable step metrics
     */
    private function validateCurrentStep()
    {
        if ($this->currentStep == 1) {
            // Finding runtime user identifier to ignore uniquely mapped constraints during edit updates
            $userIdToIgnore = 'NULL';
            if ($this->selectedEmployeeId) {
                $emp = Employee::find($this->selectedEmployeeId);
                $userIdToIgnore = $emp ? $emp->user_id : 'NULL';
            }

            $this->validate([
                'employee_id'    => 'required|unique:employees,employee_id,' . $this->selectedEmployeeId,
                'first_name'     => 'required|string|max:255',
                'last_name'      => 'required|string|max:255',
                'date_of_birth'  => 'required|date',
                'gender'         => 'required|in:Male,Female,Other',
                'marital_status' => 'required|in:Single,Married,Divorced',
                'phone_number'   => 'required|string',
                'job_position'   => 'required|string',
                'country'        => 'required|string',
                'email'          => 'nullable|email|unique:users,email,' . $userIdToIgnore, // Enforces application email uniqueness
            ]);
        } elseif ($this->currentStep == 2) {
            $this->validate([
                'address_line_1'  => 'required|string',
                'city'            => 'required|string',
                'contact_name'    => 'required|string',
                'relationship'    => 'required|string',
                'emergency_phone' => 'required|string',
            ]);
        } elseif ($this->currentStep == 3) {
            $this->validate([
                'children.*.name'          => 'nullable|string',
                'children.*.date_of_birth' => 'nullable|date',
            ]);
        } elseif ($this->currentStep == 4) {
            $this->validate([
                'basic_salary'   => 'required|numeric',
                'currency'       => 'required|string|max:10',
                'payment_method' => 'required|string',
                'bank_name'      => 'required|string',
                'branch_name'    => 'required|string',
                'account_number' => 'nullable|string',
            ]);
        }
    }

    // ==========================================
    // --- DROPDOWN INTERACTION HANDLERS --------
    // ==========================================
    /**
     * Sets chosen banking brand parameters and fetches linked branch networks
     */
    public function selectBank($bankName, $bankId)
    {
        $this->bank_name = $bankName;
        $this->searchBank = $bankName;
        $this->isBankDropdownOpen = false;

        // Flushing previous branch definitions to avoid inaccurate cross-wiring maps
        $this->branch_name = '';
        $this->searchBranch = '';

        // Querying dynamic mapping branch criteria according to incoming primary bank index
        $this->branches = BankBranch::where('bank_id', $bankId)->orderBy('branch_name', 'asc')->get()->toArray();
    }

    /**
     * Binds selected branch parameters to active model tracking instance state
     */
    public function selectBranch($branchName)
    {
        $this->branch_name = $branchName;
        $this->searchBranch = $branchName;
        $this->isBranchDropdownOpen = false;
    }

    // ==========================================
    // --- DYNAMIC REPEATER FAMILY SUB-FORMS ----
    // ==========================================
    /**
     * Appends a new empty collection item onto localized children reference matrix
     */
    public function addChild()
    {
        $this->children[] = [
            'name'           => '',
            'date_of_birth'  => '',
            'gender'         => 'Male',
            'marital_status' => 'Single'
        ];
    }

    /**
     * Drops child entry item record reference index and cleans sorting layout values
     */
    public function removeChild($index)
    {
        unset($this->children[$index]);
        $this->children = array_values($this->children); // Re-indexes array to keep sequential layout indices intact
    }

    // ==========================================
    // --- UPDATE SYSTEM DATA RETRIEVAL WORK ----
    // ==========================================
    /**
     * Loads existing entity model trees along with structural relational profiles into component variables for editing
     */



    public function editEmployee($id)
    {
        $this->resetForm();
        $this->isEditMode = true;
        $this->selectedEmployeeId = $id;

        // Build bank selection dataset options
        $this->banks = Bank::orderBy('bank_name', 'asc')->get()->toArray();

        // Hydrates core context alongside related nested records
        $employee = Employee::with(['user', 'emergencyContact', 'employeeRelations', 'salaryAndBank'])->findOrFail($id);

        // --- Populate Step 1 Personal Metadata ---
        $this->employee_id = $employee->employee_id;
        $this->first_name = $employee->first_name;
        $this->last_name = $employee->last_name;
        $this->date_of_birth = $employee->date_of_birth ? \Carbon\Carbon::parse($employee->date_of_birth)->format('Y-m-d') : null;
        $this->gender = $employee->gender;
        $this->nic_number = $employee->nic_number;
        $this->passport_number = $employee->passport_number;
        $this->marital_status = $employee->marital_status;
        $this->phone_number = $employee->phone_number;
        $this->job_position = $employee->job_position;
        $this->country = $employee->country;
        $this->date_of_join = $employee->date_of_join ? \Carbon\Carbon::parse($employee->date_of_join)->format('Y-m-d') : null;
        $this->status = $employee->status;
        $this->system_access = ($employee->status === 'Active') ? true : false;

        // 🌟 [වෙනස්කම]: Email එක අරන්, ඒක string එකක් විදිහට පිරිසිදු කරලා public $email එකට දානවා
        $this->email = $employee->user ? trim($employee->user->email) : null;

        // --- Populate Step 2 Structural Residential Address info ---
        $this->address_line_1 = $employee->address_line_1;
        $this->address_line_2 = $employee->address_line_2;
        $this->city           = $employee->city;
        $this->postal_code    = $employee->postal_code;

        if ($employee->emergencyContact) {
            $this->contact_name = $employee->emergencyContact->contact_name;
            $this->relationship = $employee->emergencyContact->relationship;
            $this->emergency_phone = $employee->emergencyContact->phone_number;
        }

        // --- Populate Step 3 Dependant & Marital Group Profiles ---
        $familyRelation = $employee->employeeRelations
            ->whereIn('relation_type', ['Spouse', 'Parent', 'Guardian'])
            ->first();

        if ($familyRelation) {
            $this->spouse_name = $familyRelation->name;
            $this->spouse_dob = $familyRelation->date_of_birth ? \Carbon\Carbon::parse($familyRelation->date_of_birth)->format('Y-m-d') : null;
            $this->spouse_nic = $familyRelation->nic_number;
            $this->spouse_passport = $familyRelation->passport_number;
        }

        // Map and format children schema maps into localized state arrays
        $this->children = $employee->employeeRelations->where('relation_type', 'Child')->map(function ($child) {
            return [
                'name'           => $child->name,
                'date_of_birth'  => $child->date_of_birth ? \Carbon\Carbon::parse($child->date_of_birth)->format('Y-m-d') : null,
                'gender'         => $child->gender,
                'marital_status' => $child->marital_status ?? 'Single'
            ];
        })->toArray();

        // --- Populate Step 4: Remuneration & Financial Records ---
        if ($employee->salaryAndBank) {
            $this->basic_salary = $employee->salaryAndBank->basic_salary;
            $this->currency = $employee->salaryAndBank->currency;
            $this->allowances = $employee->salaryAndBank->allowances;
            $this->deductions = $employee->salaryAndBank->deductions;
            $this->bonus = $employee->salaryAndBank->bonus;
            $this->payment_method = $employee->salaryAndBank->payment_method;

            $this->bank_name = $employee->salaryAndBank->bank_name;
            $this->searchBank = $employee->salaryAndBank->bank_name;
            $this->branch_name = $employee->salaryAndBank->branch_name;
            $this->searchBranch = $employee->salaryAndBank->branch_name;

            $this->account_holder_name = $employee->salaryAndBank->account_holder_name;
            $this->account_number = $employee->salaryAndBank->account_number;
            $this->account_type = $employee->salaryAndBank->account_type;

            $selectedBankModel = Bank::where('bank_name', $this->bank_name)->first();
            if ($selectedBankModel) {
                $this->branches = BankBranch::where('bank_id', $selectedBankModel->id)->orderBy('branch_name', 'asc')->get()->toArray();
            }
        }

        // Initialize wizard cursor location and activate view panel
        $this->currentStep = 1;
        $this->isModalOpen = true;

        // 🌟 [වෙනස්කම]: HTML එකට සඥාවක් දෙනවා ඩේටා refresh වුණා කියලා
        $this->dispatch('employee-loaded');
    }

    // ==========================================
    // --- DATABASE TRANSACTION OPERATIONS ------
    // ==========================================
    /**
     * Validates compiled input parameters and handles records persistence using atomic operations
     */
    public function saveEmployee()
    {
        // Global form state validation block checking structural rules before executing query definitions
        $this->validate([
            'employee_id'               => 'required|unique:employees,employee_id,' . $this->selectedEmployeeId,
            'first_name'                => 'required|string|max:255',
            'last_name'                 => 'required|string|max:255',
            'date_of_birth'             => 'required|date',
            'gender'                    => 'required',
            'marital_status'            => 'required',
            'phone_number'              => 'required',
            'job_position'              => 'required',
            'country'                   => 'required',
            'address_line_1'            => 'required',
            'city'                      => 'required',
            'contact_name'              => 'required',
            'relationship'              => 'required',
            'emergency_phone'           => 'required',
            'basic_salary'              => 'required|numeric',
            'bank_name'                 => 'required|string',
            'branch_name'               => 'required|string',
            'children.*.name'           => 'nullable|string',
            'children.*.date_of_birth'  => 'nullable|date',
            'email'                     => 'email|unique:users,email,' . ($this->selectedEmployeeId ? Employee::find($this->selectedEmployeeId)->user_id : 'NULL'),
        ]);

        // Executing safe transactions block to preserve data integrity across normalized tables
        DB::transaction(function () {
            // Calculating standard status metrics based on application interface properties
            $finalStatus = $this->system_access ? 'Active' : ($this->status === 'Active' ? 'Inactive' : $this->status);

            $userId = null;

            if ($this->isEditMode) {
                // 🔄 UPDATE MODE: Sync metadata adjustments back onto the authenticated User structural definition profile
                $employeeRecord = Employee::findOrFail($this->selectedEmployeeId);
                $userId = $employeeRecord->user_id;

                if ($userId) {
                    $user = User::find($userId);
                    if ($user) {
                        $user->update([
                            'name'  => $this->first_name . ' ' . $this->last_name,
                            'email' => $this->email,
                        ]);
                    }
                }
            } else {
                // ✨ CREATE MODE: Automate fresh account authentication provisioning using a standard fallback default credential pattern
                $defaultPassword = 'password123'; // 👈 Standard default password for application testing configurations

                $user = User::create([
                    'name'     => $this->first_name . ' ' . $this->last_name,
                    'email'    => $this->email,
                    'password' => Hash::make($defaultPassword), // Safe encryption hashing pattern
                ]);

                $userId = $user->id;
                // Note: Mail server dispatch protocols are explicitly dropped here to speed up sandbox evaluations
            }

            // 1. Create or Update Core Employee Profile (Residential address fields are saved directly inside this record)
            $employee = Employee::updateOrCreate(
                ['id' => $this->selectedEmployeeId],
                [
                    'user_id'         => $userId, // Maps relation link to global platform accounts index
                    'employee_id'     => $this->employee_id,
                    'first_name'      => $this->first_name,
                    'last_name'       => $this->last_name,
                    'date_of_birth'   => $this->date_of_birth,
                    'gender'          => $this->gender,
                    'nic_number'      => $this->nic_number,
                    'passport_number' => $this->passport_number,
                    'marital_status'  => $this->marital_status,
                    'phone_number'    => $this->phone_number,
                    'job_position'    => $this->job_position,
                    'country'         => $this->country,
                    'date_of_join'    => $this->date_of_join,
                    'status'          => $finalStatus,

                    // Unified Address storage data matrix values
                    'address_line_1'  => $this->address_line_1,
                    'address_line_2'  => $this->address_line_2,
                    'city'            => $this->city,
                    'postal_code'     => $this->postal_code,
                ]
            );

            // 2. Persist Emergency Support Contact Records
            $employee->emergencyContact()->updateOrCreate(
                ['employee_id' => $employee->id],
                [
                    'contact_name' => $this->contact_name,
                    'relationship' => $this->relationship,
                    'phone_number' => $this->emergency_phone,
                ]
            );

            // 3. Re-syncing Relational Matrix Groups (Flushing old snapshots to avoid dirty keys tracking)
            $employee->employeeRelations()->delete();

            // Inserting Spouse context datasets under valid active parameters
            if ($this->marital_status === 'Married' && !empty($this->spouse_name)) {
                $employee->employeeRelations()->create([
                    'relation_type'   => 'Spouse',
                    'name'            => $this->spouse_name,
                    'date_of_birth'   => $this->spouse_dob ?: null,
                    'nic_number'      => $this->spouse_nic,
                    'passport_number' => $this->spouse_passport,
                ]);
            }

            // Processing dynamic children collections via loops
            foreach ($this->children as $child) {
                if (!empty($child['name'])) {
                    $employee->employeeRelations()->create([
                        'relation_type'  => 'Child',
                        'name'           => $child['name'],
                        'date_of_birth'  => $child['date_of_birth'] ?: null,
                        'gender'         => $child['gender'] ?: null,
                        'marital_status' => $child['marital_status'] ?: 'Single',
                    ]);
                }
            }

            // 4. Build Compensation Mapping Structure Layout Models
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

        // Broadcast storage execution alerts onto front-end layer instance
        session()->flash('message', $this->isEditMode ? 'Employee updated successfully!' : 'Employee registered successfully!');

        // Clean up session parameters and close working display frames
        $this->closeModal();
        $this->resetForm();
    }

    // ==========================================
    // --- STATE CLEANING & BUFFER FLUSH --------
    // ==========================================
    /**
     * Purges component cache allocations back to original blank states
     */
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
            'country',
            'date_of_join',
            'address_line_1',
            'address_line_2',
            'city',
            'postal_code',
            'contact_name',
            'relationship',
            'emergency_phone',
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
            'email'
        ]);

        // Enforce fallback default system values
        $this->isDropdownOpen = false;
        $this->children = [];
        $this->status = 'Active';
        $this->system_access = true;
        $this->currency = 'LKR';
        $this->payment_method = 'Bank Transfer';
        $this->account_type = 'Savings';
        $this->allowances = 0;
        $this->deductions = 0;
        $this->bonus = 0;
    }

    // ==========================================
    // --- STEPPER GRAPHICS DEFINITIONS ---------
    // ==========================================
    /**
     * Houses structural labels along with SVG Vector paths for UI execution tracking
     */
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
    // --- COMPONENT RENDER PIPELINE ------------
    // ==========================================
    /**
     * Orchestrates master query evaluations and returns live datasets into frontend templates
     */
    public function render()
    {
        // Extract designatory labels for assignment options list configurations
        $designations = Designation::orderBy('name', 'asc')->get();

        // Building base Eloquent query tree for structural updates matching
        $query = Employee::query();

        // Injecting query constraint scopes under non-empty runtime conditions
        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('first_name', 'like', '%' . $this->search . '%')
                    ->orWhere('last_name', 'like', '%' . $this->search . '%')
                    ->orWhere('employee_id', 'like', '%' . $this->search . '%')
                    ->orWhere('nic_number', 'like', '%' . $this->search . '%')
                    ->orWhere('phone_number', 'like', '%' . $this->search . '%');
            });
        }

        // Apply dropdown position filters if specified
        if (!empty($this->filterPosition)) {
            $query->where('job_position', $this->filterPosition);
        }

        // Apply active/inactive employee status filters if specified
        if (!empty($this->filterStatus)) {
            $query->where('status', $this->filterStatus);
        }

        // Processing client-side dynamic search parameters inside the current cached banks array
        $filteredBanks = collect($this->banks)->filter(function ($bank) {
            return empty($this->searchBank) || Str::contains(strtolower($bank['bank_name']), strtolower($this->searchBank));
        })->take(20);

        // Processing client-side dynamic search parameters inside the current cached branches array
        $filteredBranches = collect($this->branches)->filter(function ($branch) {
            return empty($this->searchBranch) || Str::contains(strtolower($branch['branch_name']), strtolower($this->searchBranch));
        })->take(20);

        // Binding variables map objects and firing component rendering pipeline lifecycle tasks
        return view('livewire.employee.employee-management', [
            // 🌟 මෙතනට ->with(['user']) එකතු කළා, එතකොට Email එක කලින්ම ඇදලා ගන්නවා.
            'employees'        => $query->with(['user'])->latest()->paginate(10),
            'designations'     => $designations,
            'filteredBanks'    => $filteredBanks,
            'filteredBranches' => $filteredBranches,
            'steps'            => $this->getStepsConfiguration(),
        ]);
    }
}
