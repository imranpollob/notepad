@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="auth-shell">
            <div class="text-center mb-4">
                <h2 class="page-heading mb-2" style="font-size:28px;">Create your account</h2>
                <p class="text-muted mb-0">Sign up to save notes, organize notebooks, and share your work.</p>
            </div>

            <form method="POST" action="{{ route('register') }}">
                @csrf

                <div class="form-group">
                    <label for="name">{{ __('Name') }}</label>
                    <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required autocomplete="name" autofocus>
                    @error('name')
                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="email">{{ __('E-Mail Address') }}</label>
                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email">
                    @error('email')
                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password">{{ __('Password') }}</label>
                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password">
                    @error('password')
                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password-confirm">{{ __('Confirm Password') }}</label>
                    <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password">
                </div>

                <button type="submit" class="btn btn-dark btn-block">
                    {{ __('Register') }}
                </button>
            </form>

            <hr class="my-4">

            <div class="text-center">
                <p class="mb-3 text-muted small">Or register with a social account</p>
                <div class="d-flex justify-content-center">
                    <a href="{{ url('/auth/redirect/google') }}" class="btn btn-outline-dark mr-2 px-4"><i class="fa fa-google"></i> Google</a>
                    <a href="{{ url('/auth/redirect/facebook') }}" class="btn btn-outline-dark mr-2 px-4"><i class="fa fa-facebook"></i> Facebook</a>
                    <a href="{{ url('/auth/redirect/github') }}" class="btn btn-outline-dark px-4"><i class="fa fa-github"></i> GitHub</a>
                </div>
                <p class="text-muted small mt-3 mb-0">Already have an account? <a href="{{ route('login') }}">Login here</a></p>
            </div>
        </div>
    </div>
</div>
@endsection