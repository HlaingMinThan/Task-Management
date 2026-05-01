<?php

use App\Http\Controllers\ColumnController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard')->name('home');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [ProjectController::class, 'index'])->name('dashboard');
    Route::resource('projects', ProjectController::class)->only(['store', 'update', 'destroy', 'show']);

    Route::post('/projects/{project}/columns', [ColumnController::class, 'store'])->name('columns.store');
    Route::patch('/projects/{project}/columns/{column}', [ColumnController::class, 'update'])->name('columns.update');
    Route::delete('/projects/{project}/columns/{column}', [ColumnController::class, 'destroy'])->name('columns.destroy');
    Route::post('/projects/{project}/columns/{column}/tasks', [TaskController::class, 'store'])->name('tasks.store');
    Route::patch('/projects/{project}/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
    Route::delete('/projects/{project}/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');
    Route::post('/projects/{project}/tasks/reorder', [TaskController::class, 'reorder'])->name('tasks.reorder');
});

require __DIR__.'/auth.php';
