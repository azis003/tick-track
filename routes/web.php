<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PermissionController;

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

// ==============================================
// ROUTES UNTUK ADMIN (Authenticated Users)
// ==============================================

Route::middleware(['auth'])->prefix('admin')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

    // ==============================================
    // MASTER DATA - Permissions CRUD
    // ==============================================
    Route::resource('permissions', PermissionController::class)
        ->except(['show'])
        ->names([
            'index' => 'admin.permissions.index',
            'create' => 'admin.permissions.create',
            'store' => 'admin.permissions.store',
            'edit' => 'admin.permissions.edit',
            'update' => 'admin.permissions.update',
            'destroy' => 'admin.permissions.destroy',
        ]);
});