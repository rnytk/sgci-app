<?php

namespace App\Providers;

use BezhanSalleh\FilamentShield\Facades\FilamentShield;
use Illuminate\Support\ServiceProvider;

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

        FilamentShield::enforcePolicies();

        \App\Models\User::observe(\App\Observers\UserObserver::class);
        \App\Models\Enrollment::observe(\App\Observers\EnrollmentObserver::class);

    }
}
