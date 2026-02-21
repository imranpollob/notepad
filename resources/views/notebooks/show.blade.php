@extends('layouts.app')

@section('stylesheet')
<style>
    .notebook-hero {
        background: linear-gradient(140deg, var(--color-bg-subtle) 0%, var(--color-bg-page) 65%, var(--color-accent-bg) 100%);
        border: 1px solid var(--color-border);
        border-radius: var(--radius-md);
        padding: 18px;
    }

    .notebook-panel {
        border: 1px solid var(--color-border);
        box-shadow: var(--shadow-card);
        border-radius: var(--radius-md);
    }

    .source-card {
        border: 1px solid var(--color-border);
        box-shadow: var(--shadow-card);
        border-radius: var(--radius-md);
    }
</style>
@endsection

@section('content')
<div class="notebook-hero rounded mb-4">
    <div class="d-flex justify-content-between align-items-start flex-wrap">
        <div class="mb-2 mb-md-0 pr-md-3">
            <h3 class="m-0">{{ $notebook->name }}</h3>
            @if($notebook->description)
            <p class="text-muted mt-2 mb-0">{{ $notebook->description }}</p>
            @else
            <p class="text-muted mt-2 mb-0">No notebook description yet.</p>
            @endif
        </div>
        <div class="d-flex">
            <a href="{{ route('notebooks.chat', ['notebook' => $notebook->id]) }}" class="btn btn-dark btn-sm mr-2">Open Chat</a>
            <a href="{{ route('notebooks.edit', ['notebook' => $notebook->id]) }}" class="btn btn-outline-primary btn-sm">Edit Notebook</a>
        </div>
    </div>
</div>

<div class="card notebook-panel mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center flex-wrap mb-2">
            <h5 class="card-title mb-2 mb-md-0">Sharing</h5>
            <span class="badge badge-secondary text-uppercase">{{ $notebook->visibility }}</span>
        </div>
        <p class="text-muted">Control who can open this notebook by link.</p>

        @if(in_array($notebook->visibility, ['public', 'unlisted']))
        <div class="input-group mb-2">
            <input type="text" class="form-control" readonly value="{{ route('notebooks.shared', ['token' => $notebook->share_token]) }}">
            <div class="input-group-append">
                <button class="btn btn-outline-dark btn-sm copy-share-link" type="button">Copy Link</button>
            </div>
        </div>
        <form action="{{ route('notebooks.share-token', ['notebook' => $notebook->id]) }}" method="post">
            @csrf
            <button type="submit" class="btn btn-outline-dark btn-sm">Regenerate Share Link</button>
        </form>
        @else
        <p class="text-muted mb-0">Private notebooks cannot be accessed with a share link.</p>
        @endif
    </div>
</div>

<div class="row">
    <div class="col-lg-4 mb-4">
        <div class="card notebook-panel mb-3">
            <div class="card-body">
                <h6 class="card-title">Attach Existing Note</h6>
                <form action="{{ route('notebooks.sources.note', ['notebook' => $notebook->id]) }}" method="post">
                    @csrf
                    <div class="form-group">
                        <select name="note_id" class="form-control" required>
                            <option value="">Select a note</option>
                            @foreach($notes as $note)
                            <option value="{{ $note->id }}">{{ $note->title ?: $note->url }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-dark btn-sm">Attach Note</button>
                </form>
            </div>
        </div>

        <div class="card notebook-panel mb-3">
            <div class="card-body">
                <h6 class="card-title">Upload File (PDF/DOC/DOCX)</h6>
                <form action="{{ route('notebooks.sources.file', ['notebook' => $notebook->id]) }}" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <input type="file" class="form-control-file" name="file" required>
                    </div>
                    <div class="form-group">
                        <input type="text" class="form-control" name="title" maxlength="255" placeholder="Optional title">
                    </div>
                    <button type="submit" class="btn btn-dark btn-sm">Attach File</button>
                </form>
            </div>
        </div>

        <div class="card notebook-panel">
            <div class="card-body">
                <h6 class="card-title">Attach URL</h6>
                <form action="{{ route('notebooks.sources.url', ['notebook' => $notebook->id]) }}" method="post">
                    @csrf
                    <div class="form-group">
                        <input type="url" class="form-control" name="origin_url" maxlength="2000" required placeholder="https://example.com/article">
                    </div>
                    <div class="form-group">
                        <input type="text" class="form-control" name="title" maxlength="255" placeholder="Optional title">
                    </div>
                    <button type="submit" class="btn btn-dark btn-sm">Attach URL</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card notebook-panel">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title mb-0">Sources</h5>
                    <form method="get" action="{{ route('notebooks.show', ['notebook' => $notebook->id]) }}" class="form-inline">
                        <label for="status" class="mr-2 mb-0">Status</label>
                        <select class="form-control form-control-sm mr-2" id="status" name="status">
                            <option value="all" {{ $statusFilter === 'all' ? 'selected' : '' }}>All</option>
                            <option value="pending" {{ $statusFilter === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="processing" {{ $statusFilter === 'processing' ? 'selected' : '' }}>Processing</option>
                            <option value="ready" {{ $statusFilter === 'ready' ? 'selected' : '' }}>Ready</option>
                            <option value="failed" {{ $statusFilter === 'failed' ? 'selected' : '' }}>Failed</option>
                        </select>
                        <button type="submit" class="btn btn-outline-dark btn-sm">Apply</button>
                    </form>
                </div>
                @if($sources->isEmpty())
                <p class="text-muted m-0">No sources attached yet.</p>
                @else
                <div class="row">
                    @foreach($sources as $source)
                    <div class="col-12 mb-3">
                        <div class="card source-card">
                            <div class="card-body py-3">
                                <div class="d-flex justify-content-between align-items-start flex-wrap">
                                    <div class="pr-3">
                                        <div class="mb-1">
                                            <span class="badge badge-light text-uppercase mr-2">{{ $source->source_type }}</span>
                                            <strong>{{ $source->title ?: 'Untitled source' }}</strong>
                                        </div>
                                        <div class="small text-muted mb-2">
                                            Status:
                                            <span class="badge badge-secondary">{{ $source->status }}</span>
                                        </div>
                                        @if($source->error_message)
                                        <div class="small text-danger mb-2">{{ $source->error_message }}</div>
                                        @endif
                                        <div class="small">
                                            @if($source->source_type === 'note' && $source->note)
                                            <a href="{{ route('note.show', ['url' => $source->note->url]) }}" target="_blank">{{ $source->note->title ?: $source->note->url }}</a>
                                            @elseif($source->source_type === 'url' && $source->origin_url)
                                            <a href="{{ $source->origin_url }}" target="_blank">{{ $source->origin_url }}</a>
                                            @elseif($source->source_type === 'file' && $source->files->isNotEmpty())
                                            {{ $source->files->first()->original_name }}
                                            @else
                                            -
                                            @endif
                                        </div>
                                    </div>
                                    <div class="d-flex mt-2 mt-md-0">
                                        @if($source->status === 'failed' && in_array($source->source_type, ['file', 'url']))
                                        <form action="{{ route('notebooks.sources.retry', ['notebook' => $notebook->id, 'source' => $source->id]) }}" method="post" class="mr-2">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-primary btn-sm">Retry</button>
                                        </form>
                                        @endif
                                        <form action="{{ route('notebooks.sources.destroy', ['notebook' => $notebook->id, 'source' => $source->id]) }}" method="post" onsubmit="return confirm('Remove this source from notebook?');">
                                            @csrf
                                            @method('delete')
                                            <button type="submit" class="btn btn-outline-danger btn-sm">Remove</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('javascript')
<script>
    $(document).ready(function() {
        $('.copy-share-link').on('click', function() {
            const input = $(this).closest('.input-group').find('input');
            const text = input.val();
            if (!text) {
                return;
            }

            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text);
                return;
            }

            input.trigger('select');
            document.execCommand('copy');
        });
    });
</script>
@endsection