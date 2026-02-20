<?php

namespace Tests\Feature;

use App\Conversation;
use App\Notes;
use App\Notebook;
use App\Source;
use App\SourceContent;
use App\User;
use App\Services\SourceIndexingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class NotebookChatTest extends TestCase
{
    use RefreshDatabase;

    public function testAttachingNoteBuildsChunksForRetrieval()
    {
        $user = $this->createUser('chat-owner@example.test');
        $notebook = $this->createNotebook($user->id, 'chat-token-a');
        $note = Notes::create([
            'url' => 'CHATNOTE1',
            'title' => 'Product notes',
            'data' => '<p>Laravel application using notebook sources for retrieval workflows.</p><p>Second sentence adds more context.</p>',
            'owner_id' => $user->id,
        ]);

        $this->actingAs($user)
            ->post(route('notebooks.sources.note', ['notebook' => $notebook->id]), [
                'note_id' => $note->id,
            ])
            ->assertRedirect(route('notebooks.show', ['notebook' => $notebook->id]));

        $source = Source::where('notebook_id', $notebook->id)
            ->where('source_type', 'note')
            ->firstOrFail();

        $this->assertGreaterThan(0, $source->chunks()->count());
        $this->assertNotNull($source->chunks()->first()->embedding);
    }

    public function testNotebookChatStoresConversationAndAssistantCitations()
    {
        config(['rag.min_retrieval_score' => 0.0]);

        $user = $this->createUser('chat-user@example.test');
        $notebook = $this->createNotebook($user->id, 'chat-token-b');

        $source = Source::create([
            'notebook_id' => $notebook->id,
            'created_by' => $user->id,
            'source_type' => 'url',
            'title' => 'Framework docs',
            'origin_url' => 'https://example.com/framework',
            'status' => 'ready',
        ]);

        SourceContent::create([
            'source_id' => $source->id,
            'content_text' => 'Laravel provides routing, controllers, jobs, queues, and blade views for full stack apps.',
            'word_count' => 13,
            'extracted_at' => now(),
        ]);

        app(SourceIndexingService::class)->indexSource($source->id);

        $this->actingAs($user)
            ->post(route('notebooks.chat.ask', ['notebook' => $notebook->id]), [
                'message' => 'What does this notebook say about Laravel?',
            ])
            ->assertRedirect();

        $conversation = Conversation::where('notebook_id', $notebook->id)->firstOrFail();
        $messages = $conversation->messages()->orderBy('id')->get();

        $this->assertCount(2, $messages);
        $this->assertSame('user', $messages[0]->role);
        $this->assertSame('assistant', $messages[1]->role);
        $this->assertNotNull($messages[0]->token_usage);
        $this->assertNotNull($messages[1]->token_usage);
        $this->assertGreaterThan(0, (int) $messages[0]->token_usage);
        $this->assertGreaterThan(0, (int) $messages[1]->token_usage);
        $this->assertIsArray($messages[1]->metadata['citations'] ?? null);
        $this->assertNotEmpty($messages[1]->metadata['citations'] ?? []);
        $this->assertMatchesRegularExpression('/\[\d+\]/', $messages[1]->message);
        $this->assertArrayHasKey('semantic_score', $messages[1]->metadata['citations'][0]);
        $this->assertArrayHasKey('keyword_score', $messages[1]->metadata['citations'][0]);
        $this->assertArrayHasKey('reference_url', $messages[1]->metadata['citations'][0]);
        $this->assertArrayHasKey('reference_label', $messages[1]->metadata['citations'][0]);
        $this->assertArrayHasKey('rewritten_query', $messages[1]->metadata);
        $this->assertArrayHasKey('used_message_ids', $messages[1]->metadata);
        $this->assertArrayHasKey('retrieved_chunk_ids', $messages[1]->metadata);
        $this->assertArrayHasKey('used_summary', $messages[1]->metadata);

        $this->actingAs($user)
            ->get(route('notebooks.chat', ['notebook' => $notebook->id, 'conversation' => $conversation->id]))
            ->assertOk()
            ->assertSee('Notebook Chat')
            ->assertSee('Citations')
            ->assertSee('href="#cite-' . $messages[1]->id . '-1"', false);
    }

    public function testNotebookChatCanBeScopedToSelectedSources()
    {
        $user = $this->createUser('chat-scope@example.test');
        $notebook = $this->createNotebook($user->id, 'chat-token-d');

        $sourceA = Source::create([
            'notebook_id' => $notebook->id,
            'created_by' => $user->id,
            'source_type' => 'url',
            'title' => 'Laravel source',
            'status' => 'ready',
        ]);
        SourceContent::create([
            'source_id' => $sourceA->id,
            'content_text' => 'Laravel queue worker and artisan commands.',
            'word_count' => 6,
            'extracted_at' => now(),
        ]);

        $sourceB = Source::create([
            'notebook_id' => $notebook->id,
            'created_by' => $user->id,
            'source_type' => 'url',
            'title' => 'Docker source',
            'status' => 'ready',
        ]);
        SourceContent::create([
            'source_id' => $sourceB->id,
            'content_text' => 'Docker containers and compose services.',
            'word_count' => 5,
            'extracted_at' => now(),
        ]);

        app(SourceIndexingService::class)->indexSource($sourceA->id);
        app(SourceIndexingService::class)->indexSource($sourceB->id);

        $this->actingAs($user)
            ->post(route('notebooks.chat.ask', ['notebook' => $notebook->id]), [
                'message' => 'What does this say about containers?',
                'source_ids' => [$sourceA->id],
            ])
            ->assertRedirect();

        $conversation = Conversation::where('notebook_id', $notebook->id)->firstOrFail();
        $assistant = $conversation->messages()->where('role', 'assistant')->firstOrFail();
        $citations = $assistant->metadata['citations'] ?? [];

        $this->assertNotEmpty($citations);
        $this->assertSame($sourceA->id, (int) $citations[0]['source_id']);
    }

    public function testNotebookChatReturnsStrictNoContextWhenBelowThreshold()
    {
        config(['rag.min_retrieval_score' => 0.99]);

        $user = $this->createUser('chat-threshold@example.test');
        $notebook = $this->createNotebook($user->id, 'chat-token-e');

        $source = Source::create([
            'notebook_id' => $notebook->id,
            'created_by' => $user->id,
            'source_type' => 'url',
            'title' => 'Small source',
            'status' => 'ready',
        ]);
        SourceContent::create([
            'source_id' => $source->id,
            'content_text' => 'Alpha beta gamma delta.',
            'word_count' => 4,
            'extracted_at' => now(),
        ]);
        app(SourceIndexingService::class)->indexSource($source->id);

        $this->actingAs($user)
            ->post(route('notebooks.chat.ask', ['notebook' => $notebook->id]), [
                'message' => 'Tell me about unrelated Kubernetes clusters in production.',
            ])
            ->assertRedirect();

        $conversation = Conversation::where('notebook_id', $notebook->id)->firstOrFail();
        $assistant = $conversation->messages()->where('role', 'assistant')->firstOrFail();

        $this->assertStringContainsString('not have enough relevant context', $assistant->message);
    }

    public function testNotebookChatWithExplicitNoSourceSelectionReturnsNoContext()
    {
        $user = $this->createUser('chat-no-source@example.test');
        $notebook = $this->createNotebook($user->id, 'chat-token-f');

        $source = Source::create([
            'notebook_id' => $notebook->id,
            'created_by' => $user->id,
            'source_type' => 'url',
            'title' => 'Available source',
            'status' => 'ready',
        ]);
        SourceContent::create([
            'source_id' => $source->id,
            'content_text' => 'This source has useful application context.',
            'word_count' => 6,
            'extracted_at' => now(),
        ]);
        app(SourceIndexingService::class)->indexSource($source->id);

        $this->actingAs($user)
            ->post(route('notebooks.chat.ask', ['notebook' => $notebook->id]), [
                'message' => 'What context is available?',
                'source_filter_submitted' => '1',
            ])
            ->assertRedirect();

        $conversation = Conversation::where('notebook_id', $notebook->id)->firstOrFail();
        $assistant = $conversation->messages()->where('role', 'assistant')->firstOrFail();
        $this->assertStringContainsString('not have enough relevant context', $assistant->message);
    }

    public function testFollowupMessageStoresRewriteAndConversationSummary()
    {
        config(['rag.min_retrieval_score' => 0.0]);
        config(['rag.recent_messages_window' => 2]);

        $user = $this->createUser('chat-followup@example.test');
        $notebook = $this->createNotebook($user->id, 'chat-token-g');

        $source = Source::create([
            'notebook_id' => $notebook->id,
            'created_by' => $user->id,
            'source_type' => 'url',
            'title' => 'Queue docs',
            'status' => 'ready',
        ]);

        SourceContent::create([
            'source_id' => $source->id,
            'content_text' => 'Queue workers process background jobs and can be scaled for throughput.',
            'word_count' => 11,
            'extracted_at' => now(),
        ]);

        app(SourceIndexingService::class)->indexSource($source->id);

        $this->actingAs($user)->post(route('notebooks.chat.ask', ['notebook' => $notebook->id]), [
            'message' => 'Explain queue workers in this notebook.',
        ])->assertRedirect();

        $conversation = Conversation::where('notebook_id', $notebook->id)->firstOrFail();

        $this->actingAs($user)->post(route('notebooks.chat.ask', ['notebook' => $notebook->id]), [
            'message' => 'what about that?',
            'conversation_id' => $conversation->id,
        ])->assertRedirect();

        $conversation->refresh();
        $this->assertNotNull($conversation->context_summary);
        $this->assertNotNull($conversation->summary_updated_at);

        $assistant = $conversation->messages()
            ->where('role', 'assistant')
            ->latest('id')
            ->firstOrFail();

        $this->assertIsArray($assistant->metadata['used_message_ids'] ?? null);
        $this->assertIsArray($assistant->metadata['retrieved_chunk_ids'] ?? null);
        $this->assertNotEmpty($assistant->metadata['rewritten_query'] ?? '');
    }

    public function testOwnerCanDeleteConversation()
    {
        $user = $this->createUser('chat-delete-owner@example.test');
        $notebook = $this->createNotebook($user->id, 'chat-token-h');

        $conversation = Conversation::create([
            'notebook_id' => $notebook->id,
            'user_id' => $user->id,
            'title' => 'Temporary conversation',
            'last_message_at' => now(),
        ]);

        $conversation->messages()->create([
            'role' => 'user',
            'message' => 'Hello',
            'metadata' => [],
            'token_usage' => 1,
        ]);

        $this->actingAs($user)
            ->delete(route('notebooks.chat.destroy', [
                'notebook' => $notebook->id,
                'conversation' => $conversation->id,
            ]))
            ->assertRedirect(route('notebooks.chat', ['notebook' => $notebook->id]));

        $this->assertDatabaseMissing('conversations', ['id' => $conversation->id]);
        $this->assertDatabaseCount('conversation_messages', 0);
    }

    public function testUserCannotDeleteAnotherUsersConversation()
    {
        $owner = $this->createUser('chat-delete-main-owner@example.test');
        $other = $this->createUser('chat-delete-other@example.test');
        $notebook = $this->createNotebook($owner->id, 'chat-token-i');

        $conversation = Conversation::create([
            'notebook_id' => $notebook->id,
            'user_id' => $owner->id,
            'title' => 'Owner conversation',
            'last_message_at' => now(),
        ]);

        $this->actingAs($other)
            ->delete(route('notebooks.chat.destroy', [
                'notebook' => $notebook->id,
                'conversation' => $conversation->id,
            ]))
            ->assertNotFound();

        $this->assertDatabaseHas('conversations', ['id' => $conversation->id]);
    }

    public function testNonOwnerCannotAccessNotebookChat()
    {
        $owner = $this->createUser('owner-chat@example.test');
        $other = $this->createUser('other-chat@example.test');
        $notebook = $this->createNotebook($owner->id, 'chat-token-c');

        $this->actingAs($other)
            ->get(route('notebooks.chat', ['notebook' => $notebook->id]))
            ->assertNotFound();
    }

    private function createUser(string $email): User
    {
        return User::create([
            'name' => 'Chat User',
            'email' => $email,
            'password' => Hash::make('password'),
        ]);
    }

    private function createNotebook(int $userId, string $token): Notebook
    {
        return Notebook::create([
            'user_id' => $userId,
            'name' => 'Chat Notebook',
            'visibility' => 'private',
            'share_token' => $token,
        ]);
    }
}
