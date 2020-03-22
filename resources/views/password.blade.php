@extends('layouts.app')

@section('content')

    <h3 class="user-note-heading text-center">This Note is password protected</h3>

    <form action="/{{ Request::path() }}/password" class="form-inline d-flex justify-content-center" method="post">
        @csrf

        <label class="mb-2 mx-2" for="inlineFormCustomSelectPref">Password</label>
        <input type="password" class="form-control form-control-sm mb-2 mr-sm-2" name="password">

        <button type="submit" class="btn btn-sm btn-primary mb-2">Submit</button>
    </form>

@endsection

@section('javascript')

@endsection
