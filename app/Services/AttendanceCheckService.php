<?php
namespace App\Services;

use App\Models\Employee;
use App\Models\Attendance;
use App\Models\Shift;
use App\Models\PublicHoliday;
use App\Models\CompanyCalendarSetting;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;

class AttendanceCheckService
{
    public function runAutoAbsentCheck()
    {
        $now = Carbon::now();

        // 1. Find the latest recorded date in the attendances table
        $lastAttendanceDate = Attendance::max('date');

        // 2. Start from latest recorded date if exists, or from today if fresh system
        $startDate = $lastAttendanceDate 
            ? Carbon::parse($lastAttendanceDate)->startOfDay() 
            : Carbon::now()->startOfDay(); // Default to today start of day

        $endDate = Carbon::now()->endOfDay();

        // Cap start date to today to prevent checking future dates
        if ($startDate->greaterThan($endDate)) {
            $startDate = Carbon::now()->startOfDay();
        }

        $period = CarbonPeriod::create($startDate, '1 day', $endDate);

        foreach ($period as $date) {
            $targetDateString = $date->toDateString();
            $dayOfWeekName = $date->format('l'); // e.g., 'Monday', 'Saturday', 'Sunday'

            // Read holiday and weekend configuration for the date[cite: 6, 7]
            $publicHoliday = PublicHoliday::whereDate('holiday_date', $targetDateString)->first();
            $calendarSetting = CompanyCalendarSetting::where('day_of_week', $dayOfWeekName)->first();
            $isCompanyWeekend = ($calendarSetting && !$calendarSetting->is_working_day);

            $employees = Employee::where('status', 'active')->get();

            foreach ($employees as $employee) {
                
                $alreadyRecorded = Attendance::where('employee_id', $employee->id)
                    ->where('date', $targetDateString)
                    ->exists();

                if (!$alreadyRecorded) {
                    
                    // If date is a public holiday, record directly without waiting for shift end[cite: 6, 7]
                    if ($publicHoliday) {
                        $holidayStatus = ($publicHoliday->holiday_type === 'Half Day') ? 'half_day_holiday' : 'holiday';

                        Attendance::create([
                            'employee_id'        => $employee->id,
                            'shift_id'           => $employee->shift_id,
                            'date'               => $targetDateString,
                            'in_time'            => null,
                            'out_time'           => null,
                            'late_minutes'       => 0,
                            'early_minutes'      => 0,
                            'total_worked_hours' => 0.00,
                            'ot_hours'           => 0.00,
                            'status'             => $holidayStatus,
                            'reason'             => $publicHoliday->description ?? 'Public Holiday',
                        ]);
                        continue;
                    }

                    // If date is a weekend/non-working day, record directly without waiting for shift end[cite: 6, 7]
                    if ($isCompanyWeekend) {
                        Attendance::create([
                            'employee_id'        => $employee->id,
                            'shift_id'           => $employee->shift_id,
                            'date'               => $targetDateString,
                            'in_time'            => null,
                            'out_time'           => null,
                            'late_minutes'       => 0,
                            'early_minutes'      => 0,
                            'total_worked_hours' => 0.00,
                            'ot_hours'           => 0.00,
                            'status'             => 'weekend',
                            'reason'             => 'Weekly Off Day / Weekend',
                        ]);
                        continue;
                    }

                    // On normal working days, perform absent/leave check after shift has ended[cite: 6, 7]
                    $shiftEndTime = '16:00:00';
                    $shiftStartTime = '08:00:00';

                    if (!empty($employee->shift_id)) {
                        $assignedShift = Shift::find($employee->shift_id);
                        if ($assignedShift && !empty($assignedShift->end_time)) {
                            $shiftEndTime = Carbon::parse($assignedShift->end_time)->format('H:i:s');
                            $shiftStartTime = Carbon::parse($assignedShift->start_time)->format('H:i:s');
                        }
                    }

                    $scheduledShiftStartTime = Carbon::parse($targetDateString . ' ' . $shiftStartTime);
                    $scheduledShiftEndTime = Carbon::parse($targetDateString . ' ' . $shiftEndTime);

                    // Handle overnight shift timing[cite: 6, 7]
                    if ($scheduledShiftEndTime->lessThan($scheduledShiftStartTime)) {
                        $scheduledShiftEndTime->addDay();
                    }

                    $isTargetToday = $date->isToday();
                    $shouldRunCheck = !$isTargetToday || $now->greaterThanOrEqualTo($scheduledShiftEndTime);

                    if ($shouldRunCheck) {
                        
                        // Check if employee has an approved leave request for the date[cite: 6, 7]
                        $leaveRequest = DB::table('leave_requests')
                            ->where('employee_id', $employee->id)
                            ->whereIn(DB::raw('LOWER(status)'), ['approved'])
                            ->whereDate('start_date', '<=', $targetDateString)
                            ->whereDate('end_date', '>=', $targetDateString)
                            ->first();

                        $autoStatus = 'absent';
                        $reason = null;

                        if ($leaveRequest !== null) {
                            $isHalfDay = (isset($leaveRequest->is_half_day) && (int) $leaveRequest->is_half_day === 1)
                                || (isset($leaveRequest->day_type) && strtolower($leaveRequest->day_type) === 'half_day');

                            if ($isHalfDay) {
                                $autoStatus = 'half_day_absent';
                                $reason = 'Approved Half-Day Leave - Absent on duty hours';
                            } else {
                                $autoStatus = 'leave';
                                $reason = $leaveRequest->reason ?? 'Approved Leave';
                            }
                        } else {
                            // If half-day weekend, set half_day_absent according to company rule[cite: 6, 7]
                            if ($calendarSetting && $calendarSetting->day_type === 'Half Day') {
                                $autoStatus = 'half_day_absent';
                                $reason = 'Weekend Half Day Absent';
                            } else {
                                $autoStatus = 'absent';
                                $reason = 'Absent (No Punch Record)';
                            }
                        }

                        Attendance::create([
                            'employee_id'        => $employee->id,
                            'shift_id'           => $employee->shift_id,
                            'date'               => $targetDateString,
                            'in_time'            => null,
                            'out_time'           => null,
                            'late_minutes'       => 0,
                            'early_minutes'      => 0,
                            'total_worked_hours' => 0.00,
                            'ot_hours'           => 0.00,
                            'status'             => $autoStatus,
                            'reason'             => $reason,
                        ]);
                    }
                }
            }
        }
    }
}