<?php

namespace App\Livewire\LeaveManagement;

use Livewire\Component;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\LeaveBalance;
use Illuminate\Support\Facades\Auth;
use App\Models\PublicHoliday;
use App\Models\CompanyCalendarSetting;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use Livewire\WithPagination;
use App\Models\ApplyLeaveType;
use App\Models\Payroll;
use Illuminate\Support\Facades\Storage;

#[Layout('layouts.app')]
class LeaveApplyForm extends Component
{
    use WithFileUploads;
    use WithPagination;

    /**
     * Form component state variables
     */
    public $leave_type_id;
    public $start_date;
    public $end_date;
    public $reason;
    public $total_days = 0;

    /**
     * UI control states and data collections
     */
    public $leaveTypesCollection = [];
    public $availableLeaveBalance = null;
    public $isApplicationModalOpen = false;
    public $leaveTypeSearch = '';
    public $individualLeaveTypeSearch = '';
    public $isLeaveTypeDropdownOpen = false;

    public $cancel_request_id = null;
    public $isCancelModalOpen = false;

    public $attachment;
    public $isAttachmentRequired = false;

    public $is_half_day = false;
    public $leave_shift = null;

    public $showViewModal = false;
    public $selectedLeaveDetails;

    public $leave_id;
    public $showSaveModal = false;

    public bool $showAttachmentModal = false;
    public ?int $selectedLeaveId = null;
    public ?string $attachmentUrl = null;

    public array $alternativePaidLeaveTypes = [];
    public string $fallback_leave_type_id = 'nopay';


    public array $allocatedFallbackDaysArray = [];
    public float $unassignedFallbackDaysRemaining = 0.0;

    public bool $isSplitConfirmationModalOpen = false;

    public $isEditMode = false;
    public $editingLeaveRequestId = null;

    public $existing_attachment_path = null;

    public function checkLeaveBalanceAndRoute()
    {
       
        $formValidationRules = [
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date'    => 'required|date',
            'end_date'      => 'required|date|after_or_equal:start_date',
            'reason'        => 'required|string|min:10',
            'attachment'    => ($this->isAttachmentRequired && !$this->isEditMode) ? 'required|file|mimes:pdf,jpg,jpeg,png|max:2048' : 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'is_half_day'   => 'required|boolean',
            'leave_shift'   => 'required_if:is_half_day,true|nullable|string|in:Morning,Evening',
        ];

        $this->validate($formValidationRules);

        
        if ($this->total_days <= 0) {
            $this->addError('total_days', 'The selected date range contains no working days. Please choose valid working days.');
            return;
        }

        // Period lock: Disallow applying for leave in approved or paid payroll months
        $startMonth = substr($this->start_date, 0, 7);
        $endMonth = substr($this->end_date, 0, 7);

        $isPayrollLocked = Payroll::whereIn('payroll_month', array_unique([$startMonth, $endMonth]))
            ->whereIn('status', ['approved', 'paid'])
            ->exists();

        if ($isPayrollLocked) {
            $this->addError('start_date', 'Cannot apply for leave. Payroll for this period has already been approved/paid and locked.');
            return;
        }
      
        $this->fetchAvailableLeaveBalance();

        if ($this->isEditMode) {

            if (($this->total_days > $this->availableLeaveBalance) || !empty($this->allocatedFallbackDaysArray)) {

                $this->isSplitConfirmationModalOpen = true;

                $this->calculateUnassignedSplitDaysPool();
                return;
            }
        } else {
         
            if ($this->total_days > $this->availableLeaveBalance) {
                $this->isSplitConfirmationModalOpen = true;
                $this->calculateUnassignedSplitDaysPool();
                return;
            }
        }

        if ($this->isEditMode) {
            $this->resubmitLeaveRequest();
        } else {
            $this->submitLeaveApplication();
        }
    }

    public function removeExistingFile()
    {
        $this->existing_attachment_path = null;
    }

    public function openReapplyModal($leaveRequestId)
    {
        $this->isEditMode = true;
        $this->editingLeaveRequestId = $leaveRequestId;

        $leaveRequest = LeaveRequest::findOrFail($leaveRequestId);

        $this->start_date = $leaveRequest->start_date ? \Carbon\Carbon::parse($leaveRequest->start_date)->format('Y-m-d') : null;
        $this->end_date = $leaveRequest->end_date ? \Carbon\Carbon::parse($leaveRequest->end_date)->format('Y-m-d') : null;
        $this->reason = $leaveRequest->reason;
        $this->is_half_day = (bool)$leaveRequest->is_half_day;
        $this->leave_shift = $leaveRequest->leave_shift;
        $this->existing_attachment_path = $leaveRequest->attachment_path;

        $this->total_days = $leaveRequest->total_days;

   
        $firstSplit = DB::table('apply_leave_types')
            ->join('leave_types', 'apply_leave_types.leave_type_id', '=', 'leave_types.id')
            ->where('apply_leave_types.leave_request_id', $leaveRequestId)
            ->select('leave_types.name', 'apply_leave_types.leave_type_id')
            ->first();

        if ($firstSplit) {
            $this->leave_type_id = $firstSplit->leave_type_id;
            $this->individualLeaveTypeSearch = $firstSplit->name;
        } else {
            $this->individualLeaveTypeSearch = '';
        }

        $this->fetchAvailableLeaveBalance();

        $currentAcademicYear = Carbon::now()->year;
        $authenticatedEmployeeId = Auth::user()->employee->id ?? Auth::id();

        $balances = LeaveBalance::with('leaveType')
            ->where('employee_id', $authenticatedEmployeeId)
            ->where('leave_type_id', '!=', $this->leave_type_id)
            ->where('year', $currentAcademicYear)
            ->get();

        $this->alternativePaidLeaveTypes = [];
        foreach ($balances as $balance) {
            $remaining = $balance->allocated_days - $balance->used_days;
            if ($remaining > 0) {
                $this->alternativePaidLeaveTypes[] = [
                    'id'      => $balance->leave_type_id,
                    'name'    => $balance->leaveType->name,
                    'balance' => $remaining,
                ];
            }
        }

        $this->allocatedFallbackDaysArray = [];

        $existingSplits = ApplyLeaveType::where('leave_request_id', $leaveRequestId)->get();

        $isFirst = true; 
        foreach ($existingSplits as $split) {

    
            if ($isFirst && $split->leave_type_id == $this->leave_type_id) {
                $isFirst = false;
                continue;
            }

            $key = is_null($split->leave_type_id) ? 'nopay' : $split->leave_type_id;

            $this->allocatedFallbackDaysArray[$key] = $split->days_count;
        }

        $this->calculateUnassignedSplitDaysPool();
        $this->isApplicationModalOpen = true;

        $this->dispatch('fill-edit-dates', [
            'start' => $this->start_date,
            'end' => $this->end_date
        ]);
    }


    public function initiateApplicationSubmission(): void
    {
        $this->calculateUnassignedSplitDaysPool();

        $availablePrimaryBalance = $this->availableLeaveBalance ?? 0;
        $totalRequestedDays = $this->total_days;

        if ($totalRequestedDays > $availablePrimaryBalance) {
            $this->isSplitConfirmationModalOpen = true;
        } else {
            // If balance is sufficient, bypass the modal and save directly
            $this->submitLeaveApplication();
        }
    }


    public function calculateUnassignedSplitDaysPool(): void
    {
        // Clear any previous matrix validation errors before recalculating
        $this->resetErrorBag('matrix_error');

        $totalRequestedDays = $this->total_days;
        $availablePrimaryBalance = $this->availableLeaveBalance ?? 0.0;

        if ($totalRequestedDays > $availablePrimaryBalance) {
            $totalOverflowDaysNeeded = $totalRequestedDays - $availablePrimaryBalance;

         
            $numericSanitizedFallbackDaysArray = array_map('floatval', $this->allocatedFallbackDaysArray);


            foreach ($numericSanitizedFallbackDaysArray as $leaveTypeIdKey => $assignedDaysValue) {
                if ($leaveTypeIdKey === 'nopay') {
                    continue; 
                }

                $specificAlternativeTypeMatch = collect($this->alternativePaidLeaveTypes)->firstWhere('id', $leaveTypeIdKey);
                $maximumAllowedBalanceAmount = $specificAlternativeTypeMatch['balance'] ?? 0.0;

                if ($assignedDaysValue > $maximumAllowedBalanceAmount) {
                    $this->addError('matrix_error', 'The allocated days for ' . ($specificAlternativeTypeMatch['name'] ?? 'Selected Type') . ' cannot exceed its available balance of ' . $maximumAllowedBalanceAmount . ' day(s).');
                }
            }

            $totalCurrentlyAssignedDays = (float) array_sum($numericSanitizedFallbackDaysArray);
            $this->unassignedFallbackDaysRemaining = $totalOverflowDaysNeeded - $totalCurrentlyAssignedDays;


            if ($this->unassignedFallbackDaysRemaining < 0) {
                $this->addError('matrix_error', 'You have over-allocated the required breakdown split pool by ' . abs($this->unassignedFallbackDaysRemaining) . ' day(s).');
            }
        } else {
            $this->unassignedFallbackDaysRemaining = 0.0;
            $this->allocatedFallbackDaysArray = [];
        }
    }

    /**
     * Component initialization lifestyle hook
     */
    public function mount()
    {
        // Fetch all available leave types from database
        $this->leaveTypesCollection = LeaveType::all();
    }


    public function updatedStartDate(string $newStartDateValue): void
    {
        if ($this->is_half_day) {
            $this->end_date = $newStartDateValue;
        }
        $this->calculateRequestedLeaveDays();
    }

    public function updatedEndDate(): void
    {
        $this->calculateRequestedLeaveDays();
    }

    public function updatedIsHalfDay(bool $isHalfDayChecked): void
    {
        if ($isHalfDayChecked && $this->start_date) {
            $this->end_date = $this->start_date;
        }
        $this->calculateRequestedLeaveDays();
    }

    public function updatingIndividualLeaveTypeSearch()
    {
        $this->resetPage('historyPage');
        $this->resetPage('balancePage');
    }

    public function openCancelConfirmationModal($id)
    {
        $this->cancel_request_id = $id;
        $this->isCancelModalOpen = true;
    }

    public function closeCancelModal()
    {
        $this->cancel_request_id = null;
        $this->isCancelModalOpen = false;

        $this->reset(['cancel_request_id']);
    }

    public function closeViewModal(): void
    {
        $this->showViewModal = false;
        $this->showAttachmentModal = false;
        $this->selectedLeaveId = null;
        $this->selectedLeaveDetails = null;
        $this->attachmentUrl = null;
    }
    public function openAttachmentPreview(string $attachmentPath): void
    {
        $this->attachmentUrl = Storage::url($attachmentPath);
        $this->showAttachmentModal = true;
    }

    public function closeAttachmentPreview(): void
    {
        $this->showAttachmentModal = false;
        $this->attachmentUrl = null;
    }

    public function openViewModal(int $leaveApplicationId): void
    {

        $this->selectedLeaveId = $leaveApplicationId;
        $this->selectedLeaveDetails = LeaveRequest::with('applyLeaveTypes.leaveType')->find($leaveApplicationId);
        $this->showViewModal = true;
    }

    public function openEditModal($id)
    {
        $leave = LeaveRequest::find($id);
        $this->leave_id = $leave->id;
        $this->leave_type_id = $leave->applyLeaveTypes()->whereNotNull('leave_type_id')->first()?->leave_type_id;
        $this->start_date = $leave->start_date;
        $this->end_date = $leave->end_date;
        $this->reason = $leave->reason;

        $this->isApplicationModalOpen = true;
    }

    /**
     * Lifecycle hook that monitors changes to form fields
     */
    public function updated($propertyName)
    {
       
        if ($propertyName === 'is_half_day') {
            if ($this->is_half_day) {
                $this->end_date = $this->start_date;
                $this->leave_shift = 'Morning';
            } else {
                $this->leave_shift = null;
            }
            $this->calculateRequestedLeaveDays();
            $this->fetchAvailableLeaveBalance();
        }

        if ($propertyName === 'start_date' && $this->is_half_day) {
            $this->end_date = $this->start_date;
        }

        if (in_array($propertyName, ['start_date', 'end_date', 'leave_type_id', 'leave_shift'])) {
            $this->calculateRequestedLeaveDays();
            $this->fetchAvailableLeaveBalance();
        }

    }
    /**
     * Calculates total business days excluding Saturday, Sunday AND public holidays
     */

    private function calculateRequestedLeaveDays()
    {

        if (!$this->start_date || !$this->end_date) {
            $this->total_days = 0;
            return;
        }

        $startDateInstance = \Carbon\Carbon::parse($this->start_date);
        $endDateInstance = \Carbon\Carbon::parse($this->end_date);

        if ($startDateInstance->greaterThan($endDateInstance)) {
            $this->total_days = 0;
            return;
        }

        // 1. EAGER LOAD CONFIGURATIONS
        $publicHolidaysCollection = PublicHoliday::whereBetween('holiday_date', [
            $startDateInstance->format('Y-m-d'),
            $endDateInstance->format('Y-m-d')
        ])->get()->keyBy('holiday_date');

        $companyWeeklySchedules = CompanyCalendarSetting::all()->keyBy('day_of_week');

        $totalCalculatedBusinessDaysCount = 0;
        $targetDateIntervalPeriod = \Carbon\CarbonPeriod::create($startDateInstance, $endDateInstance);

        // 2. MAIN EVALUATION LOOP
        foreach ($targetDateIntervalPeriod as $currentIterationDateInstance) {

            $formattedDateString = $currentIterationDateInstance->format('Y-m-d');
            $dayOfWeekNameString = $currentIterationDateInstance->format('l');

            $dayScheduleConfig = $companyWeeklySchedules[$dayOfWeekNameString] ?? null;


            if (!$dayScheduleConfig || (int)$dayScheduleConfig->is_working_day === 0) {
                continue;
            }


            $currentDayWeightFactor = strtolower($dayScheduleConfig->day_type) === 'half day' ? 0.5 : 1.0;

            // ----------------------------------------------------------------------
            // 3. SPECIAL HANDLING FOR USER APPLIED HALF-DAY LEAVES 
            // ----------------------------------------------------------------------
            if ($this->is_half_day) {


                if (isset($publicHolidaysCollection[$formattedDateString])) {
                    $publicHolidayRecord = $publicHolidaysCollection[$formattedDateString];

                    if ($publicHolidayRecord->holiday_type === 'Full Day') {
                        $this->total_days = 0;
                        $this->evaluateAlternativeFallbackBalances();
                        return;
                    }

                    if ($publicHolidayRecord->holiday_type === 'Half Day') {
                        if ($publicHolidayRecord->holiday_shift === $this->leave_shift) {

                            $this->total_days = 0;
                        } else {

                            $this->total_days = 0.5;
                        }
                        $this->evaluateAlternativeFallbackBalances();
                        return;
                    }
                }


                $this->total_days = 0.5;
                $this->evaluateAlternativeFallbackBalances();
                return;
            }


            if (isset($publicHolidaysCollection[$formattedDateString])) {
                $publicHolidayRecord = $publicHolidaysCollection[$formattedDateString];

                if ($publicHolidayRecord->holiday_type === 'Full Day') {
                    continue;
                }

                if ($publicHolidayRecord->holiday_type === 'Half Day') {
                    $currentDayWeightFactor -= 0.5;
                }
            }

            if ($currentDayWeightFactor > 0) {
                $totalCalculatedBusinessDaysCount += $currentDayWeightFactor;
            }
        }

        $this->total_days = $totalCalculatedBusinessDaysCount;
        $this->evaluateAlternativeFallbackBalances();
    }
    public function cancelLeaveRequest($requestId)
    {
        $leaveRequest = LeaveRequest::find($requestId);

        if ($leaveRequest && str_contains($leaveRequest->status, 'Pending')) {
            $leaveRequest->update([
                'status' => 'Cancelled'
            ]);

            session()->flash('formSuccessFeedback', 'Leave request cancelled successfully.');
        }
    }

    public function confirmCancelLeaveRequest()
    {
        if ($this->cancel_request_id) {
            $leaveRequest = LeaveRequest::find($this->cancel_request_id);


            if ($leaveRequest && str_contains($leaveRequest->status, 'Pending')) {
                $leaveRequest->update([
                    'status' => 'Cancelled'
                ]);

                session()->flash('formSuccessFeedback', 'Leave request cancelled successfully.');
            }
        }

        $this->closeCancelModal();
    }

    public function selectLeaveType($id, $name)
    {
        $this->leave_type_id = $id;
        $this->individualLeaveTypeSearch = $name;
        $this->isLeaveTypeDropdownOpen = false;

        $leaveType = LeaveType::find($id);

        $this->isAttachmentRequired = $leaveType ? (bool)$leaveType->requires_attachment : false;
        $this->fetchAvailableLeaveBalance();
    }



    private function fetchAvailableLeaveBalance()
    {
        if ($this->leave_type_id) {
            $currentAcademicYear = Carbon::now()->year;

            $authenticatedEmployeeId = Auth::user()->employee->id ?? Auth::id();

            $leaveBalanceRecord = LeaveBalance::where('employee_id', $authenticatedEmployeeId)
                ->where('leave_type_id', $this->leave_type_id)
                ->where('year', $currentAcademicYear)
                ->first();

            if ($leaveBalanceRecord) {

                $this->availableLeaveBalance = $leaveBalanceRecord->allocated_days - $leaveBalanceRecord->used_days;
            } else {
                $this->availableLeaveBalance = 0;
            }
        }
    }


    /**
     * Toggles the visibility of the application modal via pure Livewire state
     */
    public function toggleApplicationModal()
    {
        $this->isApplicationModalOpen = !$this->isApplicationModalOpen;

        if (!$this->isApplicationModalOpen) {

            $this->isSplitConfirmationModalOpen = false;
            $this->resetErrorBag();

            $this->reset([

                'isEditMode',
                'editingLeaveRequestId',
                'existing_attachment_path',

                'leave_type_id',
                'start_date',
                'end_date',
                'reason',
                'total_days',
                'availableLeaveBalance',
                'attachment',
                'isAttachmentRequired',
                'individualLeaveTypeSearch',
                'is_half_day',
                'leave_shift',
                'allocatedFallbackDaysArray',
                'unassignedFallbackDaysRemaining'
            ]);
        }
    }

    /**
     * Validates and inserts the leave request into the database with Auto-Split Paid/No-Pay logic
     */

    public function submitLeaveApplication()
    {
        // 1. Dynamic Validation Rules Definition
        $formValidationRules = [
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date'    => 'required|date',
            'end_date'      => 'required|date|after_or_equal:start_date',
            'reason'        => 'required|string|min:10',
            'attachment'    => $this->isAttachmentRequired ? 'required|file|mimes:pdf,jpg,jpeg,png|max:2048' : 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'is_half_day'   => 'required|boolean',
            'leave_shift'   => 'required_if:is_half_day,true|nullable|string|in:Morning,Evening',
        ];

        $this->validate($formValidationRules);

        if ($this->total_days <= 0) {
            $this->addError('total_days', 'The selected date range contains no working days. Please choose valid working days.');
            return;
        }

        // Period lock: Disallow applying for leave in approved or paid payroll months
        $startMonth = substr($this->start_date, 0, 7);
        $endMonth = substr($this->end_date, 0, 7);

        $isPayrollLocked = Payroll::whereIn('payroll_month', array_unique([$startMonth, $endMonth]))
            ->whereIn('status', ['approved', 'paid'])
            ->exists();

        if ($isPayrollLocked) {
            $this->addError('start_date', 'Cannot apply for leave. Payroll for this period has already been approved/paid and locked.');
            return;
        }

        // 2. Multi-Split Automation Validation Layer
        $availablePrimaryBalance = $this->availableLeaveBalance ?? 0;
        $totalRequestedDays = $this->total_days;

        if ($totalRequestedDays > $availablePrimaryBalance) {
   
            $numericSanitizedValidationArray = array_map('floatval', $this->allocatedFallbackDaysArray);
            $totalUserAllocatedMatrixSum = (float) array_sum($numericSanitizedValidationArray);
            $requiredOverflowDaysAmount = $totalRequestedDays - $availablePrimaryBalance;

            if ($totalUserAllocatedMatrixSum !== (float) $requiredOverflowDaysAmount || $this->getErrorBag()->has('matrix_error')) {

                $this->isSplitConfirmationModalOpen = true;
                $this->addError('database_error', 'The split allocation sum must perfectly match the remaining requested days (' . $requiredOverflowDaysAmount . ' days).');
                return;
            }
        }

        // 3. Attachment Management Layer
        $attachmentPath = null;
        if ($this->attachment) {
            $attachmentPath = $this->attachment->store('leave_attachments', 'public');
        }

        // 4. Database Persistence Layer Wrapped inside atomic Transactions
        try {
            DB::transaction(function () use ($totalRequestedDays, $availablePrimaryBalance, $attachmentPath) {

                // Fetch context specific employee surrogate identity values
                $employeeId = Auth::user()->employee->id ?? null;

                if (!$employeeId) {
                    throw new \Exception("Authenticated user does not have a valid employee profile linked.");
                }

                // Calculate foundational structural fields before creation
                $calculatedNoPayDaysSum = (float) ($this->allocatedFallbackDaysArray['nopay'] ?? 0);
                $calculatedPaidDaysSum = $totalRequestedDays - $calculatedNoPayDaysSum;

                // Persist central metadata inside parent leave requests relation table
                $leaveRequestInstance = LeaveRequest::create([
                    'request_number'         => 'LRQ-' . strtoupper(uniqid()),
                    'employee_id'            => $employeeId,
                    'start_date'             => $this->start_date,
                    'end_date'               => $this->end_date,
                    'total_days'             => $totalRequestedDays,
                    'paid_days'              => $calculatedPaidDaysSum,
                    'no_pay_days'            => $calculatedNoPayDaysSum,
                    'reason'                 => $this->reason,
                    'attachment_path'        => $attachmentPath,
                    'status'                 => 'Pending Supervisor Approval',
                    'current_approval_level' => 0,
                    'is_half_day'            => $this->is_half_day,
                    'leave_shift'            => $this->leave_shift,
                ]);

                // A. Record primary allocated segment up to its maximum capacity bounds
                $primaryAllowedDaysAmount = min($totalRequestedDays, $availablePrimaryBalance);
                if ($primaryAllowedDaysAmount > 0) {
                    $leaveRequestInstance->applyLeaveTypes()->create([
                        'leave_type_id' => $this->leave_type_id,
                        'days_count'    => $primaryAllowedDaysAmount,
                    ]);
                }

                // B. Loop through the fallback dynamic array matrix to map secondary segment rows
                foreach ($this->allocatedFallbackDaysArray as $leaveTypeIdKey => $assignedDaysValue) {
                    if ((float) $assignedDaysValue <= 0) {
                        continue;
                    }

                    $resolvedLeaveTypeId = ($leaveTypeIdKey === 'nopay') ? null : $leaveTypeIdKey;

                    $leaveRequestInstance->applyLeaveTypes()->create([
                        'leave_type_id' => $resolvedLeaveTypeId,
                        'days_count'    => (float) $assignedDaysValue,
                    ]);
                }
            });

            session()->flash('formSuccessFeedback', 'Your leave application has been submitted successfully!');

            $this->isApplicationModalOpen = false;
            $this->isSplitConfirmationModalOpen = false;

            $this->resetErrorBag();

            // 5. Component State Reset Sequence
            $this->reset([
                'leave_type_id',
                'start_date',
                'end_date',
                'reason',
                'total_days',
                'availableLeaveBalance',
                'attachment',
                'isAttachmentRequired',
                'individualLeaveTypeSearch',
                'is_half_day',
                'leave_shift',
                'allocatedFallbackDaysArray',
                'unassignedFallbackDaysRemaining'
            ]);
        } catch (\Exception $databaseException) {
            $this->addError('database_error', 'Database Operations Failure: ' . $databaseException->getMessage());
            return;
        }
    }

    public function resubmitLeaveRequest()
    {
        $formValidationRules = [
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date'    => 'required|date',
            'end_date'      => 'required|date|after_or_equal:start_date',
            'reason'        => 'required|string|min:10',
            'is_half_day'   => 'required|boolean',
            'leave_shift'   => 'required_if:is_half_day,true|nullable|string|in:Morning,Evening',
        ];

        $this->validate($formValidationRules);

        if (!$this->editingLeaveRequestId) {
            return;
        }

        if ($this->total_days <= 0) {
            $this->addError('total_days', 'The selected date range contains no working days.');
            return;
        }

        // Period lock: Disallow applying for leave in approved or paid payroll months
        $startMonth = substr($this->start_date, 0, 7);
        $endMonth = substr($this->end_date, 0, 7);

        $isPayrollLocked = Payroll::whereIn('payroll_month', array_unique([$startMonth, $endMonth]))
            ->whereIn('status', ['approved', 'paid'])
            ->exists();

        if ($isPayrollLocked) {
            $this->addError('start_date', 'Cannot apply for leave. Payroll for this period has already been approved/paid and locked.');
            return;
        }

        $availablePrimaryBalance = $this->availableLeaveBalance ?? 0;
        $totalRequestedDays = $this->total_days;

        if ($totalRequestedDays > $availablePrimaryBalance) {
            $numericSanitizedValidationArray = array_map('floatval', $this->allocatedFallbackDaysArray);
            $totalUserAllocatedMatrixSum = (float) array_sum($numericSanitizedValidationArray);
            $requiredOverflowDaysAmount = $totalRequestedDays - $availablePrimaryBalance;

            if ($totalUserAllocatedMatrixSum !== (float) $requiredOverflowDaysAmount || $this->getErrorBag()->has('matrix_error')) {
                $this->isSplitConfirmationModalOpen = true;
                $this->addError('database_error', 'The split allocation sum must perfectly match the remaining requested days (' . $requiredOverflowDaysAmount . ' days).');
                return;
            }
        }

        try {
            DB::transaction(function () use ($totalRequestedDays, $availablePrimaryBalance) {

                $oldLeaveRequest = LeaveRequest::with('applyLeaveTypes.leaveType')->findOrFail($this->editingLeaveRequestId);

                $oldSplitDetailsArray = [];
                foreach ($oldLeaveRequest->applyLeaveTypes as $splitItem) {
                    $oldSplitDetailsArray[] = [
                        'leave_type_id'   => $splitItem->leave_type_id,
                        'leave_type_name' => $splitItem->leaveType->name ?? 'No-Pay',
                        'days_count'      => $splitItem->days_count
                    ];
                }

                // Backup to History Table
                DB::table('leave_request_histories')->insert([
                    'leave_request_id'     => $oldLeaveRequest->id,
                    'version_number'       => DB::table('leave_request_histories')->where('leave_request_id', $oldLeaveRequest->id)->count() + 1,
                    'leave_type_id'        => $oldLeaveRequest->applyLeaveTypes()->whereNotNull('leave_type_id')->first()?->leave_type_id,
                    'employee_id'          => $oldLeaveRequest->employee_id,
                    'start_date'           => $oldLeaveRequest->start_date,
                    'end_date'             => $oldLeaveRequest->end_date,
                    'is_half_day'          => $oldLeaveRequest->is_half_day,
                    'leave_shift'          => $oldLeaveRequest->leave_shift,
                    'total_days'           => $oldLeaveRequest->total_days,
                    'paid_days'            => $oldLeaveRequest->paid_days,
                    'no_pay_days'          => $oldLeaveRequest->no_pay_days,
                    'reason'               => $oldLeaveRequest->reason,
                    'attachment_path'      => $oldLeaveRequest->attachment_path,
                    'status'               => $oldLeaveRequest->status,
                    'split_details_json'   => json_encode($oldSplitDetailsArray),
                    'rejection_remarks'    => $oldLeaveRequest->logs()->latest()->first()?->remarks ?? null,
                    'created_at'           => now(),
                    'updated_at'           => now(),
                ]);


                $finalAttachmentPath = $this->existing_attachment_path;

                if ($this->attachment) {

                    $finalAttachmentPath = $this->attachment->store('leave-attachments', 'public');
                }

                $calculatedNoPayDaysSum = (float) ($this->allocatedFallbackDaysArray['nopay'] ?? 0);
                $calculatedPaidDaysSum = $totalRequestedDays - $calculatedNoPayDaysSum;

                // Update Main Table
                $oldLeaveRequest->update([
                    'start_date'             => $this->start_date,
                    'end_date'               => $this->end_date,
                    'total_days'             => $totalRequestedDays,
                    'paid_days'              => $calculatedPaidDaysSum,
                    'no_pay_days'            => $calculatedNoPayDaysSum,
                    'reason'                 => $this->reason,
                    'attachment_path'        => $finalAttachmentPath,
                    'status'                 => 'Pending Supervisor Approval',
                    'current_approval_level' => 0,
                    'is_half_day'            => $this->is_half_day,
                    'leave_shift'            => $this->leave_shift,
                ]);

                // Split Update Logic
                $oldLeaveRequest->applyLeaveTypes()->delete();

                $primaryAllowedDaysAmount = min($totalRequestedDays, $availablePrimaryBalance);
                if ($primaryAllowedDaysAmount > 0) {
                    $oldLeaveRequest->applyLeaveTypes()->create([
                        'leave_type_id' => $this->leave_type_id,
                        'days_count'    => $primaryAllowedDaysAmount,
                    ]);
                }

                foreach ($this->allocatedFallbackDaysArray as $leaveTypeIdKey => $assignedDaysValue) {
                    if ((float) $assignedDaysValue <= 0) {
                        continue;
                    }
                    $resolvedLeaveTypeId = ($leaveTypeIdKey === 'nopay') ? null : $leaveTypeIdKey;

                    $oldLeaveRequest->applyLeaveTypes()->create([
                        'leave_type_id' => $resolvedLeaveTypeId,
                        'days_count'    => (float) $assignedDaysValue,
                    ]);
                }
            });

            session()->flash('formSuccessFeedback', 'Your leave request has been updated and re-submitted successfully!');

            // State Resetting
            $this->isApplicationModalOpen = false;
            $this->isSplitConfirmationModalOpen = false;
            $this->isEditMode = false;
            $this->editingLeaveRequestId = null;
            $this->resetErrorBag();

            $this->reset([
                'leave_type_id',
                'start_date',
                'end_date',
                'reason',
                'total_days',
                'availableLeaveBalance',
                'attachment',
                'existing_attachment_path',
                'isAttachmentRequired',
                'individualLeaveTypeSearch',
                'is_half_day',
                'leave_shift',
                'allocatedFallbackDaysArray',
                'unassignedFallbackDaysRemaining'
            ]);
        } catch (\Exception $e) {
            $this->addError('database_error', 'Re-submission Failure: ' . $e->getMessage());
            return;
        }
    }

    /**
     * Helper to get the current authenticated employee ID
     */
    private function getEmployeeId()
    {
        return Auth::user()->employee->id ?? null;
    }

    /**
     * Fetches current year leave balances for the summary cards
     */
    private function getLeaveBalances($employeeId)
    {
        if (!$employeeId) return [];

        return LeaveBalance::with(['leaveType'])
            ->where('employee_id', $employeeId)
            ->where('year', Carbon::now()->year)
            ->get();
    }



    /**
     * Fetches and paginates the employee's leave history records
     */
    private function getLeaveHistoryPaginated($employeeId)
    {

        if (!$employeeId) {
            return new \Illuminate\Pagination\LengthAwarePaginator([], 0, 5);
        }


        return LeaveRequest::with(['applyLeaveTypes.leaveType'])
            ->where('employee_id', $employeeId)
            ->latest()
            ->paginate(8, ['*'], 'historyPage');
    }


    /**
     * Fetches filtered leave types based on search input
     */
    private function getFilteredLeaveTypesList()
    {
        $employeeId = $this->getEmployeeId();

        if (!$employeeId) {
            return collect();
        }


        return LeaveType::whereHas('leaveBalances', function ($query) use ($employeeId) {
            $query->where('employee_id', $employeeId)
                ->where('year', \Carbon\Carbon::now()->year);
        })
            ->where('name', 'like', '%' . $this->individualLeaveTypeSearch . '%')
            ->get();
    }


    private function fetchActiveLeaveDetailsWithAuditLogs(): ?LeaveRequest
    {
        // Guard Clause: Bypasses execution loop if no request entity sequence identifier has been committed
        if (!$this->selectedLeaveId) {
            return null;
        }
        $leaveApplicationDetails = LeaveRequest::with(['applyLeaveTypes.leaveType', 'logs'])->find($this->selectedLeaveId);

        if ($leaveApplicationDetails) {
            // Compute and push the trailing logs commentary item as a dynamic property runtime attribute
            $latestLogEntry = $leaveApplicationDetails->logs()->latest()->first();
            $leaveApplicationDetails->latest_rejection_remark = $latestLogEntry->remarks ?? null;
        }

        return $leaveApplicationDetails;
    }

    private function evaluateAlternativeFallbackBalances(): void
    {

        $authenticatedEmployeeId = $this->getEmployeeId();
        $currentAcademicYear = \Carbon\Carbon::now()->year;

        if (!$authenticatedEmployeeId) {
            $this->alternativePaidLeaveTypes = [];
            return;
        }


        if ($this->total_days > ($this->availableLeaveBalance ?? 0.0)) {


            $this->alternativePaidLeaveTypes = LeaveBalance::with('leaveType')
                ->where('employee_id', $authenticatedEmployeeId)
                ->where('leave_type_id', '!=', $this->leave_type_id)
                ->where('year', $currentAcademicYear)
                ->get()
                ->map(function ($balanceRecord) {

                    $actualBalance = $balanceRecord->allocated_days - $balanceRecord->used_days;

                    return [
                        'id' => $balanceRecord->leave_type_id,
                        'name' => $balanceRecord->leaveType->name ?? 'Unknown',
                        'balance' => $actualBalance
                    ];
                })

                ->filter(fn($type) => $type['balance'] > 0)
                ->values()
                ->toArray();
        } else {

            $this->alternativePaidLeaveTypes = [];
        }
    }
    /**
     * Renders the corresponding blade view
     */

    public function render()
    {

        $authenticatedEmployeeId = $this->getEmployeeId();

        $selectedLeaveApplicationInstance = $this->fetchActiveLeaveDetailsWithAuditLogs();

        return view('livewire.LeaveManagement.leave-apply-form', [
            'leaveBalances'          => $this->getLeaveBalances($authenticatedEmployeeId),
            'leaveHistoryCollection' => $this->getLeaveHistoryPaginated($authenticatedEmployeeId),
            'filteredLeaveTypes'     => $this->getFilteredLeaveTypesList(),
            'selectedLeaveDetails'   => $selectedLeaveApplicationInstance,
            'latestRejectionRemark'  => $selectedLeaveApplicationInstance ? ($selectedLeaveApplicationInstance->latest_rejection_remark ?? null) : null
        ]);
    }
}
