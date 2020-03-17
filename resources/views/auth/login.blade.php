@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center social-login-text">
            Login with social account
        </div>
        <div class="col-md-12 text-center">
            <a href="{{ url('/auth/redirect/google') }}" class="btn btn-outline-danger"><i class="fa fa-google"></i> Google</a>
            <a href="{{ url('/auth/redirect/facebook') }}" class="btn btn-outline-primary"><i class="fa fa-facebook"></i> Facebook</a>
            <a href="{{ url('/auth/redirect/github') }}" class="btn btn-outline-secondary"><i class="fa fa-github"></i> GitHub</a>
        </div>
    </div>
</div>
@endsection
