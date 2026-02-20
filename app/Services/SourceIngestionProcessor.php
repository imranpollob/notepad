<?php

namespace App\Services;

use App\Jobs\IndexSourceChunks;
use App\Source;
use App\SourceContent;
use App\SourceFile;
use App\SourceIngestion;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;
use ZipArchive;

class SourceIngestionProcessor
{
    public function process(int $ingestionId): void
    {
        $ingestion = SourceIngestion::with(['source.files', 'source.note'])->find($ingestionId);

        if (!$ingestion || !$ingestion->source) {
            return;
        }

        $source = $ingestion->source;

        $ingestion->update([
            'status' => 'processing',
            'started_at' => Carbon::now(),
            'error_message' => null,
        ]);

        $source->update([
            'status' => 'processing',
            'error_message' => null,
        ]);

        try {
            $payload = $this->extractSourceContent($source);

            SourceContent::updateOrCreate(
                ['source_id' => $source->id],
                [
                    'content_text' => $payload['content_text'],
                    'content_html' => $payload['content_html'],
                    'language' => $payload['language'],
                    'word_count' => $this->wordCount($payload['content_text']),
                    'extracted_at' => Carbon::now(),
                ]
            );

            $source->update([
                'status' => 'ready',
                'error_message' => null,
            ]);

            IndexSourceChunks::dispatch($source->id);

            $ingestion->update([
                'status' => 'completed',
                'finished_at' => Carbon::now(),
                'error_message' => null,
            ]);
        } catch (Throwable $exception) {
            $message = Str::limit($exception->getMessage(), 1000, '');

            $source->update([
                'status' => 'failed',
                'error_message' => $message,
            ]);

            $ingestion->update([
                'status' => 'failed',
                'finished_at' => Carbon::now(),
                'error_message' => $message,
            ]);
        }
    }

    private function extractSourceContent(Source $source): array
    {
        if ($source->source_type === 'url') {
            return $this->extractFromUrl((string) $source->origin_url);
        }

        if ($source->source_type === 'file') {
            $file = $source->files->first();

            if (!$file) {
                throw new \RuntimeException('Source file record not found.');
            }

            return $this->extractFromFile($file);
        }

        if ($source->source_type === 'note' && $source->note) {
            $html = (string) $source->note->data;
            $text = $this->normalizeText(strip_tags($html));

            return [
                'content_text' => $text,
                'content_html' => $html,
                'language' => null,
            ];
        }

        throw new \RuntimeException('Unsupported source type for ingestion.');
    }

    private function extractFromUrl(string $url): array
    {
        $response = Http::timeout(20)
            ->retry(1, 300)
            ->get($url);

        if (!$response->ok()) {
            throw new \RuntimeException("Unable to fetch URL content: {$response->status()}");
        }

        $html = (string) $response->body();
        $text = $this->extractReadableTextFromHtml($html);

        if ($text === '') {
            throw new \RuntimeException('No readable text found on URL.');
        }

        return [
            'content_text' => $text,
            'content_html' => $html,
            'language' => null,
        ];
    }

    private function extractFromFile(SourceFile $file): array
    {
        $path = Storage::disk($file->disk)->path($file->path);
        $extension = strtolower(pathinfo($file->original_name, PATHINFO_EXTENSION));

        if ($extension === 'docx') {
            $text = $this->extractDocxText($path);
        } elseif ($extension === 'pdf') {
            $text = $this->extractPdfText($path);
        } elseif ($extension === 'doc') {
            $text = $this->extractDocText($path);
        } else {
            throw new \RuntimeException('Unsupported file extension.');
        }

        $text = $this->normalizeText($text);

        if ($text === '') {
            throw new \RuntimeException('No readable text extracted from file.');
        }

        return [
            'content_text' => $text,
            'content_html' => null,
            'language' => null,
        ];
    }

    private function extractDocxText(string $path): string
    {
        $zip = new ZipArchive();

        if ($zip->open($path) !== true) {
            throw new \RuntimeException('Could not open DOCX file.');
        }

        $xml = $zip->getFromName('word/document.xml');
        $zip->close();

        if (!$xml) {
            throw new \RuntimeException('DOCX document.xml missing.');
        }

        $xml = preg_replace('/<w:p[^>]*>/', "\n", $xml);
        $xml = preg_replace('/<[^>]+>/', ' ', (string) $xml);

        return html_entity_decode((string) $xml, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }

    private function extractPdfText(string $path): string
    {
        $text = '';

        if ($this->commandExists('pdftotext')) {
            $text = $this->runCommand(
                sprintf(
                    '%s -layout -enc UTF-8 %s - 2>/dev/null',
                    escapeshellcmd('pdftotext'),
                    escapeshellarg($path)
                )
            );

            if ($this->isLikelyValidPdfText($text)) {
                return $text;
            }
        }

        if (class_exists('\Smalot\PdfParser\Parser')) {
            $parser = new \Smalot\PdfParser\Parser();
            $pdf = $parser->parseFile($path);
            $text = (string) $pdf->getText();

            if ($this->isLikelyValidPdfText($text)) {
                return $text;
            }
        }

        if ($this->commandExists('mutool')) {
            $text = $this->runCommand(
                sprintf(
                    '%s draw -F txt %s 2>/dev/null',
                    escapeshellcmd('mutool'),
                    escapeshellarg($path)
                )
            );

            if ($this->isLikelyValidPdfText($text)) {
                return $text;
            }
        }

        $raw = file_get_contents($path);

        if ($raw === false) {
            throw new \RuntimeException('Could not read PDF file.');
        }

        preg_match_all('/\(([^()]*)\)/', $raw, $matches);
        $chunks = $matches[1] ?? [];
        $text = implode(' ', array_map('stripcslashes', $chunks));

        if (!$this->isLikelyValidPdfText($text)) {
            throw new \RuntimeException('Unable to extract readable text from PDF.');
        }

        return $text;
    }

    private function extractDocText(string $path): string
    {
        $raw = file_get_contents($path);

        if ($raw === false) {
            throw new \RuntimeException('Could not read DOC file.');
        }

        return preg_replace('/[^\PC\s]/u', ' ', $raw) ?? '';
    }

    private function extractReadableTextFromHtml(string $html): string
    {
        libxml_use_internal_errors(true);
        $document = new \DOMDocument();
        $document->loadHTML($html);
        libxml_clear_errors();

        foreach (['script', 'style', 'noscript'] as $tag) {
            while (($nodes = $document->getElementsByTagName($tag))->length > 0) {
                $node = $nodes->item(0);
                if ($node && $node->parentNode) {
                    $node->parentNode->removeChild($node);
                }
            }
        }

        $body = $document->getElementsByTagName('body')->item(0);
        $text = $body ? $body->textContent : $document->textContent;

        return $this->normalizeText((string) $text);
    }

    private function normalizeText(string $text): string
    {
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        $text = preg_replace("/[ \t]+/", ' ', $text) ?? $text;
        $text = preg_replace("/\n{2,}/", "\n\n", $text) ?? $text;

        return trim($text);
    }

    private function wordCount(string $text): int
    {
        $trimmed = trim($text);

        if ($trimmed === '') {
            return 0;
        }

        return count(preg_split('/\s+/', $trimmed) ?: []);
    }

    private function isLikelyValidPdfText(string $text): bool
    {
        $normalized = $this->normalizeText($text);

        if (mb_strlen($normalized) < 20) {
            return false;
        }

        if (!preg_match('/[A-Za-z]{3,}/', $normalized)) {
            return false;
        }

        return true;
    }

    private function commandExists(string $command): bool
    {
        $result = shell_exec('command -v ' . escapeshellarg($command) . ' 2>/dev/null');

        return is_string($result) && trim($result) !== '';
    }

    private function runCommand(string $command): string
    {
        $output = shell_exec($command);

        return is_string($output) ? $output : '';
    }
}
