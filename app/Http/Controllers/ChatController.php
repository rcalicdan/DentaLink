<?php

namespace App\Http\Controllers;

use App\Services\GeminiKnowledgeService;
use App\Services\Helpers\SSEFormatter;
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
                while (ob_get_level()) {
                    ob_end_clean();
                }
                if (function_exists('apache_setenv')) {
                    apache_setenv('no-gzip', '1');
                }
                ini_set('zlib.output_compression', 0);
                ini_set('implicit_flush', 1);

                set_time_limit(0);

                $chunkCount = 0;
                $totalLength = 0;
                $startTime = microtime(true);

                try {
                    $contextualMessage = $this->buildContextualMessage(
                        $validated['message'],
                        $conversationContext
                    );

                    Log::info('Starting manual stream', [
                        'message' => $validated['message'],
                        'has_history' => !empty($conversationContext),
                        'enhanced' => $validated['enhanced'] ?? false
                    ]);

                    $useEnhanced = $validated['enhanced'] ?? false;

                    if ($useEnhanced) {
                        $promise = $this->knowledgeService->enhancedStreamChat(
                            userMessage: $contextualMessage,
                            onChunk: function (string $chunk) use (&$chunkCount, &$totalLength) {
                                $chunkCount++;
                                $totalLength += strlen($chunk);


                                SSEFormatter::sendAndFlush(
                                    SSEFormatter::message($chunk)
                                );

                                Log::debug("Chunk #{$chunkCount}", [
                                    'size' => strlen($chunk),
                                    'total' => $totalLength
                                ]);
                            },
                            entityType: $validated['entity_type'] ?? null,
                            isFirstMessage: empty($conversationContext)
                        );
                    } else {
                        $promise = $this->knowledgeService->streamChat(
                            userMessage: $contextualMessage,
                            onChunk: function (string $chunk) use (&$chunkCount, &$totalLength) {
                                $chunkCount++;
                                $totalLength += strlen($chunk);

                                // Send chunk as SSE message event
                                SSEFormatter::sendAndFlush(
                                    SSEFormatter::message($chunk)
                                );

                                Log::debug("Chunk #{$chunkCount}", [
                                    'size' => strlen($chunk),
                                    'total' => $totalLength
                                ]);
                            },
                            entityType: $validated['entity_type'] ?? null,
                            isFirstMessage: empty($conversationContext)
                        );
                    }

                    $result = await($promise);

                    $duration = microtime(true) - $startTime;

                    SSEFormatter::sendAndFlush(
                        SSEFormatter::done($chunkCount, $totalLength)
                    );

                    Log::info('Stream completed successfully', [
                        'chunks_sent' => $chunkCount,
                        'length' => $totalLength,
                        'result_chunks' => $result->chunkCount(),
                        'result_length' => strlen($result->text()),
                        'duration' => round($duration, 3)
                    ]);
                } catch (\Throwable $e) {
                    Log::error('Stream error', [
                        'message' => $e->getMessage(),
                        'chunks_sent' => $chunkCount,
                        'trace' => $e->getTraceAsString()
                    ]);

                    // Send error event
                    SSEFormatter::sendAndFlush(
                        SSEFormatter::error(
                            'Failed to generate response',
                            $e->getMessage()
                        )
                    );
                }
            }, 200, [
                'Content-Type' => 'text/event-stream',
                'Cache-Control' => 'no-cache',
                'Connection' => 'keep-alive',
                'X-Accel-Buffering' => 'no',
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
}
