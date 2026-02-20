@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
            <h3 class="m-0">{{ $notebook->name }}</h3>
            @if($notebook->description)
                <p class="text-muted mt-2 mb-0">{{ $notebook->description }}</p>
            @endif
        </div>
        <div>
            <a href="{{ route('notebooks.chat', ['notebook' => $notebook->id]) }}" class="btn btn-outline-dark btn-sm mr-2">Open Chat</a>
            <a href="{{ route('notebooks.edit', ['notebook' => $notebook->id]) }}" class="btn btn-outline-primary btn-sm">Edit Notebook</a>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title mb-3">Sharing</h5>
            <p class="mb-2">Visibility: <span class="badge badge-secondary text-uppercase">{{ $notebook->visibility }}</span></p>

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
            <div class="card mb-3">
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

            <div class="card mb-3">
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

            <div class="card">
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
            <div class="card">
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
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Title</th>
                                    <th>Status</th>
                                    <th>Reference</th>
                                    <th>Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($sources as $source)
                                    <tr>
                                        <td class="text-uppercase">{{ $source->source_type }}</td>
                                        <td>{{ $source->title ?: '-' }}</td>
                                        <td>
                                            <span class="badge badge-light">{{ $source->status }}</span>
                                            @if($source->error_message)
                                                <div class="small text-danger mt-1">{{ $source->error_message }}</div>
                                            @endif
                                        </td>
                                        <td>
                                            @if($source->source_type === 'note' && $source->note)
                                                <a href="{{ route('note.show', ['url' => $source->note->url]) }}" target="_blank">{{ $source->note->title ?: $source->note->url }}</a>
                                            @elseif($source->source_type === 'url' && $source->origin_url)
                                                <a href="{{ $source->origin_url }}" target="_blank">{{ $source->origin_url }}</a>
                                            @elseif($source->source_type === 'file' && $source->files->isNotEmpty())
                                                {{ $source->files->first()->original_name }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex">
                                                @if($source->status === 'failed' && in_array($source->source_type, ['file', 'url']))
                                                    <form action="{{ route('notebooks.sources.retry', ['notebook' => $notebook->id, 'source' => $source->id]) }}" method="post" class="mr-2">
                                                        @csrf
                                                        <button type="submit" class="btn btn-outline-primary btn-sm">Retry</button>
                                                    </form>
                                                @endif
                                                <form action="{{ route('notebooks.sources.destroy', ['notebook' => $notebook->id, 'source' => $source->id]) }}" method="post">
                                                    @csrf
                                                    @method('delete')
                                                    <button type="submit" class="btn btn-outline-danger btn-sm">Remove</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@section('javascript')
    <script>
        $(document).ready(function () {
            $('.copy-share-link').on('click', function () {
                const input = $(this).closest('.input-group').find('input');
                input.trigger('select');
                document.execCommand('copy');
            });
        });
    </script>
@endsection
