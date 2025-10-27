<?php

use App\Services\GeminiKnowledgeService;

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$client = new GeminiKnowledgeService();

echo "=== Testing Enhanced Conversational Chat ===\n\n";

$queries = [
    "Hello",
    "Find user with email superadmin@nice-smile.com",
    "Who are ALL the admin users in the system?",
    "List all employees",
    "How many users are in the database?",
    "Tell me about user ID 1",
];

foreach ($queries as $query) {
    echo "User: {$query}\n";
    echo "Assistant: ";
    
    try {
        $response = $client->enhancedChat($query, null, 15);
        echo $response . "\n";
    } catch (\Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
    
    echo "\n" . str_repeat("=", 80) . "\n\n";
}