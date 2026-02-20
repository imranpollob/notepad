<?php

namespace App\Services;

use App\Conversation;
use App\ConversationMessage;
use App\Notebook;
use Illuminate\Support\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

class NotebookChatService
{
    private RagRetrievalService $retrievalService;

    public function __construct(RagRetrievalService $retrievalService)
    {
        $this->retrievalService = $retrievalService;
    }

    /**
     * @return array{conversation:\App\Conversation, answer:string, citations:array<int, array<string, mixed>>}
     */
    public function ask(
        Notebook $notebook,
        int $userId,
        string $message,
        ?int $conversationId = null,
        array $sourceIds = [],
        bool $scopeToSelectedSources = false
    ): array
    {
        $conversation = $this->resolveConversation($notebook, $userId, $conversationId);
        $recentMessages = $this->recentMessages($conversation);
        $rewrittenQuery = $this->rewriteQuery($message, $recentMessages, (string) $conversation->context_summary);

        $retrieved = $this->retrievalService->retrieve($notebook, $rewrittenQuery, 5, $sourceIds, $scopeToSelectedSources);
        $minScore = (float) config('rag.min_retrieval_score', 0.08);

        ConversationMessage::create([
            'conversation_id' => $conversation->id,
            'role' => 'user',
            'message' => $message,
            'token_usage' => $this->estimateTokens($message),
            'metadata' => [
                'selected_source_ids' => $sourceIds,
                'scope_to_selected_sources' => $scopeToSelectedSources,
                'rewritten_query' => $rewrittenQuery,
            ],
        ]);

        $citations = $this->buildCitations($retrieved, $notebook->id);
        $topScore = (float) ($citations[0]['score'] ?? 0.0);
        $hasEnoughContext = $citations !== [] && $topScore >= $minScore;

        $generated = $this->generateAnswer(
            $message,
            $rewrittenQuery,
            $citations,
            $hasEnoughContext,
            $recentMessages,
            (string) $conversation->context_summary
        );
        $answer = $generated['answer'];
        $assistantTokenUsage = (int) ($generated['token_usage'] ?? 0);
        if ($assistantTokenUsage <= 0) {
            $assistantTokenUsage = $this->estimateTokens($answer);
        }

        $usedMessageIds = $recentMessages->pluck('id')->map(fn ($id) => (int) $id)->values()->all();
        $retrievedChunkIds = collect($citations)->pluck('chunk_id')->map(fn ($id) => (int) $id)->values()->all();

        ConversationMessage::create([
            'conversation_id' => $conversation->id,
            'role' => 'assistant',
            'message' => $answer,
            'token_usage' => $assistantTokenUsage,
            'metadata' => [
                'citations' => $citations,
                'top_score' => $topScore,
                'min_required_score' => $minScore,
                'selected_source_ids' => $sourceIds,
                'scope_to_selected_sources' => $scopeToSelectedSources,
                'rewritten_query' => $rewrittenQuery,
                'used_message_ids' => $usedMessageIds,
                'retrieved_chunk_ids' => $retrievedChunkIds,
                'used_summary' => (string) $conversation->context_summary,
            ],
        ]);

        $this->refreshConversationSummary($conversation);

        $conversation->update([
            'title' => $conversation->title ?: mb_substr($message, 0, 80),
            'last_message_at' => Carbon::now(),
        ]);

        return [
            'conversation' => $conversation->fresh('messages'),
            'answer' => $answer,
            'citations' => $citations,
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $retrieved
     * @return array<int, array<string, mixed>>
     */
    private function buildCitations(array $retrieved, int $notebookId): array
    {
        $seenSources = [];
        $citations = [];

        foreach ($retrieved as $item) {
            /** @var \App\SourceChunk $chunk */
            $chunk = $item['chunk'];
            /** @var \App\Source $source */
            $source = $item['source'];

            if (isset($seenSources[$source->id])) {
                continue;
            }
            $seenSources[$source->id] = true;

            $reference = $this->sourceReference($source, $notebookId);

            $citations[] = [
                'source_id' => $source->id,
                'source_type' => $source->source_type,
                'source_title' => $source->title,
                'note_id' => $source->note_id,
                'origin_url' => $source->origin_url,
                'reference_url' => $reference['url'],
                'reference_label' => $reference['label'],
                'chunk_id' => $chunk->id,
                'chunk_index' => $chunk->chunk_index,
                'score' => round((float) $item['score'], 5),
                'semantic_score' => round((float) ($item['semantic_score'] ?? 0), 5),
                'keyword_score' => round((float) ($item['keyword_score'] ?? 0), 5),
                'snippet' => mb_substr($chunk->content, 0, 220),
            ];
        }

        return array_slice($citations, 0, 5);
    }

    /**
     * @param array<int, array<string, mixed>> $citations
     */
    private function generateAnswer(
        string $question,
        string $rewrittenQuestion,
        array $citations,
        bool $hasEnoughContext,
        Collection $recentMessages,
        string $summary
    ): array
    {
        if (!$hasEnoughContext) {
            $text = 'I do not have enough relevant context to answer this confidently. Try selecting more sources or rephrasing the question.';
            return [
                'answer' => $text,
                'token_usage' => $this->estimateTokens($text),
            ];
        }

        $apiKey = (string) config('services.openai.api_key');
        $chatModel = (string) config('services.openai.chat_model', 'gpt-4o-mini');

        if ($apiKey !== '') {
            $context = '';
            foreach ($citations as $index => $citation) {
                $context .= '[' . ($index + 1) . '] ' . ($citation['snippet'] ?? '') . "\n";
            }

            $chatHistory = $this->formatMessagesForPrompt($recentMessages);
            $summaryBlock = $summary !== '' ? "Conversation summary:\n{$summary}\n\n" : '';

            try {
                $response = Http::timeout(45)
                    ->withToken($apiKey)
                    ->post('https://api.openai.com/v1/chat/completions', [
                        'model' => $chatModel,
                        'messages' => [
                            [
                                'role' => 'system',
                                'content' => 'Answer using only the provided context and include inline citation markers like [1], [2]. If context is insufficient, say so briefly.',
                            ],
                            [
                                'role' => 'user',
                                'content' => "{$summaryBlock}Recent conversation:\n{$chatHistory}\n\nOriginal question: {$question}\nStandalone query: {$rewrittenQuestion}\n\nContext:\n{$context}",
                            ],
                        ],
                        'temperature' => 0.2,
                    ]);

                if ($response->ok()) {
                    $content = $response->json('choices.0.message.content');

                    if (is_string($content) && trim($content) !== '') {
                        $answer = $this->ensureInlineCitationMarkers(trim($content), $citations);
                        $usageTokens = (int) ($response->json('usage.total_tokens') ?? 0);

                        return [
                            'answer' => $answer,
                            'token_usage' => $usageTokens > 0 ? $usageTokens : $this->estimateTokens($answer),
                        ];
                    }
                }
            } catch (Throwable $exception) {
                // Local fallback below handles connectivity and API failures.
            }
        }

        $lines = ["Based on notebook sources, here is what I found:"];
        foreach (array_slice($citations, 0, 3) as $index => $citation) {
            $snippet = trim((string) ($citation['snippet'] ?? ''));
            if ($snippet !== '') {
                $lines[] = ($index + 1) . '. ' . $snippet . ' [' . ($index + 1) . ']';
            }
        }

        $answer = implode("\n", $lines);

        return [
            'answer' => $answer,
            'token_usage' => $this->estimateTokens($answer),
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $citations
     */
    private function ensureInlineCitationMarkers(string $answer, array $citations): string
    {
        if ($citations === []) {
            return $answer;
        }

        if (preg_match('/\[\d+\]/', $answer)) {
            return $answer;
        }

        return rtrim($answer) . ' [1]';
    }

    private function recentMessages(Conversation $conversation): Collection
    {
        $window = max(1, (int) config('rag.recent_messages_window', 6));

        return $conversation->messages()
            ->orderByDesc('id')
            ->take($window)
            ->get()
            ->sortBy('id')
            ->values();
    }

    private function rewriteQuery(string $message, Collection $recentMessages, string $summary): string
    {
        $recentUser = $recentMessages->where('role', 'user')->last();
        $recentContext = $recentUser ? (string) $recentUser->message : '';

        $shouldRewrite = $this->looksAmbiguous($message);
        if (!$shouldRewrite) {
            return $message;
        }

        $apiKey = (string) config('services.openai.api_key');
        $chatModel = (string) config('services.openai.chat_model', 'gpt-4o-mini');

        if ($apiKey !== '') {
            try {
                $response = Http::timeout(20)
                    ->withToken($apiKey)
                    ->post('https://api.openai.com/v1/chat/completions', [
                        'model' => $chatModel,
                        'messages' => [
                            ['role' => 'system', 'content' => 'Rewrite follow-up questions as standalone search queries. Return only the rewritten query.'],
                            ['role' => 'user', 'content' => "Summary: {$summary}\nRecent user context: {$recentContext}\nFollow-up question: {$message}"],
                        ],
                        'temperature' => 0.1,
                    ]);

                if ($response->ok()) {
                    $content = trim((string) $response->json('choices.0.message.content'));
                    if ($content !== '') {
                        return $content;
                    }
                }
            } catch (Throwable $exception) {
                // Local rewrite fallback below.
            }
        }

        if ($recentContext !== '') {
            return $message . ' (Context from previous question: ' . Str::limit($recentContext, 180, '') . ')';
        }

        if ($summary !== '') {
            return $message . ' (Conversation summary: ' . Str::limit($summary, 180, '') . ')';
        }

        return $message;
    }

    private function looksAmbiguous(string $message): bool
    {
        $normalized = mb_strtolower(trim($message));
        if (mb_strlen($normalized) <= 25) {
            return true;
        }

        return (bool) preg_match('/\b(it|that|this|they|them|those|these|same|previous|above)\b/', $normalized);
    }

    private function formatMessagesForPrompt(Collection $messages): string
    {
        if ($messages->isEmpty()) {
            return 'No recent messages.';
        }

        return $messages->map(function ($message) {
            return '[' . strtoupper((string) $message->role) . '] ' . Str::limit((string) $message->message, 300, '');
        })->implode("\n");
    }

    private function refreshConversationSummary(Conversation $conversation): void
    {
        $window = max(1, (int) config('rag.recent_messages_window', 6));
        $allMessages = $conversation->messages()->orderBy('id')->get();

        if ($allMessages->count() < 2) {
            return;
        }

        if ($allMessages->count() > $window) {
            $summaryMessages = $allMessages->slice(0, $allMessages->count() - $window);
        } else {
            $summaryMessages = $allMessages;
        }

        $limit = max(300, (int) config('rag.summary_char_limit', 1200));
        $summary = $summaryMessages->map(function ($message) {
            return strtoupper((string) $message->role) . ': ' . trim((string) $message->message);
        })->implode("\n");
        $summary = trim(Str::limit($summary, $limit, ''));

        $conversation->update([
            'context_summary' => $summary,
            'summary_updated_at' => Carbon::now(),
        ]);
    }

    private function estimateTokens(string $text): int
    {
        $trimmed = trim($text);
        if ($trimmed === '') {
            return 0;
        }

        $words = preg_split('/\s+/', $trimmed) ?: [];
        return max(1, (int) ceil(count($words) * 1.3));
    }

    /**
     * @return array{url:?string,label:string}
     */
    private function sourceReference($source, int $notebookId): array
    {
        if ($source->source_type === 'note' && $source->note) {
            return [
                'url' => route('note.show', ['url' => $source->note->url]),
                'label' => 'Open note',
            ];
        }

        if ($source->source_type === 'url' && $source->origin_url) {
            return [
                'url' => $source->origin_url,
                'label' => 'Open URL',
            ];
        }

        if ($source->source_type === 'file') {
            return [
                'url' => route('notebooks.sources.download', ['notebook' => $notebookId, 'source' => $source->id]),
                'label' => 'Download file',
            ];
        }

        return [
            'url' => null,
            'label' => 'No link',
        ];
    }

    private function resolveConversation(Notebook $notebook, int $userId, ?int $conversationId): Conversation
    {
        if ($conversationId) {
            return Conversation::where('id', $conversationId)
                ->where('notebook_id', $notebook->id)
                ->where('user_id', $userId)
                ->firstOrFail();
        }

        return Conversation::create([
            'notebook_id' => $notebook->id,
            'user_id' => $userId,
            'last_message_at' => Carbon::now(),
        ]);
    }
}
