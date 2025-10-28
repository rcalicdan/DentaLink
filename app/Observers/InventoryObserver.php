<?php

namespace App\Observers;

use App\Models\Inventory;
use App\Models\KnowledgeBase;
use App\Services\GeminiKnowledgeService;

class InventoryObserver
{
    protected GeminiKnowledgeService $knowledgeService;

    public function __construct(GeminiKnowledgeService $knowledgeService)
    {
        $this->knowledgeService = $knowledgeService;
    }

    /**
     * Handle the Inventory "created" event.
     */
    public function created(Inventory $inventory): void
    {
        $this->indexInventory($inventory);
    }

    /**
     * Handle the Inventory "updated" event.
     */
    public function updated(Inventory $inventory): void
    {
        $this->indexInventory($inventory);
    }

    /**
     * Handle the Inventory "deleted" event.
     */
    public function deleted(Inventory $inventory): void
    {
        KnowledgeBase::where('entity_type', 'inventory')
            ->where('entity_id', $inventory->id)
            ->delete();
    }

    /**
     * Handle the Inventory "restored" event.
     */
    public function restored(Inventory $inventory): void
    {
        $this->indexInventory($inventory);
    }

    /**
     * Index inventory asynchronously to knowledge base
     */
    protected function indexInventory(Inventory $inventory): void
    {
        defer(function () use ($inventory) {
            try {
                $inventory = Inventory::with('branch')->find($inventory->id);
                
                if ($inventory) {
                    $this->knowledgeService->indexInventory($inventory);
                }
            } catch (\Exception $e) {
                logger()->error("Failed to index inventory {$inventory->id}: {$e->getMessage()}");
            }
        });
    }
}