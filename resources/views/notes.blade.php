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
                            <form action="/{{ $note->url }}" method="post">
                                @method('delete')
                                @csrf
                                <button type="button" class="btn btn-danger btn-sm deleteNoteBtn" data-toggle="tooltip" data-placement="top" title="Delete note">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </form>
                            <button type="button" class="btn btn-primary btn-sm copyToClipboard" data-toggle="tooltip" data-placement="top" title="Copy link to clipboard">
                                <i class="fa fa-copy"></i>
                            </button>

                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>

            <input type="text" id="myInput">
        </div>
    </div>
@endsection

@section('javascript')
    <script src="{{ asset('js/bootbox.min.js') }}"></script>
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

                $(this).attr('data-original-title', 'Copied').tooltip('show');

                $(this).attr('data-original-title', 'Copy link to clipboard');
            });

            $('.deleteNoteBtn').click(function () {
                const that = $(this);
                bootbox.confirm({
                    message: "Are you sure to delete the note?",
                    size: 'small',
                    backdrop: true,
                    buttons: {
                        confirm: {
                            label: 'Yes',
                            className: 'btn-success'
                        },
                        cancel: {
                            label: 'No',
                            className: 'btn-danger'
                        }
                    },
                    callback: function (result) {
                        if (result) {
                            that.parent('form').submit();
                        }
                    }
                });
            })

        });
    </script>
@endsection
