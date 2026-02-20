@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="m-0">My Notebooks</h3>
        <a href="{{ route('notebooks.create') }}" class="btn btn-dark btn-sm">Create Notebook</a>
    </div>

    @if($notebooks->isEmpty())
        <div class="alert alert-light border">
            No notebooks yet.
        </div>
    @else
        <table class="table table-bordered table-striped">
            <thead>
            <tr>
                <th>Name</th>
                <th>Visibility</th>
                <th>Sources</th>
                <th>Updated</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            @foreach($notebooks as $notebook)
                <tr>
                    <td>{{ $notebook->name }}</td>
                    <td><span class="badge badge-secondary text-uppercase">{{ $notebook->visibility }}</span></td>
                    <td>{{ $notebook->sources_count }}</td>
                    <td>{{ $notebook->updated_at->diffForHumans() }}</td>
                    <td class="d-flex">
                        <a href="{{ route('notebooks.show', ['notebook' => $notebook->id]) }}" class="btn btn-outline-dark btn-sm mr-2">Open</a>
                        <a href="{{ route('notebooks.edit', ['notebook' => $notebook->id]) }}" class="btn btn-outline-primary btn-sm mr-2">Edit</a>
                        <form action="{{ route('notebooks.destroy', ['notebook' => $notebook->id]) }}" method="post">
                            @csrf
                            @method('delete')
                            <button type="submit" class="btn btn-outline-danger btn-sm">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endif
@endsection
