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

Route::get('/', function () {
    return view('contact');
})->name('home');

Route::match(['get', 'post'], '/botman', 'App\Http\Controllers\BotManController@handle');
Route::post('/landing', 'App\Http\Controllers\MyContactForm@send');