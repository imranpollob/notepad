<?php

namespace App\Services;

use App\Conversation;
use App\ConversationMessage;
use App\Notebook;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
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
    public function ask(Notebook $notebook, int $userId, string $message, ?int $conversationId = null): array
    {
        $conversation = $this->resolveConversation($notebook, $userId, $conversationId);
        $retrieved = $this->retrievalService->retrieve($notebook, $message, 5);

        ConversationMessage::create([
            'conversation_id' => $conversation->id,
            'role' => 'user',
            'message' => $message,
        ]);

        $citations = array_map(function ($item) {
            /** @var \App\SourceChunk $chunk */
            $chunk = $item['chunk'];
            /** @var \App\Source $source */
            $source = $item['source'];

            return [
                'source_id' => $source->id,
                'source_type' => $source->source_type,
                'source_title' => $source->title,
                'note_id' => $source->note_id,
                'origin_url' => $source->origin_url,
                'chunk_id' => $chunk->id,
                'score' => round((float) $item['score'], 5),
                'snippet' => mb_substr($chunk->content, 0, 220),
            ];
        }, $retrieved);

        $answer = $this->generateAnswer($message, $citations);

        ConversationMessage::create([
            'conversation_id' => $conversation->id,
            'role' => 'assistant',
            'message' => $answer,
            'metadata' => [
                'citations' => $citations,
            ],
        ]);

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
     * @param array<int, array<string, mixed>> $citations
     */
    private function generateAnswer(string $question, array $citations): string
    {
        if ($citations === []) {
            return 'I could not find enough relevant context in this notebook to answer confidently.';
        }

        $apiKey = (string) config('services.openai.api_key');
        $chatModel = (string) config('services.openai.chat_model', 'gpt-4o-mini');

        if ($apiKey !== '') {
            $context = '';
            foreach ($citations as $index => $citation) {
                $context .= '[' . ($index + 1) . '] ' . ($citation['snippet'] ?? '') . "\n";
            }

            try {
                $response = Http::timeout(45)
                    ->withToken($apiKey)
                    ->post('https://api.openai.com/v1/chat/completions', [
                        'model' => $chatModel,
                        'messages' => [
                            [
                                'role' => 'system',
                                'content' => 'Answer using only the provided context. If context is insufficient, say so briefly.',
                            ],
                            [
                                'role' => 'user',
                                'content' => "Question: {$question}\n\nContext:\n{$context}",
                            ],
                        ],
                        'temperature' => 0.2,
                    ]);

                if ($response->ok()) {
                    $content = $response->json('choices.0.message.content');

                    if (is_string($content) && trim($content) !== '') {
                        return trim($content);
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
                $lines[] = ($index + 1) . '. ' . $snippet;
            }
        }

        return implode("\n", $lines);
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
