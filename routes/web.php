<?php

use Illuminate\Support\Facades\Route;

Route::get('/', 'HomeController@newNote')->name('home');
Route::post('/', 'HomeController@newNote');

Auth::routes(['register' => false]);

Route::get('/notes', 'HomeController@notes')->middleware('auth')->name('notes');

Route::get('/auth/redirect/{provider}', 'SocialController@redirect');
Route::get('/callback/{provider}', 'SocialController@callback');

Route::get('{url}', 'NotesController@index');
Route::post('{url}', 'NotesController@store');
Route::delete('{url}', 'NotesController@destroy');
