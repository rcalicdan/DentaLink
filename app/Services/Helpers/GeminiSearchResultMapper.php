<?php

namespace App\Services\Helpers;

use Illuminate\Support\Collection;

class GeminiSearchResultMapper
{
    /**
     * Map knowledge base results to search result format
     */
    public static function mapResults(Collection $results): array
    {
        return $results->map(fn($item) => [
            'entity_type' => $item->entity_type,
            'entity_id' => $item->entity_id,
            'content' => $item->content,
            'metadata' => $item->metadata,
            'similarity_score' => 1 - $item->distance,
            'distance' => $item->distance,
        ])->toArray();
    }
}