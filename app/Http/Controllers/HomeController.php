<?php

namespace App\Http\Controllers;

use App\Notes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class HomeController extends Controller
{
    public function notes()
    {
        return view('home', ['notes' => Notes::where('owner_id', Auth::id())->get()]);
    }

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

    private function randomString()
    {
        return Str::random('8');
    }

}
