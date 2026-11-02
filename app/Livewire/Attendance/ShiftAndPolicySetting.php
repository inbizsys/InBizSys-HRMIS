<?php

namespace App\Livewire\Attendance;

use App\Models\AttendancePolicy;
use App\Models\BreakType;
use App\Models\LeaveType;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Illuminate\Validation\Rule;

#[Layout('layouts.app')]
class ShiftAndPolicySetting extends Component
{
    // ACTIVE TAB ('shifts', 'policy', or 'breaks')
    public string $activeTab = 'shifts';

    // SHIFT FORM PROPERTIES
    public ?int $editingShiftId = null;
    public string $shift_name = '';
    public string $start_time = '08:30';
    public string $end_time = '17:00';
    public int $late_grace_minutes = 15;
    public int $early_leave_grace_minutes = 10;
    public float $standard_working_hours = 8.00;
    public float $half_day_min_hours = 4.00;
    public bool $is_active = true;

    // BREAK TYPE FORM PROPERTIES
    public ?int $editingBreakTypeId = null;
    public string $break_type_name = '';
    public string $break_type_code = '';
    public int $break_max_minutes = 15;
    public bool $break_is_paid = true;
    public bool $break_is_active = true;

    // MODAL STATES
    public bool $showShiftModal = false;
    public bool $showViewShiftModal = false;
    public bool $showDeleteModal = false;
    public bool $showBreakTypeModal = false;

    // VIEW & DELETE STATE
    public ?Shift $selectedShift = null;
    public ?int $deletingShiftId = null;
    public string $deletingShiftName = '';

    // POLICY FORM PROPERTIES
    public ?int $policyId = null;
    public string $policy_name = 'Standard Corporate Policy';
    
    // Late Policy
    public int $max_late_allowed_per_month = 3;
    public string $late_penalty_type = 'deduct_leave';
    public float $late_penalty_rate = 0.50;
    public ?int $deduct_leave_type_id = null;

    // Early Leave Policy
    public int $max_early_leave_allowed_per_month = 3;
    public string $early_leave_penalty_type = 'none';
    public float $early_leave_penalty_rate = 0.50;

    // Break & Overtime Policy
    public int $break_deduction_minutes = 60;
    public int $overtime_minimum_minutes = 60;

    // UNPAID BREAK OVERUSE POLICY PROPERTIES
    public int $max_unpaid_break_allowed_per_month = 3;
    public string $unpaid_break_penalty_type = 'none';
    public float $unpaid_break_penalty_rate = 0.50;
    public ?int $unpaid_break_leave_type_id = null;

    // Break Type Delete State 
    public ?int $deletingBreakTypeId = null;
    public string $deletingBreakTypeName = '';
    public bool $showDeleteBreakTypeModal = false;

    // Validation Rules
    protected function rules(): array
    {
        if ($this->activeTab === 'shifts') {
            return [
                'shift_name' => ['required', 'string', 'max:100'],
                'start_time' => ['required', 'date_format:H:i'],
                'end_time' => ['required', 'date_format:H:i'],
                'late_grace_minutes' => ['required', 'integer', 'min:0', 'max:120'],
                'early_leave_grace_minutes' => ['required', 'integer', 'min:0', 'max:120'],
                'standard_working_hours' => ['required', 'numeric', 'min:1', 'max:24'],
                'half_day_min_hours' => ['required', 'numeric', 'min:1', 'max:12'],
                'is_active' => ['boolean'],
            ];
        }

        if ($this->activeTab === 'breaks') {
            return [
                'break_type_name' => ['required', 'string', 'max:100'],
                'break_type_code' => ['required', 'string', 'max:50', 'unique:break_types,code,' . $this->editingBreakTypeId],
                'break_max_minutes' => ['required', 'integer', 'min:1', 'max:480'],
                'break_is_paid' => ['boolean'],
                'break_is_active' => ['boolean'],
            ];
        }

        // Policy Tab Validation Rules
        return [
            'policy_name' => ['required', 'string', 'max:100'],
            
            // Late Rules
            'max_late_allowed_per_month' => ['required', 'integer', 'min:0', 'max:31'],
            'late_penalty_type' => ['required', 'in:none,deduct_leave,salary_deduction'],
            'late_penalty_rate' => [
                Rule::excludeIf(fn () => $this->late_penalty_type === 'none'),
                'required', 'numeric', 'min:0.01', 'max:1.0'
            ],

            // Early Leave Rules
            'max_early_leave_allowed_per_month' => ['required', 'integer', 'min:0', 'max:31'],
            'early_leave_penalty_type' => ['required', 'in:none,deduct_leave,salary_deduction'],
            'early_leave_penalty_rate' => [
                Rule::excludeIf(fn () => $this->early_leave_penalty_type === 'none'),
                'required', 'numeric', 'min:0.01', 'max:1.0'
            ],

            'deduct_leave_type_id' => [
                'nullable',
                Rule::requiredIf(fn () => $this->late_penalty_type === 'deduct_leave' || $this->early_leave_penalty_type === 'deduct_leave'),
                'exists:leave_types,id',
            ],

            // General Break & Overtime
            'break_deduction_minutes' => ['required', 'integer', 'min:0', 'max:180'],
            'overtime_minimum_minutes' => ['required', 'integer', 'min:0', 'max:240'],

            // Unpaid Break Overuse Rules
            'max_unpaid_break_allowed_per_month' => ['required', 'integer', 'min:0', 'max:31'],
            'unpaid_break_penalty_type' => ['required', 'in:none,deduct_leave,salary_deduction'],
            'unpaid_break_penalty_rate' => [
                Rule::excludeIf(fn () => $this->unpaid_break_penalty_type === 'none'),
                'required', 'numeric', 'min:0.01', 'max:1.0'
            ],
            'unpaid_break_leave_type_id' => [
                'nullable',
                Rule::requiredIf(fn () => $this->unpaid_break_penalty_type === 'deduct_leave'),
                'exists:leave_types,id',
            ],
        ];
    }

    public function mount(): void
    {
        $policy = AttendancePolicy::first();
        if ($policy) {
            $this->policyId = $policy->id;
            $this->policy_name = $policy->policy_name;
            
            // Late
            $this->max_late_allowed_per_month = $policy->max_late_allowed_per_month;
            $this->late_penalty_type = $policy->late_penalty_type;
            $this->late_penalty_rate = (float) $policy->late_penalty_rate;
            $this->deduct_leave_type_id = $policy->deduct_leave_type_id;

            // Early Leave
            $this->max_early_leave_allowed_per_month = $policy->max_early_leave_allowed_per_month ?? 3;
            $this->early_leave_penalty_type = $policy->early_leave_penalty_type ?? 'none';
            $this->early_leave_penalty_rate = (float) ($policy->early_leave_penalty_rate ?? 0.50);

            // Break General & OT
            $this->break_deduction_minutes = $policy->break_deduction_minutes;
            $this->overtime_minimum_minutes = $policy->overtime_minimum_minutes;

            // Unpaid Break Overuse
            $this->max_unpaid_break_allowed_per_month = $policy->max_unpaid_break_allowed_per_month ?? 3;
            $this->unpaid_break_penalty_type = $policy->unpaid_break_penalty_type ?? 'none';
            $this->unpaid_break_penalty_rate = (float) ($policy->unpaid_break_penalty_rate ?? 0.50);
            $this->unpaid_break_leave_type_id = $policy->unpaid_break_leave_type_id;
        }
    }

    // SHIFT ACTIONS
    public function openCreateShiftModal(): void
    {
        $this->resetShiftForm();
        $this->showShiftModal = true;
    }

    public function viewShift(int $shiftId): void
    {
        $this->selectedShift = Shift::findOrFail($shiftId);
        $this->showViewShiftModal = true;
    }

    public function openEditModal(int $shiftId): void
    {
        $shift = Shift::findOrFail($shiftId);
        $this->editingShiftId = $shift->id;
        $this->shift_name = $shift->name;
        $this->start_time = Carbon::parse($shift->start_time)->format('H:i');
        $this->end_time = Carbon::parse($shift->end_time)->format('H:i');
        $this->late_grace_minutes = $shift->late_grace_minutes;
        $this->early_leave_grace_minutes = $shift->early_leave_grace_minutes;
        $this->standard_working_hours = (float) $shift->standard_working_hours;
        $this->half_day_min_hours = (float) $shift->half_day_min_hours;
        $this->is_active = (bool) $shift->is_active;

        $this->showShiftModal = true;
    }

    public function saveShift(): void
    {
        $this->validate();

        Shift::updateOrCreate(
            ['id' => $this->editingShiftId],
            [
                'name' => $this->shift_name,
                'start_time' => $this->start_time,
                'end_time' => $this->end_time,
                'late_grace_minutes' => $this->late_grace_minutes,
                'early_leave_grace_minutes' => $this->early_leave_grace_minutes,
                'standard_working_hours' => $this->standard_working_hours,
                'half_day_min_hours' => $this->half_day_min_hours,
                'is_active' => $this->is_active,
            ]
        );

        $this->closeModals();
        session()->flash('success', 'Shift schedule saved successfully.');
    }

    public function confirmDelete(int $shiftId, string $shiftName): void
    {
        $this->deletingShiftId = $shiftId;
        $this->deletingShiftName = $shiftName;
        $this->showDeleteModal = true;
    }

    public function deleteShift(): void
    {
        if ($this->deletingShiftId) {
            Shift::findOrFail($this->deletingShiftId)->delete();
            $this->closeModals();
            session()->flash('success', 'Shift schedule deleted successfully.');
        }
    }

    public function toggleShiftStatus(int $shiftId): void
    {
        $shift = Shift::findOrFail($shiftId);
        $shift->update(['is_active' => !$shift->is_active]);
        session()->flash('success', 'Shift status updated.');
    }

    // BREAK TYPE ACTIONS
    public function openCreateBreakTypeModal(): void
    {
        $this->resetBreakTypeForm();
        $this->showBreakTypeModal = true;
    }

    public function confirmBreakTypeDelete(int $id, string $name): void
    {
        $this->deletingBreakTypeId = $id;
        $this->deletingBreakTypeName = $name;
        $this->showDeleteBreakTypeModal = true;
    }

    public function openEditBreakTypeModal(int $id): void
    {
        $breakType = BreakType::findOrFail($id);

        $this->editingBreakTypeId = $breakType->id;
        $this->break_type_name = $breakType->name;
        $this->break_type_code = $breakType->code;
        $this->break_max_minutes = $breakType->max_minutes;
        $this->break_is_paid = $breakType->is_paid;
        $this->break_is_active = $breakType->is_active;

        $this->showBreakTypeModal = true;
    }

    public function saveBreakType(): void
    {
        $this->validate();

        BreakType::updateOrCreate(
            ['id' => $this->editingBreakTypeId],
            [
                'name' => $this->break_type_name,
                'code' => $this->break_type_code,
                'max_minutes' => $this->break_max_minutes,
                'is_paid' => $this->break_is_paid,
                'is_active' => $this->break_is_active,
            ]
        );

        $this->closeModals();
        session()->flash('success', $this->editingBreakTypeId ? 'Break category updated successfully.' : 'Break category created successfully.');
    }

    public function toggleBreakTypeStatus(int $id): void
    {
        $breakType = BreakType::findOrFail($id);
        $breakType->update(['is_active' => !$breakType->is_active]);
        session()->flash('success', 'Break category status updated.');
    }

    public function deleteBreakType(): void
    {
        if ($this->deletingBreakTypeId) {
            BreakType::findOrFail($this->deletingBreakTypeId)->delete();
            $this->closeModals();
            session()->flash('success', 'Break category deleted successfully.');
        }
    }

    // GENERAL MODAL & FORM RESET
    public function closeModals(): void
    {
        $this->showShiftModal = false;
        $this->showViewShiftModal = false;
        $this->showDeleteModal = false;
        $this->showBreakTypeModal = false;
        $this->showDeleteBreakTypeModal = false; 

        $this->resetShiftForm();
        $this->resetBreakTypeForm();
    }

    private function resetBreakTypeForm(): void
    {
        $this->reset([
            'editingBreakTypeId',
            'break_type_name',
            'break_type_code',
            'break_max_minutes',
            'break_is_paid',
            'break_is_active',
            'deletingBreakTypeId',
            'deletingBreakTypeName',
            'showDeleteBreakTypeModal', 
        ]);
        $this->break_max_minutes = 15;
        $this->break_is_paid = true;
        $this->break_is_active = true;
        $this->resetValidation();
    }

    private function resetShiftForm(): void
    {
        $this->reset([
            'editingShiftId',
            'shift_name',
            'start_time',
            'end_time',
            'late_grace_minutes',
            'early_leave_grace_minutes',
            'standard_working_hours',
            'half_day_min_hours',
            'is_active',
            'selectedShift',
            'deletingShiftId',
            'deletingShiftName',
        ]);
        $this->start_time = '08:30';
        $this->end_time = '17:00';
        $this->late_grace_minutes = 15;
        $this->early_leave_grace_minutes = 10;
        $this->standard_working_hours = 8.00;
        $this->half_day_min_hours = 4.00;
        $this->is_active = true;
        $this->resetValidation();
    }

    // POLICY SAVE METHOD
    public function savePolicy(): void
    {
        $this->validate();

        $latePenaltyRate = ($this->late_penalty_type === 'none') ? 0 : (float) $this->late_penalty_rate;
        $earlyLeavePenaltyRate = ($this->early_leave_penalty_type === 'none') ? 0 : (float) $this->early_leave_penalty_rate;
        $unpaidBreakPenaltyRate = ($this->unpaid_break_penalty_type === 'none') ? 0 : (float) $this->unpaid_break_penalty_rate;

        $deductLeaveTypeId = ($this->late_penalty_type === 'deduct_leave' || $this->early_leave_penalty_type === 'deduct_leave') 
            ? $this->deduct_leave_type_id 
            : null;

        $unpaidBreakLeaveTypeId = ($this->unpaid_break_penalty_type === 'deduct_leave')
            ? $this->unpaid_break_leave_type_id
            : null;

        $policy = AttendancePolicy::first();

        AttendancePolicy::updateOrCreate(
            ['id' => $policy ? $policy->id : null], 
            [
                'policy_name' => $this->policy_name,
                
                // Late
                'max_late_allowed_per_month' => $this->max_late_allowed_per_month,
                'late_penalty_type' => $this->late_penalty_type,
                'late_penalty_rate' => $latePenaltyRate,

                // Early Leave
                'max_early_leave_allowed_per_month' => $this->max_early_leave_allowed_per_month, 
                'early_leave_penalty_type' => $this->early_leave_penalty_type,                  
                'early_leave_penalty_rate' => $earlyLeavePenaltyRate,

                // Target Leave for Late & Early Leave
                'deduct_leave_type_id' => $deductLeaveTypeId,

                // Break General & OT
                'break_deduction_minutes' => $this->break_deduction_minutes,
                'overtime_minimum_minutes' => $this->overtime_minimum_minutes,

                // Unpaid Break Overuse Policy
                'max_unpaid_break_allowed_per_month' => $this->max_unpaid_break_allowed_per_month,
                'unpaid_break_penalty_type' => $this->unpaid_break_penalty_type,
                'unpaid_break_penalty_rate' => $unpaidBreakPenaltyRate,
                'unpaid_break_leave_type_id' => $unpaidBreakLeaveTypeId,

                'is_active' => true,
            ]
        );

        if (!$this->policyId) {
            $this->policyId = AttendancePolicy::first()->id;
        }

        session()->flash('success', 'Attendance policy rules updated successfully.');
    }

    public function render(): View
    {
        $shifts = Shift::latest()->get()->map(function ($shift) {
            $shift->formatted_start_time = Carbon::parse($shift->start_time)->format('h:i A');
            $shift->formatted_end_time = Carbon::parse($shift->end_time)->format('h:i A');
            return $shift;
        });

        $leaveTypes = LeaveType::where('is_active', true)->get();
        $breakTypes = BreakType::latest()->get();

        return view('livewire.attendance.shift-and-policy-setting', [
            'shifts'     => $shifts,
            'leaveTypes' => $leaveTypes,
            'breakTypes' => $breakTypes,
        ]);
    }
}