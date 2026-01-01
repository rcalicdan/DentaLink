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
use function Hibla\await;
use function Hibla\async;

class GeminiKnowledgeService
{
    private const DEFAULT_CONTEXT_LIMIT = 50;
    private const EMBEDDING_MODEL = 'text-embedding-004';
    private const GENERATION_MODEL = 'gemma-3-27b-it';
    private const BATCH_SIZE = 50;
    private const RATE_LIMIT_DELAY = 100000;
    private const CACHE_TTL_SHORT = 1800;      
    private const CACHE_TTL_MEDIUM = 10800; 
    private const CACHE_TTL_LONG = 18000;  
    private const CACHE_TTL_VERY_LONG = 43200; 

    protected GeminiClient $client;
    protected GeminiClient $cachedClient;
    protected string $systemPrompt;

    public function __construct()
    {
        $this->client = new GeminiClient(
            apiKey: config('gemini.api_key'),
            model: self::GENERATION_MODEL
        );

        $this->cachedClient = new GeminiClient(
            apiKey: config('gemini.api_key'),
            model: self::GENERATION_MODEL
        );

        $cachePath = storage_path('app/gemini-cache');
        $this->cachedClient = $this->cachedClient
            ->withCachePath($cachePath)
            ->withCache(
                ttlSeconds: self::CACHE_TTL_MEDIUM,
                respectServerHeaders: false
            );

        $this->systemPrompt = GeminiPromptHelper::buildSystemPrompt();
    }

    // ==========================================
    // CHAT METHODS
    // ==========================================

    /**
     * Chat with RAG - uses caching for identical queries
     */
    public function chat(
        string $userMessage,
        ?string $entityType = null,
        int $contextLimit = self::DEFAULT_CONTEXT_LIMIT,
        bool $isFirstMessage = false,
        ?int $cacheTtl = null
    ): string {
        $stats = $this->getEntityStats();
        $searchResults = $this->search($userMessage, $entityType, $contextLimit);

        $context = GeminiPromptHelper::buildEnhancedContext($stats, $searchResults);
        $userPrompt = GeminiPromptHelper::buildEnhancedUserPrompt($context, $userMessage, $isFirstMessage);

        try {
            $response = await($this->sendCachedChatRequest($userPrompt, $cacheTtl));
            return $response->text();
        } catch (\Exception $e) {
            logger()->error('Failed to generate chat response: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Chat with custom cache key and TTL
     */
    public function chatWithCache(
        string $userMessage,
        string $cacheKey,
        int $cacheTtl = self::CACHE_TTL_MEDIUM,
        ?string $entityType = null,
        int $contextLimit = self::DEFAULT_CONTEXT_LIMIT,
        bool $isFirstMessage = false
    ): string {
        $stats = $this->getEntityStats();
        $searchResults = $this->search($userMessage, $entityType, $contextLimit);

        $context = GeminiPromptHelper::buildEnhancedContext($stats, $searchResults);
        $userPrompt = GeminiPromptHelper::buildEnhancedUserPrompt($context, $userMessage, $isFirstMessage);

        try {
            $response = await($this->sendChatRequestWithCacheKey($userPrompt, $cacheKey, $cacheTtl));
            return $response->text();
        } catch (\Exception $e) {
            logger()->error('Failed to generate cached chat response: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Generate forecast with caching (for dashboard)
     */
    public function generateForecast(
        array $clinicData,
        int $cacheTtl = self::CACHE_TTL_LONG
    ): string {
        $prompt = $this->buildForecastPrompt($clinicData);
        
        try {
            $cacheKey = 'forecast_' . md5(json_encode($clinicData));
            $response = await($this->sendChatRequestWithCacheKey($prompt, $cacheKey, $cacheTtl));
            return $response->text();
        } catch (\Exception $e) {
            logger()->error('Failed to generate forecast: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Generate insights with caching (for analytics)
     */
    public function generateInsights(
        string $insightType,
        array $data,
        int $cacheTtl = self::CACHE_TTL_LONG
    ): string {
        $prompt = $this->buildInsightsPrompt($insightType, $data);
        
        try {
            $cacheKey = "insights_{$insightType}_" . md5(json_encode($data));
            $response = await($this->sendChatRequestWithCacheKey($prompt, $cacheKey, $cacheTtl));
            return $response->text();
        } catch (\Exception $e) {
            logger()->error("Failed to generate {$insightType} insights: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Stream chat with RAG - no caching for streaming
     */
    public function streamChat(
        string $userMessage,
        callable $onChunk,
        ?string $entityType = null,
        int $contextLimit = self::DEFAULT_CONTEXT_LIMIT,
        bool $isFirstMessage = false
    ): PromiseInterface {
        $stats = $this->getEntityStats();
        $searchResults = $this->search($userMessage, $entityType, $contextLimit);

        $context = GeminiPromptHelper::buildEnhancedContext($stats, $searchResults);
        $userPrompt = GeminiPromptHelper::buildEnhancedUserPrompt($context, $userMessage, $isFirstMessage);

        return $this->streamWithPrompt($userPrompt, $onChunk);
    }

    /**
     * Stream chat with SSE - no caching
     */
    public function streamChatSSE(
        string $userMessage,
        ?string $entityType = null,
        int $contextLimit = self::DEFAULT_CONTEXT_LIMIT,
        bool $isFirstMessage = false,
        array $sseConfig = []
    ): PromiseInterface {
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
     * Stream chat with custom SSE events - no caching
     */
    public function streamChatWithEvents(
        string $userMessage,
        string $messageEvent = 'message',
        ?string $doneEvent = 'done',
        bool $includeMetadata = true,
        ?string $entityType = null,
        int $contextLimit = self::DEFAULT_CONTEXT_LIMIT,
        bool $isFirstMessage = false
    ): PromiseInterface {
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
     * Stream chat with progress tracking - no caching
     */
    public function streamChatWithProgress(
        string $userMessage,
        string $progressEvent = 'progress',
        ?string $entityType = null,
        int $contextLimit = self::DEFAULT_CONTEXT_LIMIT,
        bool $isFirstMessage = false
    ): PromiseInterface {
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
     * Get a greeting/introduction response with caching
     */
    public function getIntroduction(): string
    {
        try {
            $prompt = "This is the user's first message in this conversation. The user is greeting you. Introduce yourself.";
            $cacheKey = 'introduction_greeting';
            $response = await($this->sendChatRequestWithCacheKey($prompt, $cacheKey, self::CACHE_TTL_VERY_LONG));
            return $response->text();
        } catch (\Exception $e) {
            logger()->error('Failed to generate introduction: ' . $e->getMessage());
            return "I'm your AI assistant for Nice Smile Clinic. Let me know anything about the clinic operation.";
        }
    }

    /**
     * Stream introduction with SSE - no caching
     */
    public function streamIntroductionSSE(array $sseConfig = []): PromiseInterface
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
    // EMBEDDING METHODS (No caching for embeddings)
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

            // Use non-cached client for embeddings
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
    
    
    public function indexUser(User $user): void
    {
        $content = GeminiIndexer::getContentForUser($user);
        $embedding = $this->generateEmbedding($content);
        GeminiIndexer::indexUser($user, $embedding);
    }

    public function indexPatient(Patient $patient): void
    {
        $content = GeminiIndexer::getContentForPatient($patient);
        $embedding = $this->generateEmbedding($content);
        GeminiIndexer::indexPatient($patient, $embedding);
    }

    public function indexAppointment(Appointment $appointment): void
    {
        $content = GeminiIndexer::getContentForAppointment($appointment);
        $embedding = $this->generateEmbedding($content);
        GeminiIndexer::indexAppointment($appointment, $embedding);
    }

    public function indexDentalService(DentalService $service): void
    {
        $content = GeminiIndexer::getContentForDentalService($service);
        $embedding = $this->generateEmbedding($content);
        GeminiIndexer::indexDentalService($service, $embedding);
    }

    public function indexPatientVisit(PatientVisit $visit): void
    {
        $content = GeminiIndexer::getContentForPatientVisit($visit);
        $embedding = $this->generateEmbedding($content);
        GeminiIndexer::indexPatientVisit($visit, $embedding);
    }

    public function indexBranch(Branch $branch): void
    {
        $content = GeminiIndexer::getContentForBranch($branch);
        $embedding = $this->generateEmbedding($content);
        GeminiIndexer::indexBranch($branch, $embedding);
    }

    public function indexDentalServiceType(DentalServiceType $dentalServiceType): void
    {
        $content = GeminiIndexer::getContentForDentalServiceType($dentalServiceType);
        $embedding = $this->generateEmbedding($content);
        GeminiIndexer::indexDentalServiceType($dentalServiceType, $embedding);
    }

    public function indexInventory(Inventory $inventory): void
    {
        $content = GeminiIndexer::getContentForInventory($inventory);
        $embedding = $this->generateEmbedding($content);
        GeminiIndexer::indexInventory($inventory, $embedding);
    }

    public function indexPatientVisitService(PatientVisitService $patientVisitService): void
    {
        $content = GeminiIndexer::getContentForPatientVisitService($patientVisitService);
        $embedding = $this->generateEmbedding($content);
        GeminiIndexer::indexPatientVisitService($patientVisitService, $embedding);
    }

    public function indexAuditLog(AuditLog $auditLog): void
    {
        $content = GeminiIndexer::getContentForAuditLog($auditLog);
        $embedding = $this->generateEmbedding($content);
        GeminiIndexer::indexAuditLog($auditLog, $embedding);
    }

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

    public function search(string $query, ?string $entityType = null, int $limit = 5): array
    {
        $queryEmbedding = $this->generateEmbedding($query);
        $results = KnowledgeBase::findSimilar($queryEmbedding, $limit, $entityType);

        return GeminiSearchResultMapper::mapResults($results);
    }

    public function searchAsync(string $query, ?string $entityType = null, int $limit = 5): PromiseInterface
    {
        return async(fn() => $this->search($query, $entityType, $limit));
    }

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

    public function listModels(): array
    {
        return await($this->client->listModels())->json();
    }

    public function listModelsAsync(): PromiseInterface
    {
        return $this->client->listModels();
    }

    // ==========================================
    // CACHE MANAGEMENT METHODS
    // ==========================================

    /**
     * Clear all Gemini client cache
     */
    public function clearCache(): void
    {
        $cachePath = storage_path('app/gemini-cache');
        
        if (is_dir($cachePath)) {
            $files = glob($cachePath . '/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }
        
        logger()->info('Gemini cache cleared successfully');
    }

    /**
     * Get cache statistics
     */
    public function getCacheStats(): array
    {
        $cachePath = storage_path('app/gemini-cache');
        
        if (!is_dir($cachePath)) {
            return [
                'exists' => false,
                'count' => 0,
                'size' => 0,
            ];
        }

        $files = glob($cachePath . '/*');
        $totalSize = 0;
        
        foreach ($files as $file) {
            if (is_file($file)) {
                $totalSize += filesize($file);
            }
        }

        return [
            'exists' => true,
            'count' => count($files),
            'size' => $totalSize,
            'size_formatted' => $this->formatBytes($totalSize),
            'path' => $cachePath,
        ];
    }

    /**
     * Update cache configuration dynamically
     */
    public function updateCacheConfig(int $ttlSeconds, bool $respectServerHeaders = false): void
    {
        $cachePath = storage_path('app/gemini-cache');
        $this->cachedClient = $this->cachedClient
            ->withoutCache()
            ->withCachePath($cachePath)
            ->withCache($ttlSeconds, $respectServerHeaders);
    }

    // ==========================================
    // PRIVATE HELPER METHODS
    // ==========================================

    /**
     * Send cached chat request with automatic cache key generation
     */
    private function sendCachedChatRequest(string $userPrompt, ?int $cacheTtl = null): PromiseInterface
    {
        $ttl = $cacheTtl ?? self::CACHE_TTL_MEDIUM;
        
        if ($ttl !== self::CACHE_TTL_MEDIUM) {
            $tempClient = $this->cachedClient->withCache($ttl, false);
        } else {
            $tempClient = $this->cachedClient;
        }

        if (GeminiPromptHelper::supportsSystemInstructions(self::GENERATION_MODEL)) {
            return $tempClient
                ->prompt($userPrompt)
                ->system($this->systemPrompt)
                ->send();
        }

        $combinedPrompt = $this->systemPrompt . "\n\n---\n\n" . $userPrompt;
        return $tempClient
            ->prompt($combinedPrompt)
            ->send();
    }

    /**
     * Send chat request with custom cache key
     */
    private function sendChatRequestWithCacheKey(
        string $userPrompt,
        string $cacheKey,
        int $cacheTtl
    ): PromiseInterface {
        $tempClient = $this->cachedClient->withCacheKey($cacheKey, $cacheTtl, false);

        if (GeminiPromptHelper::supportsSystemInstructions(self::GENERATION_MODEL)) {
            return $tempClient
                ->prompt($userPrompt)
                ->system($this->systemPrompt)
                ->send();
        }

        $combinedPrompt = $this->systemPrompt . "\n\n---\n\n" . $userPrompt;
        return $tempClient
            ->prompt($combinedPrompt)
            ->send();
    }

    /**
     * Stream response with model-aware prompt handling (non-cached)
     */
    private function streamWithPrompt(string $userPrompt, callable $onChunk): PromiseInterface
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
     * Stream SSE response with model-aware prompt handling (non-cached)
     */
    private function streamSSEWithPrompt(string $userPrompt, array $sseConfig = []): PromiseInterface
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

    /**
     * Build forecast prompt for AI analysis
     */
    private function buildForecastPrompt(array $data): string
    {
        return <<<PROMPT
Based on the following Nice Smile Clinic performance data, provide a comprehensive forecast and strategic recommendations for the next month. Be specific, actionable, and focus on opportunities for growth.

CLINIC PERFORMANCE DATA:

Patient Metrics:
- Total Patients: {$data['total_patients']}
- Patients This Month: {$data['patients_this_month']}
- Patients Last Month: {$data['patients_last_month']}
- Patient Growth Rate: {$data['patient_growth_rate']}%

Appointment Metrics:
- Appointments Today: {$data['appointments_today']}
- Upcoming Appointments: {$data['upcoming_appointments']}
- Appointments This Month: {$data['appointments_this_month']}
- Appointments Last Month: {$data['appointments_last_month']}
- Average Appointments Per Day: {$data['average_appointments_per_day']}

Revenue Metrics:
- Revenue This Month: ₱{$data['revenue_this_month']}
- Revenue Last Month: ₱{$data['revenue_last_month']}
- Revenue Growth Rate: {$data['revenue_growth_rate']}%
- Average Visit Value: ₱{$data['average_visit_value']}
- Total Visits This Month: {$data['total_visits_this_month']}

Operational Metrics:
- Cancellation Rate: {$data['cancellation_rate']}%
- No-Show Rate: {$data['no_show_rate']}%

Provide a forecast covering:
1. **Expected Patient Volume**: Predicted number of patients and appointments for next month
2. **Revenue Forecast**: Expected revenue based on current trends
3. **Key Opportunities**: 2-3 specific growth opportunities
4. **Risk Alerts**: Any concerning trends that need attention
5. **Strategic Recommendations**: 3-4 actionable recommendations to improve performance

Keep the response concise (200-300 words), professional, and data-driven. Use bullet points for clarity.
PROMPT;
    }

    /**
     * Build insights prompt for specific analysis types
     */
    private function buildInsightsPrompt(string $insightType, array $data): string
    {
        return match($insightType) {
            'patient_retention' => $this->buildPatientRetentionPrompt($data),
            'service_performance' => $this->buildServicePerformancePrompt($data),
            'operational_efficiency' => $this->buildOperationalEfficiencyPrompt($data),
            default => "Analyze the following data: " . json_encode($data)
        };
    }

    private function buildPatientRetentionPrompt(array $data): string
    {
        return "Analyze patient retention patterns and provide recommendations...";
    }

    private function buildServicePerformancePrompt(array $data): string
    {
        return "Analyze service performance and identify top-performing services...";
    }

    private function buildOperationalEfficiencyPrompt(array $data): string
    {
        return "Analyze operational efficiency metrics and suggest improvements...";
    }

    /**
     * Format bytes to human-readable format
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
}