<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Organisation\OrganisationProfile;
use App\Livewire\Organisation\WorkLocation;
use App\Livewire\Organisation\Department;
use App\Livewire\Organisation\Designation;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : view('welcome');
});


Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

// Add your routes here


Route::prefix('org-settings')->name('settings.')->group(function () {
    Route::get('/organisation', OrganisationProfile::class)->name('organisation');
    Route::get('/work-locations', WorkLocation::class)->name('work-locations');
    Route::get('/departments', Department::class)->name('departments');
    Route::get('/designations', Designation::class)->name('designations');
});


require __DIR__ . '/auth.php';
