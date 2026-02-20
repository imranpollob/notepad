<?php

namespace App\Http\Controllers;

use App\Notebook;
use App\Notes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class NotebookController extends Controller
{
    public function index()
    {
        $notebooks = Notebook::where('user_id', Auth::id())
            ->withCount('sources')
            ->latest('updated_at')
            ->get();

        return view('notebooks.index', ['notebooks' => $notebooks]);
    }

    public function create()
    {
        return view('notebooks.create');
    }

    public function store(Request $request)
    {
        $data = $this->validateNotebook($request);
        $data['user_id'] = Auth::id();
        $data['share_token'] = Str::random(40);

        $notebook = Notebook::create($data);

        return redirect()->route('notebooks.show', ['notebook' => $notebook->id])
            ->with('success', 'Notebook created successfully.');
    }

    public function show(int $notebook)
    {
        $allowedFilters = ['pending', 'processing', 'ready', 'failed'];
        $statusFilter = request()->query('status');
        if (!in_array($statusFilter, $allowedFilters, true)) {
            $statusFilter = 'all';
        }

        $notebook = $this->ownedNotebook($notebook);
        $sourceQuery = $notebook->sources()->with(['files', 'note']);

        if ($statusFilter !== 'all') {
            $sourceQuery->where('status', $statusFilter);
        }

        $sources = $sourceQuery
            ->latest('updated_at')
            ->get();

        $notes = Notes::where('owner_id', Auth::id())
            ->latest('updated_at')
            ->get(['id', 'url', 'title']);

        return view('notebooks.show', [
            'notebook' => $notebook,
            'notes' => $notes,
            'sources' => $sources,
            'statusFilter' => $statusFilter,
        ]);
    }

    public function edit(int $notebook)
    {
        return view('notebooks.edit', [
            'notebook' => $this->ownedNotebook($notebook),
        ]);
    }

    public function update(Request $request, int $notebook)
    {
        $notebook = $this->ownedNotebook($notebook);
        $notebook->update($this->validateNotebook($request));

        return redirect()->route('notebooks.show', ['notebook' => $notebook->id])
            ->with('success', 'Notebook updated successfully.');
    }

    public function destroy(int $notebook)
    {
        $this->ownedNotebook($notebook)->delete();

        return redirect()->route('notebooks.index')
            ->with('success', 'Notebook deleted successfully.');
    }

    public function regenerateShareToken(int $notebook)
    {
        $notebook = $this->ownedNotebook($notebook);
        $notebook->update(['share_token' => Str::random(40)]);

        return redirect()->route('notebooks.show', ['notebook' => $notebook->id])
            ->with('success', 'Share link regenerated.');
    }

    public function shared(string $token)
    {
        $notebook = Notebook::where('share_token', $token)
            ->whereIn('visibility', ['public', 'unlisted'])
            ->with(['sources.files', 'sources.note'])
            ->firstOrFail();

        return view('notebooks.shared', ['notebook' => $notebook]);
    }

    private function validateNotebook(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'visibility' => 'required|in:private,unlisted,public',
        ]);
    }

    private function ownedNotebook(int $id): Notebook
    {
        return Notebook::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();
    }
}
