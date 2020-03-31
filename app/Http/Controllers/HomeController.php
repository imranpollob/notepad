<?php

namespace App\Http\Controllers;

use App\Notes;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class HomeController extends Controller
{

    public function newNote()
    {
        $random_string = '';

        while (true) {
            $random_string = $this->randomString();

            if (!Notes::where('url', $random_string)->first()) {
                break;
            }
        }

        return redirect($random_string);

    }

    private function randomString(): string
    {
        return Str::random('8');
    }

    public function dashboard()
    {
        return view('dashboard')->with([
            'totalNotes' => Notes::count(),
            'totalUser' => User::count(),
            'nonEmptyNotes' => Notes::where('data', '!=', '')->count(),
            'notesPerUser' => DB::table('notes')
                ->join('users', 'users.id', '=', 'notes.owner_id')
                ->select('notes.id', 'owner_id', DB::raw('count(*) as notes'), 'users.name', 'users.email')
                ->groupBy('owner_id')
                ->having('owner_id', '>', 0)
                ->orderBy('notes', 'desc')
                ->get(),
        ]);
    }

    public function delete()
    {
        $notes = Notes::where('data', null)->delete();

        return back()->with('success', "$notes empty notes are deleted successfully.");
    }

}
