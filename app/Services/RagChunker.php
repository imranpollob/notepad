<?php

namespace App\Services;

class RagChunker
{
    /**
     * @return array<int, string>
     */
    public function chunkText(string $text, int $chunkSize = 1200, int $overlap = 200): array
    {
        $normalized = trim(preg_replace('/\s+/', ' ', $text) ?? $text);

        if ($normalized === '') {
            return [];
        }

        $chunks = [];
        $length = mb_strlen($normalized);
        $offset = 0;
        $step = max(1, $chunkSize - $overlap);

        while ($offset < $length) {
            $chunk = mb_substr($normalized, $offset, $chunkSize);
            $chunk = trim($chunk);

            if ($chunk !== '') {
                $chunks[] = $chunk;
            }

            if (($offset + $chunkSize) >= $length) {
                break;
            }

            $offset += $step;
        }

        return $chunks;
    }
}
