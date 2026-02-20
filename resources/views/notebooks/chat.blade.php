@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="m-0">Notebook Chat</h3>
            <p class="text-muted mb-0">{{ $notebook->name }}</p>
        </div>
        <a href="{{ route('notebooks.show', ['notebook' => $notebook->id]) }}" class="btn btn-outline-dark btn-sm">Back to Notebook</a>
    </div>

    <div class="row">
        <div class="col-lg-3 mb-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">Conversations</h6>
                    @if($conversations->isEmpty())
                        <p class="text-muted mb-0">No conversations yet.</p>
                    @else
                        <ul class="list-group list-group-flush">
                            @foreach($conversations as $conversation)
                                <li class="list-group-item px-0 py-2 border-0">
                                    <a href="{{ route('notebooks.chat', ['notebook' => $notebook->id, 'conversation' => $conversation->id]) }}">
                                        {{ $conversation->title ?: ('Conversation #' . $conversation->id) }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-9">
            <div class="card mb-3">
                <div class="card-body">
                    <form action="{{ route('notebooks.chat.ask', ['notebook' => $notebook->id]) }}" method="post">
                        @csrf
                        @if($selectedConversation)
                            <input type="hidden" name="conversation_id" value="{{ $selectedConversation->id }}">
                        @endif
                        <div class="form-group">
                            <label for="message">Ask a question</label>
                            <textarea name="message" id="message" rows="3" class="form-control" required maxlength="4000" placeholder="Ask about your notebook sources"></textarea>
                        </div>
                        <button type="submit" class="btn btn-dark btn-sm">Send</button>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Messages</h5>
                    @if(!$selectedConversation || $selectedConversation->messages->isEmpty())
                        <p class="text-muted mb-0">No messages yet.</p>
                    @else
                        @foreach($selectedConversation->messages as $message)
                            <div class="mb-3 p-2 border rounded">
                                <div class="small text-muted text-uppercase mb-1">{{ $message->role }}</div>
                                <div style="white-space: pre-wrap;">{{ $message->message }}</div>

                                @if($message->role === 'assistant' && is_array($message->metadata['citations'] ?? null))
                                    <div class="mt-2">
                                        <div class="small text-muted mb-1">Citations</div>
                                        <ul class="mb-0">
                                            @foreach($message->metadata['citations'] as $citation)
                                                <li>
                                                    <strong>{{ $citation['source_title'] ?: ('Source #' . $citation['source_id']) }}</strong>:
                                                    {{ $citation['snippet'] ?? '' }}
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
