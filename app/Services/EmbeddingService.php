<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Throwable;

class EmbeddingService
{
    /**
     * @return array{model:string, vector:array<int, float>}
     */
    public function embed(string $text): array
    {
        $apiKey = (string) config('services.openai.api_key');
        $model = (string) config('services.openai.embedding_model', 'text-embedding-3-small');

        if ($apiKey !== '') {
            try {
                $response = Http::timeout(30)
                    ->withToken($apiKey)
                    ->post('https://api.openai.com/v1/embeddings', [
                        'model' => $model,
                        'input' => $text,
                    ]);

                if ($response->ok()) {
                    $vector = $response->json('data.0.embedding');

                    if (is_array($vector) && $vector !== []) {
                        return [
                            'model' => $model,
                            'vector' => array_map('floatval', $vector),
                        ];
                    }
                }
            } catch (Throwable $exception) {
                // Fallback embedding below handles connectivity and API failures.
            }
        }

        return [
            'model' => 'hash-fallback-v1',
            'vector' => $this->fallbackEmbedding($text),
        ];
    }

    /**
     * Deterministic local embedding so development and tests work without external APIs.
     *
     * @return array<int, float>
     */
    private function fallbackEmbedding(string $text): array
    {
        $dimension = 128;
        $vector = array_fill(0, $dimension, 0.0);

        $tokens = preg_split('/[^A-Za-z0-9]+/', mb_strtolower($text)) ?: [];
        foreach ($tokens as $token) {
            if ($token === '') {
                continue;
            }

            $hash = crc32($token);
            $index = (int) ($hash % $dimension);
            $vector[$index] += 1.0;
        }

        $norm = 0.0;
        foreach ($vector as $value) {
            $norm += $value * $value;
        }
        $norm = sqrt($norm);

        if ($norm > 0) {
            foreach ($vector as $i => $value) {
                $vector[$i] = $value / $norm;
            }
        }

        return $vector;
    }
}
