@extends('layouts.app')

@section('title', 'Page Title')

@section('content')
    <form action="/{{ Request::path() }}" method="post">
        @csrf
        <textarea name="data" rows="5" cols="40">{{ $note->data }}</textarea>
        <input type="text" name="title" value="{{ $note->title }}" placeholder="Optional Title">
        <button type="submit" class="btn btn-primary">Submit</button>
    </form>
@endsection
