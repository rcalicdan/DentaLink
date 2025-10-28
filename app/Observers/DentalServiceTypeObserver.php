<?php

namespace App\Observers;

use App\Models\DentalServiceType;
use App\Models\KnowledgeBase;
use App\Services\GeminiKnowledgeService;

class DentalServiceTypeObserver
{
    protected GeminiKnowledgeService $knowledgeService;

    public function __construct(GeminiKnowledgeService $knowledgeService)
    {
        $this->knowledgeService = $knowledgeService;
    }

    /**
     * Handle the DentalServiceType "created" event.
     */
    public function created(DentalServiceType $dentalServiceType): void
    {
        $this->indexDentalServiceType($dentalServiceType);
    }

    /**
     * Handle the DentalServiceType "updated" event.
     */
    public function updated(DentalServiceType $dentalServiceType): void
    {
        $this->indexDentalServiceType($dentalServiceType);
    }

    /**
     * Handle the DentalServiceType "deleted" event.
     */
    public function deleted(DentalServiceType $dentalServiceType): void
    {
        KnowledgeBase::where('entity_type', 'dental_service_type')
            ->where('entity_id', $dentalServiceType->id)
            ->delete();
    }

    /**
     * Handle the DentalServiceType "restored" event.
     */
    public function restored(DentalServiceType $dentalServiceType): void
    {
        $this->indexDentalServiceType($dentalServiceType);
    }

    /**
     * Index dental service type asynchronously to knowledge base
     */
    protected function indexDentalServiceType(DentalServiceType $dentalServiceType): void
    {
        defer(function () use ($dentalServiceType) {
            try {
                $dentalServiceType = DentalServiceType::find($dentalServiceType->id);
                
                if ($dentalServiceType) {
                    $this->knowledgeService->indexDentalServiceType($dentalServiceType);
                }
            } catch (\Exception $e) {
                logger()->error("Failed to index dental service type {$dentalServiceType->id}: {$e->getMessage()}");
            }
        });
    }
}