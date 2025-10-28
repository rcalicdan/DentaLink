<?php

namespace App\Observers;

use App\Models\PatientVisitService;
use App\Models\KnowledgeBase;
use App\Services\GeminiKnowledgeService;

class PatientVisitServiceObserver
{
    protected GeminiKnowledgeService $knowledgeService;

    public function __construct(GeminiKnowledgeService $knowledgeService)
    {
        $this->knowledgeService = $knowledgeService;
    }

    /**
     * Handle the PatientVisitService "created" event.
     */
    public function created(PatientVisitService $patientVisitService): void
    {
        $this->indexPatientVisitService($patientVisitService);
    }

    /**
     * Handle the PatientVisitService "updated" event.
     */
    public function updated(PatientVisitService $patientVisitService): void
    {
        $this->indexPatientVisitService($patientVisitService);
    }

    /**
     * Handle the PatientVisitService "deleted" event.
     */
    public function deleted(PatientVisitService $patientVisitService): void
    {
        KnowledgeBase::where('entity_type', 'patient_visit_service')
            ->where('entity_id', $patientVisitService->id)
            ->delete();
    }

    /**
     * Handle the PatientVisitService "restored" event.
     */
    public function restored(PatientVisitService $patientVisitService): void
    {
        $this->indexPatientVisitService($patientVisitService);
    }

    /**
     * Index patient visit service asynchronously to knowledge base
     */
    protected function indexPatientVisitService(PatientVisitService $patientVisitService): void
    {
        defer(function () use ($patientVisitService) {
            try {
                $patientVisitService = PatientVisitService::with([
                    'patientVisit.patient',
                    'dentalService.dentalServiceType'
                ])->find($patientVisitService->id);
                
                if ($patientVisitService) {
                    $this->knowledgeService->indexPatientVisitService($patientVisitService);
                }
            } catch (\Exception $e) {
                logger()->error("Failed to index patient visit service {$patientVisitService->id}: {$e->getMessage()}");
            }
        });
    }
}