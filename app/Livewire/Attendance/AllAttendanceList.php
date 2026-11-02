<?php

namespace App\Livewire\Attendance;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Department;
use App\Models\AttendanceApproval;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\View\View;
use App\Services\AttendanceCheckService;
use App\Models\Payroll;

class AllAttendanceList extends Component
{
    use WithPagination;

    // Section 1: Filters & Search Properties
    public string $startDate = '';
    public string $endDate = '';
    public string $selectedDepartmentId = '';
    public string $searchQuery = '';
    public string $selectedStatus = '';

    // Section 4: Modals Properties
    public bool $isViewModalOpen = false;
    public bool $isEditModalOpen = false;
    public ?Attendance $selectedAttendanceRecord = null;
    public int $viewRecordEarlyMins = 0;

    // Edit Modal Form Fields
    public ?int $editingAttendanceId = null;
    public ?string $editDate = null;
    public ?string $editInTime = null;
    public ?string $editOutTime = null;
    public ?string $editReason = null;
    public array $editBreaks = [];

    // Properties
    public bool $isConfirmModalOpen = false;
    public ?string $editEmployeeName = null;

    public bool $isViewRecordModalOpen = false;
    public ?Attendance $selectedRecordForView = null;
    public $selectedLeaveDetails = null;
    
    // Filter Pagination Reset 
    public function updatedStartDate(): void { $this->resetPage(); }
    public function updatedEndDate(): void { $this->resetPage(); }
    public function updatedSelectedDepartmentId(): void { $this->resetPage(); }
    public function updatedSearchQuery(): void { $this->resetPage(); }
    public function updatedSelectedStatus(): void { $this->resetPage(); }

    // For Dynamic Breaks and Break Types 
    public $breakTypes = [];

    public function mount(): void
    {
        // 1. Run the auto-absent check when the component is mounted
        (new AttendanceCheckService())->runAutoAbsentCheck();

        // 2. Default 
        $this->startDate = now()->toDateString();
        $this->endDate   = now()->toDateString();

        // 3. Database Get Dynamic Break Types From Database and load into the Component Property 
        $this->breakTypes = DB::table('break_types')
            ->select('id', 'name', 'code')
            ->get();
    }

    public function openEditModal(int $attendanceId): void
    {
        $attendance = Attendance::with(['employee', 'breaks'])->find($attendanceId);
        
        if ($attendance) {
            $this->editingAttendanceId = $attendance->id;
            // Period lock: Block if payroll is approved or paid
            $targetMonth = substr($attendance->date, 0, 7);
            $isLocked = Payroll::where('payroll_month', $targetMonth)
                ->whereIn('status', ['approved', 'paid'])
                ->exists();

            if ($isLocked) {
                session()->flash('errorMessage', 'Cannot edit attendance. Payroll for ' . date('F Y', strtotime($targetMonth . '-01')) . ' is approved/paid and locked.');
                return;
            }

            $this->editEmployeeName    = $attendance->employee ? $attendance->employee->first_name . ' ' . $attendance->employee->last_name : 'Employee';
            $this->editDate            = $attendance->date;
            $this->editInTime          = $attendance->in_time ? Carbon::parse($attendance->in_time)->format('H:i') : '';
            $this->editOutTime         = $attendance->out_time ? Carbon::parse($attendance->out_time)->format('H:i') : '';
            $this->editReason          = '';

            $this->editBreaks = $attendance->breaks->map(function ($breakItem) {
                return [
                    'break_type'  => $breakItem->break_type ?? '',
                    'break_start' => $breakItem->break_start ? Carbon::parse($breakItem->break_start)->format('H:i') : '',
                    'break_end'   => $breakItem->break_end ? Carbon::parse($breakItem->break_end)->format('H:i') : '',
                ];
            })->toArray();

            $this->isEditModalOpen    = true;
            $this->isConfirmModalOpen = false;
        }
    }

    public function addBreakRow(): void
    {
        $this->editBreaks[] = [
            'break_type'  => '',
            'break_start' => '',
            'break_end'   => '',
        ];
    }

    public function removeBreakRow(int $index): void
    {
        unset($this->editBreaks[$index]);
        $this->editBreaks = array_values($this->editBreaks);
    }

    public function prepareUpdateConfirmation(): void
    {
        $this->validate([
            'editReason' => 'required|string|min:3',
        ], [
            'editReason.required' => 'Please enter a reason for updating this record.',
        ]);

        $this->isConfirmModalOpen = true;
    }
    
    public function confirmAndUpdateAttendance(): void
    {
        if (!$this->editingAttendanceId) {
            return;
        }

        $attendance = Attendance::with(['shift', 'employee'])->find($this->editingAttendanceId);

        if ($attendance) {
            // Period lock: Double check before saving
            $targetMonth = substr($attendance->date, 0, 7);
            $isLocked = Payroll::where('payroll_month', $targetMonth)
                ->whereIn('status', ['approved', 'paid'])
                ->exists();

            if ($isLocked) {
                session()->flash('errorMessage', 'Cannot update attendance. Payroll for ' . date('F Y', strtotime($targetMonth . '-01')) . ' is approved/paid and locked.');
                $this->closeModals();
                return;
            }

            $dateStr       = Carbon::parse($attendance->date)->toDateString();
            $dayOfWeekName = Carbon::parse($attendance->date)->format('l');

            $fullInTime  = !empty($this->editInTime) ? Carbon::parse($dateStr . ' ' . $this->editInTime)->toDateTimeString() : null;
            $fullOutTime = !empty($this->editOutTime) ? Carbon::parse($dateStr . ' ' . $this->editOutTime)->toDateTimeString() : null;

            
            $shift = $attendance->shift;
            if (!$shift && !empty($attendance->employee?->shift_id)) {
                $shift = \App\Models\Shift::find($attendance->employee->shift_id);
            }

            
            $breakTypesMap = DB::table('break_types')
                ->get()
                ->keyBy(fn($item) => strtolower(trim($item->code ?? $item->name)));

          
            $attendance->breaks()->delete();
            $unpaidBreakMinutes = 0;

            foreach ($this->editBreaks as $brk) {
                if (!empty($brk['break_start']) && !empty($brk['break_end'])) {
                    $bStart = Carbon::parse($dateStr . ' ' . $brk['break_start']);
                    $bEnd   = Carbon::parse($dateStr . ' ' . $brk['break_end']);
                    
                    if ($bEnd->lessThan($bStart)) {
                        $bEnd->addDay();
                    }

                    $duration  = max(0, $bStart->diffInMinutes($bEnd));
                    $bTypeKey  = strtolower(trim($brk['break_type'] ?: 'general'));
                    $matchedBt = $breakTypesMap->get($bTypeKey);

                    $isPaid = $matchedBt !== null
                        ? (bool) $matchedBt->is_paid
                        : in_array($bTypeKey, ['tea', 'official']);

                    if (!$isPaid) {
                        $unpaidBreakMinutes += $duration;
                    }

                    $attendance->breaks()->create([
                        'employee_id'      => $attendance->employee_id,
                        'break_type'       => $brk['break_type'] ?: 'General',
                        'break_start'      => $bStart->toTimeString(),
                        'break_end'        => $bEnd->toTimeString(),
                        'duration_minutes' => $duration,
                        'is_paid'          => $isPaid,
                    ]);
                }
            }

          
            $totalWorkedHours = 0.00;
            $inCarbon         = $fullInTime ? Carbon::parse($fullInTime) : null;
            $outCarbon        = $fullOutTime ? Carbon::parse($fullOutTime) : null;

            if ($inCarbon && $outCarbon) {
                if ($outCarbon->lessThan($inCarbon)) {
                    $outCarbon->addDay();
                }
                $grossMinutes     = abs($outCarbon->diffInMinutes($inCarbon));
                $netMinutes       = max(0, $grossMinutes - $unpaidBreakMinutes);
                $totalWorkedHours = round($netMinutes / 60, 2);
            }

            // 3. Public Holiday 
            $publicHoliday = \App\Models\PublicHoliday::whereDate('holiday_date', $dateStr)->first();

            // 4. Company Calendar Setting and Shift-Based Working Day 
            $calendarSetting  = \App\Models\CompanyCalendarSetting::where('day_of_week', $dayOfWeekName)->first();
            $isCompanyWeekend = true;

            if ($calendarSetting && $calendarSetting->is_working_day) {
                if ($shift && !empty($shift->start_time) && !empty($shift->end_time)) {
                    $isCompanyWeekend = false; 
                } else {
                    $isCompanyWeekend = true;
                }
            } else {
                $isCompanyWeekend = true;
            }

            // 5. Expected Shift Start and End Time 
            $expectedStartTime = $shift?->start_time ?? '08:00:00';
            $expectedEndTime   = $shift?->end_time ?? '17:00:00';

            if (!$isCompanyWeekend && $calendarSetting && $calendarSetting->day_type === 'Half Day') {
                if (!empty($calendarSetting->half_day_end_time) && !empty($shift?->end_time)) {
                    if (Carbon::parse($shift->end_time)->lt(Carbon::parse($calendarSetting->half_day_end_time))) {
                        $expectedEndTime = $shift->end_time;
                    } else {
                        $expectedEndTime = $calendarSetting->half_day_end_time;
                    }
                } elseif (!empty($calendarSetting->half_day_end_time)) {
                    $expectedEndTime = $calendarSetting->half_day_end_time;
                }
            }

            $shiftStart = Carbon::parse($dateStr . ' ' . $expectedStartTime);
            $shiftEnd   = Carbon::parse($dateStr . ' ' . $expectedEndTime);
            if ($shiftEnd->lessThan($shiftStart)) {
                $shiftEnd->addDay();
            }

            // 6. alculate Overtime (OT Hours) 
            $calculatedOtHours = 0.00;
            if ($inCarbon && $outCarbon && $outCarbon->greaterThan($shiftEnd)) {
                $calculatedOtHours = round(abs($outCarbon->diffInMinutes($shiftEnd)) / 60, 2);
            }

            // 7. check leave details for the employee on that date
            $leaveDetails = DB::table('leave_requests')
                ->where('employee_id', $attendance->employee_id)
                ->whereIn('status', ['approved', 'worked_on_leave'])
                ->whereDate('start_date', '<=', $dateStr)
                ->whereDate('end_date', '>=', $dateStr)
                ->first();

            $isHalfDay  = $leaveDetails && (int) $leaveDetails->is_half_day === 1;
            $leaveShift = strtolower(trim((string) ($leaveDetails->leave_shift ?? '')));

            // 8. Status, Late Minutes and Early Minutes 
            $status       = 'absent';
            $lateMinutes  = 0;
            $earlyMinutes = 0;

            if ($inCarbon) {
                if ($publicHoliday !== null) {
                    $status       = 'holiday_present';
                    $lateMinutes  = 0;
                    $earlyMinutes = 0;
                } elseif ($isCompanyWeekend) {
                    $status       = 'weekend_present';
                    $lateMinutes  = 0;
                    $earlyMinutes = 0;
                } else {
                    $lateGraceMinutes  = (int) ($shift?->late_grace_minutes ?? 0);
                    $earlyGraceMinutes = (int) ($shift?->early_leave_grace_minutes ?? 0);
                    $halfDayHours      = !empty($shift?->half_day_min_hours) ? (float) $shift->half_day_min_hours : 1.0;
                    $halfDayMinutes    = (int) ($halfDayHours * 60);

                    $firstHalfEnd    = $shiftStart->copy()->addMinutes($halfDayMinutes);
                    $secondHalfStart = $shiftStart->copy()->addMinutes($halfDayMinutes);

                    $isWorkedDuringMorningLeave = ($isHalfDay && in_array($leaveShift, ['morning', 'first_half']) && $inCarbon->lessThan($secondHalfStart));

                    if ($isHalfDay && in_array($leaveShift, ['evening', 'second_half'])) {
                        if ($outCarbon && $outCarbon->greaterThan($firstHalfEnd)) {
                            if ($inCarbon->gt($shiftStart)) {
                                $rawDiff     = (int) $shiftStart->diffInMinutes($inCarbon);
                                $lateMinutes = ($rawDiff > $lateGraceMinutes) ? $rawDiff : 0;
                                $status      = ($lateMinutes > 0) ? 'late' : 'present';
                            } else {
                                $status = 'present';
                            }

                            if ($outCarbon->lessThan($shiftEnd)) {
                                $rawEarly     = (int) $outCarbon->diffInMinutes($shiftEnd);
                                $earlyMinutes = ($rawEarly > $earlyGraceMinutes) ? $rawEarly : 0;
                            }
                        } else {
                            if ($inCarbon->greaterThanOrEqualTo($firstHalfEnd)) {
                                $status      = 'half_day_absent';
                                $lateMinutes = 0;
                            } elseif ($inCarbon->gt($shiftStart)) {
                                $rawDiff     = (int) $shiftStart->diffInMinutes($inCarbon);
                                $lateMinutes = ($rawDiff > $lateGraceMinutes) ? $rawDiff : 0;
                                $status      = ($lateMinutes > 0) ? 'half_day_evening_late' : 'half_day_evening_present';
                            } else {
                                $status = 'half_day_evening_present';
                            }

                            if ($outCarbon && $status !== 'half_day_absent' && $outCarbon->lessThan($firstHalfEnd)) {
                                $rawEarly     = (int) $outCarbon->diffInMinutes($firstHalfEnd);
                                $earlyMinutes = ($rawEarly > $earlyGraceMinutes) ? $rawEarly : 0;
                            }
                        }
                    } elseif ($isHalfDay && in_array($leaveShift, ['morning', 'first_half']) && !$isWorkedDuringMorningLeave) {
                        if ($inCarbon->greaterThanOrEqualTo($shiftEnd)) {
                            $status      = 'half_day_absent';
                            $lateMinutes = 0;
                        } elseif ($inCarbon->gt($secondHalfStart)) {
                            $rawDiff     = (int) $secondHalfStart->diffInMinutes($inCarbon);
                            $lateMinutes = ($rawDiff > $lateGraceMinutes) ? $rawDiff : 0;
                            $status      = ($lateMinutes > 0) ? 'half_day_morning_late' : 'half_day_morning_present';
                        } else {
                            $status = 'half_day_morning_present';
                        }

                        if ($outCarbon && $status !== 'half_day_absent') {
                            if ($outCarbon->lessThanOrEqualTo($secondHalfStart)) {
                                $status       = 'half_day_absent';
                                $earlyMinutes = 0;
                            } elseif ($outCarbon->lessThan($shiftEnd)) {
                                $rawEarly     = (int) $outCarbon->diffInMinutes($shiftEnd);
                                $earlyMinutes = ($rawEarly > $earlyGraceMinutes) ? $rawEarly : 0;
                            }
                        }
                    } else {
                        if ($isWorkedDuringMorningLeave && $outCarbon && $outCarbon->lessThanOrEqualTo($secondHalfStart)) {
                            $status       = 'half_day_absent';
                            $lateMinutes  = 0;
                            $earlyMinutes = 0;
                        } else {
                            if ($inCarbon->gt($shiftStart)) {
                                $rawDiff     = (int) $shiftStart->diffInMinutes($inCarbon);
                                $lateMinutes = ($rawDiff > $lateGraceMinutes) ? $rawDiff : 0;
                                $status      = ($lateMinutes > 0) ? 'late' : 'present';
                            } else {
                                $status = 'present';
                            }

                            if ($outCarbon && $outCarbon->lessThan($shiftEnd)) {
                                $rawEarly     = (int) $outCarbon->diffInMinutes($shiftEnd);
                                $earlyMinutes = ($rawEarly > $earlyGraceMinutes) ? $rawEarly : 0;
                            }
                        }
                    }
                }
            } else {
                if ($leaveDetails && !$publicHoliday && !$isCompanyWeekend) {
                    $status = $isHalfDay ? 'half_day_absent' : 'leave';
                } else {
                    $status = 'absent';
                }
            }

            // 9. Upadate Database 
            $attendance->update([
                'shift_id'           => $shift?->id ?? $attendance->shift_id,
                'in_time'            => $inCarbon ? $inCarbon->toTimeString() : null,
                'out_time'           => $outCarbon ? $outCarbon->toTimeString() : null,
                'total_worked_hours' => $totalWorkedHours,
                'ot_hours'           => $calculatedOtHours,
                'late_minutes'       => $lateMinutes,
                'early_minutes'      => $earlyMinutes,
                'status'             => $status,
                'reason'             => $this->editReason,
            ]);

            session()->flash('message', 'Attendance record updated successfully with all shift, calendar, and policy rules!');
        }

        $this->closeModals();
    }

    public function closeModals(): void
    {
        $this->isEditModalOpen    = false;
        $this->isConfirmModalOpen = false;
        $this->reset(['editingAttendanceId', 'editEmployeeName', 'editDate', 'editInTime', 'editOutTime', 'editReason', 'editBreaks']);
    }

    /**
     * Helper Method to Filters according to Base Query
     */
    private function getFilteredAttendanceQuery()
    {
        return Attendance::query()
            ->with(['employee.department', 'shift', 'breaks'])
            ->when($this->startDate && $this->endDate, function ($query) {
                $query->whereBetween('date', [$this->startDate, $this->endDate]);
            })
            ->when($this->selectedDepartmentId, function ($query) {
                $query->whereHas('employee', function ($employeeQuery) {
                    $employeeQuery->where('department_id', $this->selectedDepartmentId);
                });
            })
            ->when($this->selectedStatus, function ($query) {
                if ($this->selectedStatus === 'late') {
                    $query->where('late_minutes', '>', 0);
                } elseif ($this->selectedStatus === 'half_day') {
                    $query->whereIn('status', [
                        'half_day_morning_present',
                        'half_day_morning_late',
                        'half_day_evening_present',
                        'half_day_evening_late',
                    ]);
                } elseif ($this->selectedStatus === 'half_day_absent') {
                    $query->where('status', 'half_day_absent');
                } elseif ($this->selectedStatus === 'holiday_present') {
                    $query->where('status', 'holiday_present');
                } elseif ($this->selectedStatus === 'weekend_present') {
                    $query->where('status', 'weekend_present');
                } elseif ($this->selectedStatus === 'pending_approval') {
                    $query->whereExists(function ($sub) {
                        $sub->select(DB::raw(1))
                            ->from('attendance_approvals')
                            ->whereColumn('attendance_approvals.employee_id', 'attendances.employee_id')
                            ->whereColumn('attendance_approvals.requested_date', 'attendances.date')
                            ->where('attendance_approvals.status', 'pending');
                    });
                } else {
                    $query->where('status', $this->selectedStatus);
                }
            })
            ->when($this->searchQuery, function ($query) {
                $query->whereHas('employee', function ($employeeQuery) {
                    $employeeQuery->where(function ($nameSearchQuery) {
                        $nameSearchQuery->where('first_name', 'like', '%' . $this->searchQuery . '%')
                            ->orWhere('last_name', 'like', '%' . $this->searchQuery . '%')
                            ->orWhere('employee_id', 'like', '%' . $this->searchQuery . '%');
                    });
                });
            });
    }

    /**
     * Section 2: Helper Method to Calculate Summary Metrics for Attendance Records
     */
    private function calculateSummaryMetrics(): array
    {
        $filteredQuery = $this->getFilteredAttendanceQuery();

        return [
            'total_employees' => Employee::when($this->selectedDepartmentId, fn($empQuery) => $empQuery->where('department_id', $this->selectedDepartmentId))->count(),
            
            'present_count'   => (clone $filteredQuery)->whereIn('status', [
                'present',
                'late',
                'holiday_present',
                'weekend_present',
                'half_day_morning_present',
                'half_day_morning_late',
                'half_day_evening_present',
                'half_day_evening_late',
            ])->count(),

            'absent_count'    => (clone $filteredQuery)->where('status', 'absent')->whereNull('in_time')->count(),

            'half_day_absent_count' => (clone $filteredQuery)->where('status', 'half_day_absent')->count(),

            'leave_count'     => (clone $filteredQuery)->where('status', 'leave')->count(),

            'late_count'      => (clone $filteredQuery)->where(function ($statusQuery) {
                $statusQuery->where('status', 'late')
                  ->orWhere('late_minutes', '>', 0);
            })->whereNotNull('in_time')->count(),

            'pending_edits'   => AttendanceApproval::where('status', 'pending')
                ->when($this->startDate && $this->endDate, fn($approvalQuery) => $approvalQuery->whereBetween('requested_date', [$this->startDate, $this->endDate]))
                ->count(),
        ];
    }

    /**
     * Section 3: Helper Method to Get Formatted Attendance Records with Pagination and Additional Details
     */
    private function getFormattedAttendanceRecords(string $dateFormat)
    {
        $paginatedRecords = $this->getFilteredAttendanceQuery()
            ->orderBy('date', 'desc')
            ->paginate(15);

        $paginatedRecords->getCollection()->transform(function ($record) use ($dateFormat) {
            $recordDateStr = Carbon::parse($record->date)->toDateString();
            $dayOfWeekName = Carbon::parse($record->date)->format('l');

            $publicHoliday = \App\Models\PublicHoliday::whereDate('holiday_date', $recordDateStr)->first();
            $calendarSetting = \App\Models\CompanyCalendarSetting::where('day_of_week', $dayOfWeekName)->first();
            
            $shift = $record->shift;
            if (!$shift && !empty($record->employee?->shift_id)) {
                $shift = \App\Models\Shift::find($record->employee->shift_id);
            }

            $isCompanyWeekend = true;
            if ($calendarSetting && $calendarSetting->is_working_day) {
                if ($shift && !empty($shift->start_time) && !empty($shift->end_time)) {
                    $isCompanyWeekend = false;
                }
            }

            $record->display_date        = Carbon::parse($record->date)->format($dateFormat);
            $record->display_shift       = $record->shift?->name ?? 'Default Shift';
            $record->display_in_time     = $record->in_time ? Carbon::parse($record->in_time)->format('h:i A') : '--:--';
            $record->display_out_time    = $record->out_time ? Carbon::parse($record->out_time)->format('h:i A') : '--:--';
            $record->display_late_mins   = (int) ($record->late_minutes ?? 0);
            $record->display_work_hours  = number_format((float) ($record->total_worked_hours ?? 0), 2);
            $record->display_ot_hours    = number_format((float) ($record->ot_hours ?? 0), 2);

            if ($record->status === 'holiday_present' || ($publicHoliday !== null && !empty($record->in_time))) {
                $record->custom_status_label = 'Holiday Work';
                $record->is_holiday_or_weekend = true;
            } elseif ($record->status === 'weekend_present' || ($isCompanyWeekend && !empty($record->in_time))) {
                $record->custom_status_label = 'Weekend Work';
                $record->is_holiday_or_weekend = true;
            } elseif ($publicHoliday !== null) {
                $record->custom_status_label = 'Holiday';
                $record->is_holiday_or_weekend = true;
            } elseif ($isCompanyWeekend) {
                $record->custom_status_label = 'Weekend';
                $record->is_holiday_or_weekend = true;
            } else {
                $record->custom_status_label = null;
                $record->is_holiday_or_weekend = false;
            }

            $leaveDetails = DB::table('leave_requests')
                ->leftJoin('apply_leave_types', 'leave_requests.id', '=', 'apply_leave_types.leave_request_id')
                ->leftJoin('leave_types', 'apply_leave_types.leave_type_id', '=', 'leave_types.id')
                ->where('leave_requests.employee_id', $record->employee_id)
                ->where('leave_requests.status', 'approved')
                ->whereDate('leave_requests.start_date', '<=', $recordDateStr)
                ->whereDate('leave_requests.end_date', '>=', $recordDateStr)
                ->select('leave_types.name as leave_type_name', 'leave_requests.is_half_day', 'leave_requests.leave_shift', 'leave_requests.reason')
                ->first();

            $record->leave_type_name    = $leaveDetails?->leave_type_name ?? 'General Leave';
            $record->is_half_day        = (bool) ($leaveDetails?->is_half_day ?? false);
            $record->leave_shift        = $leaveDetails?->leave_shift;
            $record->leave_reason       = $leaveDetails?->reason;
            $record->has_approved_leave = $leaveDetails !== null;

            $record->is_edited = AttendanceApproval::where('attendance_id', $record->id)
    ->where('status', 'approved')
    ->exists();

            $record->has_pending = DB::table('attendance_approvals')
                ->where('employee_id', $record->employee_id)
                ->whereDate('requested_date', $recordDateStr)
                ->where('status', 'pending')
                ->exists();

            return $record;
        });

        return $paginatedRecords;
    }

    public function viewAttendanceRecord(int $id): void
    {
        $this->selectedRecordForView = Attendance::with(['employee.department', 'shift', 'breaks', 'workLocation'])->find($id);

        if ($this->selectedRecordForView !== null) {
            $recordDateStr = Carbon::parse($this->selectedRecordForView->date)->toDateString();

            $this->selectedLeaveDetails = DB::table('leave_requests')
                ->leftJoin('apply_leave_types', 'leave_requests.id', '=', 'apply_leave_types.leave_request_id')
                ->leftJoin('leave_types', 'apply_leave_types.leave_type_id', '=', 'leave_types.id')
                ->where('leave_requests.employee_id', $this->selectedRecordForView->employee_id)
                ->where('leave_requests.status', 'approved')
                ->whereDate('leave_requests.start_date', '<=', $recordDateStr)
                ->whereDate('leave_requests.end_date', '>=', $recordDateStr)
                ->select(
                    'leave_types.name as leave_type_name',
                    'leave_requests.is_half_day',
                    'leave_requests.leave_shift',
                    'leave_requests.reason'
                )
                ->first();

            $earlyMins = (int) ($this->selectedRecordForView->early_minutes ?? 0);
            $this->viewRecordEarlyMins = $earlyMins;
            $this->selectedRecordForView->display_early_mins = $earlyMins;
        }

        $this->isViewRecordModalOpen = true;
    }

    public function openViewModal(int $attendanceId): void
    {
        $this->selectedAttendanceRecord = Attendance::with(['employee.department', 'shift', 'breaks'])->find($attendanceId);
        $this->isViewModalOpen = true;
    }

    public function render(): View
    {
        $dateFormat = view()->shared('globalCalendarFormat', 'd/m/Y');

        return view('livewire.attendance.all-attendance-list', [
            'departmentList'    => Department::orderBy('name')->get(),
            'attendanceMetrics' => $this->calculateSummaryMetrics(),
            'attendanceRecords' => $this->getFormattedAttendanceRecords($dateFormat),
        ])->layout('layouts.app');
    }
}