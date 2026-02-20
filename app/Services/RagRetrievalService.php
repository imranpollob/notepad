<?php

namespace App\Services;

use App\Notebook;
use App\SourceChunk;

class RagRetrievalService
{
    private EmbeddingService $embeddingService;

    public function __construct(EmbeddingService $embeddingService)
    {
        $this->embeddingService = $embeddingService;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function retrieve(Notebook $notebook, string $query, int $topK = 5): array
    {
        $queryEmbedding = $this->embeddingService->embed($query)['vector'];

        $chunks = SourceChunk::with('source')
            ->whereHas('source', function ($builder) use ($notebook) {
                $builder->where('notebook_id', $notebook->id)
                    ->where('status', 'ready');
            })
            ->get();

        $scored = [];
        foreach ($chunks as $chunk) {
            $chunkEmbedding = $chunk->embedding;

            if (!is_array($chunkEmbedding) || $chunkEmbedding === []) {
                continue;
            }

            $score = $this->cosineSimilarity($queryEmbedding, $chunkEmbedding);
            $scored[] = [
                'score' => $score,
                'chunk' => $chunk,
                'source' => $chunk->source,
            ];
        }

        usort($scored, function ($left, $right) {
            return $right['score'] <=> $left['score'];
        });

        return array_slice($scored, 0, max(1, $topK));
    }

    /**
     * @param array<int, float> $a
     * @param array<int, float> $b
     */
    private function cosineSimilarity(array $a, array $b): float
    {
        $length = min(count($a), count($b));
        if ($length === 0) {
            return 0.0;
        }

        $dot = 0.0;
        $normA = 0.0;
        $normB = 0.0;

        for ($i = 0; $i < $length; $i++) {
            $dot += ((float) $a[$i]) * ((float) $b[$i]);
            $normA += ((float) $a[$i]) * ((float) $a[$i]);
            $normB += ((float) $b[$i]) * ((float) $b[$i]);
        }

        if ($normA <= 0.0 || $normB <= 0.0) {
            return 0.0;
        }

        return $dot / (sqrt($normA) * sqrt($normB));
    }
}
