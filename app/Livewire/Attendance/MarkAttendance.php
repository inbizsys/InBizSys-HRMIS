<?php

namespace App\Livewire\Attendance;

use App\Models\Attendance;
use App\Models\AttendanceBreak;
use App\Models\Employee;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\AttendanceApproval;
use App\Models\AttendanceBreakApproval;
use App\Models\BreakType;
use App\Services\AttendanceCheckService;
use App\Models\Payroll;

#[Layout('layouts.app')]
class MarkAttendance extends Component
{
    use WithPagination;

    public ?Attendance $currentAttendanceRecord = null;
    public ?Employee $authenticatedEmployee = null;
    public ?AttendanceBreak $activeBreakRecord = null;
    public ?Attendance $unclosedPreviousRecord = null;
    public bool $isShiftExpired = false;
    public ?string $editingDate = null;

    public string $currentRunningTime = '';

    // Modal States
    public bool $isTerminalModalOpen = false;
    public bool $isBreakModalOpen = false;
    public bool $isViewRecordModalOpen = false;
    public ?Attendance $selectedRecordForView = null;
    public ?object $selectedLeaveDetails = null;
    public int $viewRecordEarlyMins = 0;

    // Break Form States
    public string $selectedBreakType = 'tea';
    public string $breakReason = '';

    public $showEditModal = false;
    public $selectedAttendanceId;
    public $editDate, $editInTime, $editOutTime, $editReason;
    public $breaks = [];
    public $editBreaks = [];
    public $allBreakTypes;

    public ?string $filterDateFrom = null;
    public ?string $filterDateTo = null;

    public bool $isLeaveConfirmModalOpen = false;
    public ?array $pendingCoordinates = null;
    public ?object $detectedFullDayLeave = null;
    public bool $isConfirmedToWorkOnLeave = false;
    public ?object $detectedLeaveRecord = null;

    public bool $isHolidayConfirmModalOpen = false;
    public ?object $detectedPublicHoliday = null;
    public bool $isConfirmedToWorkOnHoliday = false;

    public bool $isHolidayOff = false;
    public bool $isWeekendOff = false;

    public function mount(): void
    {
        // Run the auto-absent check when the component is mounted
        (new AttendanceCheckService())->runAutoAbsentCheck();

        $this->loadEmployeeData();
        $this->loadCurrentAttendanceRecord();
        $this->updateServerTime();
    }

    public function updatedFilterDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatedFilterDateTo(): void
    {
        $this->resetPage();
    }

    public function resetDateFilter(): void
    {
        $this->reset(['filterDateFrom', 'filterDateTo']);
        $this->resetPage();
    }

    public function updateServerTime(): void
    {
        $this->currentRunningTime = Carbon::now()->format('h:i:s A');
    }

    public function loadEmployeeData(): void
    {
        $this->authenticatedEmployee = Auth::user()->employee ?? null;
    }

    public function openAttendanceEditModal($attendanceId)
    {
        $attendance = Attendance::with(['breaks'])->find($attendanceId);

        if ($attendance) {
            $this->selectedAttendanceId = $attendance->id;
            // Period lock: Block if payroll is approved or paid
            $targetMonth = substr($attendance->date, 0, 7);
            $isLocked = Payroll::where('payroll_month', $targetMonth)
                ->whereIn('status', ['approved', 'paid'])
                ->exists();

            if ($isLocked) {
                session()->flash('errorMessage', 'Cannot request edit. Payroll for ' . date('F Y', strtotime($targetMonth . '-01')) . ' is approved/paid and locked.');
                return;
            }

            $this->editDate = date('Y-m-d', strtotime($attendance->date));
            $this->editInTime = $attendance->in_time;
            $this->editOutTime = $attendance->out_time;
            $this->editReason = '';

            // Load only active break types configured by Admin in the database
            $this->allBreakTypes = BreakType::where('is_active', true)->get();

            $this->breaks = $attendance->breaks;

            $this->editBreaks = [];
            foreach ($this->breaks as $break) {
                $this->editBreaks[] = [
                    'id'         => $break->id,
                    'break_type' => $break->break_type,
                    'start_time' => $break->break_start,
                    'end_time'   => $break->break_end,
                ];
            }

            $this->showEditModal = true;
        }
    }

    public function addBreakToEdit(): void
    {
        $this->allBreakTypes = BreakType::where('is_active', true)->get();
        $defaultType = $this->allBreakTypes->first()?->code ?? 'tea';

        $this->editBreaks[] = [
            'id'         => null,
            'break_type' => $defaultType,
            'start_time' => '',
            'end_time'   => '',
        ];
    }

    public function removeBreakFromEdit(int $index): void
    {
        unset($this->editBreaks[$index]);
        $this->editBreaks = array_values($this->editBreaks);
    }

    public function submitAttendanceEditRequest()
    {
        $this->validate([
            'editDate'     => 'required|date',
            'editInTime'   => 'nullable',
            'editOutTime'  => 'nullable',
            'editReason'   => 'required|string|max:255',
        ]);

        // Period lock: Block edit requests for approved or paid payroll periods
        $targetMonth = substr($this->editDate, 0, 7);
        $isLocked = Payroll::where('payroll_month', $targetMonth)
            ->whereIn('status', ['approved', 'paid'])
            ->exists();

        if ($isLocked) {
            session()->flash('errorMessage', 'Attendance edit requests cannot be submitted for ' . date('F Y', strtotime($targetMonth . '-01')) . '. Payroll for this month is already approved/paid and locked.');
            return;
        }

        $existingPending = AttendanceApproval::where('employee_id', $this->authenticatedEmployee->id)
            ->where('requested_date', $this->editDate)
            ->where('status', 'pending')
            ->exists();

        if ($existingPending) {
            session()->flash('errorMessage', 'You already have a pending edit request for this date.');
            return;
        }

        DB::transaction(function () {
            $attendanceApproval = AttendanceApproval::create([
                'employee_id'        => $this->authenticatedEmployee->id,
                'attendance_id'      => $this->selectedAttendanceId,
                'requested_date'     => $this->editDate,
                'requested_in_time'  => !empty($this->editInTime) ? $this->editInTime : null,
                'requested_out_time' => !empty($this->editOutTime) ? $this->editOutTime : null,
                'reason'             => $this->editReason,
                'status'             => 'pending',
            ]);

            foreach ($this->editBreaks as $breakData) {
                if (!empty($breakData['start_time']) || !empty($breakData['end_time'])) {
                    
                    $startTime = !empty($breakData['start_time']) 
                        ? date('Y-m-d H:i:s', strtotime($this->editDate . ' ' . $breakData['start_time'])) 
                        : null;

                    $endTime = !empty($breakData['end_time']) 
                        ? date('Y-m-d H:i:s', strtotime($this->editDate . ' ' . $breakData['end_time'])) 
                        : null;

                    AttendanceBreakApproval::create([
                        'attendance_approval_id' => $attendanceApproval->id, 
                        'employee_id'            => $this->authenticatedEmployee->id,
                        'attendance_break_id'    => $breakData['id'] ?? null,
                        'break_type'             => $breakData['break_type'] ?? 'tea',
                        'requested_start_time'   => $startTime,
                        'requested_end_time'     => $endTime,
                        'reason'                 => $this->editReason,
                        'status'                 => 'pending',
                    ]);
                }
            }
        });

        $this->showEditModal = false;
        session()->flash('successMessage', 'Attendance and Break edit requests submitted successfully!');
        $this->reset(['selectedAttendanceId', 'editDate', 'editInTime', 'editOutTime', 'editReason', 'breaks', 'editBreaks']);
    }

    public function loadCurrentAttendanceRecord(): void
    {
        if ($this->authenticatedEmployee !== null) {
            $now = Carbon::now();
            $todayDateString = $now->toDateString();

            // 1. Unclosed Previous Record Check
            $unclosedRecord = Attendance::where('employee_id', $this->authenticatedEmployee->id)
                ->whereNull('out_time')
                ->whereNotNull('in_time')
                ->where('date', '<', $todayDateString)
                ->orderBy('date', 'desc')
                ->first();

            if ($unclosedRecord !== null) {
                $hasPendingOrApprovedRequest = DB::table('attendance_approvals')
                    ->where('attendance_id', $unclosedRecord->id)
                    ->whereIn('status', ['pending', 'approved'])
                    ->exists();

                if (!$hasPendingOrApprovedRequest) {
                    $this->unclosedPreviousRecord = $unclosedRecord;
                } else {
                    $this->unclosedPreviousRecord = null;
                }
            } else {
                $this->unclosedPreviousRecord = null;
            }

            // 2. Load Today's Attendance Record
            $this->currentAttendanceRecord = Attendance::where('employee_id', $this->authenticatedEmployee->id)
                ->where('date', $todayDateString)
                ->first();

            // 3. calculate shift end time (default is 16:00:00 if not assigned)
            $shiftEndTime = '16:00:00';
            $personalShiftEndTime = null;
            if (!empty($this->authenticatedEmployee->shift_id)) {
                $assignedShift = Shift::find($this->authenticatedEmployee->shift_id);
                if ($assignedShift && !empty($assignedShift->end_time)) {
                    $shiftEndTime = Carbon::parse($assignedShift->end_time)->format('H:i:s');
                    $personalShiftEndTime = $shiftEndTime;
                }
            }

            // Check Company Calendar Setting to determine end time if it is a Half Day
            $dayOfWeekName = $now->format('l');
            $calendarSetting = \App\Models\CompanyCalendarSetting::where('day_of_week', $dayOfWeekName)->first();
            
            if ($calendarSetting && $calendarSetting->day_type === 'Half Day' && !empty($calendarSetting->half_day_end_time)) {
                $calendarEndTime = Carbon::parse($calendarSetting->half_day_end_time)->format('H:i:s');
                // If employee shift ends earlier than calendar half-day, use shift end time
                if ($personalShiftEndTime && Carbon::parse($personalShiftEndTime)->lt(Carbon::parse($calendarEndTime))) {
                    $shiftEndTime = $personalShiftEndTime;
                } else {
                    $shiftEndTime = $calendarEndTime;
                }
            }

            $scheduledShiftEndTime = Carbon::parse($todayDateString . ' ' . $shiftEndTime);

            // 4. Auto-Absent / Leave 
            if ($this->currentAttendanceRecord === null) {
                if ($now->greaterThan($scheduledShiftEndTime)) {
                    $leaveRequest = DB::table('leave_requests')
                        ->where('employee_id', $this->authenticatedEmployee->id)
                        ->where('status', 'approved')
                        ->whereDate('start_date', '<=', $todayDateString)
                        ->whereDate('end_date', '>=', $todayDateString)
                        ->first();

                    $autoStatus = $leaveRequest !== null ? 'leave' : 'absent';

                    $this->currentAttendanceRecord = Attendance::create([
                        'employee_id'        => $this->authenticatedEmployee->id,
                        'shift_id'           => $this->authenticatedEmployee->shift_id,
                        'date'               => $todayDateString,
                        'in_time'            => null,
                        'out_time'           => null,
                        'late_minutes'       => 0,
                        'total_worked_hours' => 0.00,
                        'ot_hours'           => 0.00,
                        'status'             => $autoStatus,
                    ]);
                }
            }

            // 5. set isShiftExpired flag
            if ($now->greaterThan($scheduledShiftEndTime) && ($this->currentAttendanceRecord === null || $this->currentAttendanceRecord->in_time === null)) {
                $this->isShiftExpired = true; 
            } else {
                $this->isShiftExpired = false; 
            }

            // 6. Active Break Record Load 
            if ($this->currentAttendanceRecord !== null) {
                $this->activeBreakRecord = AttendanceBreak::where('attendance_id', $this->currentAttendanceRecord->id)
                    ->whereNull('break_end')
                    ->latest()
                    ->first();
            } else {
                $this->activeBreakRecord = null;
            }
        }
    }

    public function clockOut(): void
    {
        if ($this->authenticatedEmployee === null) {
            session()->flash('errorMessage', 'Employee record not found.');
            return;
        }

        if ($this->activeBreakRecord !== null) {
            session()->flash('errorMessage', 'You are on break. Click Resume Work first.');
            return;
        }

        $currentDateTime = Carbon::now();
        $todayDateString = $currentDateTime->toDateString();

        $activeAttendance = Attendance::where('employee_id', $this->authenticatedEmployee->id)
            ->whereNull('out_time')
            ->whereNotNull('in_time')
            ->orderBy('date', 'asc')
            ->first();

        if ($activeAttendance === null) {
            session()->flash('errorMessage', 'No active clock-in record found to clock out.');
            return;
        }

        // Work Hours calculation
        $clockInTimestamp = Carbon::parse($activeAttendance->date)->setTimeFromTimeString($activeAttendance->in_time);
        $grossMinutes = abs($currentDateTime->diffInMinutes($clockInTimestamp));

        $unpaidBreakMinutes = (float) AttendanceBreak::where('attendance_id', $activeAttendance->id)
            ->where('is_paid', false)
            ->sum('duration_minutes');

        $netMinutes = max(0, $grossMinutes - $unpaidBreakMinutes);
        $totalWorkedHoursCalculated = round($netMinutes / 60, 2);

        $assignedShift = $activeAttendance->shift ?? Shift::find($this->authenticatedEmployee->shift_id);

        $expectedStartTime = $assignedShift?->start_time ?? '08:00:00';
        $expectedEndTime   = $assignedShift?->end_time ?? '17:00:00';

        $attendanceDate = Carbon::parse($activeAttendance->date)->format('Y-m-d');
        $dayOfWeekName = Carbon::parse($activeAttendance->date)->format('l');

        // Check Company Calendar Setting (determine end time if it is a Half Day)
        $calendarSetting = \App\Models\CompanyCalendarSetting::where('day_of_week', $dayOfWeekName)->first();
        if ($calendarSetting && $calendarSetting->day_type === 'Half Day') {
            if (!empty($calendarSetting->half_day_end_time) && !empty($assignedShift?->end_time)) {
                // If employee shift ends earlier than calendar half-day, use shift end time
                if (Carbon::parse($assignedShift->end_time)->lt(Carbon::parse($calendarSetting->half_day_end_time))) {
                    $expectedEndTime = $assignedShift->end_time;
                } else {
                    $expectedEndTime = $calendarSetting->half_day_end_time;
                }
            } elseif (!empty($calendarSetting->half_day_end_time)) {
                $expectedEndTime = $calendarSetting->half_day_end_time;
            }
        }

        // Overtime calculation (Using $expectedEndTime)
        $calculatedOvertimeHours = 0.00;
        if (!empty($this->authenticatedEmployee->is_ot_eligible) && !empty($expectedEndTime)) {
            $scheduledShiftEndTime = Carbon::parse($attendanceDate . ' ' . $expectedEndTime);
            if ($currentDateTime->greaterThan($scheduledShiftEndTime)) {
                $calculatedOvertimeHours = round(abs($currentDateTime->diffInMinutes($scheduledShiftEndTime)) / 60, 2);
            }
        }

        // -------------------------------------------------------------
        // EARLY MINUTES, STATUS AND OVERSTAY LEAVE APPROVAL CALCULATION
        // -------------------------------------------------------------
        $calculatedEarlyMinutes = 0;
        $updatedStatus = $activeAttendance->status;
        $isEveningOverstay = false;

        if (!empty($expectedStartTime) && !empty($expectedEndTime)) {
            
            $shiftStart = Carbon::parse($attendanceDate . ' ' . $expectedStartTime);
            $shiftEnd = Carbon::parse($attendanceDate . ' ' . $expectedEndTime);

            // Overnight shift protection
            if ($shiftEnd->lessThan($shiftStart)) {
                $shiftEnd->addDay();
            }

            $earlyGraceMinutes = (int) ($assignedShift->early_leave_grace_minutes ?? 0);

            $halfDayHours = !empty($assignedShift->half_day_min_hours) ? (float) $assignedShift->half_day_min_hours : 1.0;
            $halfDayMinutes = (int) ($halfDayHours * 60);

            // Leave details check
            $leaveDetails = DB::table('leave_requests')
                ->where('employee_id', $this->authenticatedEmployee->id)
                ->where('status', 'approved')
                ->whereDate('start_date', '<=', $attendanceDate)
                ->whereDate('end_date', '>=', $attendanceDate)
                ->first();

            $isHalfDay = $leaveDetails && (int) $leaveDetails->is_half_day === 1;
            $leaveShift = strtolower(trim((string) ($leaveDetails->leave_shift ?? '')));

            if ($isHalfDay && in_array($leaveShift, ['evening', 'second_half'])) {
                // Evening Leave Boundary Time (Shift Start + Half Day Minutes)
                $targetEndTime = $shiftStart->copy()->addMinutes($halfDayMinutes);

                // If employee worked past boundary time into the evening (Overstayed on Evening Leave):
                if ($currentDateTime->greaterThan($targetEndTime)) {
                    $isEveningOverstay = true;

                    // Calculate early departure minutes only if clocked out before full day shift end
                    if ($currentDateTime->lessThan($shiftEnd)) {
                        $rawEarly = (int) $currentDateTime->diffInMinutes($shiftEnd);
                        $calculatedEarlyMinutes = ($rawEarly > $earlyGraceMinutes) ? $rawEarly : 0;
                    }
                } else {
                    // Normal case: clocked out before boundary time:
                    $rawEarly = (int) $currentDateTime->diffInMinutes($targetEndTime);
                    $calculatedEarlyMinutes = ($rawEarly > $earlyGraceMinutes) ? $rawEarly : 0;
                }
            } elseif ($isHalfDay && in_array($leaveShift, ['morning', 'first_half'])) {
                // Morning Leave: employee is expected to work during the second half
                $secondHalfStartTime = $shiftStart->copy()->addMinutes($halfDayMinutes);

                if ($currentDateTime->lessThanOrEqualTo($secondHalfStartTime)) {
                    $calculatedEarlyMinutes = 0;
                    $updatedStatus = 'half_day_absent';
                } elseif ($currentDateTime->lessThan($shiftEnd)) {
                    $rawEarly = (int) $currentDateTime->diffInMinutes($shiftEnd);
                    $calculatedEarlyMinutes = ($rawEarly > $earlyGraceMinutes) ? $rawEarly : 0;
                }
            } else {
                // Regular Full Day Shift
                if ($currentDateTime->lessThan($shiftEnd)) {
                    $rawEarly = (int) $currentDateTime->diffInMinutes($shiftEnd);
                    $calculatedEarlyMinutes = ($rawEarly > $earlyGraceMinutes) ? $rawEarly : 0;
                }
            }
        }

        // Database update
        DB::table('attendances')
            ->where('id', $activeAttendance->id)
            ->update([
                'out_time'           => $currentDateTime->toTimeString(),
                'total_worked_hours' => $totalWorkedHoursCalculated,
                'ot_hours'           => $calculatedOvertimeHours,
                'early_minutes'      => $calculatedEarlyMinutes,
                'status'             => $updatedStatus,
                'updated_at'         => $currentDateTime,
            ]);

        // ========================================================
        // Create admin approval request when evening leave is overstayed
        // ========================================================
        if ($isEveningOverstay) {
            $existingPendingApproval = AttendanceApproval::where('employee_id', $this->authenticatedEmployee->id)
                ->where('requested_date', $todayDateString)
                ->where('status', 'pending')
                ->exists();

            if (!$existingPendingApproval) {
                AttendanceApproval::create([
                    'employee_id'        => $this->authenticatedEmployee->id,
                    'attendance_id'      => $activeAttendance->id,
                    'requested_date'     => $todayDateString,
                    'requested_in_time'  => $activeAttendance->in_time,
                    'requested_out_time' => $currentDateTime->toTimeString(),
                    'reason'             => 'Auto-Generated: Employee reported in morning and worked throughout approved Evening Half-Day Leave.',
                    'status'             => 'pending',
                ]);

                session()->flash('successMessage', 'Clocked out successfully. You worked during your approved Evening Leave; an approval request has been sent to Admin to re-credit your leave.');
                $this->loadCurrentAttendanceRecord();
                return;
            }
        }

        $this->loadCurrentAttendanceRecord();
        session()->flash('successMessage', 'Clocked out successfully. Total Worked: ' . $totalWorkedHoursCalculated . ' hrs');
    }

    public function openTerminal(): void
    {
        $this->loadCurrentAttendanceRecord();
        $this->updateServerTime();
        $this->isTerminalModalOpen = true;
    }

    public function closeTerminal(): void
    {
        $this->isTerminalModalOpen = false;
    }

    public function viewAttendanceRecord(int $id): void
    {
        $this->selectedRecordForView = Attendance::with(['shift', 'breaks', 'workLocation'])->find($id);

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

            $this->viewRecordEarlyMins = (int) ($this->selectedRecordForView->early_minutes ?? 0);
        }

        $this->isViewRecordModalOpen = true;
    }

    public function clockIn(?float $latitude = null, ?float $longitude = null): void
    {
        if ($this->authenticatedEmployee === null) {
            session()->flash('errorMessage', 'Employee profile record could not be found.');
            return;
        }

        if ($latitude === null || $longitude === null) {
            session()->flash('errorMessage', 'Location access is strictly required to mark attendance.');
            return;
        }

        $currentDateTime = Carbon::now();
        $todayDateString = $currentDateTime->toDateString();
        $dayOfWeekName = $currentDateTime->format('l'); // e.g., 'Monday', 'Saturday', etc.

        $activeAttendance = Attendance::where('employee_id', $this->authenticatedEmployee->id)
            ->where('date', $todayDateString)
            ->first();

        if ($activeAttendance !== null && $activeAttendance->in_time !== null) {
            session()->flash('errorMessage', 'You have already recorded clock-in for today.');
            return;
        }

        // 1. Check Public Holiday or Company Calendar Setting (Weekend / Off-Day)
        if (!$this->isConfirmedToWorkOnHoliday) {
            $publicHoliday = \App\Models\PublicHoliday::whereDate('holiday_date', $todayDateString)->first();
            $calendarSetting = \App\Models\CompanyCalendarSetting::where('day_of_week', $dayOfWeekName)->first();
            $isCompanyWeekend = ($calendarSetting && !$calendarSetting->is_working_day);

            if ($publicHoliday !== null || $isCompanyWeekend) {
                $this->pendingCoordinates = [
                    'lat' => $latitude,
                    'lng' => $longitude,
                ];

                if ($publicHoliday !== null) {
                    $this->detectedPublicHoliday = $publicHoliday;
                    $this->isHolidayOff = true;
                    $this->isWeekendOff = false;
                } else {
                    $this->detectedPublicHoliday = (object)[
                        'description' => $calendarSetting->day_type ?? ($dayOfWeekName . ' (Company Off-Day / Weekend)')
                    ];
                    $this->isHolidayOff = false;
                    $this->isWeekendOff = true;
                }

                $this->isHolidayConfirmModalOpen = true;
                return;
            }
        }

        // ========================================================
        // 2. FULL DAY AND DYNAMIC HALF-DAY LEAVE CHECK (MORNING & EVENING)
        // ========================================================
        if (!$this->isConfirmedToWorkOnLeave) {
            // A) Full Day Leave Check
            $fullDayLeave = DB::table('leave_requests')
                ->leftJoin('apply_leave_types', 'leave_requests.id', '=', 'apply_leave_types.leave_request_id')
                ->leftJoin('leave_types', 'apply_leave_types.leave_type_id', '=', 'leave_types.id')
                ->where('leave_requests.employee_id', $this->authenticatedEmployee->id)
                ->where('leave_requests.status', 'approved')
                ->where(function ($leaveQuery) {
                    $leaveQuery->whereNull('leave_requests.is_half_day')
                      ->orWhere('leave_requests.is_half_day', 0);
                })
                ->whereDate('leave_requests.start_date', '<=', $todayDateString)
                ->whereDate('leave_requests.end_date', '>=', $todayDateString)
                ->select('leave_requests.id', 'leave_types.name as leave_type_name', DB::raw("'full_day' as leave_mode"))
                ->first();

            if ($fullDayLeave !== null) {
                $this->pendingCoordinates = [
                    'lat' => $latitude,
                    'lng' => $longitude,
                ];
                $this->detectedFullDayLeave = $fullDayLeave;
                $this->detectedLeaveRecord = $fullDayLeave;
                $this->isLeaveConfirmModalOpen = true;
                return;
            }

            // B) Half Day Leave Check
            $halfDayLeave = DB::table('leave_requests')
                ->leftJoin('apply_leave_types', 'leave_requests.id', '=', 'apply_leave_types.leave_request_id')
                ->leftJoin('leave_types', 'apply_leave_types.leave_type_id', '=', 'leave_types.id')
                ->where('leave_requests.employee_id', $this->authenticatedEmployee->id)
                ->where('leave_requests.status', 'approved')
                ->where('leave_requests.is_half_day', 1)
                ->whereDate('leave_requests.start_date', '<=', $todayDateString)
                ->whereDate('leave_requests.end_date', '>=', $todayDateString)
                ->select(
                    'leave_requests.id',
                    'leave_requests.leave_shift',
                    'leave_types.name as leave_type_name',
                    DB::raw("'half_day' as leave_mode")
                )
                ->first();

            if ($halfDayLeave !== null) {
                $assignedShiftForCheck = !empty($this->authenticatedEmployee->shift_id) 
                    ? Shift::find($this->authenticatedEmployee->shift_id) 
                    : null;

                if ($assignedShiftForCheck && !empty($assignedShiftForCheck->start_time)) {
                    $shiftStart = Carbon::parse($todayDateString . ' ' . $assignedShiftForCheck->start_time);
                    
                    $halfDayHours = !empty($assignedShiftForCheck->half_day_min_hours) 
                        ? (float) $assignedShiftForCheck->half_day_min_hours 
                        : 1.0;
                    $halfDayMinutes = (int) ($halfDayHours * 60);

                    $halfDayBoundaryTime = $shiftStart->copy()->addMinutes($halfDayMinutes);
                    $lShift = strtolower(trim((string) ($halfDayLeave->leave_shift ?? '')));

                    $isMorningConflict = in_array($lShift, ['morning', 'first_half']) && $currentDateTime->lessThan($halfDayBoundaryTime);
                    $isEveningConflict = in_array($lShift, ['evening', 'second_half']) && $currentDateTime->greaterThanOrEqualTo($halfDayBoundaryTime);

                    if ($isMorningConflict || $isEveningConflict) {
                        $this->pendingCoordinates = [
                            'lat' => $latitude,
                            'lng' => $longitude,
                        ];
                        $this->detectedLeaveRecord = $halfDayLeave;
                        $this->isLeaveConfirmModalOpen = true;
                        return;
                    }
                }
            }
        }

        $assignedShift = !empty($this->authenticatedEmployee->shift_id) 
            ? Shift::find($this->authenticatedEmployee->shift_id) 
            : null;

        // Get employee personal shift start time to calculate late minutes
        $expectedStartTime = $assignedShift?->start_time ?? '08:00:00';
        $expectedEndTime   = $assignedShift?->end_time ?? '17:00:00';

        // Check Company Calendar Setting (determine end time if it is a Half Day)
        $calendarSetting = \App\Models\CompanyCalendarSetting::where('day_of_week', $dayOfWeekName)->first();
        if ($calendarSetting && $calendarSetting->day_type === 'Half Day') {
            if (!empty($calendarSetting->half_day_end_time) && !empty($assignedShift?->end_time)) {
                if (Carbon::parse($assignedShift->end_time)->lt(Carbon::parse($calendarSetting->half_day_end_time))) {
                    $expectedEndTime = $assignedShift->end_time;
                } else {
                    $expectedEndTime = $calendarSetting->half_day_end_time;
                }
            } elseif (!empty($calendarSetting->half_day_end_time)) {
                $expectedEndTime = $calendarSetting->half_day_end_time;
            }
        }

        $calculatedLateMinutes = 0;
        $attendanceStatus = 'present';

        // 3. Holiday / Off-Day Confirmation Check & Status Assignment (To prevent overwriting)
        if ($this->isConfirmedToWorkOnHoliday) {
            // Assign 'holiday_present' for public holidays, or 'weekend_present' for company weekends/off-days
            $attendanceStatus = $this->isWeekendOff ? 'weekend_present' : 'holiday_present';
            $calculatedLateMinutes = 0;
        } else {
            // Normal shift and late calculations (based on employee personal shift start time)
            if ($assignedShift !== null && !empty($expectedStartTime)) {
                $scheduledShiftStartTime = Carbon::parse($todayDateString . ' ' . $expectedStartTime);
                $scheduledShiftEndTime = !empty($expectedEndTime) 
                    ? Carbon::parse($todayDateString . ' ' . $expectedEndTime) 
                    : null;

                if ($scheduledShiftEndTime && $scheduledShiftEndTime->lessThan($scheduledShiftStartTime)) {
                    $scheduledShiftEndTime->addDay();
                }

                $lateGraceMinutes = (int) ($assignedShift->late_grace_minutes ?? 0);
                $halfDayHours = !empty($assignedShift->half_day_min_hours) ? (float) $assignedShift->half_day_min_hours : 1.0;
                $halfDayMinutes = (int) ($halfDayHours * 60);

                $halfDayLeave = DB::table('leave_requests')
                    ->where('employee_id', $this->authenticatedEmployee->id)
                    ->whereIn(DB::raw('LOWER(status)'), ['approved'])
                    ->where('is_half_day', 1)
                    ->whereDate('start_date', '<=', $todayDateString)
                    ->whereDate('end_date', '>=', $todayDateString)
                    ->first();

                $leaveShift = strtolower(trim((string) ($halfDayLeave->leave_shift ?? '')));

                if ($halfDayLeave && in_array($leaveShift, ['evening', 'second_half'])) {
                    $firstHalfEndTime = $scheduledShiftStartTime->copy()->addMinutes($halfDayMinutes);

                    if ($this->isConfirmedToWorkOnLeave) {
                        $attendanceStatus = 'present';
                        $calculatedLateMinutes = 0;
                    } else {
                        if ($currentDateTime->greaterThanOrEqualTo($firstHalfEndTime)) {
                            $calculatedLateMinutes = 0;
                            $attendanceStatus = 'half_day_absent';
                        } elseif ($currentDateTime->gt($scheduledShiftStartTime)) {
                            $rawDiff = (int) $scheduledShiftStartTime->diffInMinutes($currentDateTime);
                            if ($rawDiff > $lateGraceMinutes) {
                                $calculatedLateMinutes = $rawDiff;
                                $attendanceStatus = 'half_day_evening_late';
                            } else {
                                $calculatedLateMinutes = 0;
                                $attendanceStatus = 'half_day_evening_present';
                            }
                        } else {
                            $calculatedLateMinutes = 0;
                            $attendanceStatus = 'half_day_evening_present';
                        }
                    }
                } elseif ($halfDayLeave && in_array($leaveShift, ['morning', 'first_half'])) {
                    $secondHalfStartTime = $scheduledShiftStartTime->copy()->addMinutes($halfDayMinutes);

                    if ($scheduledShiftEndTime && $currentDateTime->greaterThanOrEqualTo($scheduledShiftEndTime)) {
                        $calculatedLateMinutes = 0;
                        $attendanceStatus = 'half_day_absent';
                    } elseif ($currentDateTime->gt($secondHalfStartTime)) {
                        $rawDiff = (int) $secondHalfStartTime->diffInMinutes($currentDateTime);
                        if ($rawDiff > $lateGraceMinutes) {
                            $calculatedLateMinutes = $rawDiff;
                            $attendanceStatus = 'half_day_morning_late';
                        } else {
                            $calculatedLateMinutes = 0;
                            $attendanceStatus = 'half_day_morning_present';
                        }
                    } else {
                        $calculatedLateMinutes = 0;
                        $attendanceStatus = 'half_day_morning_present';
                    }
                } else {
                    if ($currentDateTime->gt($scheduledShiftStartTime)) {
                        $rawDiff = (int) $scheduledShiftStartTime->diffInMinutes($currentDateTime);
                        if ($rawDiff > $lateGraceMinutes) {
                            $calculatedLateMinutes = $rawDiff;
                            $attendanceStatus = 'late';
                        } else {
                            $calculatedLateMinutes = 0;
                            $attendanceStatus = 'present';
                        }
                    } else {
                        $attendanceStatus = 'present';
                    }
                }
            }
        }

        $employeeLocationString = trim((string) ($this->authenticatedEmployee->work_location ?? ''));
        $matchedLocation = null;
        if ($employeeLocationString !== '') {
            $matchedLocation = DB::table('work_locations')
                ->whereRaw('LOWER(TRIM(name)) = ?', [strtolower($employeeLocationString)])
                ->first();
            if ($matchedLocation === null) {
                $matchedLocation = DB::table('work_locations')
                    ->where('name', 'LIKE', '%' . $employeeLocationString . '%')
                    ->first();
            }
        }
        if ($matchedLocation === null) {
            $matchedLocation = DB::table('work_locations')->first();
        }

        $resolvedWorkLocationId = $matchedLocation?->id;
        $detectedClientDevice = substr(request()->header('User-Agent') ?? 'Terminal-Web', 0, 255);

        $attendanceDataArray = [
            'employee_id'         => $this->authenticatedEmployee->id,
            'shift_id'            => $assignedShift?->id,
            'work_location_id'    => $resolvedWorkLocationId,
            'date'                => $todayDateString,
            'in_time'             => $currentDateTime->toTimeString(),
            'device_id'           => $detectedClientDevice,
            'in_latitude'         => $latitude,
            'in_longitude'        => $longitude,
            'is_location_flagged' => $resolvedWorkLocationId !== null,
            'late_minutes'        => $calculatedLateMinutes,
            'status'              => $attendanceStatus,
        ];

        if ($activeAttendance !== null) {
            $activeAttendance->update($attendanceDataArray);
            $savedAttendanceId = $activeAttendance->id;
        } else {
            $createdRecord = Attendance::create($attendanceDataArray);
            $savedAttendanceId = $createdRecord->id;
        }

        // Work on leave auto approval creation
        if ($this->isConfirmedToWorkOnLeave && ($this->detectedLeaveRecord || $this->detectedFullDayLeave)) {
            $leaveRec = $this->detectedLeaveRecord ?? $this->detectedFullDayLeave;
            $leaveTypeName = $leaveRec->leave_type_name ?? 'Leave';

            $reasonText = ($leaveRec->leave_mode ?? 'full_day') === 'half_day'
                ? 'Auto-Generated: Employee reported to work during approved Half-Day Leave (' . ucfirst($leaveRec->leave_shift ?? 'Shift') . ' - ' . $leaveTypeName . ')'
                : 'Auto-Generated: Employee reported to work on approved Full-Day Leave (' . $leaveTypeName . ')';

            AttendanceApproval::create([
                'employee_id'        => $this->authenticatedEmployee->id,
                'attendance_id'      => $savedAttendanceId,
                'requested_date'     => $todayDateString,
                'requested_in_time'  => $currentDateTime->toTimeString(),
                'requested_out_time' => null,
                'reason'             => $reasonText,
                'status'             => 'pending',
            ]);

            $this->isConfirmedToWorkOnLeave = false;
            $this->detectedFullDayLeave = null;
            $this->detectedLeaveRecord = null;
            $this->pendingCoordinates = null;
            $this->isConfirmedToWorkOnHoliday = false;
            $this->detectedPublicHoliday = null;
            $this->isHolidayOff = false;
            $this->isWeekendOff = false;

            $this->loadCurrentAttendanceRecord();
            session()->flash('successMessage', 'Clocked in successfully! An automated request has been sent to the Admin to adjust your approved leave.');
            return;
        }

        // Reset flags
        $this->isConfirmedToWorkOnHoliday = false;
        $this->detectedPublicHoliday = null;
        $this->isHolidayOff = false;
        $this->isWeekendOff = false;

        $this->loadCurrentAttendanceRecord();
        session()->flash('successMessage', 'Clocked in successfully at ' . $currentDateTime->format('h:i A'));
    }

    public function confirmClockInOnHoliday(): void
    {
        if (empty($this->pendingCoordinates)) {
            $this->isHolidayConfirmModalOpen = false;
            return;
        }

        $lat = (float) $this->pendingCoordinates['lat'];
        $lng = (float) $this->pendingCoordinates['lng'];

        $this->isConfirmedToWorkOnHoliday = true;
        $this->isHolidayConfirmModalOpen = false;

        $this->clockIn($lat, $lng);
    }

    public function cancelHolidayClockIn(): void
    {
        $this->isHolidayConfirmModalOpen = false;
        $this->pendingCoordinates = null;
        $this->detectedPublicHoliday = null;
        $this->isConfirmedToWorkOnHoliday = false;
        $this->isHolidayOff = false;
        $this->isWeekendOff = false;
    }

    public function openBreakModal(): void
    {
        $this->isBreakModalOpen = true;
    }

    public function startBreak(?float $latitude = null, ?float $longitude = null): void
    {
        if ($this->currentAttendanceRecord === null || $this->activeBreakRecord !== null) {
            return;
        }

        $now = Carbon::now();
        $isPaid = in_array($this->selectedBreakType, ['tea', 'official']);

        AttendanceBreak::create([
            'attendance_id'   => $this->currentAttendanceRecord->id,
            'break_type'      => $this->selectedBreakType,
            'break_start'     => $now->toTimeString(),
            'reason'          => !empty($this->breakReason) ? $this->breakReason : null,
            'is_paid'         => $isPaid,
            'start_latitude'  => $latitude,
            'start_longitude' => $longitude,
        ]);

        $this->isBreakModalOpen = false;
        $this->breakReason = '';
        $this->loadCurrentAttendanceRecord();
        session()->flash('successMessage', 'Break started.');
    }

    public function confirmClockInOnLeave(): void
    {
        if (empty($this->pendingCoordinates)) {
            $this->isLeaveConfirmModalOpen = false;
            return;
        }

        $lat = (float) $this->pendingCoordinates['lat'];
        $lng = (float) $this->pendingCoordinates['lng'];

        $this->isConfirmedToWorkOnLeave = true;
        $this->isLeaveConfirmModalOpen = false;

        $this->clockIn($lat, $lng);
    }

    public function cancelLeaveClockIn(): void
    {
        $this->isLeaveConfirmModalOpen = false;
        $this->pendingCoordinates = null;
        $this->detectedFullDayLeave = null;
        $this->detectedLeaveRecord = null;
        $this->isConfirmedToWorkOnLeave = false;
    }

    public function endBreak(?float $latitude = null, ?float $longitude = null): void
    {
        if ($this->activeBreakRecord === null) {
            return;
        }

        $now = Carbon::now();
        $startTime = Carbon::parse(Carbon::today()->toDateString() . ' ' . $this->activeBreakRecord->break_start);
        $durationMinutes = (int) round(abs($now->diffInMinutes($startTime)));

        $this->activeBreakRecord->update([
            'break_end'        => $now->toTimeString(),
            'duration_minutes' => $durationMinutes,
            'end_latitude'     => $latitude,
            'end_longitude'    => $longitude,
        ]);

        $this->activeBreakRecord = null;
        $this->loadCurrentAttendanceRecord();
        session()->flash('successMessage', 'Resumed work.');
    }

    /**
     * Retrieve attendance data, apply date filtering, and perform record transformation
     */
    private function getAttendanceData()
    {
        if ($this->authenticatedEmployee === null) {
            return collect();
        }

        $dateFormat = view()->shared('globalCalendarFormat', 'd/m/Y');

        $query = Attendance::with(['shift', 'breaks'])
            ->where('employee_id', $this->authenticatedEmployee->id);

        if (!empty($this->filterDateFrom) && !empty($this->filterDateTo)) {
            $query->whereBetween('date', [$this->filterDateFrom, $this->filterDateTo]);
        } elseif (!empty($this->filterDateFrom)) {
            $query->whereDate('date', '>=', $this->filterDateFrom);
        } elseif (!empty($this->filterDateTo)) {
            $query->whereDate('date', '<=', $this->filterDateTo);
        }

        $paginatedAttendances = $query->orderBy('date', 'desc')->paginate(15);

        $paginatedAttendances->getCollection()->transform(function ($record) use ($dateFormat) {
            $recordDateStr = Carbon::parse($record->date)->toDateString();

            // Retrieve approved leave details for the specific date
            $leaveDetails = DB::table('leave_requests')
                ->leftJoin('apply_leave_types', 'leave_requests.id', '=', 'apply_leave_types.leave_request_id')
                ->leftJoin('leave_types', 'apply_leave_types.leave_type_id', '=', 'leave_types.id')
                ->where('leave_requests.employee_id', $this->authenticatedEmployee->id)
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

            $record->is_edited = AttendanceApproval::where('attendance_id', $record->id)
                ->where('status', 'approved')
                ->exists();

            // Set record properties (directly from database values)
            $record->display_date        = Carbon::parse($record->date)->format($dateFormat);
            $record->display_shift       = $record->shift?->name ?? 'Default';
            $record->display_in_time     = !empty($record->in_time) ? Carbon::parse($record->in_time)->format('h:i A') : '--:--';
            $record->display_out_time    = !empty($record->out_time) ? Carbon::parse($record->out_time)->format('h:i A') : '--:--';
            $record->display_break_mins  = (int) $record->breaks->sum('duration_minutes');
            $record->display_break_count = $record->breaks->count();
            $record->display_work_hours  = (float) ($record->total_worked_hours ?? 0);
            $record->display_ot_hours    = (float) ($record->ot_hours ?? 0);
            $record->display_late_mins   = (int) ($record->late_minutes ?? 0);
            $record->display_early_mins  = (int) ($record->early_minutes ?? 0);
            $record->display_status      = $record->status ?? 'present';

            // Assign leave properties
            $record->leave_type_name = $leaveDetails?->leave_type_name ?? 'General Leave';
            $record->is_half_day     = $leaveDetails?->is_half_day ?? 0;
            $leaveShift     = $leaveDetails?->leave_shift;

            // Check for pending approval requests
            $record->has_pending = AttendanceApproval::where('employee_id', $this->authenticatedEmployee->id)
                ->where('requested_date', $recordDateStr)
                ->where('status', 'pending')
                ->exists();

            return $record;
        });

        return $paginatedAttendances;
    }

    /**
     * Render the Livewire component view
     */
    public function render(): View
    {
        return view('livewire.attendance.mark-attendance', [
            'attendances' => $this->getAttendanceData()
        ]);
    }
}