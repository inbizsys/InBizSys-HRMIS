<?php

namespace App\Livewire\Attendance;

use App\Models\Attendance;
use App\Models\AttendanceApproval;
use App\Models\AttendanceBreak;
use App\Models\Shift;
use App\Models\Payroll;
use Livewire\Attributes\Layout;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class ManageAttendanceApprovals extends Component
{
    use WithPagination;

    public bool $isViewModalOpen = false;
    public $selectedApproval = null;

    // Confirmation Modal States
    public bool $isConfirmModalOpen = false;
    public $confirmId = null;
    public $confirmType = null;
    public $confirmActionType = null; // 'approve' or 'reject'

    public function confirmAction($id, $type, $actionType): void
    {
        if ($actionType === 'approve') {
            $approval = AttendanceApproval::with('attendance')->find($id);
            $targetDate = $approval?->requested_date ?? $approval?->attendance?->date;
            if ($targetDate) {
                $targetMonth = substr($targetDate, 0, 7);
                $isLocked = Payroll::where('payroll_month', $targetMonth)
                    ->whereIn('status', ['approved', 'paid'])
                    ->exists();

                if ($isLocked) {
                    session()->flash('errorMessage', 'Cannot approve request. Payroll for ' . date('F Y', strtotime($targetMonth . '-01')) . ' is approved/paid and locked. Please reverse the payroll first.');
                    return;
                }
            }
        }

        $this->confirmId = $id;
        $this->confirmType = $type;
        $this->confirmActionType = $actionType;
        $this->isConfirmModalOpen = true;
    }

    public function closeConfirmationModal(): void
    {
        $this->isConfirmModalOpen = false;
        $this->confirmId = null;
        $this->confirmType = null;
        $this->confirmActionType = null;
    }

    public function executeAction(): void
    {
        if (!$this->confirmId) {
            return;
        }

        if ($this->confirmActionType === 'approve') {
            $this->approveAttendanceRequest($this->confirmId);
        } else {
            $this->rejectAttendanceRequest($this->confirmId);
        }

        $this->closeConfirmationModal();
    }
    
    public function openApprovalDetails(int $id): void
    {
        $this->selectedApproval = AttendanceApproval::with([
            'employee', 
            'attendance.shift', 
            'attendance.breaks',
            'breakApprovals.attendanceBreak.breakType'
        ])->findOrFail($id);

        $this->isViewModalOpen = true;
    }

    public function closeApprovalDetails(): void
    {
        $this->isViewModalOpen = false;
        $this->selectedApproval = null;
    }

    public function approveAttendanceRequest(int $id): void
    {
        DB::transaction(function () use ($id) {
            $approval = AttendanceApproval::with('breakApprovals')->findOrFail($id);
            $attendance = Attendance::with(['employee'])->find($approval->attendance_id);

            if (!$attendance) {
                session()->flash('errorMessage', 'Attendance record not found.');
                return;
            }

            // Method 1 enforcement: Disallow approval while employee is on duty without out_time
            $finalDate = $approval->requested_date ?? $attendance->date;
            $finalInTime = $approval->requested_in_time ?? $attendance->in_time;
            $finalOutTime = $approval->requested_out_time ?? $attendance->out_time;
            $dayOfWeekName = Carbon::parse($finalDate)->format('l');

            // Period lock: Block approval if payroll is approved or paid
            $targetMonth = substr($finalDate, 0, 7);
            $isLocked = Payroll::where('payroll_month', $targetMonth)
                ->whereIn('status', ['approved', 'paid'])
                ->exists();

            if ($isLocked) {
                session()->flash('errorMessage', 'Cannot approve request. Payroll for ' . date('F Y', strtotime($targetMonth . '-01')) . ' is approved/paid and locked. Please reverse the payroll first.');
                return;
            }

            // 1. Company Calendar, Holiday & Weekend Check
            $publicHoliday = \App\Models\PublicHoliday::whereDate('holiday_date', $finalDate)->first();
            $calendarSetting = \App\Models\CompanyCalendarSetting::where('day_of_week', $dayOfWeekName)->first();
            $isCompanyWeekend = ($calendarSetting && !$calendarSetting->is_working_day);

          
            $shiftId = $attendance->shift_id 
                ?: DB::table('employees')->where('id', $approval->employee_id)->value('shift_id');
            $assignedShift = $shiftId ? DB::table('shifts')->where('id', $shiftId)->first() : null;

            $scheduledShiftEndTime = null;
            if ($assignedShift && !empty($assignedShift->end_time)) {
                $shiftEndParsed = date('H:i:s', strtotime($assignedShift->end_time));
                $scheduledShiftEndTime = Carbon::parse($finalDate . ' ' . $shiftEndParsed);
            }

            
            if (empty($finalOutTime)) {
                $now = Carbon::now();
                
               
                if ($scheduledShiftEndTime && $finalDate === $now->toDateString() && $now->lessThan($scheduledShiftEndTime)) {
                    session()->flash('errorMessage', 'Cannot approve: Employee is currently on duty. Please wait until shift ends or clock-out.');
                    return;
                }

                
                $finalOutTime = $assignedShift?->end_time ? date('H:i:s', strtotime($assignedShift->end_time)) : '17:00:00';
            }

           // =========================================================================
            // 2. PROCESS BREAK ADJUSTMENTS (Create, Update, Delete)
            // =========================================================================
            
            $breakTypesMap = DB::table('break_types')
                ->get()
                ->keyBy(fn($item) => strtolower(trim($item->code ?? $item->name)));

            $processedBreakIds = [];
            foreach ($approval->breakApprovals as $bApp) {
                $startTime = $bApp->requested_start_time 
                    ? date('H:i:s', strtotime($bApp->requested_start_time)) 
                    : null;

                $endTime = $bApp->requested_end_time 
                    ? date('H:i:s', strtotime($bApp->requested_end_time)) 
                    : null;

                $durationMinutes = 0;
                if ($startTime && $endTime) {
                    $durationMinutes = (int) round(
                        abs(Carbon::parse($endTime)->diffInMinutes(Carbon::parse($startTime)))
                    );
                }

                $bTypeKey  = strtolower(trim($bApp->break_type ?? 'general'));
                $matchedBt = $breakTypesMap->get($bTypeKey);

                $isPaid = $matchedBt !== null
                    ? (bool) $matchedBt->is_paid
                    : in_array($bTypeKey, ['tea', 'official']);

                if (!empty($bApp->attendance_break_id)) {
                    $break = AttendanceBreak::find($bApp->attendance_break_id);
                    if ($break) {
                        $break->update([
                            'break_type'       => $bApp->break_type ?? $break->break_type,
                            'break_start'      => $startTime ?? $break->break_start,
                            'break_end'        => $endTime ?? $break->break_end,
                            'duration_minutes' => $durationMinutes > 0 ? $durationMinutes : $break->duration_minutes,
                            'is_paid'          => $isPaid,
                        ]);
                        $processedBreakIds[] = $break->id;
                    }
                } else {
                    $newBreak = AttendanceBreak::create([
                        'attendance_id'    => $attendance->id,
                        'break_type'       => $bApp->break_type ?? 'tea',
                        'break_start'      => $startTime,
                        'break_end'        => $endTime,
                        'duration_minutes' => $durationMinutes,
                        'is_paid'          => $isPaid, 
                    ]);
                    $processedBreakIds[] = $newBreak->id;
                }

                $bApp->update(['status' => 'approved']);
            }

            AttendanceBreak::where('attendance_id', $attendance->id)
                ->whereNotIn('id', $processedBreakIds)
                ->delete();

            // =========================================================================
            // 3. METRICS RECALCULATION & ALL COMPLEX LOGICS (Half-Days, Leaves, Shifts)
            // =========================================================================
            $shiftId = DB::table('employees')->where('id', $approval->employee_id)->value('shift_id')
                ?? $attendance->shift_id;

            $assignedShift = $shiftId ? DB::table('shifts')->where('id', $shiftId)->first() : null;

            $calculatedLateMinutes = 0;
            $calculatedEarlyMinutes = 0;
            $calculatedOtHours = 0.00;
            $totalWorkedHoursCalculated = 0.00;
            $attendanceStatus = 'present';

            // Work Hours calculation
            if (!empty($finalInTime) && !empty($finalOutTime)) {
                $inTimeParsed = date('H:i:s', strtotime($finalInTime));
                $outTimeParsed = date('H:i:s', strtotime($finalOutTime));

                $inTimestamp = Carbon::parse($finalDate . ' ' . $inTimeParsed);
                $outTimestamp = Carbon::parse($finalDate . ' ' . $outTimeParsed);
                
                if ($outTimestamp->lessThan($inTimestamp)) {
                    $outTimestamp->addDay();
                }

                $grossMinutes = abs($outTimestamp->diffInMinutes($inTimestamp));

                $unpaidBreakMinutes = (float) AttendanceBreak::where('attendance_id', $attendance->id)
                    ->where('is_paid', false)
                    ->sum('duration_minutes');

                $netMinutes = max(0, $grossMinutes - $unpaidBreakMinutes);
                $totalWorkedHoursCalculated = round($netMinutes / 60, 2);
            }

            // 3.1 Holiday, Weekend, or Full Shift-based / Half-Day Recalculations
            if ($publicHoliday !== null) {
                $attendanceStatus = 'holiday_present';
                $calculatedLateMinutes = 0;
                $calculatedEarlyMinutes = 0;
            } elseif ($isCompanyWeekend) {
                $attendanceStatus = 'weekend_present';
                $calculatedLateMinutes = 0;
                $calculatedEarlyMinutes = 0;
            } else {
                if ($assignedShift && !empty($assignedShift->start_time)) {
                    $shiftStartParsed = date('H:i:s', strtotime($assignedShift->start_time));
                    $scheduledShiftStartTime = Carbon::parse($finalDate . ' ' . $shiftStartParsed);

                    $scheduledShiftEndTime = null;
                    if (!empty($assignedShift->end_time)) {
                        $shiftEndParsed = date('H:i:s', strtotime($assignedShift->end_time));
                        $scheduledShiftEndTime = Carbon::parse($finalDate . ' ' . $shiftEndParsed);
                    }

                    if ($scheduledShiftEndTime && $scheduledShiftEndTime->lessThan($scheduledShiftStartTime)) {
                        $scheduledShiftEndTime->addDay();
                    }

                    $lateGraceMinutes = (int) ($assignedShift->late_grace_minutes ?? 0);
                    $earlyGraceMinutes = (int) ($assignedShift->early_leave_grace_minutes ?? 0);

                    $halfDayHours = !empty($assignedShift->half_day_min_hours) ? (float) $assignedShift->half_day_min_hours : 1.0;
                    $halfDayMinutes = (int) ($halfDayHours * 60);

                    $isWorkOnLeaveRequest = str_starts_with($approval->reason, 'Auto-Generated: Employee reported');

                    $leaveDetails = DB::table('leave_requests')
                        ->where('employee_id', $attendance->employee_id)
                        ->whereIn('status', ['approved', 'worked_on_leave'])
                        ->whereDate('start_date', '<=', $finalDate)
                        ->whereDate('end_date', '>=', $finalDate)
                        ->first();

                    $isHalfDay = $leaveDetails && (int) $leaveDetails->is_half_day === 1;
                    $leaveShift = strtolower(trim((string) ($leaveDetails->leave_shift ?? '')));

                    $inTimeParsed = !empty($finalInTime) ? date('H:i:s', strtotime($finalInTime)) : null;
                    $outTimeParsed = !empty($finalOutTime) ? date('H:i:s', strtotime($finalOutTime)) : null;

                    $inTimestamp = $inTimeParsed ? Carbon::parse($finalDate . ' ' . $inTimeParsed) : null;
                    $outTimestamp = $outTimeParsed ? Carbon::parse($finalDate . ' ' . $outTimeParsed) : null;

                    if ($inTimestamp && $outTimestamp && $outTimestamp->lessThan($inTimestamp)) {
                        $outTimestamp->addDay();
                    }

                    $secondHalfStartTime = $scheduledShiftStartTime->copy()->addMinutes($halfDayMinutes);
                    $isWorkedDuringMorningLeave = ($isHalfDay && in_array($leaveShift, ['morning', 'first_half']) && $inTimestamp && $inTimestamp->lessThan($secondHalfStartTime));

                    // CASE A: EVENING HALF-DAY LEAVE SCENARIO
                    if ($isHalfDay && in_array($leaveShift, ['evening', 'second_half'])) {
                        $firstHalfEndTime = $scheduledShiftStartTime->copy()->addMinutes($halfDayMinutes);

                        if ($isWorkOnLeaveRequest || ($outTimestamp && $outTimestamp->greaterThan($firstHalfEndTime))) {
                            if ($inTimestamp && $inTimestamp->gt($scheduledShiftStartTime)) {
                                $rawDiff = (int) $scheduledShiftStartTime->diffInMinutes($inTimestamp);
                                $calculatedLateMinutes = ($rawDiff > $lateGraceMinutes) ? $rawDiff : 0;
                                $attendanceStatus = ($calculatedLateMinutes > 0) ? 'late' : 'present';
                            } else {
                                $attendanceStatus = 'present';
                            }

                            if ($outTimestamp && $scheduledShiftEndTime && $outTimestamp->lessThan($scheduledShiftEndTime)) {
                                $rawEarly = (int) $outTimestamp->diffInMinutes($scheduledShiftEndTime);
                                $calculatedEarlyMinutes = ($rawEarly > $earlyGraceMinutes) ? $rawEarly : 0;
                            }
                        } else {
                            if ($inTimestamp) {
                                if ($inTimestamp->gt($scheduledShiftStartTime)) {
                                    $rawDiff = (int) $scheduledShiftStartTime->diffInMinutes($inTimestamp);
                                    $calculatedLateMinutes = ($rawDiff > $lateGraceMinutes) ? $rawDiff : 0;
                                    $attendanceStatus = ($calculatedLateMinutes > 0) ? 'half_day_evening_late' : 'half_day_evening_present';
                                } else {
                                    $attendanceStatus = 'half_day_evening_present';
                                }
                            }

                            if ($outTimestamp) {
                                $rawEarly = (int) $outTimestamp->diffInMinutes($firstHalfEndTime);
                                $calculatedEarlyMinutes = ($rawEarly > $earlyGraceMinutes) ? $rawEarly : 0;
                            }
                        }

                    // CASE B: MORNING HALF-DAY LEAVE SCENARIO
                    } elseif ($isHalfDay && in_array($leaveShift, ['morning', 'first_half']) && !$isWorkedDuringMorningLeave) {
                        if ($inTimestamp) {
                            if ($inTimestamp->gt($secondHalfStartTime)) {
                                $rawDiff = (int) $secondHalfStartTime->diffInMinutes($inTimestamp);
                                $calculatedLateMinutes = ($rawDiff > $lateGraceMinutes) ? $rawDiff : 0;
                                $attendanceStatus = ($calculatedLateMinutes > 0) ? 'half_day_morning_late' : 'half_day_morning_present';
                            } else {
                                $calculatedLateMinutes = 0;
                                $attendanceStatus = 'half_day_morning_present';
                            }
                        }

                        if ($outTimestamp) {
                            if ($scheduledShiftEndTime && $outTimestamp->lessThan($scheduledShiftEndTime)) {
                                $rawEarly = (int) $outTimestamp->diffInMinutes($scheduledShiftEndTime);
                                $calculatedEarlyMinutes = ($rawEarly > $earlyGraceMinutes) ? $rawEarly : 0;
                            }
                        }

                    // CASE C: REGULAR FULL-DAY SCENARIO
                    } else {
                        if ($inTimestamp) {
                            if ($inTimestamp->gt($scheduledShiftStartTime)) {
                                $rawDiff = (int) $scheduledShiftStartTime->diffInMinutes($inTimestamp);
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

                        if ($outTimestamp && $scheduledShiftEndTime) {
                            if ($outTimestamp->lessThan($scheduledShiftEndTime)) {
                                $rawEarly = (int) $outTimestamp->diffInMinutes($scheduledShiftEndTime);
                                $calculatedEarlyMinutes = ($rawEarly > $earlyGraceMinutes) ? $rawEarly : 0;
                            }
                        }
                    }
                }
            }

            // =========================================================================
            // 4. UPDATE ATTENDANCE RECORD WITH RECALCULATED METRICS
            // =========================================================================
            $attendance->update([
                'shift_id'           => $assignedShift?->id ?? $attendance->shift_id,
                'date'               => $finalDate,
                'in_time'            => $finalInTime,
                'out_time'           => $finalOutTime,
                'late_minutes'       => $calculatedLateMinutes,
                'early_minutes'      => $calculatedEarlyMinutes,
                'total_worked_hours' => $totalWorkedHoursCalculated,
                'ot_hours'           => $calculatedOtHours,
                'status'             => $attendanceStatus,
                'reason'             => $approval->reason,
            ]);

            // =========================================================================
            // 5. RESTORE LEAVE QUOTA ONLY IF THIS WAS A WORK-ON-LEAVE REQUEST
            // =========================================================================
            $isWorkOnLeave = str_starts_with($approval->reason, 'Auto-Generated: Employee reported');

            $restoreDays = 0;
            if ($isWorkOnLeave) {
                $approvedLeave = DB::table('leave_requests')
                    ->where('employee_id', $approval->employee_id)
                    ->whereIn('status', ['approved', 'worked_on_leave'])
                    ->whereDate('start_date', '<=', $finalDate)
                    ->whereDate('end_date', '>=', $finalDate)
                    ->first();

                if ($approvedLeave) {
                    $restoreDays = ((int) $approvedLeave->is_half_day === 1) ? 0.5 : 1.0;

                    DB::table('leave_requests')
                        ->where('id', $approvedLeave->id)
                        ->update(['status' => 'worked_on_leave']);

                    $leaveTypeId = DB::table('apply_leave_types')
                        ->where('leave_request_id', $approvedLeave->id)
                        ->value('leave_type_id')
                        ?? ($approvedLeave->leave_type_id ?? 1);

                    if (Schema::hasTable('leave_balances')) {
                        DB::table('leave_balances')
                            ->where('employee_id', $approval->employee_id)
                            ->where('leave_type_id', $leaveTypeId)
                            ->decrement('used_days', $restoreDays);
                    }
                }
            }

            // Finalize approval record
            $approval->update(['status' => 'approved']);

            $msg = 'Attendance approval processed successfully with calendar and half-day verification.';
            if ($restoreDays > 0) {
                $msg .= ' Leave quota restored (+' . $restoreDays . ' day(s)).';
            }
            session()->flash('successMessage', $msg);
        });
    }

    public function rejectAttendanceRequest(int $id): void
    {
        DB::transaction(function () use ($id) {
            $approval = AttendanceApproval::with('breakApprovals')->findOrFail($id);
            $attendance = Attendance::find($approval->attendance_id);

            if ($approval->status === 'pending') {
                foreach ($approval->breakApprovals as $bApp) {
                    $bApp->update(['status' => 'rejected']);
                }

                if ($attendance && str_starts_with((string) $approval->reason, 'Auto-Generated: Employee reported')) {
                    $attendance->update([
                        'in_time'            => null,
                        'out_time'           => null,
                        'late_minutes'       => 0,
                        'early_minutes'      => 0,
                        'total_worked_hours' => 0.00,
                        'ot_hours'           => 0.00,
                        'status'             => 'leave',
                        'reason'             => 'Work on leave request rejected by Admin.',
                    ]);
                }

                $approval->update(['status' => 'rejected']);
                session()->flash('successMessage', 'Request rejected. Safety rollbacks applied.');
            }
        });
    }

    private function checkIsOnDuty($app): bool
    {
        $hasNoOutTime = empty($app->attendance?->out_time) && empty($app->requested_out_time);
        $recordDate = $app->requested_date ?? $app->attendance?->date;
        $now = Carbon::now();

        if (!$hasNoOutTime || $recordDate !== $now->toDateString()) {
            return false;
        }

        $shift = $app->attendance?->shift;
        if ($shift && !empty($shift->end_time)) {
            $shiftEndTime = Carbon::parse($recordDate . ' ' . date('H:i:s', strtotime($shift->end_time)));
            return $now->lessThan($shiftEndTime);
        }

        return true;
    }

    public function render()
    {
        $approvals = AttendanceApproval::with([
                'employee', 
                'attendance.shift', 
                'breakApprovals.attendanceBreak.breakType'
            ])
            ->where('status', 'pending')
            ->latest()
            ->get();

        $approvals->transform(function ($app) {
            $app->is_on_duty = $this->checkIsOnDuty($app);
            return $app;
        });

        return view('livewire.attendance.manage-attendance-approvals', [
            'attendanceApprovals' => $approvals,
        ]);
    }
}