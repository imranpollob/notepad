@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            @if (session('status'))
                <div class="alert alert-success" role="alert">
                    {{ session('status') }}
                </div>
            @endif

            <h3 class="user-note-heading">{{ Auth::user()->name }}'s Notes</h3>

            <table class="table table-bordered table-striped table-hover">
                <thead>
                <tr>
                    <th>Note</th>
                    <th style="width: 30%">Actions</th>
                </tr>
                </thead>
                <tbody>
                @foreach($notes as $note)
                    <tr>
                        <td><a href="/{{ $note->url }}" target="_blank">{{ $note->title ?? $note->url }}</a></td>
                        <td class="d-flex action-buttons">
                            <form action="/{{ $note->url }}" method="post">
                                @method('delete')
                                @csrf
                                <button type="submit" class="btn btn-danger btn-sm" data-toggle="tooltip" data-placement="top"  title="Delete note"><i class="fa fa-trash"></i></button>
                            </form>
{{--                            <button type="button" class="btn btn-primary btn-sm" title="Copy link to clipboard"><i class="fa fa-copy"></i></button>--}}
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
