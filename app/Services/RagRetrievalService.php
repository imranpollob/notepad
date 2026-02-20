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
    public function retrieve(
        Notebook $notebook,
        string $query,
        int $topK = 5,
        array $sourceIds = [],
        bool $scopeToSelectedSources = false
    ): array
    {
        $queryEmbedding = $this->embeddingService->embed($query)['vector'];
        $queryTokens = $this->tokenize($query);
        $semanticWeight = (float) config('rag.semantic_weight', 0.75);
        $keywordWeight = (float) config('rag.keyword_weight', 0.25);

        $sourceIds = array_values(array_unique(array_map('intval', $sourceIds)));

        $chunks = SourceChunk::with(['source.note', 'source.files'])
            ->whereHas('source', function ($builder) use ($notebook, $sourceIds, $scopeToSelectedSources) {
                $builder->where('notebook_id', $notebook->id)
                    ->where('status', 'ready');

                if ($scopeToSelectedSources) {
                    if ($sourceIds === []) {
                        $builder->whereRaw('1 = 0');
                    } else {
                        $builder->whereIn('id', $sourceIds);
                    }
                }
            })
            ->get();

        $scored = [];
        foreach ($chunks as $chunk) {
            $chunkEmbedding = $chunk->embedding;

            if (!is_array($chunkEmbedding) || $chunkEmbedding === []) {
                continue;
            }

            $semanticScore = $this->cosineSimilarity($queryEmbedding, $chunkEmbedding);
            $keywordScore = $this->keywordSimilarity($queryTokens, $this->tokenize($chunk->content));
            $score = ($semanticWeight * $semanticScore) + ($keywordWeight * $keywordScore);

            $scored[] = [
                'score' => $score,
                'semantic_score' => $semanticScore,
                'keyword_score' => $keywordScore,
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

    /**
     * @param array<int, string> $queryTokens
     * @param array<int, string> $chunkTokens
     */
    private function keywordSimilarity(array $queryTokens, array $chunkTokens): float
    {
        if ($queryTokens === [] || $chunkTokens === []) {
            return 0.0;
        }

        $querySet = array_values(array_unique($queryTokens));
        $chunkSet = array_values(array_unique($chunkTokens));

        $matches = count(array_intersect($querySet, $chunkSet));
        if ($matches === 0) {
            return 0.0;
        }

        return $matches / max(1, count($querySet));
    }

    /**
     * @return array<int, string>
     */
    private function tokenize(string $text): array
    {
        $tokens = preg_split('/[^A-Za-z0-9]+/', mb_strtolower($text)) ?: [];

        return array_values(array_filter($tokens, fn ($token) => $token !== ''));
    }
}
