<?php

namespace App\Observers;

use App\Models\User;
use App\Models\KnowledgeBase;
use App\Services\GeminiKnowledgeService;

class UserObserver
{
    protected GeminiKnowledgeService $knowledgeService;

    public function __construct(GeminiKnowledgeService $knowledgeService)
    {
        $this->knowledgeService = $knowledgeService;
    }

    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        $this->indexUser($user);
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        $this->indexUser($user);
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        KnowledgeBase::where('entity_type', 'user')
            ->where('entity_id', $user->id)
            ->delete();
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        $this->indexUser($user);
    }

    /**
     * Index user asynchronously to knowledge base
     */
    protected function indexUser(User $user): void
    {
        defer(function () use ($user) {
            try {
                $user = User::with('branch')->find($user->id);
                
                if ($user) {
                    $this->knowledgeService->indexUser($user);
                }
            } catch (\Exception $e) {
                logger()->error("Failed to index user {$user->id}: {$e->getMessage()}");
            }
        });
    }
}