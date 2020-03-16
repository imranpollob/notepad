@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            @if (session('status'))
                <div class="alert alert-success" role="alert">
                    {{ session('status') }}
                </div>
            @endif

            <table class="table table-bordered table-striped table-hover">
                <thead>
                <tr>
                    <th scope="col">Note</th>
                    <th scope="col">Delete</th>
                </tr>
                </thead>
                <tbody>
                @foreach($notes as $note)
                    <tr>
                        <td><a href="/{{ $note->url }}" target="_blank">{{ $note->url }}</a></td>
                        <td>
                            <form action="/{{ $note->url }}" method="post">
                                @method('delete')
                                @csrf
                                <button type="submit" class="btn btn-danger">Delete Note</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
