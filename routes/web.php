<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

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
    return view('welcome');
});

// Route::get('/', [App\Http\Controllers\ArticleController::class, 'index']);

// Route::resource('/articles', App\Http\Controllers\ArticleController::class)->middleware('auth');

Route::group(['middleware' => ['auth']], function () {
    Route::get('/articles', App\Http\Controllers\ArticleController::class);
});

// Route::redirect('/', route('articles.index'), 302);

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::get('/bs/index', function () {
    return view('bs.index');
});
