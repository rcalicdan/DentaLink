<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('CREATE EXTENSION IF NOT EXISTS vector');

        Schema::create('knowledge_base', function (Blueprint $table) {
            $table->id();
            $table->string('entity_type'); 
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->text('content'); 
            $table->text('metadata')->nullable(); 
            $table->vector('embedding', 768); 
            $table->timestamps();

            $table->index(['entity_type', 'entity_id']);
        });

        DB::statement('
            CREATE INDEX knowledge_base_embedding_idx 
            ON knowledge_base 
            USING ivfflat (embedding vector_cosine_ops) 
            WITH (lists = 100)
        ');
    }

    public function down(): void
    {
        Schema::dropIfExists('knowledge_base');
        DB::statement('DROP EXTENSION IF EXISTS vector');
    }
};