<?php

namespace App\Http\Controllers;

use App\Notes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class NotesController extends Controller
{
    public function notes()
    {
        return view('notes', ['notes' => Notes::where('owner_id', Auth::id())->latest('updated_at')->get()]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $url)
    {
        $url = $this->normalizeUrl($url);

        if (!$url) {
            return redirect()->route('home');
        }

        $note = Notes::where('url', $url)->first();

        if (!$note) {
            Notes::create([
                'url' => $url,
                'owner_id' => Auth::id() ?? null
            ]);

            return redirect()->route('note.show', ['url' => $url]);
        }

        if ($note->password && !$this->passwordMatches($note->password, (string) session('note_password'))) {
            return view('password', ['note' => $note]);
        }

        return view('note', [
            'note' => $note,
            'canEdit' => $this->canMutate($note),
        ]);
    }

    public function legacyRedirect(string $url)
    {
        $url = $this->normalizeUrl($url);

        if (!$url) {
            return redirect()->route('home');
        }

        return redirect()->route('note.show', ['url' => $url]);
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
    public function store(Request $request, string $url)
    {
        $url = $this->normalizeUrl($url);
        $note = Notes::where('url', $url)->first();

        if (!$note) {
            $note = Notes::create([
                'url' => $url,
                'owner_id' => Auth::id() ?? null,
            ]);
        }

        $this->ensureCanMutate($note);

        $note->update([
            'data' => $request->data,
            'title' => $request->title,
        ]);

        return response()->noContent();
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
    public function update(Request $request, string $url)
    {
        $url = $this->normalizeUrl($url);
        $note = Notes::where('url', $url)->first();

        if (!$note) {
            return redirect()->route('home');
        }

        $this->ensureCanMutate($note);

        if ($request->has('update-password')) {
            $password = (string) $request->password;

            if ($password !== '') {
                $password = Hash::make($password);
            }

            $note->update([
                'password' => $password ?: null,
            ]);
        }

        if ($request->has('delete-password')) {
            $note->update([
                'password' => null,
            ]);
        }

        return redirect()->route('note.show', ['url' => $url]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function updateAuthorized(Request $request)
    {
        $url = $this->normalizeUrl((string) $request->url);

        if (!$url) {
            return redirect()->back();
        }

        $note = Notes::where('url', $url)
            ->where('owner_id', Auth::id())
            ->first();

        if (!$note) {
            abort(403);
        }

        if ($request->has('update-password')) {
            $password = (string) $request->password;

            if ($password !== '') {
                $password = Hash::make($password);
            }

            $note->update([
                'password' => $password ?: null,
            ]);
        }

        if ($request->has('delete-password')) {
            $note->update([
                'password' => null,
            ]);
        }

        return redirect()->back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, string $url)
    {
        $note = Notes::where('url', $this->normalizeUrl($url))->first();

        if (!$note) {
            return redirect('/notes');
        }

        $this->ensureCanMutate($note);

        $note->delete();

        return redirect('/notes');
    }

    /**
     * @param Request $request
     * @param $url
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function password(Request $request, $url)
    {
        $note = Notes::where('url', $this->normalizeUrl($url))->first();

        if (!$note) {
            return redirect()->route('home');
        }

        if ($note->password && !$this->passwordMatches($note->password, (string) $request->password)) {
            return redirect()->route('note.show', ['url' => $note->url])->with('error', 'Oops! Password is not right.');
        }

        if ($note->password && !$this->isHashedValue($note->password)) {
            $note->update([
                'password' => Hash::make((string) $request->password),
            ]);
        }

        session(['note_password' => $request->password]);

        return redirect()->route('note.show', ['url' => $note->url]);
    }

    private function normalizeUrl(string $url): string
    {
        return preg_replace("/[^a-zA-Z0-9]+/", '', $url);
    }

    private function ensureCanMutate(Notes $note): void
    {
        if (!$this->canMutate($note)) {
            abort(403);
        }
    }

    private function canMutate(Notes $note): bool
    {
        if ($note->owner_id && !$this->isOwner($note)) {
            return false;
        }

        if ($note->password && !$this->isOwner($note)) {
            return $this->passwordMatches($note->password, (string) session('note_password'));
        }

        return true;
    }

    private function isOwner(Notes $note): bool
    {
        return Auth::check() && (int) $note->owner_id === (int) Auth::id();
    }

    private function passwordMatches(string $storedPassword, string $inputPassword): bool
    {
        if ($inputPassword === '') {
            return false;
        }

        if ($this->isHashedValue($storedPassword)) {
            return Hash::check($inputPassword, $storedPassword);
        }

        return hash_equals($storedPassword, $inputPassword);
    }

    private function isHashedValue(string $value): bool
    {
        $hashInfo = Hash::info($value);

        return ($hashInfo['algo'] ?? 0) !== 0;
    }
}
