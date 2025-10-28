<?php

namespace App\Observers;

use App\Models\AuditLog;
use App\Models\KnowledgeBase;
use App\Services\GeminiKnowledgeService;

class AuditLogObserver
{
    protected GeminiKnowledgeService $knowledgeService;

    public function __construct(GeminiKnowledgeService $knowledgeService)
    {
        $this->knowledgeService = $knowledgeService;
    }

    /**
     * Handle the AuditLog "created" event.
     */
    public function created(AuditLog $auditLog): void
    {
        $this->indexAuditLog($auditLog);
    }

    /**
     * Index audit log asynchronously to knowledge base
     */
    protected function indexAuditLog(AuditLog $auditLog): void
    {
        defer(function () use ($auditLog) {
            try {
                $auditLog = AuditLog::find($auditLog->id);
                
                if ($auditLog) {
                    $this->knowledgeService->indexAuditLog($auditLog);
                }
            } catch (\Exception $e) {
                logger()->error("Failed to index audit log {$auditLog->id}: {$e->getMessage()}");
            }
        });
    }
}