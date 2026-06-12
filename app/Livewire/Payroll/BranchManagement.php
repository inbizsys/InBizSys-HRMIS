<?php

namespace App\Livewire\Payroll;

use Livewire\Component;
use App\Models\Bank;
use App\Models\BankBranch;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\DB;

#[Layout('layouts.app')]
class BranchManagement extends Component
{
    // Enable Livewire's standard pagination features for the component
    use WithPagination;

    // Global Search Property: Binds to the main search box for filtering branches in the data table
    public $search = '';

    // Modal States: Manage visibility of create/edit modals and track deletion IDs
    public bool $showModal = false;       // Controls whether the main form modal is visible
    public $editingId = null;             // Stores the ID of the branch being updated (null means creating a new branch)
    public $confirmDeleteId = null;       // Stores the ID of the branch flagged for deletion

    // Form Inputs: Properties directly bound to form entry input fields via wire:model
    public $bank_id = '';
    public $branch_code = '';
    public $branch_name = '';
    public $is_active = 1;

    // Inline Searchable Dropdown Properties: Managed states for searching banks dynamically inside the modal form
    public $bank_search = '';         
    public bool $showDropdown = false; 

    // Restriction Check State: Keeps track of whether the current branch is tied to existing employee profiles
    public bool $isBranchUsed = false;

    // Validation Rules: Server-side validation criteria executed before inserting/updating records
    protected $rules = [
        'bank_id' => 'required|exists:banks,id',
        'branch_code' => 'required|string|max:10',
        'branch_name' => 'required|string|max:255',
        'is_active' => 'required|boolean',
    ];

    // Lifecycle Hook: Automatically triggers and resets the pagination back to page 1 whenever the global search value changes
    public function updatingSearch()
    {
        $this->resetPage();
    }

    // Opens the creation form modal after resetting all form fields to their defaults
    public function openModal()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    // Assigns selected bank information from the custom searchable dropdown back to the form properties
    public function selectBank($id, $name)
    {
        $this->bank_id = $id;
        $this->bank_search = $name; 
        $this->showDropdown = false; 
    }

    // Handles the UI closing action of the searchable bank dropdown, maintaining consistency or resetting search values
    public function closeDropdown()
    {
        $this->showDropdown = false;

        // If a valid bank has been selected previously, restore its name into the search display field
        if ($this->bank_id) {
            $bank = Bank::find($this->bank_id);
            if ($bank) {
                $this->bank_search = $bank->bank_name;
            }
        } else {
            // If no bank was selected, clear out the search field entirely
            $this->bank_search = '';
        }
    }

    // Initiates editing mode, fetches existing bank-branch records along with relationship models, and fills the form
    public function edit(int $id)
    {
        $record = BankBranch::with('bank')->findOrFail($id);
        $this->editingId = $id;
        $this->bank_id = $record->bank_id;
        
        // Displays the parent bank's name inside the searchable input using optional chaining safely
        $this->bank_search = $record->bank?->bank_name ?? ''; 
        
        $this->branch_code = $record->branch_code;
        $this->branch_name = $record->branch_name;
        $this->is_active = $record->is_active;
        $this->showModal = true;
    }

    // Processes form submissions (Saves new record additions or updates existing entries)
    public function save()
    {
        // Executes standard input validation rules declared above
        $this->validate();

        $data = [
            'bank_id' => $this->bank_id,
            'branch_code' => $this->branch_code,
            'branch_name' => $this->branch_name,
            'is_active' => $this->is_active,
        ];

        // Determines whether to perform an Update query or a Create query depending on editingId status
        if ($this->editingId) {
            BankBranch::findOrFail($this->editingId)->update($data);
            session()->flash('message', 'Branch updated successfully!');
        } else {
            BankBranch::create($data);
            session()->flash('message', 'Branch added successfully!');
        }

        // Clean up actions: automatically closes modals and resets forms upon completion
        $this->closeModal();
    }

    // Opens delete confirmations while checking safety links to prevent unintended data loss/relational errors
    public function confirmDelete(int $id)
    {
        $this->confirmDeleteId = $id;
        $branch = BankBranch::with('bank')->findOrFail($id);

        // Verifies whether this particular branch name is actively assigned to any employee profile logs
        if ($branch->bank) {
            $this->isBranchUsed = DB::table('employee_salaries_and_banks')
                ->where('bank_name', $branch->bank->bank_name)
                ->where('branch_name', $branch->branch_name)
                ->exists();
        } else {
            $this->isBranchUsed = false;
        }
    }

    // Executes final delete operations after re-confirming safety criteria on the database
    public function delete()
    {
        if ($this->confirmDeleteId) {
            $branch = BankBranch::with('bank')->findOrFail($this->confirmDeleteId);

            // Double security check: Ensure branch hasn't been assigned to an employee profile in the background
            if ($branch->bank) {
                $isUsed = DB::table('employee_salaries_and_banks')
                    ->where('bank_name', $branch->bank->bank_name)
                    ->where('branch_name', $branch->branch_name)
                    ->exists();

                // Blocks action and pushes a warning notice if data usage verification triggers true
                if ($isUsed) {
                    session()->flash('error', 'Cannot delete! This branch is currently assigned to employee profiles.');
                    $this->cancelDelete();
                    return;
                }
            }

            // Safe to delete when no background record assignments are found
            $branch->delete();
            session()->flash('message', 'Branch deleted successfully!');
            $this->cancelDelete();
        }
    }

    // Cancels active delete configurations and flushes related security checks
    public function cancelDelete()
    {
        $this->confirmDeleteId = null;
        $this->isBranchUsed = false; 
    }

    // Terminates modal views and invokes internal cleanups of all reactive components
    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    // Helper Method: Clears form data parameters, validation flags, and dropdown search arrays back to factory defaults
    private function resetForm()
    {
        $this->editingId = null;
        $this->bank_id = '';
        $this->branch_code = '';
        $this->branch_name = '';
        $this->is_active = 1;
        
        $this->bank_search = '';
        $this->showDropdown = false;
        
        $this->resetValidation();
    }

    // Main Renderer Method: Generates layout structures and streams requested query bindings straight into the active UI view template
    public function render()
    {
        // Fetches branch queries with parent bank relationships, enabling combined string filters (Branch name, Code, Bank name)
        $branchesQuery = BankBranch::with('bank')
            ->when($this->search, function($query) {
                $query->where('branch_name', 'like', '%' . $this->search . '%')
                      ->orWhere('branch_code', 'like', '%' . $this->search . '%')
                      ->orWhereHas('bank', function($q) {
                          $q->where('bank_name', 'like', '%' . $this->search . '%');
                      });
            })
            ->orderBy('id', 'desc')
            ->paginate(10);

        // Filters list options specifically for rendering the real-time searchable Bank selection dropdown in the form
        $filteredBanks = Bank::orderBy('bank_name', 'asc')
            ->when($this->bank_search, function($query) {
                $query->where('bank_name', 'like', '%' . $this->bank_search . '%');
            })
            ->get();

        // Delivers parameters cleanly over to your corresponding frontend blade files
        return view('livewire.payroll.branch-management', [
            'banks' => $filteredBanks, 
            'branchList' => $branchesQuery
        ]);
    }
}