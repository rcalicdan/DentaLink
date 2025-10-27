<?php

namespace App\Services;

use App\Models\KnowledgeBase;
use App\Models\Patient;
use App\Models\User;
use App\Models\Appointment;
use App\Models\DentalService;
use App\Models\PatientVisit;
use Rcalicdan\GeminiClient\GeminiClient;
use Hibla\HttpClient\SSE\SSEEvent;
use Hibla\Promise\Interfaces\PromiseInterface;
use Hibla\Promise\Interfaces\CancellablePromiseInterface;
use function Hibla\await;
use function Hibla\async;

class GeminiKnowledgeService
{
    private const DEFAULT_CONTEXT_LIMIT = 3;
    private const ENHANCED_CONTEXT_LIMIT = 5;
    private const EMBEDDING_MODEL = 'text-embedding-004';
    private const GENERATION_MODEL = 'gemini-2.0-flash-exp';
    private const BATCH_SIZE = 50;
    private const RATE_LIMIT_DELAY = 100000; // 100ms in microseconds

    protected GeminiClient $client;
    protected string $systemPrompt;

    public function __construct()
    {
        $this->client = new GeminiClient(
            apiKey: config('gemini.api_key'),
            model: self::GENERATION_MODEL
        );

        $this->systemPrompt = $this->buildSystemPrompt();
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
        $context = $this->buildContext($userMessage, $entityType, $contextLimit);
        $userPrompt = $this->buildUserPrompt($context, $userMessage, $isFirstMessage);

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
        $context = $this->buildContext($userMessage, $entityType, $contextLimit);
        $userPrompt = $this->buildUserPrompt($context, $userMessage, $isFirstMessage);

        return $this->client
            ->prompt($userPrompt)
            ->system($this->systemPrompt)
            ->stream($onChunk);
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
        
        $context = $this->buildEnhancedContext($stats, $searchResults);
        $userPrompt = $this->buildEnhancedUserPrompt($context, $userMessage, $isFirstMessage);

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
        
        $context = $this->buildEnhancedContext($stats, $searchResults);
        $userPrompt = $this->buildEnhancedUserPrompt($context, $userMessage, $isFirstMessage);

        return $this->client
            ->prompt($userPrompt)
            ->system($this->systemPrompt)
            ->stream($onChunk);
    }

    /**
     * Get a greeting/introduction response (only for first interaction)
     */
    public function getIntroduction(): string
    {
        try {
            $response = await(
                $this->client
                    ->prompt("This is the user's first message in this conversation. The user is greeting you. Introduce yourself.")
                    ->system($this->systemPrompt)
                    ->send()
            );
            
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
        $content = $this->buildUserContent($user);
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
        $content = $this->buildPatientContent($patient);
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
        $content = $this->buildAppointmentContent($appointment);
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
        $content = $this->buildServiceContent($service);
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
        $content = $this->buildVisitContent($visit);
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

        return KnowledgeBase::findSimilar($queryEmbedding, $limit, $entityType)
            ->map(fn($item) => [
                'entity_type' => $item->entity_type,
                'entity_id' => $item->entity_id,
                'content' => $item->content,
                'metadata' => $item->metadata,
                'similarity_score' => 1 - $item->distance,
                'distance' => $item->distance,
            ])
            ->toArray();
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
     * Build system prompt
     */
    private function buildSystemPrompt(): string
    {
        return <<<PROMPT
You are an AI assistant exclusively for Nice Smile Clinic operations. Your role is to help with clinic-related queries only.

IMPORTANT RULES:
1. ONLY answer questions related to Nice Smile Clinic operations, including:
   - Patient information and records
   - Appointments and scheduling
   - Dental services and procedures
   - Staff and employee information
   - Patient visits and treatment history
   - Clinic branches and locations
   - Billing and payment information

2. For ANY question NOT related to Nice Smile Clinic operations, politely decline and redirect:
   "I'm sorry, but I can only assist with questions related to Nice Smile Clinic operations. Please ask me about patients, appointments, services, staff, or other clinic-related matters."

3. INTRODUCTION RULE - VERY IMPORTANT:
   - If the user's message is a greeting (like "hi", "hello", "hey", etc.) AND this appears to be the start of a conversation, introduce yourself with:
     "I'm your AI assistant for Nice Smile Clinic. Let me know anything about the clinic operation."
   - For all OTHER messages (including follow-up questions), do NOT introduce yourself again. Just answer the question directly.
   - Never repeat the introduction in the same conversation.

4. Be professional, accurate, and helpful for all clinic-related queries.
5. Base your answers on the provided context from the clinic database.
6. If you don't have enough information to answer a clinic-related question, say so clearly.

Remember: You are NOT a general-purpose AI. You are specifically designed for Nice Smile Clinic operations only.
PROMPT;
    }

    /**
     * Build context from search results
     */
    private function buildContext(string $userMessage, ?string $entityType, int $contextLimit): string
    {
        $searchResults = $this->search($userMessage, $entityType, $contextLimit);

        if (empty($searchResults)) {
            return '';
        }

        $context = "Here is relevant information from the clinic database:\n\n";
        foreach ($searchResults as $result) {
            $context .= "- " . $result['content'] . "\n";
        }
        $context .= "\n";

        return $context;
    }

    /**
     * Build enhanced context with statistics
     */
    private function buildEnhancedContext(array $stats, array $searchResults): string
    {
        $context = "Nice Smile Clinic Database Statistics:\n";
        foreach ($stats as $type => $count) {
            $context .= "- Total {$type}s: {$count}\n";
        }
        $context .= "\nRelevant information from the clinic:\n";

        foreach ($searchResults as $index => $result) {
            $relevance = round($result['similarity_score'] * 100, 1);
            $context .= ($index + 1) . ". " . $result['content'] . " (Relevance: {$relevance}%)\n";
        }

        return $context;
    }

    /**
     * Build user prompt
     */
    private function buildUserPrompt(string $context, string $userMessage, bool $isFirstMessage): string
    {
        $conversationHint = $isFirstMessage 
            ? "This is the user's first message in this conversation.\n" 
            : "This is a follow-up message in an ongoing conversation.\n";

        return $conversationHint . $context . "User message: " . $userMessage;
    }

    /**
     * Build enhanced user prompt
     */
    private function buildEnhancedUserPrompt(string $context, string $userMessage, bool $isFirstMessage): string
    {
        $conversationHint = $isFirstMessage 
            ? "This is the user's first message in this conversation.\n" 
            : "This is a follow-up message in an ongoing conversation.\n";

        return $conversationHint 
            . $context 
            . "\nUser question: " . $userMessage 
            . "\n\nProvide a complete and accurate answer based on the clinic data. If the user asks for a list or count, make sure to provide the full information based on the statistics and search results.";
    }

    /**
     * Send chat request
     */
    private function sendChatRequest(string $userPrompt): PromiseInterface
    {
        return $this->client
            ->prompt($userPrompt)
            ->system($this->systemPrompt)
            ->send();
    }

    /**
     * Build user content for embedding
     */
    private function buildUserContent(User $user): string
    {
        $roleName = match ($user->role) {
            'super_admin' => 'Super Admin',
            'admin' => 'Admin',
            'employee' => 'Employee',
            default => ucfirst(str_replace('_', ' ', $user->role ?? 'Unknown'))
        };

        return sprintf(
            "User: %s. Full Name: %s %s. Email: %s. Phone: %s. User ID: %s. Role: %s. Branch: %s. Created: %s",
            $user->full_name,
            $user->first_name,
            $user->last_name,
            $user->email,
            $user->phone ?? 'Not provided',
            $user->id,
            $roleName,
            $user->branch_name,
            $user->created_at->format('F d, Y')
        );
    }

    /**
     * Build patient content for embedding
     */
    private function buildPatientContent(Patient $patient): string
    {
        return sprintf(
            "Patient: %s. Phone: %s. Email: %s. Date of Birth: %s. Age: %s. Branch: %s. Address: %s. Registration Date: %s",
            $patient->full_name,
            $patient->phone,
            $patient->email ?? 'Not provided',
            $patient->date_of_birth?->format('F d, Y') ?? 'Not provided',
            $patient->age ?? 'Unknown',
            $patient->registration_branch_name,
            $patient->address ?? 'Not provided',
            $patient->created_at->format('F d, Y')
        );
    }

    /**
     * Build appointment content for embedding
     */
    private function buildAppointmentContent(Appointment $appointment): string
    {
        return sprintf(
            "Appointment for patient %s scheduled on %s at %s. Status: %s. Queue number: %s. Reason: %s. Branch: %s. Notes: %s",
            $appointment->patient_name,
            $appointment->formatted_date,
            $appointment->formatted_time_range ?? 'Time not set',
            ucfirst($appointment->status->value),
            $appointment->queue_number ?? 'Not assigned',
            $appointment->reason,
            $appointment->branch->name,
            $appointment->notes ?? 'No additional notes'
        );
    }

    /**
     * Build service content for embedding
     */
    private function buildServiceContent(DentalService $service): string
    {
        return sprintf(
            "Dental Service: %s. Category: %s. Price: ₱%s. %s. Description: Professional dental care service.",
            $service->name,
            $service->service_type_name,
            number_format($service->price, 2),
            $service->is_quantifiable ? 'Quantity-based service' : 'Fixed service'
        );
    }

    /**
     * Build visit content for embedding
     */
    private function buildVisitContent(PatientVisit $visit): string
    {
        $services = $visit->patientVisitServices->pluck('dentalService.name')->join(', ');

        return sprintf(
            "Patient visit for %s on %s at %s branch. Type: %s. Services: %s. Total amount: ₱%s. Notes: %s",
            $visit->patient_name,
            $visit->visit_date->format('F d, Y g:i A'),
            $visit->branch_name,
            $visit->visit_type,
            $services ?: 'No services recorded',
            number_format($visit->total_amount_paid, 2),
            $visit->notes ?? 'No notes'
        );
    }
}