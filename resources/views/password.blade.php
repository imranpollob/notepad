@extends('layouts.app')

@section('content')

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="auth-shell text-center">
            <h3 class="page-heading" style="font-size:22px;"><i class="fa fa-lock mr-2"></i>This note is password protected</h3>

            <form action="{{ route('note.password', ['url' => $note->url]) }}" class="form-inline d-flex justify-content-center" method="post">
                @csrf

                <label class="mx-2" for="inlineFormCustomSelectPref">Password</label>
                <input type="password" class="form-control form-control-sm mr-sm-2" name="password">

                <button type="submit" class="btn btn-sm btn-dark px-3 mt-2 mt-sm-0">Submit</button>
            </form>
        </div>
    </div>
</div>

@endsection

@section('javascript')

@endsection