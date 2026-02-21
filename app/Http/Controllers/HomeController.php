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
    public function home()
    {
        return view('welcome');
    }

    public function newNote()
    {
        $random_string = $this->randomUniqueUrl();

        return redirect()->route('note.show', ['url' => $random_string]);
    }

    public function storeFromHome(Request $request)
    {
        $data = $request->validate([
            'title' => 'nullable|string|max:255',
            'data' => 'required|string|max:5000000',
        ]);

        $sanitizedHtml = $this->sanitizeRichText((string) $data['data']);
        $plainContent = trim(strip_tags($sanitizedHtml));
        if ($plainContent === '') {
            return redirect()->route('home')
                ->withErrors(['data' => 'Please write something before creating a cloud note.'])
                ->withInput();
        }

        $note = Notes::create([
            'url' => $this->randomUniqueUrl(),
            'title' => $data['title'] ?? null,
            'data' => $sanitizedHtml,
            'owner_id' => Auth::id() ?? null,
        ]);

        return redirect()->route('note.show', ['url' => $note->url])
            ->with('success', 'Your note was saved to cloud.');
    }

    private function randomString(): string
    {
        return Str::random('8');
    }

    private function randomUniqueUrl(): string
    {
        while (true) {
            $randomString = $this->randomString();
            if (!Notes::where('url', $randomString)->exists()) {
                return $randomString;
            }
        }
    }

    private function sanitizeRichText(string $html): string
    {
        $allowedTags = '<p><br><strong><em><u><s><a><ul><ol><li><blockquote><pre><code><h1><h2><h3><img>';
        $clean = strip_tags($html, $allowedTags);
        return trim($clean);
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
