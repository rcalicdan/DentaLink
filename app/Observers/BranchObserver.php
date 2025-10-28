<?php

namespace App\Observers;

use App\Models\Branch;
use App\Models\KnowledgeBase;
use App\Services\GeminiKnowledgeService;

class BranchObserver
{
    protected GeminiKnowledgeService $knowledgeService;

    public function __construct(GeminiKnowledgeService $knowledgeService)
    {
        $this->knowledgeService = $knowledgeService;
    }

    /**
     * Handle the Branch "created" event.
     */
    public function created(Branch $branch): void
    {
        $this->indexBranch($branch);
    }

    /**
     * Handle the Branch "updated" event.
     */
    public function updated(Branch $branch): void
    {
        $this->indexBranch($branch);
    }

    /**
     * Handle the Branch "deleted" event.
     */
    public function deleted(Branch $branch): void
    {
        KnowledgeBase::where('entity_type', 'branch')
            ->where('entity_id', $branch->id)
            ->delete();
    }

    /**
     * Handle the Branch "restored" event.
     */
    public function restored(Branch $branch): void
    {
        $this->indexBranch($branch);
    }

    /**
     * Index branch asynchronously to knowledge base
     */
    protected function indexBranch(Branch $branch): void
    {
        defer(function () use ($branch) {
            try {
                $branch = Branch::find($branch->id);
                
                if ($branch) {
                    $this->knowledgeService->indexBranch($branch);
                }
            } catch (\Exception $e) {
                logger()->error("Failed to index branch {$branch->id}: {$e->getMessage()}");
            }
        });
    }
}