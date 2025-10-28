<?php

require __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Contracts\Console\Kernel;

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

$service = $app->make(\App\Services\GeminiKnowledgeService::class);

$message = $argv[1] ?? 'What are the clinic services?';

echo "===========================================\n";
echo "Testing streamAndFlush\n";
echo "===========================================\n\n";
echo "Message: {$message}\n\n";

$startTime = microtime(true);
$eventCount = 0;
$messageCount = 0;
$doneCount = 0;

try {
    // Capture output to analyze
    ob_start();
    
    $promise = $service->streamChatAsSSE(
        userMessage: $message,
        entityType: null,
        isFirstMessage: true,
        sendDoneEvent: true,
        doneEventName: 'done'
    );
    
    $result = \Hibla\await($promise);
    
    $output = ob_get_clean();
    
    // Parse SSE output
    $lines = explode("\n", $output);
    foreach ($lines as $line) {
        if (strpos($line, 'event: ') === 0) {
            $eventType = trim(substr($line, 7));
            if ($eventType === 'message') $messageCount++;
            if ($eventType === 'done') $doneCount++;
            $eventCount++;
        }
    }
    
    $duration = microtime(true) - $startTime;
    
    echo "\n✅ Completed!\n\n";
    echo "Events: {$messageCount} message, {$doneCount} done\n";
    echo "Result chunks: {$result->chunkCount()}\n";
    echo "Text length: " . strlen($result->text()) . "\n";
    echo "Duration: " . number_format($duration, 3) . "s\n\n";
    
    echo "Generated text:\n";
    echo str_repeat('-', 50) . "\n";
    echo $result->text() . "\n";
    echo str_repeat('-', 50) . "\n";
    
} catch (\Throwable $e) {
    if (ob_get_level()) ob_end_clean();
    echo "\n❌ Error: {$e->getMessage()}\n";
    echo "File: {$e->getFile()}:{$e->getLine()}\n";
    exit(1);
}