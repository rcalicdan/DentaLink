<?php

namespace App\Providers;

use App\Models\Appointment;
use App\Models\DentalService;
use App\Models\Patient;
use App\Models\PatientVisit;
use App\Observers\AppointmentObserver;
use App\Observers\DentalServiceObserver;
use App\Observers\PatientObserver;
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
        Patient::observe(PatientObserver::class);
        Appointment::observe(AppointmentObserver::class);
        DentalService::observe(DentalServiceObserver::class);
        PatientVisit::observe(PatientVisitObserver::class);
    }
}
