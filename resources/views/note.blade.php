@extends('layouts.app')

@section('stylesheet')
<style>
    .home-shell {
        background:
            radial-gradient(circle at top right, rgba(15, 93, 165, 0.08), transparent 42%),
            radial-gradient(circle at bottom left, rgba(255, 173, 71, 0.10), transparent 38%),
            linear-gradient(140deg, var(--color-bg-subtle) 0%, var(--color-bg-page) 58%, var(--color-accent-bg) 100%);
        border: 1px solid var(--color-border);
        border-radius: var(--radius-md);
        padding: var(--space-lg);
    }
</style>
@endsection

@section('content')
<div class="home-shell mb-4">
    <form action="{{ url()->current() }}" method="post" id="note-form" data-editable="{{ $canEdit ? '1' : '0' }}" data-save-mode="remote">
        @csrf

        <x-rich-editor
            :initial-data="$note->data"
            :initial-title="$note->title"
            placeholder="Just dump data!!"
            :status-text="$canEdit ? 'Start Typing' : 'Read only'">
            <x-slot name="actions">
                @if($canEdit)
                <button type="button" class="btn btn-outline-dark btn-sm" data-toggle="modal" data-target="#exampleModal">
                    <i class="fa fa-key"></i> Password
                </button>
                @endif
                <button type="button" class="btn btn-outline-dark btn-sm copyToClipboard" data-toggle="tooltip" data-placement="top" title="Copy link to clipboard">
                    <i class="fa fa-copy"></i> <span class="d-none d-md-inline">Copy link to clipboard</span>
                </button>
            </x-slot>
        </x-rich-editor>
    </form>
</div>

@if(!$canEdit)
<div class="alert alert-warning mt-3" role="alert">
    This note is read-only for your account.
</div>
@endif

@guest
<div class="text-center text-muted mt-4 mb-2">
    <p class="mb-1">Notebase is a free tool for writing, saving, and sharing your notes. Group notes into notebooks and chat with your knowledge base.</p>
    <p class="mb-0">Manage all of your created notes by <a href="{{ route('login') }}">logging in</a>.</p>
</div>
@endguest

<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Password Management</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <form action="{{ url()->current() }}" method="post" id="note-form2">
                    @method('put')
                    @csrf

                    <div class="form-group">
                        <input type="password" name="password" class="form-control" id="password" placeholder="Give a password">
                    </div>

                    <button type="submit" name="update-password" class="btn btn-sm btn-dark">
                        {{ $note->password ? 'Update' : 'Add' }} Password
                    </button>

                    @if($note->password)
                    <button type="submit" name="delete-password" class="btn btn-sm btn-outline-dark">Remove Password</button>
                    @endif
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('javascript')
<script>
    $(document).ready(function() {

        $('.copyToClipboard').click(function() {
            let text = window.location.href;

            const copyTextInput = $("#myInput");
            copyTextInput.show();
            copyTextInput.val(text);
            copyTextInput.select();
            document.execCommand("copy");
            copyTextInput.hide();

            $(this).attr('data-original-title', 'Link is copied to clipboard').tooltip('show');

            $(this).attr('data-original-title', 'Copy link to clipboard');
        })

    });
</script>
@endsection