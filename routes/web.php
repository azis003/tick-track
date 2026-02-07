<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\TicketController;
use App\Http\Controllers\Admin\TicketAttachmentController;

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

    // ==============================================
    // TICKETS CRUD
    // ==============================================
    Route::prefix('tickets')->group(function () {
        // Phase 1: Basic CRUD
        Route::get('/', [TicketController::class, 'index'])->name('admin.tickets.index');
        Route::get('/my-tickets', [TicketController::class, 'myTickets'])->name('admin.tickets.my-tickets');
        Route::get('/unit', [TicketController::class, 'unitTickets'])->name('admin.tickets.unit');
        Route::get('/create', [TicketController::class, 'create'])->name('admin.tickets.create');
        Route::post('/', [TicketController::class, 'store'])->name('admin.tickets.store');

        // Phase 2: Triage & Assignment (static routes before wildcard)
        Route::get('/triage', [TicketController::class, 'triage'])->name('admin.tickets.triage');
        Route::get('/assigned', [TicketController::class, 'assignedTickets'])->name('admin.tickets.assigned');

        // Wildcard routes (must be after static routes)
        Route::get('/{ticket}', [TicketController::class, 'show'])->name('admin.tickets.show');
        Route::post('/{ticket}/triage', [TicketController::class, 'processTriage'])->name('admin.tickets.process-triage');
        Route::post('/{ticket}/assign', [TicketController::class, 'assign'])->name('admin.tickets.assign');
        Route::post('/{ticket}/self-handle', [TicketController::class, 'selfHandle'])->name('admin.tickets.self-handle');
        Route::post('/{ticket}/accept', [TicketController::class, 'accept'])->name('admin.tickets.accept');
        Route::post('/{ticket}/return', [TicketController::class, 'returnTicket'])->name('admin.tickets.return');

        // Phase 3: Work & Resolve
        Route::post('/{ticket}/pending', [TicketController::class, 'setPending'])->name('admin.tickets.pending');
        Route::post('/{ticket}/resume', [TicketController::class, 'resume'])->name('admin.tickets.resume');
        Route::post('/{ticket}/request-approval', [TicketController::class, 'requestApproval'])->name('admin.tickets.request-approval');
        Route::post('/{ticket}/resolve', [TicketController::class, 'resolve'])->name('admin.tickets.resolve');

        // Phase 4: Close & Reopen
        Route::post('/{ticket}/close', [TicketController::class, 'close'])->name('admin.tickets.close');
        Route::post('/{ticket}/reopen', [TicketController::class, 'reopen'])->name('admin.tickets.reopen');

        // Comments
        Route::post('/{ticket}/comments', [\App\Http\Controllers\Admin\TicketCommentController::class, 'store'])->name('admin.tickets.comments.store');
    });

    // Comments (outside ticket prefix for update/delete)
    Route::put('/comments/{comment}', [\App\Http\Controllers\Admin\TicketCommentController::class, 'update'])->name('admin.comments.update');
    Route::delete('/comments/{comment}', [\App\Http\Controllers\Admin\TicketCommentController::class, 'destroy'])->name('admin.comments.destroy');

    // ==============================================
    // ATTACHMENTS
    // ==============================================
    Route::get('/attachments/{attachment}/download', [TicketAttachmentController::class, 'download'])
        ->name('admin.attachments.download');
});