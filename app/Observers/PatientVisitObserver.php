<?php

namespace App\Observers;

use App\Models\PatientVisit;
use App\Models\Appointment;
use App\Enums\AppointmentStatuses;

class PatientVisitObserver
{
    /**
     * Handle the PatientVisit "created" event.
     */
    public function created(PatientVisit $patientVisit): void
    {
        if ($patientVisit->appointment_id) {
            $appointment = Appointment::find($patientVisit->appointment_id);
            if ($appointment) {
                $appointment->update([
                    'has_visit' => true,
                    'status' => AppointmentStatuses::COMPLETED
                ]);
            }
        }
    }

    /**
     * Handle the PatientVisit "deleted" event.
     */
    public function deleted(PatientVisit $patientVisit): void
    {
        if ($patientVisit->appointment_id) {
            $appointment = Appointment::find($patientVisit->appointment_id);
            if ($appointment) {
                $hasOtherVisits = PatientVisit::where('appointment_id', $patientVisit->appointment_id)
                    ->where('id', '!=', $patientVisit->id)
                    ->exists();
                
                if (!$hasOtherVisits) {
                    $appointment->update([
                        'has_visit' => false,
                        'status' => AppointmentStatuses::WAITING->value 
                    ]);
                }
            }
        }
    }
}