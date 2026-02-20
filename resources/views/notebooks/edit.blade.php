@extends('layouts.app')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <h3 class="mb-3">Edit Notebook</h3>

            <form action="{{ route('notebooks.update', ['notebook' => $notebook->id]) }}" method="post">
                @include('notebooks._form')
            </form>
        </div>
    </div>
@endsection
