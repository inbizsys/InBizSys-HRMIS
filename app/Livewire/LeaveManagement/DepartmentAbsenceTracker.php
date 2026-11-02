<?php

namespace App\Livewire\LeaveManagement;

use Livewire\Component;
use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Exception;

class DepartmentAbsenceTracker extends Component
{
    // --- PROPERTY DECLARATION BLOCK ---
    public string $searchDate = '';

   
    public function mount(): void
    {
        /** @var User|null $authenticatedUser */
        $authenticatedUser = Auth::user();

        // Basic authentication enforcement layer (Must be logged in to the application)
        if (!$authenticatedUser) {
            abort(401, 'Unauthenticated session encounter.');
        }

        // Default calendar state value maps straight to today's execution format
        $this->searchDate = Carbon::today()->format('Y-m-d');
    }

    
    public function fetchApprovedAbsentEmployees(): Collection
    {
        /** @var User|null $authenticatedUser */
        $authenticatedUser = Auth::user();
        $authenticatedEmployeeProfile = $authenticatedUser ? $authenticatedUser->employee : null;

        try {
            $targetAbsenceDate = !empty($this->searchDate) 
                ? Carbon::parse($this->searchDate)->format('Y-m-d') 
                : Carbon::today()->format('Y-m-d');
        } catch (Exception $invalidDateException) {
            Log::error('Invalid calendar token input caught during absence evaluation: ' . $this->searchDate);
            $targetAbsenceDate = Carbon::today()->format('Y-m-d');
        }

        $absenceQueryBuilder = LeaveRequest::with(['employee.user', 'applyLeaveTypes.leaveType'])
        ->where('status', 'Approved');

        // Check evaluating authorization overrides (Super Admin, ID 1, or HR can view all departments)
        $isElevatedUser = $authenticatedUser && ($authenticatedUser->hasRole('Super Admin') || $authenticatedUser->hasRole('HR'));
        
        // Check if the current user explicitly possesses a manager/supervisor execution context
        $isSupervisor = $authenticatedUser && $authenticatedUser->hasRole('Supervisor');

        // --- SCOPE DECK INJECTION BOUNDS ---
        // Strictly filter by department ONLY if the user is a Supervisor and not an elevated administrator
        if ($isSupervisor && !$isElevatedUser && $authenticatedEmployeeProfile && !empty($authenticatedEmployeeProfile->department_id)) {
            $absenceQueryBuilder->whereHas('employee', function ($departmentFilterQuery) use ($authenticatedEmployeeProfile) {
                $departmentFilterQuery->where('department_id', $authenticatedEmployeeProfile->department_id);
            });
        }

        // Match records where the target calendar date falls strictly inside the approved leave allocation interval
        return $absenceQueryBuilder->where(function ($dateEvaluationQuery) use ($targetAbsenceDate) {
                $dateEvaluationQuery->whereDate('start_date', '<=', $targetAbsenceDate)
                                    ->whereDate('end_date', '>=', $targetAbsenceDate);
            })
            ->get();
    }

   
    #[Layout('layouts.app')] 
    public function render(): View
    {
        return view('livewire.LeaveManagement.department-absence-tracker', [
            'absentEmployeesCollection' => $this->fetchApprovedAbsentEmployees(),
        ]);
    }
}