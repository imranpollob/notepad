@extends('layouts.app')

@section('content')
    <form action="/{{ Request::path() }}" method="post" id="note-form">
        @csrf

        <div class="form-group">
            <textarea name="data" class="form-control" id="data" rows="15"
                      placeholder="Just dump data!!">{{ $note->data }}</textarea>
        </div>

        <div class="form-group">
            <input type="text" name="title" class="form-control" id="title" value="{{ $note->title }}"
                   placeholder="Optional Title">
        </div>
    </form>

    <div id="save-status" class="badge badge-success"></div>

    <div class="site-info text-muted">
        <h1>Notepad online is a free tool for storing and sharing you notes.</h1>
        @guest
        <h2>Registration benefits: view all created notes</h2>
        @endguest
    </div>
@endsection

@section('javascript')
    <script>
        $(document).ready(function () {

            //setup before functions
            let typingTimer;                //timer identifier
            let doneTypingInterval = 2000;  //time in ms (5 seconds)

            //on keyup, start the countdown
            $('#data, #title').keyup(function () {
                $('#save-status').text('Saving ...');
                clearTimeout(typingTimer);

                typingTimer = setTimeout(doneTyping, doneTypingInterval);
            });

            //user is "finished typing," do something
            function doneTyping() {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

                $.ajax({
                    url: '/{{ Request::path() }}',
                    type: "POST",
                    data: $('#note-form').serialize(),
                    success: function (response) {
                        console.log('saved');
                        $('#save-status').text('Saved');
                    }
                });
            }

        });

    </script>
@endsection

