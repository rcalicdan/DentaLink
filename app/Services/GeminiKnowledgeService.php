<?php

namespace App\Services;

use App\Models\KnowledgeBase;
use App\Models\Patient;
use App\Models\User;
use App\Models\Appointment;
use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\DentalService;
use App\Models\DentalServiceType;
use App\Models\Inventory;
use App\Models\PatientVisit;
use App\Models\PatientVisitService;
use App\Services\Helpers\GeminiPromptHelper;
use App\Services\Helpers\GeminiSearchResultMapper;
use App\Services\Helpers\GeminiIndexer;
use Rcalicdan\GeminiClient\GeminiClient;
use Hibla\Promise\Interfaces\PromiseInterface;
use Hibla\Promise\Interfaces\CancellablePromiseInterface;
use function Hibla\await;
use function Hibla\async;

class GeminiKnowledgeService
{
    private const DEFAULT_CONTEXT_LIMIT = 50;
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
     * Chat with RAG - combines search with AI response (with statistics)
     */
    public function chat(
        string $userMessage,
        ?string $entityType = null,
        int $contextLimit = self::DEFAULT_CONTEXT_LIMIT,
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
        $stats = $this->getEntityStats();
        $searchResults = $this->search($userMessage, $entityType, $contextLimit);

        $context = GeminiPromptHelper::buildEnhancedContext($stats, $searchResults);
        $userPrompt = GeminiPromptHelper::buildEnhancedUserPrompt($context, $userMessage, $isFirstMessage);

        return $this->streamWithPrompt($userPrompt, $onChunk);
    }

    /**
     * Stream chat with SSE - automatic SSE event emission
     */
    public function streamChatSSE(
        string $userMessage,
        ?string $entityType = null,
        int $contextLimit = self::DEFAULT_CONTEXT_LIMIT,
        bool $isFirstMessage = false,
        array $sseConfig = []
    ): CancellablePromiseInterface {
        $stats = $this->getEntityStats();
        $searchResults = $this->search($userMessage, $entityType, $contextLimit);

        $context = GeminiPromptHelper::buildEnhancedContext($stats, $searchResults);
        $userPrompt = GeminiPromptHelper::buildEnhancedUserPrompt($context, $userMessage, $isFirstMessage);

        $defaultMetadata = [
            'search_results_count' => count($searchResults),
            'entity_stats' => $stats,
            'context_limit' => $contextLimit,
        ];

        $sseConfig['customMetadata'] = array_merge(
            $defaultMetadata,
            $sseConfig['customMetadata'] ?? []
        );

        return $this->streamSSEWithPrompt($userPrompt, $sseConfig);
    }

    /**
     * Stream chat with custom SSE events
     */
    public function streamChatWithEvents(
        string $userMessage,
        string $messageEvent = 'message',
        ?string $doneEvent = 'done',
        bool $includeMetadata = true,
        ?string $entityType = null,
        int $contextLimit = self::DEFAULT_CONTEXT_LIMIT,
        bool $isFirstMessage = false
    ): CancellablePromiseInterface {
        $stats = $this->getEntityStats();
        $searchResults = $this->search($userMessage, $entityType, $contextLimit);

        $context = GeminiPromptHelper::buildEnhancedContext($stats, $searchResults);
        $userPrompt = GeminiPromptHelper::buildEnhancedUserPrompt($context, $userMessage, $isFirstMessage);

        return $this->streamSSEWithPrompt($userPrompt, [
            'messageEvent' => $messageEvent,
            'doneEvent' => $doneEvent,
            'includeMetadata' => $includeMetadata,
            'customMetadata' => [
                'search_results_count' => count($searchResults),
                'entity_stats' => $stats,
            ],
        ]);
    }

    /**
     * Stream chat with progress tracking
     */
    public function streamChatWithProgress(
        string $userMessage,
        string $progressEvent = 'progress',
        ?string $entityType = null,
        int $contextLimit = self::DEFAULT_CONTEXT_LIMIT,
        bool $isFirstMessage = false
    ): CancellablePromiseInterface {
        $stats = $this->getEntityStats();
        $searchResults = $this->search($userMessage, $entityType, $contextLimit);

        $context = GeminiPromptHelper::buildEnhancedContext($stats, $searchResults);
        $userPrompt = GeminiPromptHelper::buildEnhancedUserPrompt($context, $userMessage, $isFirstMessage);

        return $this->streamSSEWithPrompt($userPrompt, [
            'messageEvent' => 'message',
            'doneEvent' => 'done',
            'progressEvent' => $progressEvent,
            'customMetadata' => [
                'search_results_count' => count($searchResults),
                'entity_stats' => $stats,
                'entity_type' => $entityType,
            ],
        ]);
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

    /**
     * Stream introduction with SSE
     */
    public function streamIntroductionSSE(array $sseConfig = []): CancellablePromiseInterface
    {
        $prompt = "This is the user's first message in this conversation. The user is greeting you. Introduce yourself.";

        return $this->streamSSEWithPrompt($prompt, array_merge([
            'customMetadata' => [
                'message_type' => 'introduction',
                'is_first_message' => true,
            ],
        ], $sseConfig));
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
        $content = GeminiIndexer::getContentForUser($user);
        $embedding = $this->generateEmbedding($content);
        GeminiIndexer::indexUser($user, $embedding);
    }

    /**
     * Index a patient record
     */
    public function indexPatient(Patient $patient): void
    {
        $content = GeminiIndexer::getContentForPatient($patient);
        $embedding = $this->generateEmbedding($content);
        GeminiIndexer::indexPatient($patient, $embedding);
    }

    /**
     * Index an appointment
     */
    public function indexAppointment(Appointment $appointment): void
    {
        $content = GeminiIndexer::getContentForAppointment($appointment);
        $embedding = $this->generateEmbedding($content);
        GeminiIndexer::indexAppointment($appointment, $embedding);
    }

    /**
     * Index a dental service
     */
    public function indexDentalService(DentalService $service): void
    {
        $content = GeminiIndexer::getContentForDentalService($service);
        $embedding = $this->generateEmbedding($content);
        GeminiIndexer::indexDentalService($service, $embedding);
    }

    /**
     * Index a patient visit
     */
    public function indexPatientVisit(PatientVisit $visit): void
    {
        $content = GeminiIndexer::getContentForPatientVisit($visit);
        $embedding = $this->generateEmbedding($content);
        GeminiIndexer::indexPatientVisit($visit, $embedding);
    }

    /**
     * Index a branch
     */
    public function indexBranch(Branch $branch): void
    {
        $content = GeminiIndexer::getContentForBranch($branch);
        $embedding = $this->generateEmbedding($content);
        GeminiIndexer::indexBranch($branch, $embedding);
    }

    /**
     * Index a dental service type
     */
    public function indexDentalServiceType(DentalServiceType $dentalServiceType): void
    {
        $content = GeminiIndexer::getContentForDentalServiceType($dentalServiceType);
        $embedding = $this->generateEmbedding($content);
        GeminiIndexer::indexDentalServiceType($dentalServiceType, $embedding);
    }

    /**
     * Index an inventory item
     */
    public function indexInventory(Inventory $inventory): void
    {
        $content = GeminiIndexer::getContentForInventory($inventory);
        $embedding = $this->generateEmbedding($content);
        GeminiIndexer::indexInventory($inventory, $embedding);
    }

    /**
     * Index a patient visit service
     */
    public function indexPatientVisitService(PatientVisitService $patientVisitService): void
    {
        $content = GeminiIndexer::getContentForPatientVisitService($patientVisitService);
        $embedding = $this->generateEmbedding($content);
        GeminiIndexer::indexPatientVisitService($patientVisitService, $embedding);
    }

    /**
     * Index an audit log
     */
    public function indexAuditLog(AuditLog $auditLog): void
    {
        $content = GeminiIndexer::getContentForAuditLog($auditLog);
        $embedding = $this->generateEmbedding($content);
        GeminiIndexer::indexAuditLog($auditLog, $embedding);
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

    /**
     * Stream SSE response with model-aware prompt handling
     */
    private function streamSSEWithPrompt(string $userPrompt, array $sseConfig = []): CancellablePromiseInterface
    {
        if (GeminiPromptHelper::supportsSystemInstructions(self::GENERATION_MODEL)) {
            return $this->client
                ->prompt($userPrompt)
                ->system($this->systemPrompt)
                ->streamSSE($sseConfig);
        }

        $combinedPrompt = $this->systemPrompt . "\n\n---\n\n" . $userPrompt;
        return $this->client
            ->prompt($combinedPrompt)
            ->streamSSE($sseConfig);
    }
}