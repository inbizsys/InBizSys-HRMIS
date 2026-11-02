<?php

namespace App\Livewire\LeaveManagement;

use Livewire\Component;
use App\Models\LeaveBalance;
use App\Models\Employee;
use App\Models\LeaveType;
use Livewire\WithPagination;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use App\Models\LeaveRequest;

#[Layout('layouts.app')]
class ManageLeaveBalances extends Component
{
    use WithPagination;

    /**
     * Search query string bound to the main data table filter.
     */
    public $searchQuery = '';

    /**
     * Modals visibility state parameters.
     */
    public $isBulkModalVisible = false;
    public $isIndividualModalVisible = false;
    public $isEditModalVisible = false;

    /**
     * Bulk Allocation state properties.
     */
    public $bulkSelectedLeaveTypes = [];
    public $leaveTypeSearch = '';

    /**
     * Individual Allocation state properties with custom dropdown filters.
     */
    public $individualEmployeeId = '';
    public $individualEmployeeName = '';
    public $individualEmployeeSearch = '';
    public $isEmployeeDropdownOpen = false;

    public $individualLeaveTypeId = '';
    public $individualLeaveTypeName = '';
    public $individualLeaveTypeSearch = '';
    public $isLeaveTypeDropdownOpen = false;

    /**
     * Edit balance state fields.
     */
    public $selectedBalanceIdentifier;
    public $allocatedDaysInput;
    public $isMainDropdownOpen = false;

    public $isViewModalVisible = false;
    public $isDeleteModalVisible = false;

    
    public $selectedLeaveBalance = null;
    public $canDelete = true;
    public $deleteId = null;
    public $deleteEmployeeName = null;

    public function selectFromMainSearch($name)
    {
        $this->searchQuery = $name;
        $this->isMainDropdownOpen = false;
    }
    /**
     * Resets pagination pointer automatically when database tracking filters shift.
     */
    public function updatingSearchQuery()
    {
        $this->resetPage();
    }

    /**
     * Toggles visibility and resets internal tracking components for bulk action workflows.
     */
    public function openBulkModal()
    {
        $this->bulkSelectedLeaveTypes = [];
        $this->leaveTypeSearch = '';
        $this->isBulkModalVisible = true;
    }


    /**
     * Toggles visibility and resets state trackers for specific single assignment interactions.
     */
    public function openIndividualModal()
    {
        $this->individualEmployeeId = '';
        $this->individualEmployeeName = '';
        $this->individualEmployeeSearch = '';
        $this->individualLeaveTypeId = '';
        $this->individualLeaveTypeName = '';
        $this->individualLeaveTypeSearch = '';
        $this->isIndividualModalVisible = true;
    }
    /**
     * Selects an employee from the custom searchable dropdown.
     */
    public function selectEmployee($id, $name)
    {
        $this->individualEmployeeId = $id;
        $this->individualEmployeeSearch = $name;
        $this->isEmployeeDropdownOpen = false;
    }

    /**
     * Selects a leave type from the custom searchable dropdown.
     */
    public function selectIndividualLeaveType($id, $name)
    {
        $this->individualLeaveTypeId = $id;
        $this->individualLeaveTypeSearch = $name;

        $this->isLeaveTypeDropdownOpen = false;
    }

    public function openViewModal($id)
    {
        $this->selectedLeaveBalance = LeaveBalance::with(['employee.user', 'leaveType'])->find($id);
        $this->isViewModalVisible = true;
    }

   
    public function confirmDelete($id, $employeeName)
    {
        $this->deleteId = $id; 
        $this->deleteEmployeeName = $employeeName;

 
        $leaveBalance = LeaveBalance::find($id);
        
        if ($leaveBalance) {
        
            $hasRequests = LeaveRequest::where('employee_id', $leaveBalance->employee_id)
                ->where('leave_type_id', $leaveBalance->leave_type_id)
                ->exists();
        } else {
            $hasRequests = false;
        }

        $hasLogs = \Illuminate\Support\Facades\DB::table('leave_logs')
            ->whereIn('leave_request_id', function($query) use ($leaveBalance) {
                $query->select('id')
                    ->from('leave_requests')
                    ->where('employee_id', $leaveBalance->employee_id)
                    ->where('leave_type_id', $leaveBalance->leave_type_id);
            })
            ->exists();

        if ($hasRequests || $hasLogs) {
            $this->canDelete = false;
        } else {
            $this->canDelete = true;
        }

        $this->isDeleteModalVisible = true;
    }

    
    public function deleteLeaveBalance()
    {
        if ($this->canDelete && $this->deleteId) {
            LeaveBalance::destroy($this->deleteId);
            $this->isDeleteModalVisible = false;
            session()->flash('successMessage', "Leave balance deleted successfully.");
        }
    }
    /**
     * Method 1: Bulk allocates selected leave types to all existing employees.
     */
    public function allocateBulkLeaves()
    {
        $selectedTypes = array_filter($this->bulkSelectedLeaveTypes);

        if (empty($selectedTypes)) {
            $this->addError('bulkSelection', 'Please select at least one leave type.');
            return;
        }

        $activeLeaveTypesCollection = LeaveType::whereIn('id', array_keys($selectedTypes))->get();
        $allEmployeesCollection = Employee::all();
        $currentFinancialYear = Carbon::now()->year;
        $insertedCount = 0;

        foreach ($allEmployeesCollection as $employeeInstance) {
            foreach ($activeLeaveTypesCollection as $leaveTypeInstance) {
                $exists = LeaveBalance::where('employee_id', $employeeInstance->id)
                    ->where('leave_type_id', $leaveTypeInstance->id)
                    ->where('year', $currentFinancialYear)
                    ->exists();

                if (!$exists) {
                    LeaveBalance::create([
                        'employee_id' => $employeeInstance->id,
                        'leave_type_id' => $leaveTypeInstance->id,
                        'year' => $currentFinancialYear,
                        'allocated_days' => $leaveTypeInstance->max_days_per_year,
                        'used_days' => 0,
                    ]);
                    $insertedCount++;
                }
            }
        }

        $this->isBulkModalVisible = false;
        session()->flash('successMessage', "Bulk allocation completed! Generated {$insertedCount} new balance records.");
        $this->resetPage();
    }

    /**
     * Method 2: Allocates a specific leave type to a single selected employee.
     */
    public function allocateIndividualLeave()
    {
        if (empty($this->individualEmployeeId) || empty($this->individualLeaveTypeId)) {
            $this->addError('individualSelection', 'Both employee and leave type elements must be specified.');
            return;
        }

        $currentFinancialYear = Carbon::now()->year;

        $exists = LeaveBalance::where('employee_id', $this->individualEmployeeId)
            ->where('leave_type_id', $this->individualLeaveTypeId)
            ->where('year', $currentFinancialYear)
            ->exists();

        if ($exists) {
            $this->addError('individualSelection', 'This employee already contains an allocated balance record for the chosen type.');
            return;
        }

        $leaveTypeInstance = LeaveType::findOrFail($this->individualLeaveTypeId);

        LeaveBalance::create([
            'employee_id' => $this->individualEmployeeId,
            'leave_type_id' => $this->individualLeaveTypeId,
            'year' => $currentFinancialYear,
            'allocated_days' => $leaveTypeInstance->max_days_per_year,
            'used_days' => 0,
        ]);

        $this->isIndividualModalVisible = false;
        session()->flash('successMessage', 'Leave type allocated successfully to the selected employee.');
        $this->resetPage();
    }

    /**
     * Rule 6 Compliance: Abstracted table filtering structure database engine query loop.
     */
    private function getFilteredLeaveBalances()
    {
        return LeaveBalance::with(['employee.user', 'leaveType'])
            ->where(function ($query) {
                $query->whereHas('employee.user', function ($subQuery) {
                    $subQuery->where('name', 'like', '%' . $this->searchQuery . '%');
                })
                    ->orWhereHas('leaveType', function ($subQuery) {
                        $subQuery->where('name', 'like', '%' . $this->searchQuery . '%');
                    });
            })
            ->paginate(10);
    }

    /**
     * Master render pipeline compiling filtered dynamic outputs into views.
     */
    public function render()
    {
        // Search filter matching scopes for bulk component lists
        $bulkLeaveTypesList = LeaveType::where('is_active', 1)
            ->where('name', 'like', '%' . $this->leaveTypeSearch . '%')
            ->get();

        // Search filter matching scopes for individual dropdown components
        $filteredEmployeesList = Employee::with('user')
            ->whereHas('user', function ($query) {
                $query->where('name', 'like', '%' . $this->individualEmployeeSearch . '%');
            })->get();

        $filteredLeaveTypesList = LeaveType::where('is_active', 1)
            ->where('name', 'like', '%' . $this->individualLeaveTypeSearch . '%')
            ->get();

        return view('livewire.LeaveManagement.manage-leave-balances', [
            'leaveBalancesCollection' => $this->getFilteredLeaveBalances(),
            'bulkLeaveTypesList' => $bulkLeaveTypesList,
            'filteredEmployeesList' => $filteredEmployeesList,
            'filteredLeaveTypesList' => $filteredLeaveTypesList,
        ]);
    }
}
