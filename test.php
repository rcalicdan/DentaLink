<?php

use App\Models\User;
use App\Services\GeminiKnowledgeService;

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();


$client = new GeminiKnowledgeService();

$results = $client->search("who is the user in the database with id 1");

var_dump($results);
