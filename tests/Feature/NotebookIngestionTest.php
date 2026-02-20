<?php

namespace Tests\Feature;

use App\Notebook;
use App\Source;
use App\SourceIngestion;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use ZipArchive;

class NotebookIngestionTest extends TestCase
{
    use RefreshDatabase;

    public function testUrlSourceGetsExtractedAndMarkedReady()
    {
        Http::fake([
            'https://example.com/*' => Http::response('<html><body><h1>Hello</h1><script>alert("x")</script><p>World content</p></body></html>', 200),
        ]);

        $user = $this->createUser('url-ingestion@example.test');
        $notebook = $this->createNotebook($user->id, 'url-token');

        $this->actingAs($user)->post(route('notebooks.sources.url', ['notebook' => $notebook->id]), [
            'origin_url' => 'https://example.com/page',
            'title' => 'Example Page',
        ])->assertRedirect(route('notebooks.show', ['notebook' => $notebook->id]));

        $source = Source::where('notebook_id', $notebook->id)
            ->where('source_type', 'url')
            ->firstOrFail();

        $this->assertSame('ready', $source->status);
        $this->assertDatabaseHas('source_ingestions', [
            'source_id' => $source->id,
            'status' => 'completed',
            'job_type' => 'extract_url',
        ]);
        $this->assertDatabaseHas('source_contents', [
            'source_id' => $source->id,
        ]);
    }

    public function testDocxFileSourceGetsExtractedAndMarkedReady()
    {
        Storage::fake('local');

        $user = $this->createUser('docx-ingestion@example.test');
        $notebook = $this->createNotebook($user->id, 'docx-token');

        $docxContent = $this->buildDocxWithText('Alpha Bravo Charlie');

        $this->actingAs($user)->post(route('notebooks.sources.file', ['notebook' => $notebook->id]), [
            'file' => UploadedFile::fake()->createWithContent('sample.docx', $docxContent),
            'title' => 'Sample Docx',
        ])->assertRedirect(route('notebooks.show', ['notebook' => $notebook->id]));

        $source = Source::where('notebook_id', $notebook->id)
            ->where('source_type', 'file')
            ->firstOrFail();

        $source->refresh();
        $this->assertSame('ready', $source->status);
        $this->assertDatabaseHas('source_ingestions', [
            'source_id' => $source->id,
            'status' => 'completed',
            'job_type' => 'extract_file',
        ]);
        $this->assertDatabaseHas('source_contents', [
            'source_id' => $source->id,
        ]);
    }

    public function testInvalidPdfSourceIsMarkedFailed()
    {
        Storage::fake('local');

        $user = $this->createUser('pdf-ingestion@example.test');
        $notebook = $this->createNotebook($user->id, 'pdf-token');

        $this->actingAs($user)->post(route('notebooks.sources.file', ['notebook' => $notebook->id]), [
            'file' => UploadedFile::fake()->createWithContent('broken.pdf', 'this is not a valid pdf stream'),
            'title' => 'Broken PDF',
        ])->assertRedirect(route('notebooks.show', ['notebook' => $notebook->id]));

        $source = Source::where('notebook_id', $notebook->id)
            ->where('source_type', 'file')
            ->firstOrFail();

        $source->refresh();
        $this->assertSame('failed', $source->status);
        $this->assertDatabaseHas('source_ingestions', [
            'source_id' => $source->id,
            'status' => 'failed',
            'job_type' => 'extract_file',
        ]);
    }

    public function testRetryQueuesNewIngestionAttemptForFailedSource()
    {
        Queue::fake();
        $user = $this->createUser('retry-source@example.test');
        $notebook = $this->createNotebook($user->id, 'retry-token');

        $source = Source::create([
            'notebook_id' => $notebook->id,
            'created_by' => $user->id,
            'source_type' => 'url',
            'title' => 'Retry URL',
            'origin_url' => 'https://example.com/retry',
            'status' => 'failed',
            'error_message' => 'Previous failure',
        ]);

        SourceIngestion::create([
            'source_id' => $source->id,
            'job_type' => 'extract_url',
            'status' => 'failed',
            'attempt' => 1,
        ]);

        $this->actingAs($user)
            ->post(route('notebooks.sources.retry', ['notebook' => $notebook->id, 'source' => $source->id]))
            ->assertRedirect(route('notebooks.show', ['notebook' => $notebook->id]));

        $source->refresh();
        $this->assertSame('pending', $source->status);
        $this->assertNull($source->error_message);

        $this->assertDatabaseHas('source_ingestions', [
            'source_id' => $source->id,
            'job_type' => 'extract_url',
            'status' => 'pending',
            'attempt' => 2,
        ]);

        Queue::assertPushed(\App\Jobs\ProcessSourceIngestion::class);
    }

    private function buildDocxWithText(string $text): string
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'docx_test_');
        $zip = new ZipArchive();
        $zip->open($tmpFile, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $zip->addFromString('word/document.xml', '<w:document><w:body><w:p><w:r><w:t>' . htmlspecialchars($text, ENT_XML1) . '</w:t></w:r></w:p></w:body></w:document>');
        $zip->close();

        $binary = file_get_contents($tmpFile) ?: '';
        @unlink($tmpFile);

        return $binary;
    }

    private function createUser(string $email): User
    {
        return User::create([
            'name' => 'Ingestion User',
            'email' => $email,
            'password' => Hash::make('password'),
        ]);
    }

    private function createNotebook(int $userId, string $token): Notebook
    {
        return Notebook::create([
            'user_id' => $userId,
            'name' => 'Notebook',
            'visibility' => 'private',
            'share_token' => $token,
        ]);
    }
}
