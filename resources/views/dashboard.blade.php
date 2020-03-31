@extends('layouts.app')

@section('content')

    <div class="row justify-content-center">
        <h3 class="user-note-heading">Dashboard</h3>
    </div>

    <div class="card-deck">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title m-0">Total Notes <span class="float-right">{{ $totalNotes }}</span></h5>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <h5 class="card-title m-0">Total Users <span class="float-right">{{ $totalUser }}</span></h5>
            </div>
        </div>
    </div>

    <div class="row mt-4 justify-content-center">

        <table class="table table-bordered table-hover">
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Notes</th>
            </tr>

            @foreach($notesPerUser as $npu)
                <tr>
                    <td>{{ $npu->name }}</td>
                    <td>{{ $npu->email }}</td>
                    <td>{{ $npu->notes }}</td>
                </tr>
            @endforeach
        </table>
    </div>

@endsection

@section('javascript')

@endsection
