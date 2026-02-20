<?php

namespace App\Http\Controllers;

use App\Notebook;
use App\Notes;
use App\Source;
use App\SourceFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotebookSourceController extends Controller
{
    public function attachNote(Request $request, int $notebook)
    {
        $notebook = $this->ownedNotebook($notebook);
        $data = $request->validate([
            'note_id' => 'required|integer',
        ]);

        $note = Notes::where('id', $data['note_id'])
            ->where('owner_id', Auth::id())
            ->firstOrFail();

        Source::firstOrCreate(
            [
                'notebook_id' => $notebook->id,
                'source_type' => 'note',
                'note_id' => $note->id,
            ],
            [
                'created_by' => Auth::id(),
                'title' => $note->title ?: $note->url,
                'status' => 'ready',
                'metadata' => ['note_url' => $note->url],
            ]
        );

        return redirect()->route('notebooks.show', ['notebook' => $notebook->id])
            ->with('success', 'Note attached to notebook.');
    }

    public function attachFile(Request $request, int $notebook)
    {
        $notebook = $this->ownedNotebook($notebook);
        $data = $request->validate([
            'file' => 'required|file|mimes:pdf,doc,docx|max:10240',
            'title' => 'nullable|string|max:255',
        ]);

        $uploaded = $data['file'];
        $path = $uploaded->store("notebooks/{$notebook->id}", 'local');

        $source = Source::create([
            'notebook_id' => $notebook->id,
            'created_by' => Auth::id(),
            'source_type' => 'file',
            'title' => $data['title'] ?: $uploaded->getClientOriginalName(),
            'status' => 'pending',
            'metadata' => [
                'original_name' => $uploaded->getClientOriginalName(),
            ],
        ]);

        SourceFile::create([
            'source_id' => $source->id,
            'disk' => 'local',
            'path' => $path,
            'original_name' => $uploaded->getClientOriginalName(),
            'mime_type' => $uploaded->getClientMimeType(),
            'size_bytes' => $uploaded->getSize(),
        ]);

        return redirect()->route('notebooks.show', ['notebook' => $notebook->id])
            ->with('success', 'File attached to notebook.');
    }

    public function attachUrl(Request $request, int $notebook)
    {
        $notebook = $this->ownedNotebook($notebook);
        $data = $request->validate([
            'origin_url' => 'required|url|max:2000',
            'title' => 'nullable|string|max:255',
        ]);

        Source::create([
            'notebook_id' => $notebook->id,
            'created_by' => Auth::id(),
            'source_type' => 'url',
            'title' => $data['title'] ?: parse_url($data['origin_url'], PHP_URL_HOST),
            'origin_url' => $data['origin_url'],
            'status' => 'pending',
            'checksum' => sha1(strtolower(trim($data['origin_url']))),
            'metadata' => [
                'host' => parse_url($data['origin_url'], PHP_URL_HOST),
            ],
        ]);

        return redirect()->route('notebooks.show', ['notebook' => $notebook->id])
            ->with('success', 'URL attached to notebook.');
    }

    public function destroy(int $notebook, int $source)
    {
        $notebook = $this->ownedNotebook($notebook);

        Source::where('id', $source)
            ->where('notebook_id', $notebook->id)
            ->firstOrFail()
            ->delete();

        return redirect()->route('notebooks.show', ['notebook' => $notebook->id])
            ->with('success', 'Source removed from notebook.');
    }

    private function ownedNotebook(int $id): Notebook
    {
        return Notebook::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();
    }
}
