<?php

declare(strict_types=1);

use App\Http\Controllers\UdemyAuthController;
use App\Http\Controllers\UdemyProjectController;
use App\Http\Controllers\UdemyTaskController;
use Illuminate\Support\Facades\Route;

// Udemy Auth...
Route::post('udemy-auth/register', [UdemyAuthController::class, 'register'])->name('udemy-auth.register');
Route::post('udemy-auth/login', [UdemyAuthController::class, 'login'])->name('udemy-auth.login');
Route::middleware('auth:sanctum')->post('udemy-auth/logout', [UdemyAuthController::class, 'logout'])->name('udemy-auth.logout');

Route::middleware('auth:sanctum')->group(function (): void {
    // Udemy Projects...
    Route::get('udemy-projects', [UdemyProjectController::class, 'index'])->name('udemy-projects.index');
    Route::post('udemy-projects', [UdemyProjectController::class, 'store'])->name('udemy-projects.store');
    Route::get('udemy-projects/{udemyProject}', [UdemyProjectController::class, 'show'])->name('udemy-projects.show');
    Route::patch('udemy-projects/{udemyProject}', [UdemyProjectController::class, 'update'])->name('udemy-projects.update');
    Route::delete('udemy-projects/{udemyProject}', [UdemyProjectController::class, 'destroy'])->name('udemy-projects.destroy');

    // Udemy Tasks...
    Route::get('udemy-tasks', [UdemyTaskController::class, 'index'])->name('udemy-tasks.index');
    Route::post('udemy-tasks', [UdemyTaskController::class, 'store'])->name('udemy-tasks.store');
    Route::get('udemy-tasks/{udemyTask}', [UdemyTaskController::class, 'show'])->name('udemy-tasks.show');
    Route::patch('udemy-tasks/{udemyTask}', [UdemyTaskController::class, 'update'])->name('udemy-tasks.update');
    Route::delete('udemy-tasks/{udemyTask}', [UdemyTaskController::class, 'destroy'])->name('udemy-tasks.destroy');
});
