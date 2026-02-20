<?php

use Illuminate\Support\Facades\Route;

Route::get('/', 'HomeController@newNote')->name('home');

Auth::routes([
    'register' => false,
    'reset' => false,
    'confirm' => false,
]);

Route::get('/notes', 'NotesController@notes')->middleware('auth')->name('notes');
Route::put('/notes', 'NotesController@updateAuthorized')->middleware('auth');

Route::get('/auth/redirect/{provider}', 'SocialController@redirect');
Route::get('/callback/{provider}', 'SocialController@callback');

Route::get('/dashboard', 'HomeController@dashboard')->middleware(['auth', 'admin'])->name('dashboard');
Route::delete('/dashboard', 'HomeController@delete')->middleware(['auth', 'admin'])->name('dashboard');

Route::prefix('n')->group(function () {
    Route::get('{url}', 'NotesController@index')
        ->where('url', '[A-Za-z0-9]+')
        ->name('note.show');
    Route::post('{url}', 'NotesController@store')
        ->where('url', '[A-Za-z0-9]+')
        ->name('note.store');
    Route::post('{url}/password', 'NotesController@password')
        ->where('url', '[A-Za-z0-9]+')
        ->name('note.password');
    Route::put('{url}', 'NotesController@update')
        ->where('url', '[A-Za-z0-9]+')
        ->name('note.update');
    Route::delete('{url}', 'NotesController@destroy')
        ->where('url', '[A-Za-z0-9]+')
        ->name('note.destroy');
});

Route::get('{url}', 'NotesController@legacyRedirect')->where('url', '[A-Za-z0-9]+');
