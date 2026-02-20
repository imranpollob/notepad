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

Route::middleware('auth')->group(function () {
    Route::get('/notebooks', 'NotebookController@index')->name('notebooks.index');
    Route::get('/notebooks/create', 'NotebookController@create')->name('notebooks.create');
    Route::post('/notebooks', 'NotebookController@store')->name('notebooks.store');
    Route::get('/notebooks/{notebook}', 'NotebookController@show')->name('notebooks.show');
    Route::get('/notebooks/{notebook}/edit', 'NotebookController@edit')->name('notebooks.edit');
    Route::put('/notebooks/{notebook}', 'NotebookController@update')->name('notebooks.update');
    Route::delete('/notebooks/{notebook}', 'NotebookController@destroy')->name('notebooks.destroy');
    Route::get('/notebooks/{notebook}/chat', 'NotebookChatController@show')->name('notebooks.chat');
    Route::post('/notebooks/{notebook}/chat', 'NotebookChatController@ask')->name('notebooks.chat.ask');
    Route::delete('/notebooks/{notebook}/chat/{conversation}', 'NotebookChatController@destroyConversation')->name('notebooks.chat.destroy');
    Route::post('/notebooks/{notebook}/share-token', 'NotebookController@regenerateShareToken')->name('notebooks.share-token');

    Route::post('/notebooks/{notebook}/sources/note', 'NotebookSourceController@attachNote')->name('notebooks.sources.note');
    Route::post('/notebooks/{notebook}/sources/file', 'NotebookSourceController@attachFile')->name('notebooks.sources.file');
    Route::post('/notebooks/{notebook}/sources/url', 'NotebookSourceController@attachUrl')->name('notebooks.sources.url');
    Route::get('/notebooks/{notebook}/sources/{source}/download', 'NotebookSourceController@download')->name('notebooks.sources.download');
    Route::post('/notebooks/{notebook}/sources/{source}/retry', 'NotebookSourceController@retry')->name('notebooks.sources.retry');
    Route::delete('/notebooks/{notebook}/sources/{source}', 'NotebookSourceController@destroy')->name('notebooks.sources.destroy');
});

Route::get('/shared/notebooks/{token}', 'NotebookController@shared')->name('notebooks.shared');

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
