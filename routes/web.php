<?php

use App\Http\Controllers\ColumnController;
use App\Http\Controllers\ProjectController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard')->name('home');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [ProjectController::class, 'index'])->name('dashboard');
    Route::resource('projects', ProjectController::class)->only(['store', 'update', 'destroy', 'show']);

    Route::post('/projects/{project}/columns', [ColumnController::class, 'store'])->name('columns.store');
    Route::patch('/projects/{project}/columns/{column}', [ColumnController::class, 'update'])->name('columns.update');
    Route::delete('/projects/{project}/columns/{column}', [ColumnController::class, 'destroy'])->name('columns.destroy');
    Route::post('/projects/{project}/columns/reorder', [ColumnController::class, 'reorder'])->name('columns.reorder');
});

require __DIR__.'/auth.php';
