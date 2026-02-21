@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="auth-shell">
            <div class="text-center mb-4">
                <h2 class="page-heading mb-2" style="font-size:28px;">Welcome back</h2>
                <p class="text-muted mb-0">Sign in to manage your notes, notebooks, and shared content.</p>
            </div>

            <div class="mb-4">
                <h6 class="text-muted text-uppercase small mb-3" style="letter-spacing:0.5px;">What you get with an account</h6>
                <div class="row">
                    <div class="col-sm-6 mb-2">
                        <div class="d-flex align-items-center">
                            <i class="fa fa-eye mr-2 text-muted"></i>
                            <span>View all your saved notes</span>
                        </div>
                    </div>
                    <div class="col-sm-6 mb-2">
                        <div class="d-flex align-items-center">
                            <i class="fa fa-key mr-2 text-muted"></i>
                            <span>Password-protect notes</span>
                        </div>
                    </div>
                    <div class="col-sm-6 mb-2">
                        <div class="d-flex align-items-center">
                            <i class="fa fa-book mr-2 text-muted"></i>
                            <span>Organize with notebooks</span>
                        </div>
                    </div>
                    <div class="col-sm-6 mb-2">
                        <div class="d-flex align-items-center">
                            <i class="fa fa-share-alt mr-2 text-muted"></i>
                            <span>Share links instantly</span>
                        </div>
                    </div>
                </div>
            </div>

            <hr class="my-4">

            <div class="text-center">
                <p class="mb-3 font-weight-bold">Sign in with</p>
                <div class="d-flex justify-content-center">
                    <a href="{{ url('/auth/redirect/google') }}" class="btn btn-outline-dark mr-2 px-4"><i class="fa fa-google"></i> Google</a>
                    <a href="{{ url('/auth/redirect/github') }}" class="btn btn-outline-dark px-4"><i class="fa fa-github"></i> GitHub</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection