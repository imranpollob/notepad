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
        <div class="card">
            <div class="card-body">
                <h5 class="card-title m-0">Filled Notes <span class="float-right">{{ $nonEmptyNotes }}</span></h5>
            </div>
        </div>
    </div>

    <div class="row mt-4 justify-content-center">
        <div class="col">
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
    </div>

    <div class="row mt-4">
        <div class="col d-flex justify-content-end">
            <button type="button" class="btn btn-danger btn-sm deleteNoteBtn" data-toggle="modal" data-target="#deleteEmptyNotes">
                Delete Empty Notes
            </button>
        </div>
    </div>


    <!-- Delete All Empty Notes Modal -->
    <div class="modal fade" id="deleteEmptyNotes" tabindex="-1" role="dialog" aria-labelledby="deleteEmptyNotesLabel"
         aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteEmptyNotesLabel">Are you sure to delete <span class="text-danger">all</span> empty notes?</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-footer">
                    <form action="/dashboard" method="post">
                        @method('delete')
                        @csrf
                        <button type="submit" class="btn btn-danger">YES</button>
                    </form>
                    <button type="button" class="btn btn-success" data-dismiss="modal">NO</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('javascript')

@endsection
