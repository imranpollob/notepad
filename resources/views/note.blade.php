@extends('layouts.app')

@section('content')
<form action="{{ url()->current() }}" method="post" id="note-form">
    @csrf

    <div class="form-group">
        <textarea name="data" class="form-control" id="data" rows="15" placeholder="Just dump data!!">{{ $note->data }}</textarea>
    </div>

    <div class="form-group">
        <input type="text" name="title" class="form-control" id="title" value="{{ $note->title }}" placeholder="Optional Title">
    </div>
</form>

<div class="bottom-panel">
    <div id="save-status" class="badge badge-success">Start Typing</div>
    <div>
        <button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#exampleModal">
            <i class="fa fa-key"></i> Password
        </button>
        <button type="button" class="btn btn-primary btn-sm copyToClipboard" data-toggle="tooltip" data-placement="top" title="Copy link to clipboard">
            <i class="fa fa-copy"></i> <span class="d-none d-md-inline">Copy link to clipboard</span>
        </button>
    </div>
</div>

<div class="site-info text-muted">
    <h1>Paste online is a free tool for storing and sharing you notes.</h1>
    @guest
    <h2>Manage all of your created notes by <a class="" href="{{ route('login') }}">login</a>.</h2>
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
                <form action="{{ url()->current() }}" method="post" id="note-form">
                    @method('put')
                    @csrf

                    <div class="form-group">
                        <input type="password" name="password" class="form-control" id="password" placeholder="Give a password" value="{{ $note->password }}">
                    </div>

                    <button type="submit" name="update-password" class="btn btn-warning">Add or Update Password
                    </button>
                    <button type="submit" name="delete-password" class="btn btn-success">Remove Password</button>
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