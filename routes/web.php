<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;

Route::get('/', function () {
    return view('welcome');
});

// ==============================================
// ROUTES UNTUK AUTH
// ==============================================

// route login
Route::get('/login', [LoginController::class, 'index'])
    ->name('login')
    ->middleware('guest');

// route login store
Route::post('/login', [LoginController::class, 'store'])
    ->name('login.store')
    ->middleware('guest');

// route logout
Route::post('/logout', [LoginController::class, 'logout'])
    ->name('logout')
    ->middleware('auth');