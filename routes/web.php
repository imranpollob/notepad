<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', 'HomeController@newNote')->name('home');
Route::post('/', 'HomeController@newNote');

Auth::routes(['register' => false]);

Route::get('/notes', 'HomeController@notes')->middleware('auth')->name('notes');

Route::get('/auth/redirect/{provider}', 'SocialController@redirect');
Route::get('/callback/{provider}', 'SocialController@callback');

Route::get('{url}', 'NotesController@index');
Route::post('{url}', 'NotesController@store');
Route::delete('{url}', 'NotesController@destroy');
