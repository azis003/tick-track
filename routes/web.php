<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\RoleController;

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

    // ==============================================
    // MASTER DATA - Roles CRUD
    // ==============================================
    Route::resource('roles', RoleController::class)
        ->except(['show'])
        ->names([
            'index' => 'admin.roles.index',
            'create' => 'admin.roles.create',
            'store' => 'admin.roles.store',
            'edit' => 'admin.roles.edit',
            'update' => 'admin.roles.update',
            'destroy' => 'admin.roles.destroy',
        ]);

    // ==============================================
    // MASTER DATA - Users CRUD
    // ==============================================
    Route::resource('users', App\Http\Controllers\Admin\UserController::class)
        ->except(['show'])
        ->names([
            'index' => 'admin.users.index',
            'create' => 'admin.users.create',
            'store' => 'admin.users.store',
            'edit' => 'admin.users.edit',
            'update' => 'admin.users.update',
            'destroy' => 'admin.users.destroy',
        ]);

    // ==============================================
    // MASTER DATA - Units CRUD
    // ==============================================
    Route::resource('units', App\Http\Controllers\Admin\UnitController::class)
        ->except(['show'])
        ->names([
            'index' => 'admin.units.index',
            'create' => 'admin.units.create',
            'store' => 'admin.units.store',
            'edit' => 'admin.units.edit',
            'update' => 'admin.units.update',
            'destroy' => 'admin.units.destroy',
        ]);

    // ==============================================
    // MASTER DATA - Categories CRUD
    // ==============================================
    Route::resource('categories', App\Http\Controllers\Admin\CategoryController::class)
        ->except(['show'])
        ->names([
            'index' => 'admin.categories.index',
            'create' => 'admin.categories.create',
            'store' => 'admin.categories.store',
            'edit' => 'admin.categories.edit',
            'update' => 'admin.categories.update',
            'destroy' => 'admin.categories.destroy',
        ]);

    // ==============================================
    // MASTER DATA - Priorities CRUD
    // ==============================================
    Route::resource('priorities', App\Http\Controllers\Admin\PriorityController::class)
        ->except(['show'])
        ->names([
            'index' => 'admin.priorities.index',
            'create' => 'admin.priorities.create',
            'store' => 'admin.priorities.store',
            'edit' => 'admin.priorities.edit',
            'update' => 'admin.priorities.update',
            'destroy' => 'admin.priorities.destroy',
        ]);
});