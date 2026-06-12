<?php

namespace App\Livewire\Payroll;

use Livewire\Component;
use App\Models\Bank;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

#[Layout('layouts.app')]
class BankManagement extends Component
{
    // Enable Livewire's built-in pagination features
    use WithPagination;

    // Search Property: Binds to the search input field in the frontend
    public $search = '';

    // Modal States: Manage visibility of UI modals and track action IDs
    public bool $showModal = false;       // Controls the create/edit modal visibility
    public $editingId = null;             // Stores the ID of the bank being edited (null means creating new)
    public $confirmDeleteId = null;       // Stores the ID of the bank pending deletion

    // Form Fields: Binds directly to the HTML form inputs via wire:model
    public $bank_code = '';
    public $bank_name = '';
    public $is_active = 1;

    // Flags whether the selected bank is linked to any employee record (used for UI delete warnings)
    public bool $isBankUsed = false;

    // Validation Rules: Defines the criteria that form inputs must meet before saving
    protected function rules(): array
    {
        return [
            'bank_code' => [
                'required', 
                'string', 
                'max:10', 
                // Checks uniqueness in the 'banks' table, ignoring the current record if we are editing
                Rule::unique('banks', 'bank_code')->ignore($this->editingId)
            ],
            'bank_name' => 'required|string|max:255',
            'is_active' => 'required|boolean',
        ];
    }

    // Lifecycle Hook: Resets pagination back to page 1 whenever the search query changes
    public function updatingSearch()
    {
        $this->resetPage();
    }

    // Opens the modal in creation mode by resetting the form values first
    public function openModal()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    // Opens the modal in edit mode and populates fields with existing data from the database
    public function edit(int $id)
    {
        $record = Bank::findOrFail($id);
        $this->editingId = $id;
        $this->bank_code = $record->bank_code;
        $this->bank_name = $record->bank_name;
        $this->is_active = $record->is_active;
        $this->showModal = true;
    }

    // Handles saving data (both creating a new bank or updating an existing one)
    public function save()
    {
        // Triggers validation based on the rules() method defined above
        $this->validate();

        $data = [
            'bank_code' => $this->bank_code,
            'bank_name' => $this->bank_name,
            'is_active' => $this->is_active,
        ];

        // If editingId is present, update the record; otherwise, create a brand new one
        if ($this->editingId) {
            Bank::findOrFail($this->editingId)->update($data);
            session()->flash('message', 'Bank updated successfully!');
        } else {
            Bank::create($data);
            session()->flash('message', 'Bank added successfully!');
        }

        // Close modal and clear form after successful operation
        $this->closeModal();
    }

    // Initiates the delete confirmation flow and checks if the bank is currently in use
    public function confirmDelete(int $id)
    {
        $this->confirmDeleteId = $id;
        $bank = Bank::findOrFail($id);

        // Check if the bank name exists inside the employee records table
        $this->isBankUsed = DB::table('employee_salaries_and_banks')
            ->where('bank_name', $bank->bank_name)
            ->exists();
    }

    // Finalizes the deletion process after passing safety checks
    public function delete()
    {
        if ($this->confirmDeleteId) {
            $bank = Bank::findOrFail($this->confirmDeleteId);

            // Double-Check backend: Re-verify usage security right before deletion
            $isUsed = DB::table('employee_salaries_and_banks')
                ->where('bank_name', $bank->bank_name)
                ->exists();

            // Prevent deletion and flash an error if the bank is linked to employee profiles
            if ($isUsed) {
                session()->flash('error', 'Cannot delete! This bank name is currently assigned to employee profiles.');
                $this->cancelDelete();
                return;
            }

            // Proceed with deletion if not used anywhere
            $bank->delete();
            session()->flash('message', 'Bank deleted successfully!');
            $this->cancelDelete();
        }
    }

    // Cancels the delete action and resets relevant state tracking properties
    public function cancelDelete()
    {
        $this->confirmDeleteId = null;
        $this->isBankUsed = false; // Reset property
    }

    // Closes the modal and structural cleanup of form data
    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    // Helper Method: Resets all form fields to default states and clears active validation errors
    private function resetForm()
    {
        $this->editingId = null;
        $this->bank_code = '';
        $this->bank_name = '';
        $this->is_active = 1;
        $this->resetValidation();
    }

    // Renders the blade view component and passes the processed database collection
    public function render()
    {
        // Base query with optional search filtering by bank name or bank code
        $banksQuery = Bank::when($this->search, function($query) {
                $query->where('bank_name', 'like', '%' . $this->search . '%')
                      ->orWhere('bank_code', 'like', '%' . $this->search . '%');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Inline Transformation: Appends a dynamic 'is_used' boolean status flag to each individual row item
        $banksQuery->getCollection()->transform(function ($bank) {
            $bank->is_used = DB::table('employee_salaries_and_banks')
                ->where('bank_name', $bank->bank_name)
                ->exists();
            return $bank;
        });

        // Returns the final processed view component
        return view('livewire.payroll.bank-management', [
            'banks' => $banksQuery
        ]);
    }
}