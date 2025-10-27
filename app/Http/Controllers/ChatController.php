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
            ]);

            $conversationContext = [];
            
            if (!empty($validated['history'])) {
                try {
                    $historyArray = json_decode($validated['history'], true);
                    if (is_array($historyArray) && json_last_error() === JSON_ERROR_NONE) {
                        $conversationContext = array_slice($historyArray, -10);
                    }
                } catch (\Exception $e) {
                    Log::warning('Failed to parse conversation history: ' . $e->getMessage());
                }
            }

            return response()->stream(function () use ($validated, $conversationContext) {
                set_time_limit(0);
                
                try {
                    $contextualMessage = $this->buildContextualMessage(
                        $validated['message'],
                        $conversationContext
                    );

                    Log::info('Starting stream', [
                        'message' => $validated['message'],
                        'has_history' => !empty($conversationContext)
                    ]);

                    $promise = $this->knowledgeService->streamChatAsSSE(
                        userMessage: $contextualMessage,
                        entityType: $validated['entity_type'] ?? null,
                        isFirstMessage: empty($conversationContext),
                        sendDoneEvent: true,
                        doneEventName: 'done'
                    );

                    await($promise);
                    
                    Log::info('Stream completed successfully');

                } catch (\Throwable $e) {
                    Log::error('Stream error', [
                        'message' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                
                    echo "event: error\n";
                    echo "data: " . json_encode([
                        'error' => 'Failed to generate response',
                        'message' => $e->getMessage()
                    ]) . "\n\n";
                }
            }, 200, [
                'Content-Type' => 'text/event-stream',
                'Cache-Control' => 'no-cache',
                'X-Accel-Buffering' => 'no',
                'Connection' => 'keep-alive',
            ]);

        } catch (\Exception $e) {
            Log::error('Chat stream validation error: ' . $e->getMessage());
            
            return response()->json([
                'error' => 'Validation failed',
                'message' => $e->getMessage()
            ], 422);
        }
    }

    private function buildContextualMessage(string $currentMessage, array $history): string
    {
        if (empty($history)) {
            return $currentMessage;
        }

        // Format conversation history (last few messages only)
        $contextText = "Previous conversation:\n";
        $messageCount = 0;
        
        foreach ($history as $msg) {
            if (!isset($msg['role']) || !isset($msg['content'])) {
                continue;
            }
            
            $role = $msg['role'] === 'user' ? 'User' : 'Assistant';
            $contextText .= "{$role}: {$msg['content']}\n";
            $messageCount++;
            
            // Limit context to prevent token overflow
            if ($messageCount >= 8) {
                break;
            }
        }

        return $contextText . "\nCurrent question: " . $currentMessage;
    }
}