<?php

namespace App\Services;

use App\Source;
use App\SourceChunk;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SourceIndexingService
{
    private RagChunker $chunker;

    private EmbeddingService $embeddingService;

    public function __construct(RagChunker $chunker, EmbeddingService $embeddingService)
    {
        $this->chunker = $chunker;
        $this->embeddingService = $embeddingService;
    }

    public function indexSource(int $sourceId): void
    {
        $source = Source::with('content')->findOrFail($sourceId);
        $content = (string) optional($source->content)->content_text;

        if (trim($content) === '') {
            throw new \RuntimeException('Cannot index source without extracted content.');
        }

        $chunks = $this->chunker->chunkText($content);

        DB::transaction(function () use ($source, $chunks) {
            SourceChunk::where('source_id', $source->id)->delete();

            foreach ($chunks as $index => $chunkText) {
                $embedding = $this->embeddingService->embed($chunkText);

                SourceChunk::create([
                    'source_id' => $source->id,
                    'chunk_index' => $index,
                    'content' => $chunkText,
                    'token_count' => $this->tokenCount($chunkText),
                    'embedding' => $embedding['vector'],
                    'embedding_model' => $embedding['model'],
                    'embedded_at' => Carbon::now(),
                ]);
            }

            $metadata = is_array($source->metadata) ? $source->metadata : [];
            $metadata['chunk_count'] = count($chunks);
            $metadata['indexed_at'] = Carbon::now()->toIso8601String();

            $source->update([
                'metadata' => $metadata,
            ]);
        });
    }

    private function tokenCount(string $text): int
    {
        $trimmed = trim($text);

        if ($trimmed === '') {
            return 0;
        }

        return count(preg_split('/\s+/', $trimmed) ?: []);
    }
}
