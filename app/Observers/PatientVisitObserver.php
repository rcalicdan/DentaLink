<?php

namespace App\Observers;

use App\Models\PatientVisit;
use App\Models\Appointment;
use App\Models\KnowledgeBase;
use App\Enums\AppointmentStatuses;
use App\Services\GeminiKnowledgeService;

class PatientVisitObserver
{
    protected GeminiKnowledgeService $knowledgeService;

    public function __construct(GeminiKnowledgeService $knowledgeService)
    {
        $this->knowledgeService = $knowledgeService;
    }

    /**
     * Handle the PatientVisit "created" event.
     */
    public function created(PatientVisit $patientVisit): void
    {
        // Update related appointment
        if ($patientVisit->appointment_id) {
            $appointment = Appointment::find($patientVisit->appointment_id);
            if ($appointment) {
                $appointment->update([
                    'has_visit' => true,
                    'status' => AppointmentStatuses::COMPLETED
                ]);
            }
        }

        // Index to knowledge base
        $this->indexPatientVisit($patientVisit);
    }

    /**
     * Handle the PatientVisit "updated" event.
     */
    public function updated(PatientVisit $patientVisit): void
    {
        $this->indexPatientVisit($patientVisit);
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

        KnowledgeBase::where('entity_type', 'patient_visit')
            ->where('entity_id', $patientVisit->id)
            ->delete();
    }

    /**
     * Handle the PatientVisit "restored" event.
     */
    public function restored(PatientVisit $patientVisit): void
    {
        $this->indexPatientVisit($patientVisit);
    }

    /**
     * Index patient visit asynchronously to knowledge base
     */
    protected function indexPatientVisit(PatientVisit $patientVisit): void
    {
        dispatch(function () use ($patientVisit) {
            try {
                $patientVisit = PatientVisit::with([
                    'patient', 
                    'branch', 
                    'patientVisitServices.dentalService'
                ])->find($patientVisit->id);
                
                if ($patientVisit) {
                    $this->knowledgeService->indexPatientVisit($patientVisit);
                }
            } catch (\Exception $e) {
                logger()->error("Failed to index patient visit {$patientVisit->id}: {$e->getMessage()}");
            }
        })->afterResponse();
    }
}