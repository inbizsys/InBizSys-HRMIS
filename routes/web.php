<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Organisation\OrganisationProfile;
use App\Livewire\Organisation\WorkLocation;
use App\Livewire\Organisation\Department;
use App\Livewire\Organisation\Designation;
use App\Livewire\Employee\EmployeeManagement;
use Illuminate\Support\Facades\Auth;
use App\Livewire\Payroll\BankManagement;
use App\Livewire\Payroll\BranchManagement;
use App\Livewire\Organisation\TaxSlab;
use App\Livewire\SalaryComponent\SalaryComponent;
use App\Livewire\SalaryComponent\EpfEtf;
use App\Livewire\SalaryComponent\Currency;
use App\Livewire\EmployeeManagement\Employee;
use App\Livewire\BankManagement\Banks;
use App\Livewire\BankManagement\Branches;



Route::get('/', function () {
    return Auth::check() ? redirect()->route('dashboard') : view('welcome');
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
        Route::get('/manage', EmployeeManagement::class)->name('manage');
    });

 
    Route::prefix('payroll')->name('payroll.')->group(function () {
        
        Route::get('/salary-components', BankManagement::class)->name('salary-components');
        // /payroll/banks -> route('payroll.banks')
        Route::get('/banks', BankManagement::class)->name('banks');
        // /payroll/branches -> route('payroll.branches')
        Route::get('/branches', BranchManagement::class)->name('branches');
    });
Route::middleware(['auth', 'verified'])->group(function () {

    // Organisation Settings Routes
    Route::prefix('org-settings')->name('settings.')->group(function () {
        Route::get('/organisation', OrganisationProfile::class)->name('organisation');
        Route::get('/work-locations', WorkLocation::class)->name('work-locations');
        Route::get('/departments', Department::class)->name('departments');
        Route::get('/designations', Designation::class)->name('designations');
        Route::get('/tax-slabs', TaxSlab::class)->name('tax-slabs');
    });

    // Setup & Configuration Routes
    Route::prefix('configurations')->name('configurations.')->group(function () {
        // Salary Components
        Route::get('/salary-components', SalaryComponent::class)->name('salary-components');
        // Currency Management
        Route::get('/currencies', Currency::class)->name('currencies');
        // Bank Management
        Route::get('/banks', Banks::class)->name('banks.index');
        Route::get('/banks/{bankId}/branches', Branches::class)->name('banks.branches');
        // EPF/ETF Settings
        Route::get('/epf-etf', EpfEtf::class)->name('epf-etf');
    });

    // Employee Management Routes
    Route::get('/employees', Employee::class)->name('employees.index');
});

require __DIR__ . '/auth.php';
