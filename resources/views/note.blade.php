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

        <button type="submit" id="submit" class="btn btn-primary">Submit</button>
    </form>
@endsection

@section('javascript')
    <script>

        $(document).ready(function () {

            $('#submit').click(function (e) {
                e.preventDefault();

                console.log('df')

                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

                $.ajax({
                    url: '/{{ Request::path() }}',
                    type: "POST",
                    data: $('#note_form').serialize(),
                    success: function (response) {

                        // console.log(response)
                        //
                        // setTimeout(function(){
                        //
                        // },10000);
                    }
                });
            });
        });

    </script>
@endsection
