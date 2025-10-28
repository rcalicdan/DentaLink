<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Appointment;
use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\DentalService;
use App\Models\DentalServiceType;
use App\Models\Inventory;
use App\Models\Patient;
use App\Models\PatientVisit;
use App\Models\PatientVisitService;
use App\Models\User;
use App\Observers\AppointmentObserver;
use App\Observers\AuditLogObserver;
use App\Observers\BranchObserver;
use App\Observers\DentalServiceObserver;
use App\Observers\DentalServiceTypeObserver;
use App\Observers\InventoryObserver;
use App\Observers\PatientObserver;
use App\Observers\PatientVisitObserver;
use App\Observers\PatientVisitServiceObserver;
use App\Observers\UserObserver;

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
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Appointment::observe(AppointmentObserver::class);
        Branch::observe(BranchObserver::class);
        DentalService::observe(DentalServiceObserver::class);
        DentalServiceType::observe(DentalServiceTypeObserver::class);
        Inventory::observe(InventoryObserver::class);
        Patient::observe(PatientObserver::class);
        PatientVisit::observe(PatientVisitObserver::class);
        PatientVisitService::observe(PatientVisitServiceObserver::class);
        User::observe(UserObserver::class);
        AuditLog::observe(AuditLogObserver::class);
    }
}
