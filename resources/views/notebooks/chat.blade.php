@extends('layouts.app')

@section('stylesheet')
<style>
    .chat-sidebar .card {
        border: 1px solid var(--color-border);
        box-shadow: var(--shadow-card);
        border-radius: var(--radius-md);
    }

    .chat-main .card {
        border: 1px solid var(--color-border);
        box-shadow: var(--shadow-card);
        border-radius: var(--radius-md);
    }

    .chat-source-panel {
        max-height: 180px;
        overflow-y: auto;
        border-radius: var(--radius-sm);
    }
</style>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h3 class="page-heading m-0" style="font-size:22px; margin-bottom:4px !important;">Notebook Chat</h3>
        <p class="text-muted mb-0">{{ $notebook->name }}</p>
    </div>
    <div>
        <a href="{{ route('notebooks.chat', ['notebook' => $notebook->id, 'new' => 1]) }}" class="btn btn-dark btn-sm mr-2">New Conversation</a>
        <a href="{{ route('notebooks.show', ['notebook' => $notebook->id]) }}" class="btn btn-outline-dark btn-sm">Back to Notebook</a>
    </div>
</div>

<div class="row">
    <div class="col-lg-3 mb-3 chat-sidebar">
        <div class="card">
            <div class="card-body">
                <h6 class="card-title">Conversations</h6>
                @if($conversations->isEmpty())
                <p class="text-muted mb-0">No conversations yet.</p>
                @else
                <ul class="list-group list-group-flush conversation-list">
                    @foreach($conversations as $conversation)
                    <li class="list-group-item px-0 py-2 border-0">
                        <div class="d-flex justify-content-between align-items-start">
                            <a href="{{ route('notebooks.chat', ['notebook' => $notebook->id, 'conversation' => $conversation->id]) }}"
                                class="{{ $selectedConversation && $selectedConversation->id === $conversation->id ? 'font-weight-bold' : '' }}">
                                {{ $conversation->title ?: ('Conversation #' . $conversation->id) }}
                            </a>
                            <form action="{{ route('notebooks.chat.destroy', ['notebook' => $notebook->id, 'conversation' => $conversation->id]) }}"
                                method="post"
                                onsubmit="return confirm('Delete this conversation permanently?');">
                                @csrf
                                @method('delete')
                                <button type="submit" class="btn btn-link btn-sm text-danger p-0 ml-2">Delete</button>
                            </form>
                        </div>
                    </li>
                    @endforeach
                </ul>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-9 chat-main">
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="card-title">Messages</h5>
                <div id="chat-messages" class="chat-messages-panel">
                    @if(!$selectedConversation || $selectedConversation->messages->isEmpty())
                    <p class="text-muted mb-0">No messages yet.</p>
                    @else
                    @foreach($selectedConversation->messages as $message)
                    <div class="chat-bubble {{ $message->role === 'user' ? 'chat-bubble--user' : 'chat-bubble--assistant' }}">
                        <div class="chat-role-label {{ $message->role === 'user' ? 'chat-role-label--user' : 'chat-role-label--assistant' }}">{{ $message->role }}</div>
                        @php
                        $citations = is_array($message->metadata['citations'] ?? null) ? $message->metadata['citations'] : [];
                        $messageHtml = e($message->message);
                        if ($message->role === 'assistant' && !empty($citations)) {
                        $messageHtml = preg_replace_callback('/\[(\d+)\]/', function ($matches) use ($message, $citations) {
                        $citationIndex = (int) $matches[1];
                        if (!isset($citations[$citationIndex - 1])) {
                        return $matches[0];
                        }

                        $targetId = 'cite-' . $message->id . '-' . $citationIndex;
                        return '<a href="#' . $targetId . '" class="citation-ref">' . $matches[0] . '</a>';
                        }, $messageHtml) ?? $messageHtml;
                        }
                        @endphp
                        <div style="white-space: pre-wrap;">{!! nl2br($messageHtml) !!}</div>

                        @if($message->role === 'assistant' && is_array($message->metadata['citations'] ?? null))
                        <div class="mt-2">
                            <div class="small text-muted mb-1">Citations</div>
                            <ul class="mb-0">
                                @foreach($message->metadata['citations'] as $citationIndex => $citation)
                                <li id="cite-{{ $message->id }}-{{ $citationIndex + 1 }}">
                                    <span class="badge badge-light mr-1">[{{ $citationIndex + 1 }}]</span>
                                    <strong>{{ $citation['source_title'] ?: ('Source #' . $citation['source_id']) }}</strong>
                                    <span class="text-muted small">({{ strtoupper($citation['source_type'] ?? 'source') }})</span>
                                    @if(!empty($citation['reference_url']))
                                    <a href="{{ $citation['reference_url'] }}" target="_blank" class="ml-2 small">{{ $citation['reference_label'] ?? 'Open source' }}</a>
                                    @endif
                                    :
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

        <div class="card mt-3">
            <div class="card-body">
                <form action="{{ route('notebooks.chat.ask', ['notebook' => $notebook->id]) }}" method="post">
                    @csrf
                    @if($selectedConversation)
                    <input type="hidden" name="conversation_id" value="{{ $selectedConversation->id }}">
                    @endif
                    <input type="hidden" name="source_filter_submitted" value="1">
                    @php
                    $availableSourceIds = $availableSources->pluck('id')->map(fn ($id) => (int) $id)->all();
                    if (old('source_filter_submitted') === '1') {
                    $selectedSourceIds = collect(old('source_ids', []))->map(fn ($id) => (int) $id)->all();
                    } elseif ($selectedConversation && $selectedConversation->messages->isNotEmpty()) {
                    $latestUserMessage = $selectedConversation->messages->where('role', 'user')->last();
                    $selectedSourceIds = collect($latestUserMessage->metadata['selected_source_ids'] ?? [])->map(fn ($id) => (int) $id)->all();
                    } else {
                    $selectedSourceIds = $availableSourceIds;
                    }
                    if ($selectedConversation && $selectedConversation->messages->isNotEmpty()) {
                    $latestUserMessage = $selectedConversation->messages->where('role', 'user')->last();
                    $scopeToSelectedSources = (bool) ($latestUserMessage->metadata['scope_to_selected_sources'] ?? true);
                    if (!$scopeToSelectedSources && old('source_filter_submitted') !== '1') {
                    $selectedSourceIds = $availableSourceIds;
                    }
                    }
                    @endphp
                    <div class="form-group">
                        <label for="message">Ask a question</label>
                        <textarea name="message" id="message" rows="3" class="form-control" required maxlength="4000" placeholder="Ask about your notebook sources"></textarea>
                    </div>
                    <div class="form-group">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="mb-0">Sources</label>
                            <div>
                                <button type="button" class="btn btn-outline-dark btn-sm py-0 px-2" id="check-all-sources">Check all</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-2" id="uncheck-all-sources">Uncheck all</button>
                            </div>
                        </div>
                        <div class="border p-2 chat-source-panel">
                            @forelse($availableSources as $sourceIndex => $source)
                            <div class="form-check mb-1">
                                <input class="form-check-input source-checkbox" type="checkbox" name="source_ids[]" value="{{ $source->id }}" id="source_{{ $source->id }}"
                                    {{ in_array($source->id, $selectedSourceIds, true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="source_{{ $source->id }}">
                                    [{{ $sourceIndex + 1 }}] [{{ strtoupper($source->source_type) }}] {{ $source->title ?: ('Source #' . $source->id) }}
                                </label>
                            </div>
                            @empty
                            <p class="text-muted mb-0">No ready sources available for retrieval yet.</p>
                            @endforelse
                        </div>
                        <small class="form-text text-muted">By default all sources are selected. Uncheck to narrow context.</small>
                    </div>
                    <button type="submit" class="btn btn-dark btn-sm">Send</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('javascript')
<script>
    $(document).ready(function() {
        $('#check-all-sources').on('click', function() {
            $('.source-checkbox').prop('checked', true);
        });

        $('#uncheck-all-sources').on('click', function() {
            $('.source-checkbox').prop('checked', false);
        });

        const chatMessages = document.getElementById('chat-messages');
        if (chatMessages) {
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }
    });
</script>
@endsection