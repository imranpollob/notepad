@extends('layouts.app')

@section('stylesheet')
    <style>
        .notebooks-toolbar {
            background: linear-gradient(145deg, #f4f9ff 0%, #fff 55%, #fff7ec 100%);
            border: 1px solid #e9edf2;
            padding: 14px;
        }

        .notebook-card {
            border: 1px solid #e8e8e8;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
            transition: transform 0.15s ease, box-shadow 0.15s ease;
            height: 100%;
        }

        .notebook-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 14px 28px rgba(0, 0, 0, 0.09);
        }

        .notebook-card .card-title {
            font-weight: 600;
            color: #161616;
        }
    </style>
@endsection

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="m-0">My Notebooks</h3>
        <a href="{{ route('notebooks.create') }}" class="btn btn-dark btn-sm">Create Notebook</a>
    </div>

    @if($notebooks->isEmpty())
        <div class="alert alert-light border">
            No notebooks yet.
        </div>
    @else
        <div class="notebooks-toolbar rounded mb-3">
            <div class="row align-items-end">
                <div class="col-md-5 mb-2 mb-md-0">
                    <label for="notebook-search" class="small text-muted mb-1">Search notebooks</label>
                    <input id="notebook-search" type="text" class="form-control" placeholder="Search by name or description">
                </div>
                <div class="col-md-3 mb-2 mb-md-0">
                    <label for="notebook-visibility" class="small text-muted mb-1">Visibility</label>
                    <select id="notebook-visibility" class="form-control">
                        <option value="all">All</option>
                        <option value="private">Private</option>
                        <option value="unlisted">Unlisted</option>
                        <option value="public">Public</option>
                    </select>
                </div>
                <div class="col-md-2 mb-2 mb-md-0">
                    <label for="notebook-sort" class="small text-muted mb-1">Sort</label>
                    <select id="notebook-sort" class="form-control">
                        <option value="updated_desc">Recently updated</option>
                        <option value="updated_asc">Oldest updated</option>
                        <option value="sources_desc">Most sources</option>
                        <option value="name_asc">Name A-Z</option>
                    </select>
                </div>
                <div class="col-md-2 text-md-right">
                    <span class="badge badge-dark" id="notebook-count">{{ $notebooks->count() }} notebooks</span>
                </div>
            </div>
        </div>

        <div class="row" id="notebooks-grid">
            @foreach($notebooks as $notebook)
                @php
                    $description = trim((string) ($notebook->description ?? ''));
                @endphp
                <div class="col-sm-6 col-xl-4 mb-3 notebook-col"
                     data-name="{{ strtolower($notebook->name) }}"
                     data-description="{{ strtolower($description) }}"
                     data-visibility="{{ $notebook->visibility }}"
                     data-updated="{{ $notebook->updated_at->timestamp }}"
                     data-sources="{{ (int) $notebook->sources_count }}">
                    <div class="card notebook-card">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="card-title mb-1">{{ $notebook->name }}</h5>
                                <span class="badge badge-secondary text-uppercase">{{ $notebook->visibility }}</span>
                            </div>
                            <p class="text-muted mb-3" style="min-height:48px;">
                                {{ $description !== '' ? \Illuminate\Support\Str::limit($description, 120) : 'No description yet.' }}
                            </p>
                            <div class="small text-muted mb-3 mt-auto">
                                <div>Sources: {{ $notebook->sources_count }}</div>
                                <div>Updated {{ $notebook->updated_at->diffForHumans() }}</div>
                            </div>
                            <div class="d-flex">
                                <a href="{{ route('notebooks.show', ['notebook' => $notebook->id]) }}" class="btn btn-outline-dark btn-sm mr-2">Open</a>
                                <a href="{{ route('notebooks.edit', ['notebook' => $notebook->id]) }}" class="btn btn-outline-primary btn-sm mr-2">Edit</a>
                                <form action="{{ route('notebooks.destroy', ['notebook' => $notebook->id]) }}" method="post" onsubmit="return confirm('Delete this notebook permanently?');">
                                    @csrf
                                    @method('delete')
                                    <button type="submit" class="btn btn-outline-danger btn-sm">Delete</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="alert alert-light border text-muted d-none" id="notebooks-empty-state">
            No notebooks match your current filters.
        </div>
    @endif
@endsection

@section('javascript')
    <script>
        $(document).ready(function () {
            const grid = $('#notebooks-grid');
            const getItems = () => grid.find('.notebook-col');

            function applyFilters() {
                const query = ($('#notebook-search').val() || '').toLowerCase().trim();
                const visibility = ($('#notebook-visibility').val() || 'all').toLowerCase();
                const sort = $('#notebook-sort').val();
                const items = getItems().get();
                const visible = [];

                items.forEach(function (item) {
                    const $item = $(item);
                    const name = ($item.data('name') || '').toString();
                    const description = ($item.data('description') || '').toString();
                    const itemVisibility = ($item.data('visibility') || '').toString().toLowerCase();
                    const matchesQuery = query === '' || name.includes(query) || description.includes(query);
                    const matchesVisibility = visibility === 'all' || itemVisibility === visibility;
                    const show = matchesQuery && matchesVisibility;
                    $item.toggle(show);
                    if (show) {
                        visible.push(item);
                    }
                });

                visible.sort(function (a, b) {
                    const $a = $(a);
                    const $b = $(b);
                    const aUpdated = parseInt($a.data('updated'), 10) || 0;
                    const bUpdated = parseInt($b.data('updated'), 10) || 0;
                    const aSources = parseInt($a.data('sources'), 10) || 0;
                    const bSources = parseInt($b.data('sources'), 10) || 0;
                    const aName = ($a.data('name') || '').toString();
                    const bName = ($b.data('name') || '').toString();

                    if (sort === 'updated_asc') {
                        return aUpdated - bUpdated;
                    }
                    if (sort === 'sources_desc') {
                        return bSources - aSources;
                    }
                    if (sort === 'name_asc') {
                        return aName.localeCompare(bName);
                    }
                    return bUpdated - aUpdated;
                });

                visible.forEach(function (node) {
                    grid.append(node);
                });

                const total = items.length;
                const shown = visible.length;
                $('#notebook-count').text(shown + ' of ' + total + ' notebooks');
                $('#notebooks-empty-state').toggleClass('d-none', shown > 0 || total === 0);
            }

            $('#notebook-search, #notebook-visibility, #notebook-sort').on('input change', applyFilters);
            applyFilters();
        });
    </script>
@endsection
