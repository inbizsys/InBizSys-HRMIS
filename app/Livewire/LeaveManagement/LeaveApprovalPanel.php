<?php

namespace App\Livewire\LeaveManagement;

use Livewire\Component;
use App\Models\LeaveRequest;
use App\Models\LeaveBalance;
use App\Models\LeaveLog;
use App\Models\User;
use App\Models\Employee;
use App\Models\Payroll;
use App\Models\Attendance;
use App\Models\PublicHoliday;
use App\Models\CompanyCalendarSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;

#[Layout('layouts.app')]
class LeaveApprovalPanel extends Component
{
    use WithPagination;

    public string $activeTabWorkflowState = 'pending_supervisor';

    public bool $isRejectionModalWindowOpen = false;
    public bool $isDetailsModalOpen = false;
    public bool $isAttachmentPreviewModalOpen = false;

    public ?int $targetLeaveRequestIdBeingProcessed = null;
    public string $rejectionReasonInputText = '';
    public ?LeaveRequest $selectedRequestForDetails = null;

    public $selectedEmployeeBalances = [];
    public int $noPayDaysCalculated = 0;
    public ?string $currentActiveAttachmentSecureUrl = null;
    public string $currentActiveAttachmentExtension = '';

    public string $searchQuery = '';
    public bool $isMainDropdownOpen = false;

    // Standalone tracking variables to hold historical rejection data without wiping during live wire hydration cycles
    public ?string $modalRejectionRemark = null;
    public ?string $modalRejectionAction = null;
    public ?string $modalRejectionDate = null;

    public string $searchDate = '';

    /**
     * Resets pagination overlay stack when user mutates target date criteria inputs.
     */
    public function updatingSearchDate(): void
    {
        $this->resetPage();
    }

    /**
     * Extracts secure file specifications to mount the inline attachment browser experience.
     */
    public function openAttachmentPreviewWorkflow(): void
    {
        if ($this->selectedRequestForDetails && $this->selectedRequestForDetails->attachment_path) {
            $this->currentActiveAttachmentSecureUrl = asset('storage/' . $this->selectedRequestForDetails->attachment_path);
            $this->currentActiveAttachmentExtension = strtolower(pathinfo($this->selectedRequestForDetails->attachment_path, PATHINFO_EXTENSION));
            $this->isAttachmentPreviewModalOpen = true;
        }
    }

    /**
     * Resets pagination sequence whenever employee text search patterns undergo text changes.
     */
    public function updatingSearchQuery(): void
    {
        $this->resetPage();
    }

    /**
     * Locks down selection context from search suggestion panels.
     */
    public function selectFromMainSearch(string $employeeName): void
    {
        $this->searchQuery = $employeeName;
        $this->isMainDropdownOpen = false;
    }

    /**
     * Wipes overlay metrics context upon closing asset preview modes.
     */
    public function closeAttachmentPreviewWorkflow(): void
    {
        $this->isAttachmentPreviewModalOpen = false;
        $this->currentActiveAttachmentSecureUrl = null;
    }

    /**
     * Evaluates leave requests state to progress validation workflows across organization matrix levels.
     */
    public function approveLeaveApplicationRequest(int $leaveRequestId): void
    {
        $targetLeaveRequestInstance = LeaveRequest::with('leaveType')->findOrFail($leaveRequestId);
        $authenticatedUserRecord = User::find(Auth::id());
        $currentEmployeeRecord = Employee::where('user_id', Auth::id())->first();

        // Period lock: Block approval if payroll is already approved or paid
        $startMonth = substr($targetLeaveRequestInstance->start_date, 0, 7);
        $endMonth = substr($targetLeaveRequestInstance->end_date, 0, 7);

        $isPayrollLocked = Payroll::whereIn('payroll_month', array_unique([$startMonth, $endMonth]))
            ->whereIn('status', ['approved', 'paid'])
            ->exists();

        if ($isPayrollLocked) {
            session()->flash('panelErrorFeedback', 'Cannot approve leave. Payroll for this period has already been approved/paid and locked. Please reverse the payroll first.');
            return;
        }

        /** @var \App\Models\User $authenticatedUserInstance */
        $authenticatedUserInstance = Auth::user();
        $isSuperAdmin = $authenticatedUserInstance->hasRole('super-admin') || Auth::id() === 1;

        if ($targetLeaveRequestInstance->status === 'Pending Supervisor Approval') {

            $actualAppliedLeaveType = $targetLeaveRequestInstance->applyLeaveTypes()
                ->whereNotNull('leave_type_id')
                ->first()?->leaveType;

            $autoEscalateDaysBoundary = $actualAppliedLeaveType ? $actualAppliedLeaveType->auto_escalate_above_days : 0;
            $requiredApprovalLevels = $actualAppliedLeaveType ? $actualAppliedLeaveType->required_approval_levels : 1;

            $isEscalationRequiredToHr = ($targetLeaveRequestInstance->total_days > $autoEscalateDaysBoundary)
                || ($requiredApprovalLevels > 1);

            DB::transaction(function () use ($targetLeaveRequestInstance, $authenticatedUserRecord, $isEscalationRequiredToHr, $currentEmployeeRecord, $isSuperAdmin) {
                if ($isEscalationRequiredToHr) {
                    $targetLeaveRequestInstance->update([
                        'status' => 'Pending HR Approval',
                        'current_approval_level' => 1
                    ]);


                    LeaveLog::create([
                        'leave_request_id' => $targetLeaveRequestInstance->id,
                        'employee_id'      => $targetLeaveRequestInstance->employee_id,
                        'action'           => 'Approved',
                        'performed_by'     => $currentEmployeeRecord ? $currentEmployeeRecord->id : $targetLeaveRequestInstance->employee_id,
                        'remarks'          => "Approved By: {$authenticatedUserRecord->name}. (Supervisor Level - Forwarded to HR. Application metrics exceeded single-level threshold rules)."
                    ]);
                } else {
                    $this->executeFinalApprovalSequence($targetLeaveRequestInstance, $authenticatedUserRecord);
                }
            });

            $this->closeDetailsModal();
            session()->flash('panelSuccessFeedback', 'Leave application successfully approved and advanced.');
            return;
        }

        if ($targetLeaveRequestInstance->status === 'Pending HR Approval') {
            $this->executeFinalApprovalSequence($targetLeaveRequestInstance, $authenticatedUserRecord);

            $this->closeDetailsModal();
            session()->flash('panelSuccessFeedback', 'Leave application has been fully approved and closed.');
        }
    }

    /**
     * Builds structured data matrices containing details about previous leaves and balances.
     */
    public function viewLeaveApplicationDetails(int $leaveRequestId): void
    {
        $this->selectedRequestForDetails = LeaveRequest::with([
            'employee.user',
            'leaveType',
            'applyLeaveTypes.leaveType'
        ])->findOrFail($leaveRequestId);

        $latestRejectionLog = LeaveLog::where('leave_request_id', $leaveRequestId)
            ->latest('id')
            ->first();

        if ($latestRejectionLog && !empty($latestRejectionLog->remarks)) {
            $this->modalRejectionRemark = $latestRejectionLog->remarks;
            $this->modalRejectionAction = $latestRejectionLog->action;
            $this->modalRejectionDate   = $latestRejectionLog->created_at;
        } else {
            $this->modalRejectionRemark = null;
            $this->modalRejectionAction = null;
            $this->modalRejectionDate   = null;
        }

        $targetYear = \Carbon\Carbon::parse($this->selectedRequestForDetails->start_date)->year;
        $employeeInstance = Employee::find($this->selectedRequestForDetails->employee_id);
        $this->noPayDaysCalculated = 0;
        $this->selectedEmployeeBalances = [];

        if ($employeeInstance) {
            $balancesCollection = LeaveBalance::with('leaveType')
                ->where('employee_id', $employeeInstance->id)
                ->where('year', $targetYear)
                ->get();

            $mappedBalances = $balancesCollection->map(function ($balanceItem) {
                return [
                    'leave_type_name'    => $balanceItem->leaveType->name ?? 'N/A',
                    'leave_type_id'      => $balanceItem->leave_type_id,
                    'allocated_days'     => $balanceItem->allocated_days ?? 0,
                    'used_days'          => $balanceItem->used_days ?? 0,
                    'computed_remaining' => ($balanceItem->allocated_days ?? 0) - ($balanceItem->used_days ?? 0),
                    'is_current_type'    => false,
                    'is_shortage'        => false,
                ];
            })->toArray();

            if ($this->selectedRequestForDetails->applyLeaveTypes && $this->selectedRequestForDetails->applyLeaveTypes->isNotEmpty()) {
                foreach ($this->selectedRequestForDetails->applyLeaveTypes as $splitItem) {
                    foreach ($mappedBalances as &$balanceRow) {
                        if ($balanceRow['leave_type_id'] == $splitItem->leave_type_id) {
                            $balanceRow['is_current_type'] = true;
                            if (($splitItem->days_count ?? 0) > $balanceRow['computed_remaining']) {
                                $balanceRow['is_shortage'] = true;
                                $this->noPayDaysCalculated += (($splitItem->days_count ?? 0) - $balanceRow['computed_remaining']);
                            }
                        }
                    }
                }
            } else {
                $actualDetailsLeaveTypeId = $this->selectedRequestForDetails->leave_type_id;
                if ($actualDetailsLeaveTypeId) {
                    foreach ($mappedBalances as &$balanceRow) {
                        if ($balanceRow['leave_type_id'] == $actualDetailsLeaveTypeId) {
                            $balanceRow['is_current_type'] = true;
                            if ($this->selectedRequestForDetails->total_days > $balanceRow['computed_remaining']) {
                                $balanceRow['is_shortage'] = true;
                                $this->noPayDaysCalculated = $this->selectedRequestForDetails->total_days - $balanceRow['computed_remaining'];
                            }
                        }
                    }
                }
            }
            $this->selectedEmployeeBalances = $mappedBalances;
        }

        $this->isDetailsModalOpen = true;
    }

    /**
     * Clears overlay modal properties context inside lifecycle component stack tree.
     */
    public function closeDetailsModal(): void
    {
        $this->reset([
            'isDetailsModalOpen',
            'selectedRequestForDetails',
            'selectedEmployeeBalances',
            'noPayDaysCalculated',
            'modalRejectionRemark',
            'modalRejectionAction',
            'modalRejectionDate'
        ]);
    }


    private function executeFinalApprovalSequence(LeaveRequest $leaveRequestInstance, ?User $userRecord): void
    {
        DB::transaction(function () use ($leaveRequestInstance, $userRecord) {
            $targetLeaveYearContext = Carbon::parse($leaveRequestInstance->start_date)->year;
            $totalAccumulatedPaidDeductions = 0;
            $totalAccumulatedNoPayDeductions = 0;

            // Fetch real dynamic split mapping associations stored within requested reference context
            $appliedSplitItemRows = $leaveRequestInstance->applyLeaveTypes;

            if ($appliedSplitItemRows && $appliedSplitItemRows->isNotEmpty()) {
                // Loop explicitly across each separate item split selected dynamically by the employee profile
                foreach ($appliedSplitItemRows as $splitRowItem) {
                    $currentIterationLeaveTypeId = $splitRowItem->leave_type_id;
                    $currentIterationRequestedDays = $splitRowItem->days_count ?? 0;

                    if ($currentIterationLeaveTypeId) {
                        $employeeBalanceRecord = LeaveBalance::where('employee_id', $leaveRequestInstance->employee_id)
                            ->where('leave_type_id', $currentIterationLeaveTypeId)
                            ->where('year', $targetLeaveYearContext)
                            ->first();

                        if ($employeeBalanceRecord) {
                            $remainingAvailableDays = ($employeeBalanceRecord->allocated_days ?? 0) - ($employeeBalanceRecord->used_days ?? 0);
                            if ($remainingAvailableDays < 0) {
                                $remainingAvailableDays = 0;
                            }

                            if ($currentIterationRequestedDays > $remainingAvailableDays) {
                                $paidDaysDeductibleChunk = $remainingAvailableDays;
                                $noPayDaysChunk = $currentIterationRequestedDays - $remainingAvailableDays;
                            } else {
                                $paidDaysDeductibleChunk = $currentIterationRequestedDays;
                                $noPayDaysChunk = 0;
                            }

                            if ($paidDaysDeductibleChunk > 0) {
                                $employeeBalanceRecord->increment('used_days', $paidDaysDeductibleChunk);
                            }

                            $totalAccumulatedPaidDeductions += $paidDaysDeductibleChunk;
                            $totalAccumulatedNoPayDeductions += $noPayDaysChunk;
                        } else {
                            $totalAccumulatedNoPayDeductions += $currentIterationRequestedDays;
                        }
                    } else {
                        $totalAccumulatedNoPayDeductions += $currentIterationRequestedDays;
                    }
                }
            } else {
                // Fallback standard structure if no multi-type breakdown records are present inside parameters
                $fallbackLeaveTypeId = $leaveRequestInstance->leave_type_id;
                $fallbackTotalDays = $leaveRequestInstance->total_days;

                if ($fallbackLeaveTypeId) {
                    $employeeBalanceRecord = LeaveBalance::where('employee_id', $leaveRequestInstance->employee_id)
                        ->where('leave_type_id', $fallbackLeaveTypeId)
                        ->where('year', $targetLeaveYearContext)
                        ->first();

                    if ($employeeBalanceRecord) {
                        $remainingAvailableDays = ($employeeBalanceRecord->allocated_days ?? 0) - ($employeeBalanceRecord->used_days ?? 0);
                        if ($remainingAvailableDays < 0) {
                            $remainingAvailableDays = 0;
                        }

                        if ($fallbackTotalDays > $remainingAvailableDays) {
                            $totalAccumulatedPaidDeductions = $remainingAvailableDays;
                            $totalAccumulatedNoPayDeductions = $fallbackTotalDays - $remainingAvailableDays;
                        } else {
                            $totalAccumulatedPaidDeductions = $fallbackTotalDays;
                            $totalAccumulatedNoPayDeductions = 0;
                        }

                        if ($totalAccumulatedPaidDeductions > 0) {
                            $employeeBalanceRecord->increment('used_days', $totalAccumulatedPaidDeductions);
                        }
                    } else {
                        $totalAccumulatedNoPayDeductions = $fallbackTotalDays;
                    }
                } else {
                    $totalAccumulatedNoPayDeductions = $fallbackTotalDays;
                }
            }

            $leaveRequestInstance->update([
                'status' => 'Approved',
                'current_approval_level' => 2,
                'paid_days' => $totalAccumulatedPaidDeductions,
                'no_pay_days' => $totalAccumulatedNoPayDeductions
            ]);

            // Sync with Attendance Table (Update Absent / Half-day records to Leave)
            $leaveStartDate = Carbon::parse($leaveRequestInstance->start_date);
            $leaveEndDate = Carbon::parse($leaveRequestInstance->end_date);
            $isHalfDayLeave = (bool) ($leaveRequestInstance->is_half_day ?? false);
            $paidDaysRemaining = (float) $totalAccumulatedPaidDeductions;

            $dateCursor = $leaveStartDate->copy();
            while ($dateCursor->lte($leaveEndDate)) {
                $currentDate = $dateCursor->toDateString();

                // Skip non-working days (weekend / public holiday)
                $publicHoliday = PublicHoliday::whereDate('holiday_date', $currentDate)->first();
                $calendarSetting = CompanyCalendarSetting::where('day_of_week', $dateCursor->format('l'))->first();
                $isCompanyWeekend = ($calendarSetting && (int) $calendarSetting->is_working_day === 0);

                if ($isCompanyWeekend || $publicHoliday) {
                    $dateCursor->addDay();
                    continue;
                }

                $attendanceRecord = Attendance::where('employee_id', $leaveRequestInstance->employee_id)
                    ->whereDate('date', $currentDate)
                    ->first();

                if ($isHalfDayLeave) {
                    if ($paidDaysRemaining >= 0.5) {
                        if ($attendanceRecord) {
                            if (in_array($attendanceRecord->status, ['absent', 'half_day_absent', 'pending_approval'])) {
                                $attendanceRecord->update([
                                    'status' => 'half_day',
                                    'reason' => 'Approved Half-Day Leave',
                                ]);
                            }
                        } else {
                            Attendance::create([
                                'employee_id' => $leaveRequestInstance->employee_id,
                                'shift_id'    => $leaveRequestInstance->employee?->shift_id,
                                'date'        => $currentDate,
                                'status'      => 'half_day',
                                'reason'      => 'Approved Half-Day Leave',
                            ]);
                        }
                        $paidDaysRemaining -= 0.5;
                    } else {
                        if ($attendanceRecord) {
                            $attendanceRecord->update([
                                'status' => 'half_day_absent',
                                'reason' => 'Approved No-Pay Half-Day Leave',
                            ]);
                        } else {
                            Attendance::create([
                                'employee_id' => $leaveRequestInstance->employee_id,
                                'shift_id'    => $leaveRequestInstance->employee?->shift_id,
                                'date'        => $currentDate,
                                'status'      => 'half_day_absent',
                                'reason'      => 'Approved No-Pay Half-Day Leave',
                            ]);
                        }
                    }
                } else {
                    if ($paidDaysRemaining >= 1.0) {
                        if ($attendanceRecord) {
                            if (in_array($attendanceRecord->status, ['absent', 'pending_approval'])) {
                                $attendanceRecord->update([
                                    'status' => 'leave',
                                    'reason' => 'Approved Leave (' . ($leaveRequestInstance->leaveType?->name ?? 'Leave') . ')',
                                ]);
                            }
                        } else {
                            Attendance::create([
                                'employee_id' => $leaveRequestInstance->employee_id,
                                'shift_id'    => $leaveRequestInstance->employee?->shift_id,
                                'date'        => $currentDate,
                                'status'      => 'leave',
                                'reason'      => 'Approved Leave (' . ($leaveRequestInstance->leaveType?->name ?? 'Leave') . ')',
                            ]);
                        }
                        $paidDaysRemaining -= 1.0;
                    } else {
                        if ($attendanceRecord) {
                            $attendanceRecord->update([
                                'status' => 'absent',
                                'reason' => 'Approved No-Pay Leave',
                            ]);
                        } else {
                            Attendance::create([
                                'employee_id' => $leaveRequestInstance->employee_id,
                                'shift_id'    => $leaveRequestInstance->employee?->shift_id,
                                'date'        => $currentDate,
                                'status'      => 'absent',
                                'reason'      => 'Approved No-Pay Leave',
                            ]);
                        }
                    }
                }

                $dateCursor->addDay();
            }

            $currentEmployeeRecord = Employee::where('user_id', Auth::id())->first();
            $officerNameString = $userRecord ? $userRecord->name : 'System Authorized Officer';

            LeaveLog::create([
                'leave_request_id' => $leaveRequestInstance->id,
                'employee_id'      => $leaveRequestInstance->employee_id,
                'action'           => 'Approved',
                'performed_by'     => $currentEmployeeRecord ? $currentEmployeeRecord->id : $leaveRequestInstance->employee_id,
                'remarks' => "Approved By: {$officerNameString} | Paid: {$totalAccumulatedPaidDeductions} Day(s), No-Pay: {$totalAccumulatedNoPayDeductions} Day(s)"
            ]);
        });
    }

    /**
     * Moves application parameters into an active tracking instance state to open rejection wizards.
     */
    public function initiateRejectionProcess(int $leaveRequestId): void
    {
        $this->targetLeaveRequestIdBeingProcessed = $leaveRequestId;
        $this->rejectionReasonInputText = '';
        $this->closeDetailsModal();
        $this->isRejectionModalWindowOpen = true;
    }


    public function executeRejectionSubmission(): void
    {
        $this->validate([
            'rejectionReasonInputText' => 'required|string|min:5'
        ]);

        $targetLeaveRequestInstance = LeaveRequest::findOrFail($this->targetLeaveRequestIdBeingProcessed);
        $authenticatedUserRecord = User::find(Auth::id());

        DB::transaction(function () use ($targetLeaveRequestInstance, $authenticatedUserRecord) {
            $targetLeaveRequestInstance->update([
                'status' => 'Rejected'
            ]);

            $currentEmployeeRecord = Employee::where('user_id', Auth::id())->first();
            $officerNameString = $authenticatedUserRecord ? $authenticatedUserRecord->name : 'System Authorized Officer';
            LeaveLog::create([
                'leave_request_id' => $targetLeaveRequestInstance->id,
                'employee_id'      => $targetLeaveRequestInstance->employee_id,
                'action'           => 'Rejected',
                'performed_by'     => $currentEmployeeRecord ? $currentEmployeeRecord->id : $targetLeaveRequestInstance->employee_id,
                'remarks'          => "Rejected By: {$officerNameString}. Reason: " . $this->rejectionReasonInputText
            ]);
        });

        $this->reset(['isRejectionModalWindowOpen', 'targetLeaveRequestIdBeingProcessed', 'rejectionReasonInputText']);
        $this->closeDetailsModal();

        session()->flash('panelSuccessFeedback', 'Leave application has been rejected. Balances remain unaffected.');
    }

    /**
     * Wipes reference attributes state to close input rejection windows safely.
     */
    public function dismissRejectionModal(): void
    {
        $this->reset(['isRejectionModalWindowOpen', 'targetLeaveRequestIdBeingProcessed', 'rejectionReasonInputText']);
    }

    /**
     * Evaluates active workflow visual filter tags to resolve target table status values.
     */
    private function resolveDatabaseStatusFilterString(): string
    {
        return match ($this->activeTabWorkflowState) {
            'pending_hr' => 'Pending HR Approval',
            'approved'   => 'Approved',
            'rejected'   => 'Rejected',
            default      => 'Pending Supervisor Approval',
        };
    }

    private function fetchFilteredLeaveRequests(): LengthAwarePaginator
    {
        $statusFilterString = $this->resolveDatabaseStatusFilterString();
        $baseQueryBuilder = LeaveRequest::with(['employee.user', 'leaveType', 'logs']);

        /** @var \App\Models\User $authenticatedUser */
        $authenticatedUser = Auth::user();

        $authenticatedEmployeeProfile = $authenticatedUser ? $authenticatedUser->employee : null;

        // Establish if the user has overarching system privileges bypassing strict limits
        $isSuperAdmin = $authenticatedUser && ($authenticatedUser->hasRole('super-admin') || $authenticatedUser->id === 1);

        // --- DEPARTMENT SCOPE LOGIC INJECTION MATRIX ---
        // Only apply department filtering if the user is NOT a Super Admin AND has the Supervisor role
        if (!$isSuperAdmin && $authenticatedUser && $authenticatedUser->hasRole('Supervisor') && $authenticatedEmployeeProfile && !empty($authenticatedEmployeeProfile->department_id)) {
            $baseQueryBuilder->whereHas('employee', function ($employeeScopeQuery) use ($authenticatedEmployeeProfile) {
                $employeeScopeQuery->where('department_id', $authenticatedEmployeeProfile->department_id);
            });
        }

        if (!empty(trim($this->searchQuery))) {
            $baseQueryBuilder->where(function ($searchScopeQuery) {
                $searchScopeQuery->whereHas('employee.user', function ($userConstraintQuery) {
                    $userConstraintQuery->where('name', 'like', '%' . $this->searchQuery . '%');
                });
            });
        }

        if (!empty($this->searchDate)) {
            try {
                $formattedSearchDate = \Carbon\Carbon::parse($this->searchDate)->format('Y-m-d');
                $baseQueryBuilder->where(function ($dateScopeQuery) use ($formattedSearchDate) {
                    $dateScopeQuery->whereDate('start_date', '<=', $formattedSearchDate)
                        ->whereDate('end_date', '>=', $formattedSearchDate);
                });
            } catch (\Exception $invalidDateException) {
                logger('Invalid date filter encounter: ' . $this->searchDate);
            }
        }


        if ($this->activeTabWorkflowState === 'rejected') {
            $logQueryBuilder = LeaveLog::with(['leaveRequest.employee.user', 'leaveRequest.leaveType'])
                ->where('action', 'like', '%Rejected%');

            // Apply identical super-admin safety bypass logic for historical logs tracking
            if (!$isSuperAdmin && $authenticatedUser && $authenticatedUser->hasRole('Supervisor') && $authenticatedEmployeeProfile && !empty($authenticatedEmployeeProfile->department_id)) {
                $logQueryBuilder->whereHas('leaveRequest.employee', function ($logEmployeeScopeQuery) use ($authenticatedEmployeeProfile) {
                    $logEmployeeScopeQuery->where('department_id', $authenticatedEmployeeProfile->department_id);
                });
            }

            if (!empty(trim($this->searchQuery))) {
                $logQueryBuilder->whereHas('leaveRequest.employee.user', function ($userSearchScopeQuery) {
                    $userSearchScopeQuery->where('name', 'like', '%' . $this->searchQuery . '%');
                });
            }

            if (!empty($this->searchDate)) {
                try {
                    $formattedSearchDate = \Carbon\Carbon::parse($this->searchDate)->format('Y-m-d');
                    $logQueryBuilder->whereHas('leaveRequest', function ($reqDateQuery) use ($formattedSearchDate) {
                        $reqDateQuery->whereDate('start_date', '<=', $formattedSearchDate)
                            ->whereDate('end_date', '>=', $formattedSearchDate);
                    });
                } catch (\Exception $sequentialInputException) {
                }
            }

            $paginatedLogsCollection = $logQueryBuilder->latest('id')->paginate(10);

            $paginatedLogsCollection->getCollection()->transform(function ($logItemRow) {
                $originalRequest = $logItemRow->leaveRequest;
                if ($originalRequest) {
                    $clonedRequestInstance = clone $originalRequest;
                    $clonedRequestInstance->unique_rejection_remarks = $logItemRow->remarks;
                    $clonedRequestInstance->unique_rejection_action = $logItemRow->action;

                    $clonedRequestInstance->log_id = $logItemRow->id;

                    return $clonedRequestInstance;
                }
                return null;
            });

            return $paginatedLogsCollection;
        }

        $baseQueryBuilder->where('status', $statusFilterString);
        return $baseQueryBuilder->latest()->paginate(10);
    }

    /**
     * Resolves unique autocomplete listing strings targeting dropdown components interface.
     */
    private function fetchUniqueEmployeesForDropdown(): array
    {
        if (!$this->isMainDropdownOpen || empty(trim($this->searchQuery))) {
            return [];
        }


        // Initialize query builder instance targeting structural database interactions
        $dropdownQueryBuilder = LeaveRequest::with(['employee.user']);

        /** @var \App\Models\User $authenticatedUserInstance */
        $authenticatedUserInstance = Auth::user();

        $authenticatedEmployeeProfile = $authenticatedUserInstance ? $authenticatedUserInstance->employee : null;

        // Check for elevated global permissions profiles to bypass department boundaries
        $isSuperAdmin = $authenticatedUserInstance && ($authenticatedUserInstance->hasRole('super-admin') || $authenticatedUserInstance->id === 1);

        // --- SCOPE ISOLATION INJECTION PANEL ---
        // Ensure dropdown auto-complete fields respect identical structural rules mapped across core workflows
        if (!$isSuperAdmin && $authenticatedUserInstance && $authenticatedUserInstance->hasRole('Supervisor') && $authenticatedEmployeeProfile && !empty($authenticatedEmployeeProfile->department_id)) {
            $dropdownQueryBuilder->whereHas('employee', function ($dropdownEmployeeScopeQuery) use ($authenticatedEmployeeProfile) {
                $dropdownEmployeeScopeQuery->where('department_id', $authenticatedEmployeeProfile->department_id);
            });
        }

        // Return compiled formatted list payload variables to rendering layout layers
        return $dropdownQueryBuilder->whereHas('employee.user', function ($userFilterQuery) {
            $userFilterQuery->where('name', 'like', '%' . $this->searchQuery . '%');
        })
            ->get()
            ->pluck('employee.user.name')
            ->unique()
            ->take(5)
            ->toArray();
    }

    /**
     * Isolated view renderer entrypoint keeping rendering definitions completely clean of nested core queries.
     */
    public function render(): View
    {
        return view('livewire.LeaveManagement.leave-approval-panel', [
            'leaveRequests'             => $this->fetchFilteredLeaveRequests(),
            'uniqueEmployeesCollection' => $this->fetchUniqueEmployeesForDropdown()
        ]);
    }
}
