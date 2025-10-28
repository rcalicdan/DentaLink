<?php

namespace App\Http\Controllers;

use App\Services\GeminiKnowledgeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use function Hibla\await;

class ChatController extends Controller
{
    public function __construct(
        private GeminiKnowledgeService $knowledgeService
    ) {}

    public function stream(Request $request)
    {
        try {
            $validated = $request->validate([
                'message' => 'required|string|max:1000',
                'entity_type' => 'nullable|string|in:patient,user,appointment,dental_service,patient_visit',
                'history' => 'nullable|string',
                'enhanced' => 'nullable|in:true,false,1,0',
            ]);

            $conversationContext = $this->parseConversationHistory($validated['history'] ?? null);

            return response()->stream(function () use ($validated, $conversationContext) {
                $this->disableOutputBuffering();


                $contextualMessage = $this->buildContextualMessage(
                    $validated['message'],
                    $conversationContext
                );

                $useEnhanced = filter_var($validated['enhanced'] ?? false, FILTER_VALIDATE_BOOLEAN);

                if ($useEnhanced) {
                    $promise = $this->knowledgeService->streamChatWithEvents(
                        userMessage: $contextualMessage,
                        messageEvent: 'message',
                        doneEvent: 'done',
                        includeMetadata: true,
                        entityType: $validated['entity_type'] ?? null,
                        isFirstMessage: empty($conversationContext),
                        contextLimit: 100,
                    );
                } else {
                    $promise = $this->knowledgeService->streamChatWithEvents(
                        userMessage: $contextualMessage,
                        messageEvent: 'message',
                        doneEvent: 'done',
                        includeMetadata: true,
                        entityType: $validated['entity_type'] ?? null,
                        isFirstMessage: empty($conversationContext)
                    );
                }

                $promise->await();
            }, 200, [
                'Content-Type' => 'text/event-stream',
                'Cache-Control' => 'no-cache',
                'Connection' => 'keep-alive',
                'X-Accel-Buffering' => 'no',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Validation failed',
                'message' => $e->getMessage()
            ], 422);
        }
    }

    private function parseConversationHistory(?string $history): array
    {
        if (empty($history)) {
            return [];
        }

        try {
            $historyArray = json_decode($history, true);
            if (is_array($historyArray) && json_last_error() === JSON_ERROR_NONE) {
                return array_slice($historyArray, -10);
            }
        } catch (\Exception $e) {
            Log::warning('Failed to parse conversation history: ' . $e->getMessage());
        }

        return [];
    }

    private function buildContextualMessage(string $currentMessage, array $history): string
    {
        if (empty($history)) {
            return $currentMessage;
        }

        $contextText = "Previous conversation:\n";
        $messageCount = 0;

        foreach ($history as $msg) {
            if (!isset($msg['role']) || !isset($msg['content'])) {
                continue;
            }

            $role = $msg['role'] === 'user' ? 'User' : 'Assistant';
            $contextText .= "{$role}: {$msg['content']}\n";
            $messageCount++;

            if ($messageCount >= 8) {
                break;
            }
        }

        return $contextText . "\nCurrent question: " . $currentMessage;
    }

    private function disableOutputBuffering(): void
    {
        set_time_limit(0);
        if (function_exists('apache_setenv')) {
            apache_setenv('no-gzip', '1');
        }

        ini_set('zlib.output_compression', 0);
        ini_set('implicit_flush', 1);
    }
}
