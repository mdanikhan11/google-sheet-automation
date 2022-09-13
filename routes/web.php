<?php

use App\Http\Controllers\GoogleController;
use App\Http\Controllers\SheetController;
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
    return view('index');
});

// Route::get('/callback', [SheetController::class,'index']);
// Route::get('/google/callback', [SheetController::class,'handleGoogleCallback']);
// Route::get('/create/{title}', [SheetController::class,'create']);

// Route::get('/google/login/url', [SheetController::class,'getAuthUrl']);

// Route::post('/google/auth/login', [SheetController::class,'postLogin']);

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');



Route::get('google/login/url', [GoogleController::class,'getAuthUrl'])->name('getAuthUrl');
Route::get('/google/callback', [GoogleController::class,'postLogin']);
Route::post('/google/create-sheet', [GoogleController::class,'create_sheet'])->name('create_sheet');

Route::get('/google/read-sheat', [GoogleController::class,'readGoogleSheet']);
Route::get('/google/save-data-to-sheet', [GoogleController::class,'saveDataToSheet']);



// Route::post('google/auth/login', [GoogleController::class,'postLogin']);

Route::get('google/drive', [GoogleController::class,'getDrive']);
