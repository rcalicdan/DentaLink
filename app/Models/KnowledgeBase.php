<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KnowledgeBase extends Model
{
    protected $table = 'knowledge_base';

    protected $fillable = [
        'entity_type',
        'entity_id',
        'content',
        'metadata',
        'embedding',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    /**
     * Find similar content using cosine distance
     */
    public static function findSimilar(
        array $queryEmbedding, 
        int $limit = 5, 
        ?string $entityType = null
    ) {
        $embeddingString = '[' . implode(',', $queryEmbedding) . ']';
        
        $query = self::select('*')
            ->selectRaw('embedding <=> ? as distance', [$embeddingString])
            ->orderBy('distance', 'asc')
            ->limit($limit);

        if ($entityType) {
            $query->where('entity_type', $entityType);
        }

        return $query->get();
    }

    /**
     * Store embedding
     */
    public static function storeEmbedding(
        string $entityType,
        ?int $entityId,
        string $content,
        array $embedding,
        ?array $metadata = null
    ) {
        $embeddingString = '[' . implode(',', $embedding) . ']';

        return self::updateOrCreate(
            [
                'entity_type' => $entityType,
                'entity_id' => $entityId,
            ],
            [
                'content' => $content,
                'embedding' => $embeddingString,
                'metadata' => $metadata,
            ]
        );
    }
}