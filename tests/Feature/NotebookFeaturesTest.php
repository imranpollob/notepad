<?php

namespace Tests\Feature;

use App\Notes;
use App\Notebook;
use App\Source;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class NotebookFeaturesTest extends TestCase
{
    use RefreshDatabase;

    public function testNotebookRoutesRequireAuthentication()
    {
        $this->get(route('notebooks.index'))->assertRedirect(route('login'));
    }

    public function testUserCanCreateUpdateAndDeleteNotebook()
    {
        $user = $this->createUser('notebook-owner@example.test');

        $createResponse = $this->actingAs($user)->post(route('notebooks.store'), [
            'name' => 'Project Docs',
            'description' => 'Team notes and references',
            'visibility' => 'private',
        ]);

        $notebook = Notebook::first();
        $createResponse->assertRedirect(route('notebooks.show', ['notebook' => $notebook->id]));

        $this->actingAs($user)->put(route('notebooks.update', ['notebook' => $notebook->id]), [
            'name' => 'Project Docs v2',
            'description' => 'Updated',
            'visibility' => 'unlisted',
        ])->assertRedirect(route('notebooks.show', ['notebook' => $notebook->id]));

        $this->assertDatabaseHas('notebooks', [
            'id' => $notebook->id,
            'name' => 'Project Docs v2',
            'visibility' => 'unlisted',
        ]);

        $this->actingAs($user)
            ->delete(route('notebooks.destroy', ['notebook' => $notebook->id]))
            ->assertRedirect(route('notebooks.index'));

        $this->assertDatabaseMissing('notebooks', ['id' => $notebook->id]);
    }

    public function testUserCannotAccessAnotherUsersNotebook()
    {
        $owner = $this->createUser('ownerx@example.test');
        $other = $this->createUser('otherx@example.test');

        $notebook = Notebook::create([
            'user_id' => $owner->id,
            'name' => 'Owner notebook',
            'visibility' => 'private',
            'share_token' => 'token-owner-only',
        ]);

        $this->actingAs($other)
            ->get(route('notebooks.show', ['notebook' => $notebook->id]))
            ->assertNotFound();
    }

    public function testUserCanAttachNoteFileAndUrlToNotebook()
    {
        Storage::fake('local');
        Http::fake([
            'https://example.com/*' => Http::response('<html><body><h1>Docs</h1><p>Body content</p></body></html>', 200),
        ]);

        $user = $this->createUser('attach-user@example.test');

        $notebook = Notebook::create([
            'user_id' => $user->id,
            'name' => 'Knowledge Base',
            'visibility' => 'private',
            'share_token' => 'share-12345',
        ]);

        $note = Notes::create([
            'url' => 'ATTACH01',
            'title' => 'Attached note',
            'data' => 'text',
            'owner_id' => $user->id,
        ]);

        $this->actingAs($user)->post(route('notebooks.sources.note', ['notebook' => $notebook->id]), [
            'note_id' => $note->id,
        ])->assertRedirect(route('notebooks.show', ['notebook' => $notebook->id]));

        $this->assertDatabaseHas('sources', [
            'notebook_id' => $notebook->id,
            'source_type' => 'note',
            'note_id' => $note->id,
        ]);

        $this->actingAs($user)->post(route('notebooks.sources.url', ['notebook' => $notebook->id]), [
            'origin_url' => 'https://example.com/docs/page',
            'title' => 'Example Docs',
        ])->assertRedirect(route('notebooks.show', ['notebook' => $notebook->id]));

        $this->assertDatabaseHas('sources', [
            'notebook_id' => $notebook->id,
            'source_type' => 'url',
            'origin_url' => 'https://example.com/docs/page',
        ]);

        $this->actingAs($user)->post(route('notebooks.sources.file', ['notebook' => $notebook->id]), [
            'file' => UploadedFile::fake()->create('whitepaper.pdf', 120, 'application/pdf'),
            'title' => 'Whitepaper',
        ])->assertRedirect(route('notebooks.show', ['notebook' => $notebook->id]));

        $fileSource = Source::where('notebook_id', $notebook->id)
            ->where('source_type', 'file')
            ->first();

        $this->assertNotNull($fileSource);
        $this->assertDatabaseHas('source_files', [
            'source_id' => $fileSource->id,
            'original_name' => 'whitepaper.pdf',
        ]);
    }

    public function testSharedNotebookVisibilityRules()
    {
        $owner = $this->createUser('owner-share@example.test');

        $publicNotebook = Notebook::create([
            'user_id' => $owner->id,
            'name' => 'Public KB',
            'visibility' => 'public',
            'share_token' => 'public-token-123',
        ]);

        $privateNotebook = Notebook::create([
            'user_id' => $owner->id,
            'name' => 'Private KB',
            'visibility' => 'private',
            'share_token' => 'private-token-123',
        ]);

        $this->get(route('notebooks.shared', ['token' => $publicNotebook->share_token]))
            ->assertOk()
            ->assertSee('Public KB');

        $this->get(route('notebooks.shared', ['token' => $privateNotebook->share_token]))
            ->assertNotFound();
    }

    public function testNotebookSourceStatusFilterShowsMatchingRows()
    {
        $user = $this->createUser('filter-user@example.test');
        $notebook = Notebook::create([
            'user_id' => $user->id,
            'name' => 'Filter Notebook',
            'visibility' => 'private',
            'share_token' => 'filter-token',
        ]);

        Source::create([
            'notebook_id' => $notebook->id,
            'created_by' => $user->id,
            'source_type' => 'url',
            'title' => 'Ready Source',
            'status' => 'ready',
        ]);

        Source::create([
            'notebook_id' => $notebook->id,
            'created_by' => $user->id,
            'source_type' => 'file',
            'title' => 'Failed Source',
            'status' => 'failed',
        ]);

        $this->actingAs($user)
            ->get(route('notebooks.show', ['notebook' => $notebook->id, 'status' => 'failed']))
            ->assertOk()
            ->assertSee('Failed Source')
            ->assertDontSee('Ready Source');
    }

    private function createUser(string $email): User
    {
        return User::create([
            'name' => 'Notebook User',
            'email' => $email,
            'password' => Hash::make('password'),
        ]);
    }
}
