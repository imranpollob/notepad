<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function testHomePageRenders()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Save to Cloud');
    }

    public function testQuickNewRouteRedirectsToNotePage()
    {
        $response = $this->get('/new');

        $response->assertStatus(302);
        $response->assertRedirectContains('/n/');
    }

    public function testHomeCloudSaveCreatesNoteAndRedirects()
    {
        $response = $this->post('/new', [
            'title' => 'Draft from home',
            'data' => "Line one\nLine two",
        ]);

        $response->assertStatus(302);
        $response->assertRedirectContains('/n/');

        $this->assertDatabaseHas('notes', [
            'title' => 'Draft from home',
        ]);
    }

    public function testHomeCloudSaveRejectsEmptyContent()
    {
        $response = $this->from('/')
            ->post('/new', [
                'title' => 'Empty',
                'data' => '   ',
            ]);

        $response->assertStatus(302);
        $response->assertRedirect('/');
        $response->assertSessionHasErrors('data');
    }
}
