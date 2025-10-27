<?php

namespace App\Http\Controllers;

use App\Services\GeminiKnowledgeService;
use Illuminate\Http\Request;

class AiStreamController extends Controller
{
    public function stream(Request $request, GeminiKnowledgeService $geminiService)
    {
        $validated = $request->validate([
            'message' => 'required|string',
            'isFirstMessage' => 'boolean',
        ]);

        return response()->stream(function () use ($geminiService, $validated) {
            try {
                $fullMessage = '';
                
                $promise = $geminiService->streamChat(
                    userMessage: $validated['message'],
                    onChunk: function ($chunk) use (&$fullMessage) {
                        $text = $chunk->text();
                        $fullMessage .= $text;
                        
                        echo "event: chunk\n";
                        echo "data: " . json_encode([
                            'content' => $text,
                            'fullMessage' => $fullMessage,
                        ]) . "\n\n";
                        
                        if (ob_get_level() > 0) {
                            ob_flush();
                        }
                        flush();
                    },
                    entityType: null,
                    contextLimit: 5,
                    isFirstMessage: $validated['isFirstMessage'] ?? false
                );

                // Wait for completion
                \Hibla\await($promise);

                // Send completion event
                echo "event: complete\n";
                echo "data: " . json_encode([
                    'fullMessage' => $fullMessage,
                ]) . "\n\n";
                
                if (ob_get_level() > 0) {
                    ob_flush();
                }
                flush();

            } catch (\Exception $e) {
                echo "event: error\n";
                echo "data: " . json_encode([
                    'message' => $e->getMessage(),
                ]) . "\n\n";
                
                if (ob_get_level() > 0) {
                    ob_flush();
                }
                flush();
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
            'Connection' => 'keep-alive',
        ]);
    }
}