<?php

use App\Services\GeminiKnowledgeService;

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$client = new GeminiKnowledgeService();

echo "=== Testing Enhanced Conversational Chat ===\n\n";

$queries = [
    "Find user with email superadmin@nice-smile.com",
];

foreach ($queries as $query) {
    echo "User: {$query}\n";
    echo "Assistant: ";
    

        $response = $client->enhancedChat($query, null, 15);
        echo $response . "\n";
  
    
    echo "\n" . str_repeat("=", 80) . "\n\n";
}