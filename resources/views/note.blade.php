@extends('layouts.app')

@section('title', 'Page Title')

@section('content')
    <form action="/{{ Request::path() }}" method="post">
        @csrf

        <div class="form-group">
            <textarea name="data" class="form-control" id="data" rows="15" placeholder="Just dump data!!">{{ $note->data }}</textarea>
        </div>

        <div class="form-group">
            <input type="text" name="title" class="form-control" id="title" value="{{ $note->title }}"
                   placeholder="Optional Title">
        </div>

        <button type="submit" class="btn btn-primary">Submit</button>
    </form>
@endsection
