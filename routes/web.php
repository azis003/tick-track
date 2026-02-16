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
// ROUTES UNTUK AUTHENTICATED USERS
// ==============================================

Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ==============================================
    // MASTER DATA - Permissions CRUD
    // ==============================================
    Route::resource('permissions', PermissionController::class)
        ->except(['show'])
        ->names([
            'index' => 'permissions.index',
            'create' => 'permissions.create',
            'store' => 'permissions.store',
            'edit' => 'permissions.edit',
            'update' => 'permissions.update',
            'destroy' => 'permissions.destroy',
        ]);

    // ==============================================
    // MASTER DATA - Roles CRUD
    // ==============================================
    Route::resource('roles', RoleController::class)
        ->except(['show'])
        ->names([
            'index' => 'roles.index',
            'create' => 'roles.create',
            'store' => 'roles.store',
            'edit' => 'roles.edit',
            'update' => 'roles.update',
            'destroy' => 'roles.destroy',
        ]);

    // ==============================================
    // MASTER DATA - Users CRUD
    // ==============================================
    Route::resource('users', App\Http\Controllers\Admin\UserController::class)
        ->except(['show'])
        ->names([
            'index' => 'users.index',
            'create' => 'users.create',
            'store' => 'users.store',
            'edit' => 'users.edit',
            'update' => 'users.update',
            'destroy' => 'users.destroy',
        ]);

    // ==============================================
    // MASTER DATA - Units CRUD
    // ==============================================
    Route::resource('units', App\Http\Controllers\Admin\UnitController::class)
        ->except(['show'])
        ->names([
            'index' => 'units.index',
            'create' => 'units.create',
            'store' => 'units.store',
            'edit' => 'units.edit',
            'update' => 'units.update',
            'destroy' => 'units.destroy',
        ]);

    // ==============================================
    // MASTER DATA - Categories CRUD
    // ==============================================
    Route::resource('categories', App\Http\Controllers\Admin\CategoryController::class)
        ->except(['show'])
        ->names([
            'index' => 'categories.index',
            'create' => 'categories.create',
            'store' => 'categories.store',
            'edit' => 'categories.edit',
            'update' => 'categories.update',
            'destroy' => 'categories.destroy',
        ]);

    // ==============================================
    // MASTER DATA - Priorities CRUD
    // ==============================================
    Route::resource('priorities', App\Http\Controllers\Admin\PriorityController::class)
        ->except(['show'])
        ->names([
            'index' => 'priorities.index',
            'create' => 'priorities.create',
            'store' => 'priorities.store',
            'edit' => 'priorities.edit',
            'update' => 'priorities.update',
            'destroy' => 'priorities.destroy',
        ]);

    // ==============================================
    // TICKETS CRUD
    // ==============================================
    Route::prefix('tickets')->group(function () {
        // Phase 1: Basic CRUD
        Route::get('/', [TicketController::class, 'index'])->name('tickets.index');
        Route::get('/my-tickets', [TicketController::class, 'myTickets'])->name('tickets.my-tickets');
        Route::get('/task-queue', [TicketController::class, 'taskQueue'])->name('tickets.task-queue');
        Route::get('/unit', [TicketController::class, 'unitTickets'])->name('tickets.unit');
        Route::get('/create', [TicketController::class, 'create'])->name('tickets.create');
        Route::post('/', [TicketController::class, 'store'])->name('tickets.store');

        // Phase 2: Triage & Assignment (static routes before wildcard)
        Route::get('/triage', [TicketController::class, 'triage'])->name('tickets.triage');
        Route::get('/assigned', [TicketController::class, 'assignedTickets'])->name('tickets.assigned');

        // Wildcard routes (must be after static routes)
        Route::get('/{ticket}', [TicketController::class, 'show'])->name('tickets.show');
        Route::post('/{ticket}/triage', [TicketController::class, 'processTriage'])->name('tickets.process-triage');
        Route::post('/{ticket}/assign', [TicketController::class, 'assign'])->name('tickets.assign');
        Route::post('/{ticket}/self-handle', [TicketController::class, 'selfHandle'])->name('tickets.self-handle');
        Route::post('/{ticket}/accept', [TicketController::class, 'accept'])->name('tickets.accept');
        Route::post('/{ticket}/return', [TicketController::class, 'returnTicket'])->name('tickets.return');

        // Phase 3: Work & Resolve
        Route::post('/{ticket}/pending', [TicketController::class, 'setPending'])->name('tickets.pending');
        Route::post('/{ticket}/resume', [TicketController::class, 'resume'])->name('tickets.resume');
        Route::post('/{ticket}/request-approval', [TicketController::class, 'requestApproval'])->name('tickets.request-approval');
        Route::post('/{ticket}/approve', [TicketController::class, 'approve'])->name('tickets.approve');
        Route::post('/{ticket}/reject', [TicketController::class, 'reject'])->name('tickets.reject');
        Route::post('/{ticket}/resolve', [TicketController::class, 'resolve'])->name('tickets.resolve');

        // Phase 4: Close & Reopen
        Route::post('/{ticket}/close', [TicketController::class, 'close'])->name('tickets.close');
        Route::post('/{ticket}/reopen', [TicketController::class, 'reopen'])->name('tickets.reopen');

        // Comments
        Route::post('/{ticket}/comments', [\App\Http\Controllers\Admin\TicketCommentController::class, 'store'])->name('tickets.comments.store');
    });

    // Comments (outside ticket prefix for update/delete)
    Route::put('/comments/{comment}', [\App\Http\Controllers\Admin\TicketCommentController::class, 'update'])->name('comments.update');
    Route::delete('/comments/{comment}', [\App\Http\Controllers\Admin\TicketCommentController::class, 'destroy'])->name('comments.destroy');

    // ==============================================
    // ATTACHMENTS
    // ==============================================
    Route::get('/attachments/{attachment}/download', [TicketAttachmentController::class, 'download'])
        ->name('attachments.download');
});