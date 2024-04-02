@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8 col-sm-12 card-body p-5">
            <h4 class="mb-4">Login Features</h4>
            <ul class="list-unstyled list pl-5">
                <li class="mb-3 d-flex align-items-center">
                    <i class="fa fa-hourglass-end mr-3 text-dark"></i>See all of your created notes
                </li>
                <li class="mb-3 d-flex align-items-center">
                    <i class="fa fa-inbox mr-3 text-dark"></i>Delete a note
                </li>
                <li class="mb-3 d-flex align-items-center">
                    <i class="fa fa-rocket mr-3 text-dark"></i>Easily give password to a note
                </li>
                <li class="mb-3 d-flex align-items-center">
                    <i class="fa fa-trophy mr-3 text-dark"></i>Quickly copy sharing link
                </li>
            </ul>
        </div>

        <div class="col-md-4 col-sm-12 text-center d-flex align-items-center justify-content-center">
            <a href="{{ url('/auth/redirect/google') }}" class="btn btn-outline-dark"><i class="fa fa-google"></i> Google</a>
            <a href="{{ url('/auth/redirect/github') }}" class="btn btn-outline-dark ml-1"><i class="fa fa-github"></i> GitHub</a>
        </div>
    </div>
</div>
@endsection