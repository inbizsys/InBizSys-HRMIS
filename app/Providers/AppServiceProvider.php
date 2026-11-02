<?php

namespace App\Providers;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Organisation;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

        Gate::before(function ($user, $ability) {
            return $user->hasRole('Super Admin') ? true : null;
        });


       if (Schema::hasTable('organisations')) {

            $organization = Organisation::first();
            
            $dbFormat = ($organization) ? ($organization->date_format ?? 'dd/mm/yyyy') : 'dd/mm/yyyy';
            $calendarFormat = str_replace(['dd', 'mm', 'yyyy'], ['d', 'm', 'Y'], $dbFormat);

            View::share('globalPlaceholderFormat', $dbFormat);       
            View::share('globalCalendarFormat', $calendarFormat);     
        }
    }
}
