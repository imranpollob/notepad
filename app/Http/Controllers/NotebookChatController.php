<?php

namespace App\Http\Controllers;

use App\Conversation;
use App\Notebook;
use App\Source;
use App\Services\NotebookChatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotebookChatController extends Controller
{
    public function show(int $notebook)
    {
        $notebook = $this->ownedNotebook($notebook);
        $conversationId = request()->query('conversation');
        $forceNew = request()->boolean('new');

        $conversations = Conversation::where('notebook_id', $notebook->id)
            ->where('user_id', Auth::id())
            ->latest('updated_at')
            ->get();

        $selectedConversation = null;
        if ($forceNew) {
            $selectedConversation = null;
        } elseif ($conversationId) {
            $selectedConversation = $conversations
                ->where('id', (int) $conversationId)
                ->first();
        } elseif ($conversations->isNotEmpty()) {
            $selectedConversation = $conversations->first();
        }

        if ($selectedConversation) {
            $selectedConversation->load('messages');
        }

        $availableSources = Source::where('notebook_id', $notebook->id)
            ->where('status', 'ready')
            ->orderBy('source_type')
            ->orderBy('title')
            ->get(['id', 'source_type', 'title']);

        return view('notebooks.chat', [
            'notebook' => $notebook,
            'conversations' => $conversations,
            'selectedConversation' => $selectedConversation,
            'availableSources' => $availableSources,
        ]);
    }

    public function ask(Request $request, int $notebook, NotebookChatService $chatService)
    {
        $notebook = $this->ownedNotebook($notebook);
        $data = $request->validate([
            'message' => 'required|string|max:4000',
            'conversation_id' => 'nullable|integer',
            'source_ids' => 'nullable|array',
            'source_ids.*' => 'integer',
            'source_filter_submitted' => 'nullable|in:1',
        ]);

        $selectedSourceIds = collect($data['source_ids'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        $scopeToSelectedSources = $request->has('source_ids')
            || ((string) ($data['source_filter_submitted'] ?? '') === '1');

        if ($selectedSourceIds->isNotEmpty() || $scopeToSelectedSources) {
            $validCount = Source::where('notebook_id', $notebook->id)
                ->whereIn('id', $selectedSourceIds)
                ->count();

            if ($validCount !== $selectedSourceIds->count()) {
                abort(403);
            }
        }

        $result = $chatService->ask(
            $notebook,
            (int) Auth::id(),
            (string) $data['message'],
            isset($data['conversation_id']) ? (int) $data['conversation_id'] : null,
            $selectedSourceIds->all(),
            $scopeToSelectedSources
        );

        return redirect()->route('notebooks.chat', [
            'notebook' => $notebook->id,
            'conversation' => $result['conversation']->id,
        ])->with('success', 'Response generated.');
    }

    public function destroyConversation(int $notebook, int $conversation)
    {
        $notebook = $this->ownedNotebook($notebook);

        $conversation = Conversation::where('id', $conversation)
            ->where('notebook_id', $notebook->id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $conversation->delete();

        return redirect()->route('notebooks.chat', [
            'notebook' => $notebook->id,
        ])->with('success', 'Conversation deleted.');
    }

    private function ownedNotebook(int $id): Notebook
    {
        return Notebook::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();
    }
}
