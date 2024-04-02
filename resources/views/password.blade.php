@extends('layouts.app')

@section('content')

    <h3 class="user-note-heading text-center">This Note is password protected</h3>

    <form action="/{{ Request::path() }}/password" class="form-inline d-flex justify-content-center" method="post">
        @csrf

        <label class="mx-2" for="inlineFormCustomSelectPref">Password</label>
        <input type="password" class="form-control form-control-sm mr-sm-2" name="password">

        <button type="submit" class="btn btn-sm btn-dark px-3 mt-2 mt-sm-0">Submit</button>
    </form>

@endsection

@section('javascript')

@endsection
