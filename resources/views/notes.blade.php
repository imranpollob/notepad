@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            @if (session('status'))
                <div class="alert alert-success" role="alert">
                    {{ session('status') }}
                </div>
            @endif

            <h3 class="user-note-heading">{{ Auth::user()->name }}'s Notes</h3>

            <table class="table table-bordered table-striped table-hover">
                <thead>
                <tr>
                    <th>Note</th>
                    <th style="width: 30%">Actions</th>
                </tr>
                </thead>
                <tbody>
                @foreach($notes as $note)
                    <tr>
                        <td><a href="/{{ $note->url }}" target="_blank"
                               class="note-url">{{ $note->title ?? $note->url }}</a></td>
                        <td class="d-flex action-buttons">
                            <button type="button" class="btn btn-primary btn-sm passwordBtn" data-toggle="modal" data-target="#exampleModal" data-password="{{ $note->password }}" data-url="{{ $note->url }}">
                                <span data-toggle="tooltip" data-placement="top" title="Password Options">
                                    <i class="fa fa-key"></i>
                                </span>
                            </button>

                            <button type="button" class="btn btn-danger btn-sm deleteNoteBtn" data-toggle="modal" data-target="#deleteNoteModal" data-url="{{ $note->url }}">
                                <span data-toggle="tooltip" data-placement="top" title="Delete note">
                                    <i class="fa fa-trash"></i>
                                </span>
                            </button>

                            <button type="button" class="btn btn-primary btn-sm copyToClipboard" data-toggle="tooltip" data-placement="top" title="Copy link to clipboard">
                                <i class="fa fa-copy"></i>
                            </button>

                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>

            <input type="text" id="myInput">

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

                                <button type="submit" name="update-password" class="btn btn-warning">Add or Update Password
                                </button>
                                <button type="submit" name="delete-password" class="btn btn-success">Remove Password</button>
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

                                <button type="submit" class="btn btn-danger">YES</button>
                            </form>
                            <button type="button" class="btn btn-success" data-dismiss="modal">NO</button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection

@section('javascript')
    <script>
        $(document).ready(function () {

            $('.copyToClipboard').click(function (event) {
                let text = window.location.origin + $(this).parent().siblings().find('a').attr('href');

                const copyTextInput = $("#myInput");
                copyTextInput.show();
                copyTextInput.val(text);
                copyTextInput.select();
                document.execCommand("copy");
                copyTextInput.hide();

                $(this).attr('data-original-title', 'Link is copied to clipboard').tooltip('show');

                $(this).attr('data-original-title', 'Copy link to clipboard');
            });

            $('#exampleModal').on('show.bs.modal', function (event) {
                const button = $(event.relatedTarget);
                const modal = $(this);
                modal.find('#url').val(button.data('url'));
                modal.find('#password').val(button.data('password'));
            });

            $('#deleteNoteModal').on('show.bs.modal', function (event) {
                const button = $(event.relatedTarget);
                const modal = $(this);
                modal.find('form').attr('action', button.data('url'));
                modal.find('#password').val(button.data('password'));
            });

        });
    </script>
@endsection
