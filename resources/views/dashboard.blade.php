@extends('layouts.app')

@section('stylesheet')
<style>
    .dashboard-shell {
        background: linear-gradient(140deg, var(--color-bg-subtle) 0%, var(--color-bg-page) 65%, var(--color-accent-bg) 100%);
        border: 1px solid var(--color-border);
        border-radius: var(--radius-md);
        padding: var(--space-md);
    }

    .metric-card {
        border: 1px solid var(--color-border);
        box-shadow: var(--shadow-card);
        border-radius: var(--radius-md);
    }

    .metric-label {
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--color-text-muted);
        margin-bottom: 8px;
    }

    .metric-value {
        font-size: 30px;
        line-height: 1;
        font-weight: 600;
        color: var(--color-heading);
        margin: 0;
    }

    .user-card {
        border: 1px solid var(--color-border);
        box-shadow: var(--shadow-card);
        border-radius: var(--radius-md);
    }

    .user-rank {
        width: 30px;
        text-align: center;
        font-weight: 700;
        color: var(--color-text-muted);
    }
</style>
@endsection

@section('content')

<div class="row justify-content-center">
    <h3 class="page-heading" style="font-size:24px;">Dashboard</h3>
</div>

<div class="dashboard-shell rounded mb-4">
    <div class="row">
        <div class="col-md-4 mb-3 mb-md-0">
            <div class="card metric-card h-100">
                <div class="card-body">
                    <div class="metric-label">Total Notes</div>
                    <p class="metric-value">{{ $totalNotes }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3 mb-md-0">
            <div class="card metric-card h-100">
                <div class="card-body">
                    <div class="metric-label">Total Users</div>
                    <p class="metric-value">{{ $totalUser }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card metric-card h-100">
                <div class="card-body">
                    <div class="metric-label">Filled Notes</div>
                    <p class="metric-value">{{ $nonEmptyNotes }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="dashboard-shell rounded">
    <div class="row mb-3 align-items-end">
        <div class="col-md-6 mb-2 mb-md-0">
            <label for="user-search" class="small text-muted mb-1">Search users</label>
            <input id="user-search" type="text" class="form-control" placeholder="Search by name or email">
        </div>
        <div class="col-md-3 mb-2 mb-md-0">
            <label for="user-sort" class="small text-muted mb-1">Sort</label>
            <select id="user-sort" class="form-control">
                <option value="notes_desc">Most notes</option>
                <option value="notes_asc">Least notes</option>
                <option value="name_asc">Name A-Z</option>
            </select>
        </div>
        <div class="col-md-3 text-md-right">
            <span class="badge badge-dark" id="users-count">{{ count($notesPerUser) }} users</span>
        </div>
    </div>

    <div class="row" id="users-grid">
        @foreach($notesPerUser as $index => $npu)
        <div class="col-md-6 col-xl-4 mb-3 user-col"
            data-name="{{ strtolower($npu->name) }}"
            data-email="{{ strtolower($npu->email) }}"
            data-notes="{{ (int) $npu->notes }}">
            <div class="card user-card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2">
                        <div class="user-rank mr-2">#{{ $index + 1 }}</div>
                        <div>
                            <h6 class="mb-0">{{ $npu->name }}</h6>
                            <div class="small text-muted">{{ $npu->email }}</div>
                        </div>
                    </div>
                    <div class="mt-3">
                        <span class="badge badge-secondary">Notes: {{ $npu->notes }}</span>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="alert alert-light border text-muted d-none mb-0" id="users-empty-state">
        No users match your current filters.
    </div>
</div>

<div class="row mt-4">
    <div class="col d-flex justify-content-end">
        <button type="button" class="btn btn-danger btn-sm deleteNoteBtn" data-toggle="modal" data-target="#deleteEmptyNotes">
            Delete Empty Notes
        </button>
    </div>
</div>

<!-- Delete All Empty Notes Modal -->
<div class="modal fade" id="deleteEmptyNotes" tabindex="-1" role="dialog" aria-labelledby="deleteEmptyNotesLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteEmptyNotesLabel">Are you sure to delete <span class="text-danger">all</span> empty notes?</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-footer">
                <form action="/dashboard" method="post">
                    @method('delete')
                    @csrf
                    <button type="submit" class="btn btn-danger">YES</button>
                </form>
                <button type="button" class="btn btn-success" data-dismiss="modal">NO</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('javascript')
<script>
    $(document).ready(function() {
        const grid = $('#users-grid');
        const getItems = () => grid.find('.user-col');

        function applyFilters() {
            const query = ($('#user-search').val() || '').toLowerCase().trim();
            const sort = $('#user-sort').val();
            const items = getItems().get();
            const visible = [];

            items.forEach(function(item) {
                const $item = $(item);
                const name = ($item.data('name') || '').toString();
                const email = ($item.data('email') || '').toString();
                const show = query === '' || name.includes(query) || email.includes(query);
                $item.toggle(show);
                if (show) {
                    visible.push(item);
                }
            });

            visible.sort(function(a, b) {
                const $a = $(a);
                const $b = $(b);
                const aNotes = parseInt($a.data('notes'), 10) || 0;
                const bNotes = parseInt($b.data('notes'), 10) || 0;
                const aName = ($a.data('name') || '').toString();
                const bName = ($b.data('name') || '').toString();

                if (sort === 'notes_asc') {
                    return aNotes - bNotes;
                }
                if (sort === 'name_asc') {
                    return aName.localeCompare(bName);
                }
                return bNotes - aNotes;
            });

            visible.forEach(function(node) {
                grid.append(node);
            });

            const total = items.length;
            const shown = visible.length;
            $('#users-count').text(shown + ' of ' + total + ' users');
            $('#users-empty-state').toggleClass('d-none', shown > 0 || total === 0);
        }

        $('#user-search, #user-sort').on('input change', applyFilters);
        applyFilters();
    });
</script>
@endsection