@extends('layouts.app')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <h3 class="mb-3">Create Notebook</h3>

            <form action="{{ route('notebooks.store') }}" method="post">
                @include('notebooks._form')
            </form>
        </div>
    </div>
@endsection
