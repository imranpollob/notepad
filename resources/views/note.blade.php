@extends('layouts.app')

@section('title', 'Page Title')

@section('content')
    <form action="/{{ Request::path() }}" method="post" id="note_form">
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
@endsection

@section('javascript')
    <script>
        $(document).ready(function () {

            //setup before functions
            let typingTimer;                //timer identifier
            let doneTypingInterval = 2000;  //time in ms (5 seconds)

            //on keyup, start the countdown
            $('#data').keyup(function () {
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
                    data: $('#note_form').serialize()
                });
            }

        });

    </script>
@endsection
