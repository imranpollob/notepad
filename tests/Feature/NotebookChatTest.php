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
        $this->assertIsArray($messages[1]->metadata['citations'] ?? null);
        $this->assertNotEmpty($messages[1]->metadata['citations'] ?? []);

        $this->actingAs($user)
            ->get(route('notebooks.chat', ['notebook' => $notebook->id, 'conversation' => $conversation->id]))
            ->assertOk()
            ->assertSee('Notebook Chat')
            ->assertSee('Citations');
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
