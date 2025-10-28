<?php

namespace App\Services;

use App\Models\KnowledgeBase;
use App\Models\Patient;
use App\Models\User;
use App\Models\Appointment;
use App\Models\DentalService;
use App\Models\PatientVisit;
use App\Services\Helpers\GeminiPromptHelper;
use App\Services\Helpers\GeminiContentBuilder;
use App\Services\Helpers\GeminiSearchResultMapper;
use Rcalicdan\GeminiClient\GeminiClient;
use Hibla\Promise\Interfaces\PromiseInterface;
use Hibla\Promise\Interfaces\CancellablePromiseInterface;
use function Hibla\await;
use function Hibla\async;

class GeminiKnowledgeService
{
    private const DEFAULT_CONTEXT_LIMIT = 20;
    private const ENHANCED_CONTEXT_LIMIT = 50;
    private const EMBEDDING_MODEL = 'text-embedding-004';
    private const GENERATION_MODEL = 'gemma-3-27b-it';
    private const BATCH_SIZE = 50;
    private const RATE_LIMIT_DELAY = 100000;

    protected GeminiClient $client;
    protected string $systemPrompt;

    public function __construct()
    {
        $this->client = new GeminiClient(
            apiKey: config('gemini.api_key'),
            model: self::GENERATION_MODEL
        );

        $this->systemPrompt = GeminiPromptHelper::buildSystemPrompt();
    }

    // ==========================================
    // CHAT METHODS
    // ==========================================

    /**
     * Chat with RAG - combines search with AI response
     */
    public function chat(
        string $userMessage,
        ?string $entityType = null,
        int $contextLimit = self::DEFAULT_CONTEXT_LIMIT,
        bool $isFirstMessage = false
    ): string {
        $searchResults = $this->search($userMessage, $entityType, $contextLimit);
        $context = GeminiPromptHelper::buildContext($searchResults);
        $userPrompt = GeminiPromptHelper::buildUserPrompt($context, $userMessage, $isFirstMessage);

        try {
            $response = await($this->sendChatRequest($userPrompt));
            return $response->text();
        } catch (\Exception $e) {
            logger()->error('Failed to generate chat response: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Stream chat with RAG - combines search with streaming AI response
     */
    public function streamChat(
        string $userMessage,
        callable $onChunk,
        ?string $entityType = null,
        int $contextLimit = self::DEFAULT_CONTEXT_LIMIT,
        bool $isFirstMessage = false
    ): CancellablePromiseInterface {
        $searchResults = $this->search($userMessage, $entityType, $contextLimit);
        $context = GeminiPromptHelper::buildContext($searchResults);
        $userPrompt = GeminiPromptHelper::buildUserPrompt($context, $userMessage, $isFirstMessage);

        return $this->streamWithPrompt($userPrompt, $onChunk);
    }

    /**
     * Stream chat as SSE with auto-flush (simplest SSE streaming)
     * 
     * @param string $userMessage
     * @param string|null $entityType
     * @param int $contextLimit
     * @param bool $isFirstMessage
     * @param bool $sendDoneEvent Send completion event
     * @param string|null $doneEventName Custom name for completion event
     * @return CancellablePromiseInterface
     */
    public function streamChatAsSSE(
        string $userMessage,
        ?string $entityType = null,
        int $contextLimit = self::DEFAULT_CONTEXT_LIMIT,
        bool $isFirstMessage = false,
        bool $sendDoneEvent = true,
        ?string $doneEventName = 'done'
    ): CancellablePromiseInterface {
        $searchResults = $this->search($userMessage, $entityType, $contextLimit);
        $context = GeminiPromptHelper::buildContext($searchResults);
        $userPrompt = GeminiPromptHelper::buildUserPrompt($context, $userMessage, $isFirstMessage);

        return $this->streamAsSSEWithPrompt($userPrompt, $sendDoneEvent, $doneEventName);
    }

    /**
     * Stream chat with custom event type
     * 
     * @param string $userMessage
     * @param string $eventType Event type name (e.g., 'message', 'token')
     * @param string|null $entityType
     * @param int $contextLimit
     * @param bool $isFirstMessage
     * @param bool $sendDoneEvent Send completion event
     * @return CancellablePromiseInterface
     */
    public function streamChatWithEvent(
        string $userMessage,
        string $eventType = 'message',
        ?string $entityType = null,
        int $contextLimit = self::DEFAULT_CONTEXT_LIMIT,
        bool $isFirstMessage = false,
        bool $sendDoneEvent = true
    ): CancellablePromiseInterface {
        $searchResults = $this->search($userMessage, $entityType, $contextLimit);
        $context = GeminiPromptHelper::buildContext($searchResults);
        $userPrompt = GeminiPromptHelper::buildUserPrompt($context, $userMessage, $isFirstMessage);

        return $this->streamWithEventType($userPrompt, $eventType, $sendDoneEvent);
    }

    /**
     * Stream chat with progress updates (shows chunk count and total length)
     * 
     * @param string $userMessage
     * @param string|null $entityType
     * @param int $contextLimit
     * @param bool $isFirstMessage
     * @return CancellablePromiseInterface
     */
    public function streamChatWithProgress(
        string $userMessage,
        ?string $entityType = null,
        int $contextLimit = self::DEFAULT_CONTEXT_LIMIT,
        bool $isFirstMessage = false
    ): CancellablePromiseInterface {
        $searchResults = $this->search($userMessage, $entityType, $contextLimit);
        $context = GeminiPromptHelper::buildContext($searchResults);
        $userPrompt = GeminiPromptHelper::buildUserPrompt($context, $userMessage, $isFirstMessage);

        return $this->streamWithProgressUpdates($userPrompt);
    }

    /**
     * Enhanced chat with statistics
     */
    public function enhancedChat(
        string $userMessage,
        ?string $entityType = null,
        int $contextLimit = self::ENHANCED_CONTEXT_LIMIT,
        bool $isFirstMessage = false
    ): string {
        $stats = $this->getEntityStats();
        $searchResults = $this->search($userMessage, $entityType, $contextLimit);
        
        $context = GeminiPromptHelper::buildEnhancedContext($stats, $searchResults);
        $userPrompt = GeminiPromptHelper::buildEnhancedUserPrompt($context, $userMessage, $isFirstMessage);

        try {
            $response = await($this->sendChatRequest($userPrompt));
            return $response->text();
        } catch (\Exception $e) {
            logger()->error('Failed to generate enhanced chat response: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Enhanced stream chat with statistics
     */
    public function enhancedStreamChat(
        string $userMessage,
        callable $onChunk,
        ?string $entityType = null,
        int $contextLimit = self::ENHANCED_CONTEXT_LIMIT,
        bool $isFirstMessage = false
    ): CancellablePromiseInterface {
        $stats = $this->getEntityStats();
        $searchResults = $this->search($userMessage, $entityType, $contextLimit);
        
        $context = GeminiPromptHelper::buildEnhancedContext($stats, $searchResults);
        $userPrompt = GeminiPromptHelper::buildEnhancedUserPrompt($context, $userMessage, $isFirstMessage);

        return $this->streamWithPrompt($userPrompt, $onChunk);
    }

    /**
     * Enhanced stream chat as SSE with auto-flush
     * 
     * @param string $userMessage
     * @param string|null $entityType
     * @param int $contextLimit
     * @param bool $isFirstMessage
     * @param bool $sendDoneEvent
     * @param string|null $doneEventName
     * @return CancellablePromiseInterface
     */
    public function enhancedStreamChatAsSSE(
        string $userMessage,
        ?string $entityType = null,
        int $contextLimit = self::ENHANCED_CONTEXT_LIMIT,
        bool $isFirstMessage = false,
        bool $sendDoneEvent = true,
        ?string $doneEventName = 'done'
    ): CancellablePromiseInterface {
        $stats = $this->getEntityStats();
        $searchResults = $this->search($userMessage, $entityType, $contextLimit);
        
        $context = GeminiPromptHelper::buildEnhancedContext($stats, $searchResults);
        $userPrompt = GeminiPromptHelper::buildEnhancedUserPrompt($context, $userMessage, $isFirstMessage);

        return $this->streamAsSSEWithPrompt($userPrompt, $sendDoneEvent, $doneEventName);
    }

    /**
     * Get a greeting/introduction response (only for first interaction)
     */
    public function getIntroduction(): string
    {
        try {
            $prompt = "This is the user's first message in this conversation. The user is greeting you. Introduce yourself.";
            $response = await($this->sendChatRequest($prompt));
            return $response->text();
        } catch (\Exception $e) {
            logger()->error('Failed to generate introduction: ' . $e->getMessage());
            return "I'm your AI assistant for Nice Smile Clinic. Let me know anything about the clinic operation.";
        }
    }

    // ==========================================
    // EMBEDDING METHODS
    // ==========================================

    /**
     * Generate embedding using text-embedding-004
     */
    public function generateEmbedding(string $text): array
    {
        try {
            $response = await(
                $this->client
                    ->withEmbeddingModel(self::EMBEDDING_MODEL)
                    ->embedContent($text, 'RETRIEVAL_DOCUMENT')
            );
            
            return $response->values();
        } catch (\Exception $e) {
            logger()->error('Failed to generate embedding: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Generate embedding asynchronously
     */
    public function generateEmbeddingAsync(string $text): PromiseInterface
    {
        return async(fn() => $this->generateEmbedding($text));
    }

    /**
     * Batch generate embeddings for multiple texts
     */
    public function batchGenerateEmbeddings(array $texts): array
    {
        try {
            $requests = array_map(
                fn($text) => [
                    'content' => $text,
                    'task_type' => 'RETRIEVAL_DOCUMENT'
                ],
                $texts
            );

            $response = await(
                $this->client
                    ->withEmbeddingModel(self::EMBEDDING_MODEL)
                    ->batchEmbed($requests)
            );

            return $response->embeddings();
        } catch (\Exception $e) {
            logger()->error('Failed to batch generate embeddings: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Batch generate embeddings asynchronously
     */
    public function batchGenerateEmbeddingsAsync(array $texts): PromiseInterface
    {
        return async(fn() => $this->batchGenerateEmbeddings($texts));
    }

    // ==========================================
    // INDEXING METHODS
    // ==========================================

    /**
     * Index a user
     */
    public function indexUser(User $user): void
    {
        $content = GeminiContentBuilder::buildUserContent($user);
        $embedding = $this->generateEmbedding($content);

        KnowledgeBase::storeEmbedding(
            entityType: 'user',
            entityId: $user->id,
            content: $content,
            embedding: $embedding,
            metadata: [
                'user_id' => $user->id,
                'full_name' => $user->full_name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $user->role,
                'branch_id' => $user->branch_id,
                'branch_name' => $user->branch_name,
                'updated_at' => now()->toISOString(),
            ]
        );
    }

    /**
     * Index a patient record
     */
    public function indexPatient(Patient $patient): void
    {
        $content = GeminiContentBuilder::buildPatientContent($patient);
        $embedding = $this->generateEmbedding($content);

        KnowledgeBase::storeEmbedding(
            entityType: 'patient',
            entityId: $patient->id,
            content: $content,
            embedding: $embedding,
            metadata: [
                'patient_id' => $patient->id,
                'branch_id' => $patient->registration_branch_id,
                'full_name' => $patient->full_name,
                'updated_at' => now()->toISOString(),
            ]
        );
    }

    /**
     * Index an appointment
     */
    public function indexAppointment(Appointment $appointment): void
    {
        $content = GeminiContentBuilder::buildAppointmentContent($appointment);
        $embedding = $this->generateEmbedding($content);

        KnowledgeBase::storeEmbedding(
            entityType: 'appointment',
            entityId: $appointment->id,
            content: $content,
            embedding: $embedding,
            metadata: [
                'appointment_id' => $appointment->id,
                'patient_id' => $appointment->patient_id,
                'branch_id' => $appointment->branch_id,
                'appointment_date' => $appointment->appointment_date->toISOString(),
                'status' => $appointment->status->value,
            ]
        );
    }

    /**
     * Index a dental service
     */
    public function indexDentalService(DentalService $service): void
    {
        $content = GeminiContentBuilder::buildServiceContent($service);
        $embedding = $this->generateEmbedding($content);

        KnowledgeBase::storeEmbedding(
            entityType: 'dental_service',
            entityId: $service->id,
            content: $content,
            embedding: $embedding,
            metadata: [
                'service_id' => $service->id,
                'service_type_id' => $service->dental_service_type_id,
                'price' => (float) $service->price,
                'is_quantifiable' => $service->is_quantifiable,
            ]
        );
    }

    /**
     * Index a patient visit
     */
    public function indexPatientVisit(PatientVisit $visit): void
    {
        $content = GeminiContentBuilder::buildVisitContent($visit);
        $embedding = $this->generateEmbedding($content);

        KnowledgeBase::storeEmbedding(
            entityType: 'patient_visit',
            entityId: $visit->id,
            content: $content,
            embedding: $embedding,
            metadata: [
                'visit_id' => $visit->id,
                'patient_id' => $visit->patient_id,
                'branch_id' => $visit->branch_id,
                'visit_date' => $visit->visit_date->toISOString(),
                'total_amount' => (float) $visit->total_amount_paid,
            ]
        );
    }

    /**
     * Batch index patients with rate limiting
     */
    public function batchIndexPatients(int $batchSize = self::BATCH_SIZE): void
    {
        Patient::chunk($batchSize, function ($patients) {
            foreach ($patients as $patient) {
                try {
                    $this->indexPatient($patient);
                    usleep(self::RATE_LIMIT_DELAY);
                } catch (\Exception $e) {
                    logger()->error("Failed to index patient {$patient->id}: {$e->getMessage()}");
                }
            }
        });
    }

    // ==========================================
    // SEARCH METHODS
    // ==========================================

    /**
     * Search knowledge base with semantic search
     */
    public function search(string $query, ?string $entityType = null, int $limit = 5): array
    {
        $queryEmbedding = $this->generateEmbedding($query);
        $results = KnowledgeBase::findSimilar($queryEmbedding, $limit, $entityType);
        
        return GeminiSearchResultMapper::mapResults($results);
    }

    /**
     * Search asynchronously
     */
    public function searchAsync(string $query, ?string $entityType = null, int $limit = 5): PromiseInterface
    {
        return async(fn() => $this->search($query, $entityType, $limit));
    }

    /**
     * Get entity statistics for better context
     */
    public function getEntityStats(?string $entityType = null): array
    {
        $query = KnowledgeBase::query();

        if ($entityType) {
            return [
                'entity_type' => $entityType,
                'total' => $query->where('entity_type', $entityType)->count(),
            ];
        }

        return KnowledgeBase::select('entity_type')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('entity_type')
            ->get()
            ->pluck('count', 'entity_type')
            ->toArray();
    }

    // ==========================================
    // MODEL METHODS
    // ==========================================

    /**
     * Get list of available models
     */
    public function listModels(): array
    {
        return await($this->client->listModels())->json();
    }

    /**
     * List models asynchronously
     */
    public function listModelsAsync(): PromiseInterface
    {
        return $this->client->listModels();
    }

    // ==========================================
    // PRIVATE HELPER METHODS
    // ==========================================

    /**
     * Send chat request with model-aware prompt handling
     */
    private function sendChatRequest(string $userPrompt): PromiseInterface
    {
        if (GeminiPromptHelper::supportsSystemInstructions(self::GENERATION_MODEL)) {
            return $this->client
                ->prompt($userPrompt)
                ->system($this->systemPrompt)
                ->send();
        }

        $combinedPrompt = $this->systemPrompt . "\n\n---\n\n" . $userPrompt;
        return $this->client
            ->prompt($combinedPrompt)
            ->send();
    }

    /**
     * Stream response with model-aware prompt handling
     */
    private function streamWithPrompt(string $userPrompt, callable $onChunk): CancellablePromiseInterface
    {
        if (GeminiPromptHelper::supportsSystemInstructions(self::GENERATION_MODEL)) {
            return $this->client
                ->prompt($userPrompt)
                ->system($this->systemPrompt)
                ->stream($onChunk);
        }

        $combinedPrompt = $this->systemPrompt . "\n\n---\n\n" . $userPrompt;
        return $this->client
            ->prompt($combinedPrompt)
            ->stream($onChunk);
    }

private function streamAsSSEWithPrompt(
    string $userPrompt,
    bool $sendDoneEvent = true,
    ?string $doneEventName = 'done'
): CancellablePromiseInterface {
    logger()->info('Starting SSE stream', [
        'prompt_length' => strlen($userPrompt),
        'send_done_event' => $sendDoneEvent
    ]);
    
    // Track chunks manually
    $totalChunks = 0;
    $totalLength = 0;
    
    if (GeminiPromptHelper::supportsSystemInstructions(self::GENERATION_MODEL)) {
        return $this->client
            ->prompt($userPrompt)
            ->system($this->systemPrompt)
            ->stream(function (string $chunk, $event) use (&$totalChunks, &$totalLength) {
                $totalChunks++;
                $totalLength += strlen($chunk);
                
                echo "event: message\n";
                echo "data: " . json_encode(['content' => $chunk]) . "\n\n";
                
                if (ob_get_level() > 0) {
                    ob_flush();
                }
                flush();
            })
            ->then(function ($response) use ($sendDoneEvent, $doneEventName, &$totalChunks, &$totalLength) {
                if ($sendDoneEvent) {
                    echo "event: {$doneEventName}\n";
                    echo "data: " . json_encode([
                        'status' => 'complete',
                        'chunks' => $totalChunks,
                        'length' => $totalLength
                    ]) . "\n\n";
                    
                    if (ob_get_level() > 0) {
                        ob_flush();
                    }
                    flush();
                }
                
                logger()->info('Stream completed', [
                    'chunks' => $totalChunks,
                    'length' => $totalLength
                ]);
                
                return $response;
            });
    }

    $combinedPrompt = $this->systemPrompt . "\n\n---\n\n" . $userPrompt;
    return $this->client
        ->prompt($combinedPrompt)
        ->stream(function (string $chunk, $event) use (&$totalChunks, &$totalLength) {
            $totalChunks++;
            $totalLength += strlen($chunk);
            
            echo "event: message\n";
            echo "data: " . json_encode(['content' => $chunk]) . "\n\n";
            
            if (ob_get_level() > 0) {
                ob_flush();
            }
            flush();
        })
        ->then(function ($response) use ($sendDoneEvent, $doneEventName, &$totalChunks, &$totalLength) {
            if ($sendDoneEvent) {
                echo "event: {$doneEventName}\n";
                echo "data: " . json_encode([
                    'status' => 'complete',
                    'chunks' => $totalChunks,
                    'length' => $totalLength
                ]) . "\n\n";
                
                if (ob_get_level() > 0) {
                    ob_flush();
                }
                flush();
            }
            
            logger()->info('Stream completed', [
                'chunks' => $totalChunks,
                'length' => $totalLength
            ]);
            
            return $response;
        });
}

    /**
     * Stream with custom event type (uses streamWithEvent)
     */
    private function streamWithEventType(
        string $userPrompt,
        string $eventType = 'message',
        bool $sendDoneEvent = true
    ): CancellablePromiseInterface {
        if (GeminiPromptHelper::supportsSystemInstructions(self::GENERATION_MODEL)) {
            return $this->client
                ->prompt($userPrompt)
                ->system($this->systemPrompt)
                ->streamWithEvent($eventType, $sendDoneEvent);
        }

        $combinedPrompt = $this->systemPrompt . "\n\n---\n\n" . $userPrompt;
        return $this->client
            ->prompt($combinedPrompt)
            ->streamWithEvent($eventType, $sendDoneEvent);
    }

    /**
     * Stream with progress updates (uses streamWithProgress)
     */
    private function streamWithProgressUpdates(string $userPrompt): CancellablePromiseInterface
    {
        if (GeminiPromptHelper::supportsSystemInstructions(self::GENERATION_MODEL)) {
            return $this->client
                ->prompt($userPrompt)
                ->system($this->systemPrompt)
                ->streamWithProgress();
        }

        $combinedPrompt = $this->systemPrompt . "\n\n---\n\n" . $userPrompt;
        return $this->client
            ->prompt($combinedPrompt)
            ->streamWithProgress();
    }
}