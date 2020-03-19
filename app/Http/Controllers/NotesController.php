<?php

namespace App\Http\Controllers;

use App\Notes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class NotesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($url)
    {

        $note = Notes::where('url', $url)->first();

        if (!$note) {

            $new_url = preg_replace("/[^a-zA-Z0-9]+/", '', $url);

            if ($new_url !== $url) {
                return redirect($new_url);
            }

            Notes::create([
                'url' => $url,
                'owner_id' => Auth::id() ?? null
            ]);

            return redirect($url);
        }

        return view('note', ['note' => $note]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        Notes::where('url', $request->path())
            ->update([
                'data' => $request->data,
                'title' => $request->title,
            ]);

        return redirect($request->url());
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Notes $notes
     * @return \Illuminate\Http\Response
     */
    public function show(Notes $notes)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Notes $notes
     * @return \Illuminate\Http\Response
     */
    public function edit(Notes $notes)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        if ($request->has('update-password')) {
            Notes::where('url', $request->path())
                ->update([
                    'password' => $request->password,
                ]);
        }

        if ($request->has('delete-password')) {
            Notes::where('url', $request->path())
                ->update([
                    'password' => null,
                ]);
        }

        return redirect($request->url());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        Notes::where('url', $request->path())
            ->delete();

        return redirect('/notes');
    }
}
