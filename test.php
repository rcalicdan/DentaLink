<?php

use App\Services\GeminiKnowledgeService;

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$client = new GeminiKnowledgeService();

// Check how many embeddings exist
$totalEmbeddings = \App\Models\KnowledgeBase::count();
echo "Total embeddings in database: {$totalEmbeddings}\n\n";

if ($totalEmbeddings === 0) {
    echo "No embeddings found! Please run: php artisan knowledge:index --fresh\n";
    exit;
}

// Test different searches
echo "=== Search 1: Find patients ===\n";
$results = $client->search("find patients in the database", 'patient', 3);
print_r($results);

echo "\n=== Search 2: Find dental services ===\n";
$results = $client->search("what dental services are available", 'dental_service', 3);
print_r($results);

echo "\n=== Search 3: Find appointments ===\n";
$results = $client->search("upcoming appointments", 'appointment', 3);
print_r($results);

echo "\n=== Search 4: General search (all types) ===\n";
$results = $client->search("recent patient visits with high costs", null, 5);
print_r($results);