<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Organisation\OrganisationProfile;
use App\Livewire\Organisation\WorkLocation;
use App\Livewire\Organisation\Department;
use App\Livewire\Organisation\Designation;
use App\Livewire\Employee\EmployeeManagement;
use Illuminate\Support\Facades\Auth;
use App\Livewire\Organisation\TaxSlab;
use App\Livewire\SalaryComponent\SalaryComponent;
use App\Livewire\SalaryComponent\EpfEtf;
use App\Livewire\SalaryComponent\Currency;
use App\Livewire\BankManagement\Banks;
use App\Livewire\BankManagement\Branches;
use App\Livewire\Employee\DocumentCategoryManagement;
use App\Livewire\Employee\EmployeeDocumentManagement;
use App\Livewire\Settings\ManageAccess;
use App\Livewire\LeaveManagement\LeaveApprovalPanel;
use App\Livewire\LeaveManagement\LeaveApplyForm;
use App\Livewire\LeaveManagement\LeaveTypeManagement;
use App\Livewire\LeaveManagement\ManageLeaveBalances;
use App\Livewire\LeaveManagement\HolidayManagement;
use App\Livewire\LeaveManagement\DepartmentAbsenceTracker;
use App\Livewire\Attendance\ShiftAndPolicySetting;
use App\Livewire\Attendance\MarkAttendance;
use App\Livewire\Attendance\ManageAttendanceApprovals;
use App\Livewire\Attendance\AllAttendanceList;
use App\Livewire\Payroll\EmployeeCategoryManager;
use App\Livewire\LeaveManagement\HolidayCategoryManagement;
use App\Livewire\Payroll\SalaryAssignmentManager;
use App\Livewire\Payroll\OvertimeManager;
use App\Livewire\Payroll\PayrollManager;

Route::get('/', function () {
    return Auth::check() ? redirect()->route('dashboard') : redirect()->route('login');
});


Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::middleware(['auth', 'verified'])->group(function () {

    Route::prefix('org-settings')->name('settings.')->group(function () {
        Route::get('/organisation', OrganisationProfile::class)->name('organisation');
        Route::get('/work-locations', WorkLocation::class)->name('work-locations');
        Route::get('/departments', Department::class)->name('departments');
        Route::get('/designations', Designation::class)->name('designations');
    });


    Route::prefix('employees')->name('employees.')->group(function () {
        // /employees/manage -> route('employees.manage')
        Route::get('/manage', EmployeeManagement::class)->middleware('can:view employee')->name('manage');
        Route::get('/document-categories', DocumentCategoryManagement::class)->middleware('can:view document category')->name('document-categories');
        Route::get('/employee/documents', EmployeeDocumentManagement::class)->middleware('can:view employee document')->name('employee.documents');
    });


    Route::prefix('payroll')->name('payroll.')->group(function () {

        // Employee Categories -> route('payroll.categories')
        Route::get('/categories', EmployeeCategoryManager::class)->name('categories');

        Route::get('/salary-assignments',SalaryAssignmentManager::class)->name('salary-assignments');

        Route::get('/overtimes', OvertimeManager::class)->name('overtimes');

        Route::get('/process', PayrollManager::class)->name('process');
    });


    // Organisation Settings Routes
    Route::prefix('org-settings')->name('settings.')->group(function () {
        Route::get('/organisation', OrganisationProfile::class)->middleware('can:view organization profile')->name('organisation');
        Route::get('/work-locations', WorkLocation::class)->middleware('can:view work location')->name('work-locations');
        Route::get('/departments', Department::class)->middleware('can:view department')->name('departments');
        Route::get('/designations', Designation::class)->middleware('can:view designation')->name('designations');
        Route::get('/tax-slabs', TaxSlab::class)->middleware('can:view tax')->name('tax-slabs');
    });

    // Attendance Settings Route 
    Route::middleware(['auth'])->group(function () {
        Route::get('/attendance/settings', ShiftAndPolicySetting::class)->name('attendance.settings');
        Route::get('/attendance/mark', MarkAttendance::class)->name('attendance.mark');
        Route::get('/admin/attendance-approvals', \App\Livewire\Attendance\ManageAttendanceApprovals::class)->name('admin.attendance.approvals');
        Route::get('/attendance/all-records', AllAttendanceList::class)->name('attendance.all');
    });

    // Leave Management Routes
    Route::prefix('leave-management')->name('leave.')->group(function () {

        // Route for administrative leave type configurations
        Route::get('/holiday-categories', HolidayCategoryManagement::class)->name('holiday-categories');
        Route::get('/leave-types', LeaveTypeManagement::class)->middleware('can:view leave type')->name('leave-types');
        Route::get('/leave-balances', ManageLeaveBalances::class)->middleware('can:view leave balance')->name('leave-balances');
        Route::get('/apply', LeaveApplyForm::class)->name('apply');
        Route::get('/approvals', LeaveApprovalPanel::class)->middleware('can:view leave approval')->name('approval-panel');
        Route::get('/holidays', HolidayManagement::class)->middleware('can:view holiday')->name('holidays');
        Route::get('/absence-tracker', DepartmentAbsenceTracker::class)->middleware('can:view department absence tracker')->name('absence-tracker');
    });

    // Setup & Configuration Routes
    Route::prefix('configurations')->name('configurations.')->group(function () {
        // Salary Components
        Route::get('/salary-components', SalaryComponent::class)->middleware('can:view salary component')->name('salary-components');
        // Currency Management
        Route::get('/currencies', Currency::class)->middleware('can:view currency')->name('currencies');
        // Bank Management
        Route::get('/banks', Banks::class)->middleware('can:view bank management')->name('banks.index');
        Route::get('/banks/{bankId}/branches', Branches::class)->name('banks.branches');
        // EPF/ETF Settings
        Route::get('/epf-etf', EpfEtf::class)->middleware('can:view statutory component')->name('epf-etf');
    });


    Route::prefix('settings')->name('settings.')->group(function () {
        // Profile View
        Route::view('/profile', 'profile')->name('profile');
        Route::get('/access-control', ManageAccess::class)->middleware('can:view access control')->name('access-control.index');
    });

});


require __DIR__ . '/auth.php';
