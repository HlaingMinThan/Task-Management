<?php

use App\Http\Controllers\ColumnController;
use App\Http\Controllers\InviteController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectMemberController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard')->name('home');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [ProjectController::class, 'index'])->name('dashboard');
    Route::resource('projects', ProjectController::class)->only(['store', 'update', 'destroy', 'show']);

    Route::post('/projects/{project}/columns', [ColumnController::class, 'store'])->name('columns.store');
    Route::patch('/projects/{project}/columns/{column}', [ColumnController::class, 'update'])->name('columns.update');
    Route::delete('/projects/{project}/columns/{column}', [ColumnController::class, 'destroy'])->name('columns.destroy');
    Route::post('/projects/{project}/columns/reorder', [ColumnController::class, 'reorder'])->name('columns.reorder');
    Route::post('/projects/{project}/columns/{column}/tasks', [TaskController::class, 'store'])->name('tasks.store');
    Route::patch('/projects/{project}/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
    Route::delete('/projects/{project}/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');
    Route::post('/projects/{project}/tasks/reorder', [TaskController::class, 'reorder'])->name('tasks.reorder');

    // Project member routes
    Route::get('/projects/{project}/members', [ProjectMemberController::class, 'show'])->name('members.show');
    Route::post('/projects/{project}/members', [ProjectMemberController::class, 'store'])->name('members.store');
    Route::patch('/projects/{project}/members/{member}', [ProjectMemberController::class, 'update'])->name('members.update');
    Route::delete('/projects/{project}/members/{member}', [ProjectMemberController::class, 'destroy'])->name('members.destroy');
    Route::delete('/projects/{project}/invites/{invite}', [ProjectMemberController::class, 'cancelInvite'])->name('invites.cancel');
});

// Public invite routes
Route::get('/invites/{token}', [InviteController::class, 'show'])->name('invites.show');
Route::post('/invites/{token}/accept', [InviteController::class, 'accept'])->name('invites.accept');

require __DIR__.'/auth.php';
