<?php

use Illuminate\Support\Facades\Route;

Route::get('/', 'HomeController@newNote')->name('home');
Route::post('/', 'HomeController@newNote');

Auth::routes(['register' => false]);

Route::get('/notes', 'NotesController@notes')->middleware('auth')->name('notes');
Route::put('/notes', 'NotesController@updateAuthorized')->middleware('auth');

Route::get('/auth/redirect/{provider}', 'SocialController@redirect');
Route::get('/callback/{provider}', 'SocialController@callback');

Route::get('/dashboard', 'HomeController@dashboard')->middleware(['auth', 'admin'])->name('dashboard');

Route::get('{url}', 'NotesController@index');
Route::post('{url}', 'NotesController@store');
Route::post('{url}/password', 'NotesController@password');
Route::put('{url}', 'NotesController@update');
Route::delete('{url}', 'NotesController@destroy');
