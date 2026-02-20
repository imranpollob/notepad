@extends('layouts.app')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <h3>{{ $notebook->name }}</h3>
            @if($notebook->description)
                <p class="text-muted">{{ $notebook->description }}</p>
            @endif

            <p>
                <span class="badge badge-secondary text-uppercase">{{ $notebook->visibility }}</span>
                <span class="text-muted ml-2">Shared notebook (read-only)</span>
            </p>

            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Sources</h5>
                    @if($notebook->sources->isEmpty())
                        <p class="text-muted m-0">No sources shared.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Title</th>
                                    <th>Status</th>
                                    <th>Reference</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($notebook->sources as $source)
                                    <tr>
                                        <td class="text-uppercase">{{ $source->source_type }}</td>
                                        <td>{{ $source->title ?: '-' }}</td>
                                        <td>{{ $source->status }}</td>
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
