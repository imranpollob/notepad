@extends('layouts.app')

@section('stylesheet')
<style>
    .notes-toolbar {
        background: linear-gradient(135deg, var(--color-accent-bg) 0%, var(--color-bg-page) 60%, var(--color-bg-subtle) 100%);
        border: 1px solid var(--color-border);
        border-radius: var(--radius-md);
        padding: 14px;
    }

    .notes-grid .note-col {
        margin-bottom: 1rem;
    }

    .note-card {
        border: 1px solid var(--color-border);
        box-shadow: var(--shadow-card);
        border-radius: var(--radius-md);
        transition: transform var(--transition-fast), box-shadow var(--transition-fast);
        height: 100%;
    }

    .note-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-card-hover);
    }

    .note-card-title {
        color: var(--color-heading);
        font-weight: 600;
        line-height: 1.3;
        text-decoration: none;
    }

    .note-card-title:hover {
        text-decoration: underline;
        color: var(--color-text);
    }

    .note-preview {
        color: var(--color-text-muted);
        min-height: 66px;
        white-space: pre-wrap;
    }

    .notes-count-badge {
        font-size: 12px;
        letter-spacing: 0.2px;
    }
</style>
@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">
        @if (session('status'))
        <div class="alert alert-success" role="alert">
            {{ session('status') }}
        </div>
        @endif

        <h3 class="page-heading" style="font-size:24px;">{{ Auth::user()->name }}'s Notes</h3>
    </div>

    <div class="notes-toolbar rounded mb-3">
        <div class="row align-items-end">
            <div class="col-md-5 mb-2 mb-md-0">
                <label for="note-search" class="small text-muted mb-1">Search notes</label>
                <input type="text" id="note-search" class="form-control" placeholder="Search by title or content">
            </div>
            <div class="col-md-3 mb-2 mb-md-0">
                <label for="note-sort" class="small text-muted mb-1">Sort</label>
                <select id="note-sort" class="form-control">
                    <option value="updated_desc">Recently updated</option>
                    <option value="updated_asc">Oldest updated</option>
                    <option value="title_asc">Title A-Z</option>
                    <option value="title_desc">Title Z-A</option>
                </select>
            </div>
            <div class="col-md-2 mb-2 mb-md-0">
                <div class="form-check mt-4">
                    <input class="form-check-input" type="checkbox" id="locked-only">
                    <label class="form-check-label" for="locked-only">Locked only</label>
                </div>
            </div>
            <div class="col-md-2 text-md-right">
                <span class="badge badge-dark notes-count-badge" id="notes-count">
                    {{ $notes->count() }} notes
                </span>
            </div>
        </div>
    </div>

    <div class="row notes-grid" id="notes-grid">
        @forelse($notes as $note)
        @php
        $plainContent = trim(strip_tags((string) ($note->data ?? '')));
        $preview = \Illuminate\Support\Str::limit($plainContent, 190);
        $title = $note->title ?: $note->url;
        $isLocked = !empty($note->password);
        @endphp
        <div class="col-sm-6 col-lg-4 note-col"
            data-title="{{ strtolower($title) }}"
            data-content="{{ strtolower($plainContent) }}"
            data-updated="{{ $note->updated_at->timestamp }}"
            data-locked="{{ $isLocked ? '1' : '0' }}">
            <div class="card note-card">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <a href="{{ route('note.show', ['url' => $note->url]) }}" target="_blank" class="note-card-title note-url">
                            {{ $title }}
                        </a>
                        @if($isLocked)
                        <span class="badge badge-primary ml-2"><i class="fa fa-lock"></i></span>
                        @endif
                    </div>

                    <p class="note-preview mb-3">{{ $preview !== '' ? $preview : 'No preview available yet.' }}</p>

                    <div class="small text-muted mb-3 mt-auto">
                        Updated {{ $note->updated_at->diffForHumans() }}
                    </div>

                    <div class="btn-group btn-group-sm w-100" role="group">
                        <a href="{{ route('note.show', ['url' => $note->url]) }}" target="_blank"
                            class="btn btn-outline-dark"
                            data-toggle="tooltip" data-placement="top" title="Open note">
                            <i class="fa fa-external-link"></i>
                        </a>
                        <button type="button" class="btn btn-outline-dark copyToClipboard"
                            data-link="{{ route('note.show', ['url' => $note->url]) }}"
                            data-toggle="tooltip" data-placement="top" title="Copy link">
                            <i class="fa fa-copy"></i>
                        </button>
                        <button type="button" class="btn btn-outline-secondary passwordBtn"
                            data-toggle="modal" data-target="#exampleModal"
                            data-url="{{ $note->url }}"
                            title="Password options">
                            <i class="fa fa-key"></i>
                        </button>
                        <button type="button" class="btn btn-outline-danger deleteNoteBtn"
                            data-toggle="modal" data-target="#deleteNoteModal"
                            data-url="{{ route('note.destroy', ['url' => $note->url]) }}"
                            title="Delete note">
                            <i class="fa fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="alert alert-light border text-muted mb-0">No notes found yet.</div>
        </div>
        @endforelse
    </div>

    <div class="alert alert-light border text-muted d-none" id="notes-empty-state">
        No notes match your current filters.
    </div>

    <!-- Modal -->
    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Password Management</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <form action="/notes" method="post" id="note-form">
                        @method('put')
                        @csrf

                        <input type="hidden" name="url" value="" id="url">

                        <div class="form-group">
                            <input type="password" name="password" class="form-control" id="password"
                                placeholder="Give a password" value="">
                        </div>

                        <button type="submit" name="update-password" class="btn btn-sm btn-dark">Add or Update Password
                        </button>
                        <button type="submit" name="delete-password" class="btn btn-sm btn-outline-dark">Remove Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Note Note Modal -->
    <div class="modal fade" id="deleteNoteModal" tabindex="-1" role="dialog" aria-labelledby="deleteNoteModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteNoteModalLabel">Are you sure to delete the note?</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-footer">
                    <form method="post">
                        @method('delete')
                        @csrf

                        <button type="submit" class="btn btn-light btn-sm px-3">Yes</button>
                    </form>
                    <button type="button" class="btn btn-dark btn-sm px-3 ml-3" data-dismiss="modal">No</button>
                </div>
            </div>
        </div>
    </div>

</div>
</div>
@endsection

@section('javascript')
<script>
    $(document).ready(function() {
        $('[data-toggle="tooltip"]').tooltip();

        const grid = $('#notes-grid');
        const noteCols = () => grid.find('.note-col');

        function applyFilters() {
            const query = ($('#note-search').val() || '').toLowerCase().trim();
            const sort = $('#note-sort').val();
            const lockedOnly = $('#locked-only').is(':checked');
            const cols = noteCols().get();
            const visible = [];

            cols.forEach(function(col) {
                const $col = $(col);
                const title = ($col.data('title') || '').toString();
                const content = ($col.data('content') || '').toString();
                const isLocked = ($col.data('locked') || 0).toString() === '1';
                const matchesQuery = query === '' || title.includes(query) || content.includes(query);
                const matchesLock = !lockedOnly || isLocked;
                const isVisible = matchesQuery && matchesLock;
                $col.toggle(isVisible);
                if (isVisible) {
                    visible.push(col);
                }
            });

            visible.sort(function(a, b) {
                const $a = $(a);
                const $b = $(b);
                const aUpdated = parseInt($a.data('updated'), 10) || 0;
                const bUpdated = parseInt($b.data('updated'), 10) || 0;
                const aTitle = (($a.data('title') || '').toString());
                const bTitle = (($b.data('title') || '').toString());

                if (sort === 'updated_asc') {
                    return aUpdated - bUpdated;
                }
                if (sort === 'title_asc') {
                    return aTitle.localeCompare(bTitle);
                }
                if (sort === 'title_desc') {
                    return bTitle.localeCompare(aTitle);
                }
                return bUpdated - aUpdated;
            });

            visible.forEach(function(node) {
                grid.append(node);
            });

            const total = cols.length;
            const shown = visible.length;
            $('#notes-count').text(shown + ' of ' + total + ' notes');
            $('#notes-empty-state').toggleClass('d-none', shown > 0 || total === 0);
        }

        $('.copyToClipboard').click(function(event) {
            let text = $(this).data('link');
            if (!text) {
                return;
            }

            const button = $(this);
            const setCopiedTooltip = function() {
                button.attr('data-original-title', 'Link is copied to clipboard').tooltip('show');
                button.attr('data-original-title', 'Copy link to clipboard');
            };

            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text).then(setCopiedTooltip).catch(function() {
                    const tempInput = $('<textarea>').css({
                        position: 'absolute',
                        left: '-9999px',
                        top: '0'
                    }).val(text).appendTo('body');

                    tempInput[0].focus();
                    tempInput[0].select();
                    document.execCommand('copy');
                    tempInput.remove();
                    setCopiedTooltip();
                });
                return;
            }

            const tempInput = $('<textarea>').css({
                position: 'absolute',
                left: '-9999px',
                top: '0'
            }).val(text).appendTo('body');

            tempInput[0].focus();
            tempInput[0].select();
            document.execCommand('copy');
            tempInput.remove();
            setCopiedTooltip();
        });

        $('#exampleModal').on('show.bs.modal', function(event) {
            const button = $(event.relatedTarget);
            const modal = $(this);
            modal.find('#url').val(button.data('url'));
            modal.find('#password').val('');
        });

        $('#deleteNoteModal').on('show.bs.modal', function(event) {
            const button = $(event.relatedTarget);
            const modal = $(this);
            modal.find('form').attr('action', button.data('url'));
        });

        $('#note-search, #note-sort, #locked-only').on('input change', applyFilters);
        applyFilters();
    });
</script>
@endsection