<?php

namespace App\Observers;

use App\Models\DentalService;
use App\Models\KnowledgeBase;
use App\Services\GeminiKnowledgeService;

class DentalServiceObserver
{
    protected GeminiKnowledgeService $knowledgeService;

    public function __construct(GeminiKnowledgeService $knowledgeService)
    {
        $this->knowledgeService = $knowledgeService;
    }

    /**
     * Handle the DentalService "created" event.
     */
    public function created(DentalService $dentalService): void
    {
        $this->indexDentalService($dentalService);
    }

    /**
     * Handle the DentalService "updated" event.
     */
    public function updated(DentalService $dentalService): void
    {
        $this->indexDentalService($dentalService);
    }

    /**
     * Handle the DentalService "deleted" event.
     */
    public function deleted(DentalService $dentalService): void
    {
        KnowledgeBase::where('entity_type', 'dental_service')
            ->where('entity_id', $dentalService->id)
            ->delete();
    }

    /**
     * Handle the DentalService "restored" event.
     */
    public function restored(DentalService $dentalService): void
    {
        $this->indexDentalService($dentalService);
    }

    /**
     * Index dental service asynchronously to knowledge base
     */
    protected function indexDentalService(DentalService $dentalService): void
    {
        defer(function () use ($dentalService) {
            try {
                $dentalService = DentalService::with('dentalServiceType')
                    ->find($dentalService->id);
                
                if ($dentalService) {
                    $this->knowledgeService->indexDentalService($dentalService);
                }
            } catch (\Exception $e) {
                logger()->error("Failed to index dental service {$dentalService->id}: {$e->getMessage()}");
            }
        });
    }
}