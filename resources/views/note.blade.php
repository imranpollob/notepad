@extends('layouts.app')

@section('content')
<form action="{{ url()->current() }}" method="post" id="note-form" data-editable="{{ $canEdit ? '1' : '0' }}" data-save-mode="remote">
    @csrf

    <x-rich-editor
        :initial-data="$note->data"
        :initial-title="$note->title"
        placeholder="Just dump data!!"
        :status-text="$canEdit ? 'Start Typing' : 'Read only'"
    >
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

@if(!$canEdit)
<div class="alert alert-warning mt-3" role="alert">
    This note is read-only for your account.
</div>
@endif

<div class="site-info text-muted">
    <h1>Note online is a free tool for storing and sharing you notes.</h1>
    @guest
    <h2>Manage all of your created notes by <a class="" href="{{ route('login') }}">login</a></h2>
    @endguest
</div>

<input type="text" id="myInput">

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
