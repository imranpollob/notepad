<?php

namespace Tests\Feature;

use App\Notes;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class NotesSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function testHomePageLoadsWithoutCreatingCloudNote()
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Save to Cloud');
        $this->assertDatabaseCount('notes', 0);
    }

    public function testLegacyNoteUrlRedirectsToNamespacedPath()
    {
        Notes::create([
            'url' => 'Legacy01',
            'data' => 'legacy note',
        ]);

        $response = $this->get('/Legacy01');

        $response->assertRedirect(route('note.show', ['url' => 'Legacy01']));
    }

    public function testNoteOwnerCanUpdateOwnNote()
    {
        $owner = $this->createUser('owner@example.test');
        $note = Notes::create([
            'url' => 'Owner001',
            'data' => 'before',
            'owner_id' => $owner->id,
        ]);

        $response = $this
            ->actingAs($owner)
            ->post(route('note.store', ['url' => $note->url]), [
                'data' => 'after',
                'title' => 'Updated',
            ]);

        $response->assertNoContent();
        $this->assertDatabaseHas('notes', [
            'id' => $note->id,
            'data' => 'after',
            'title' => 'Updated',
        ]);
    }

    public function testNonOwnerCannotUpdateOwnedNote()
    {
        $owner = $this->createUser('owner2@example.test');
        $otherUser = $this->createUser('other@example.test');
        $note = Notes::create([
            'url' => 'Owner002',
            'data' => 'before',
            'owner_id' => $owner->id,
        ]);

        $response = $this
            ->actingAs($otherUser)
            ->post(route('note.store', ['url' => $note->url]), [
                'data' => 'after',
                'title' => 'Should fail',
            ]);

        $response->assertForbidden();
        $this->assertDatabaseHas('notes', [
            'id' => $note->id,
            'data' => 'before',
        ]);
    }

    public function testPasswordUpdateStoresHashAndUnlockWorks()
    {
        $owner = $this->createUser('owner3@example.test');
        $note = Notes::create([
            'url' => 'Pass001',
            'data' => 'secret note',
            'owner_id' => $owner->id,
        ]);

        $this->actingAs($owner)->put(route('note.update', ['url' => $note->url]), [
            'update-password' => '1',
            'password' => 'secret123',
        ])->assertRedirect(route('note.show', ['url' => $note->url]));

        $note->refresh();
        $this->assertNotSame('secret123', (string) $note->password);
        $this->assertTrue(Hash::check('secret123', (string) $note->password));

        $this->post(route('note.password', ['url' => $note->url]), [
            'password' => 'wrong',
        ])->assertRedirect(route('note.show', ['url' => $note->url]));

        $this->post(route('note.password', ['url' => $note->url]), [
            'password' => 'secret123',
        ])->assertRedirect(route('note.show', ['url' => $note->url]));

        $this->get(route('note.show', ['url' => $note->url]))
            ->assertOk()
            ->assertDontSee('This Note is password protected');
    }

    public function testPasswordProtectedPublicNoteNeedsUnlockBeforeEdit()
    {
        $note = Notes::create([
            'url' => 'Public01',
            'data' => 'before',
            'password' => Hash::make('open123'),
        ]);

        $this->post(route('note.store', ['url' => $note->url]), [
            'data' => 'after',
            'title' => 'Blocked',
        ])->assertForbidden();

        $this->post(route('note.password', ['url' => $note->url]), [
            'password' => 'open123',
        ])->assertRedirect(route('note.show', ['url' => $note->url]));

        $this->post(route('note.store', ['url' => $note->url]), [
            'data' => 'after',
            'title' => 'Allowed',
        ])->assertNoContent();

        $this->assertDatabaseHas('notes', [
            'id' => $note->id,
            'data' => 'after',
            'title' => 'Allowed',
        ]);
    }

    private function createUser(string $email): User
    {
        return User::create([
            'name' => 'Test User',
            'email' => $email,
            'password' => Hash::make('password'),
        ]);
    }
}
