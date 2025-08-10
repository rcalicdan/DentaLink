<?php

namespace App\Providers;

use App\Models\Appointment;
use App\Models\PatientVisit;
use App\Observers\AppointmentObserver;
use App\Observers\PatientVisitObserver;
use Illuminate\Support\ServiceProvider;

class ObserverServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Appointment::observe(AppointmentObserver::class);
        PatientVisit::observe(PatientVisitObserver::class);
    }
}
