<?php

namespace App\Observers;

use App\Models\Patient;
use App\Models\KnowledgeBase;
use App\Services\GeminiKnowledgeService;

class PatientObserver
{
    protected GeminiKnowledgeService $knowledgeService;

    public function __construct(GeminiKnowledgeService $knowledgeService)
    {
        $this->knowledgeService = $knowledgeService;
    }

    /**
     * Handle the Patient "created" event.
     */
    public function created(Patient $patient): void
    {
        $this->indexPatient($patient);
    }

    /**
     * Handle the Patient "updated" event.
     */
    public function updated(Patient $patient): void
    {
        $this->indexPatient($patient);
    }

    /**
     * Handle the Patient "deleted" event.
     */
    public function deleted(Patient $patient): void
    {
        KnowledgeBase::where('entity_type', 'patient')
            ->where('entity_id', $patient->id)
            ->delete();
    }

    /**
     * Handle the Patient "restored" event.
     */
    public function restored(Patient $patient): void
    {
        $this->indexPatient($patient);
    }

    /**
     * Index patient asynchronously to knowledge base
     */
    protected function indexPatient(Patient $patient): void
    {
        dispatch(function () use ($patient) {
            try {
                $patient = Patient::with('registrationBranch')->find($patient->id);
                
                if ($patient) {
                    $this->knowledgeService->indexPatient($patient);
                }
            } catch (\Exception $e) {
                logger()->error("Failed to index patient {$patient->id}: {$e->getMessage()}");
            }
        })->afterResponse();
    }
}