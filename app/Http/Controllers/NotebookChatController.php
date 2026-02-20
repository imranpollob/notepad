<?php

namespace App\Http\Controllers;

use App\Conversation;
use App\Notebook;
use App\Services\NotebookChatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotebookChatController extends Controller
{
    public function show(int $notebook)
    {
        $notebook = $this->ownedNotebook($notebook);
        $conversationId = request()->query('conversation');

        $conversations = Conversation::where('notebook_id', $notebook->id)
            ->where('user_id', Auth::id())
            ->latest('updated_at')
            ->get();

        $selectedConversation = null;
        if ($conversationId) {
            $selectedConversation = $conversations
                ->where('id', (int) $conversationId)
                ->first();
        } elseif ($conversations->isNotEmpty()) {
            $selectedConversation = $conversations->first();
        }

        if ($selectedConversation) {
            $selectedConversation->load('messages');
        }

        return view('notebooks.chat', [
            'notebook' => $notebook,
            'conversations' => $conversations,
            'selectedConversation' => $selectedConversation,
        ]);
    }

    public function ask(Request $request, int $notebook, NotebookChatService $chatService)
    {
        $notebook = $this->ownedNotebook($notebook);
        $data = $request->validate([
            'message' => 'required|string|max:4000',
            'conversation_id' => 'nullable|integer',
        ]);

        $result = $chatService->ask(
            $notebook,
            (int) Auth::id(),
            (string) $data['message'],
            isset($data['conversation_id']) ? (int) $data['conversation_id'] : null
        );

        return redirect()->route('notebooks.chat', [
            'notebook' => $notebook->id,
            'conversation' => $result['conversation']->id,
        ])->with('success', 'Response generated.');
    }

    private function ownedNotebook(int $id): Notebook
    {
        return Notebook::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();
    }
}
