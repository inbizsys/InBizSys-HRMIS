<?php

namespace App\Livewire\LeaveManagement;

use Livewire\Component;
use App\Models\LeaveType;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class LeaveTypeManagement extends Component
{
    use WithPagination;
    /**
     * Operation property state parameters for managing a leave type record
     */
    public $leave_type_id;
    public $leave_type_name;
    public $maximum_days_per_year;
    public $required_approval_levels_count = 1;
    public $is_active = true;
    public $auto_escalate_above_days; 
  

    /**
     * Pure Livewire modal presentation status controls (0% Javascript)
     */
    public $isFormModalVisible = false;
    public $isDeleteConfModalVisible = false;

    /**
     * Informational string metadata parameters for dynamic header labels
     */
    public $modalOperationTitle = 'Configure New Leave Type';
    public $modalActionFormButtonLabel = 'Save Policy Configuration';
    public $targetLeaveTypeDeletionDescription = '';


    public $isApprovalDropdownOpen = false;
    public $isAttachmentDropdownOpen = false;
    public $isAvailabilityDropdownOpen = false;


    public function setApprovalLevel($level)
    {
        $this->required_approval_levels_count = $level;
        $this->isApprovalDropdownOpen = false; 
    }


    public function setAvailability($value)
    {
        $this->is_active = $value;
        $this->isAvailabilityDropdownOpen = false;
    }
    /**
     * Toggles the centralized data input modal presentation for registration
     */
    public function openCreateModal()
    {
        $this->reset([
            'leave_type_id',
            'leave_type_name',
            'maximum_days_per_year',
            'required_approval_levels_count',
            'auto_escalate_above_days',
        ]);
        $this->is_active = true;

        $this->modalOperationTitle = 'Configure New Leave Type';
        $this->modalActionFormButtonLabel = 'Save Policy Configuration';
        $this->isFormModalVisible = true;
    }

    /**
     * Sets operational fields and handles dynamic naming transformations for updating
     */
    public function openEditModal($targetLeaveTypeId)
    {
        $leaveTypeInstance = LeaveType::findOrFail($targetLeaveTypeId);

        $this->leave_type_id = $leaveTypeInstance->id;
        $this->leave_type_name = $leaveTypeInstance->name;
        $this->maximum_days_per_year = $leaveTypeInstance->max_days_per_year;
        $this->required_approval_levels_count = $leaveTypeInstance->required_approval_levels;
        $this->is_active = (bool) $leaveTypeInstance->is_active;
        $this->auto_escalate_above_days = $leaveTypeInstance->auto_escalate_above_days;

        $this->modalOperationTitle = 'Update Leave Type Configuration';
        $this->modalActionFormButtonLabel = 'Update Policy Configuration';
        $this->isFormModalVisible = true;
    }

    /**
     * Initiates the secure server-controlled delete confirmation layout grid overlay
     */
    public function openDeleteConfirmationModal($targetLeaveTypeId)
    {
        $leaveTypeInstance = LeaveType::findOrFail($targetLeaveTypeId);

        $this->leave_type_id = $leaveTypeInstance->id;
        $this->targetLeaveTypeDeletionDescription = $leaveTypeInstance->name;
        $this->isDeleteConfModalVisible = true;
    }

    /**
     * Universal modal teardown handler function reset state variables cleanly
     */
    public function closeActiveModals()
    {
        $this->isFormModalVisible = false;
        $this->isDeleteConfModalVisible = false;
    }

    /**
     * Processes submission validation constraints and writes to database schema mappings
     */

    public function saveLeaveTypeConfiguration()
    {
       
        if ($this->required_approval_levels_count == 2) {
            $this->auto_escalate_above_days = null;
        }

        $this->validate([
            'leave_type_name' => 'required|string|max:100|unique:leave_types,name,' . $this->leave_type_id,
            'maximum_days_per_year' => 'required|integer|min:1|max:365',
            'required_approval_levels_count' => 'required|integer|min:1|max:2',
            'is_active' => 'required|boolean',
            'auto_escalate_above_days' => $this->required_approval_levels_count == 1 ? 'nullable|integer|min:1|max:365' : 'nullable',
            
        ]);

        LeaveType::updateOrCreate(
            ['id' => $this->leave_type_id],
            [
                'name' => $this->leave_type_name,
                'max_days_per_year' => $this->maximum_days_per_year,
                'required_approval_levels' => $this->required_approval_levels_count,
                'is_active' => $this->is_active,
                'auto_escalate_above_days' => $this->auto_escalate_above_days ?: null,
              
            ]
        );

        session()->flash('operationSuccessFeedback', 'Leave type configuration processed successfully!');
        $this->closeActiveModals();
    }

    /**
     * Real-World Architecture: Performs transactional check to prevent integrity violations / delete
     */
    public function executePolicyPurgeAction()
    {
    
        $leaveTypeInstance = LeaveType::findOrFail($this->leave_type_id);
        $leaveTypeInstance->delete();

        session()->flash('operationSuccessFeedback', 'Leave type specification deleted successfully from active registries.');
        $this->closeActiveModals();
        $this->resetPage();
    }

    /**
     * Business Logic Isolation: Decoupled selection statements outside view lifecycle components
     */
    private function fetchActiveLeaveTypesCollection()
    {
        return LeaveType::latest()->paginate(9);
    }

    /**
     * Layout assembly framework engine component lifecycle execution loop
     */
    public function render()
    {
        $configuredLeaveTypesListing = $this->fetchActiveLeaveTypesCollection();

        return view('livewire.LeaveManagement.leave-type-management', [
            'leaveTypesCollection' => $configuredLeaveTypesListing
        ]);
    }
}
