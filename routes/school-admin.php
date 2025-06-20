<?php

use App\Http\Controllers\System\CourseController;
use Illuminate\Support\Facades\Route;
// use Inertia\Inertia;

Route::prefix('sistema/{school}')->group(function () {
    Route::middleware('auth')->group(function () {
        // Courses Routes
        Route::middleware('permission:superadmin')->group(function () {
            Route::get('{schoolLevel}/cursos', [CourseController::class, 'index'])->name('courses.index');
            Route::get('{schoolLevel}/cursos/crear', [CourseController::class, 'create'])->name('courses.create');
            Route::post('{schoolLevel}/cursos', [CourseController::class, 'store'])->name('courses.store');
            Route::get('{schoolLevel}/cursos/{course}/editar', [CourseController::class, 'edit'])->name('courses.edit');
            Route::get('{schoolLevel}/cursos/{course}', [CourseController::class, 'show'])->name('courses.show');
            Route::put('{schoolLevel}/cursos/{course}', [CourseController::class, 'update'])->name('courses.update');
            Route::delete('{schoolLevel}/cursos/{course}', [CourseController::class, 'destroy'])->name('courses.destroy');
        });
    });
});
