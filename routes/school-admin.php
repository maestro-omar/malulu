<?php

use App\Http\Controllers\School\CourseController;
use App\Http\Controllers\School\SchoolController;
use Illuminate\Support\Facades\Route;
// use Inertia\Inertia;

Route::prefix('sistema/mi-escuela/{school}')->group(function () {
    Route::middleware('auth')->group(function () {
        // School Routes
        Route::get('/', [SchoolController::class, 'show'])->name('schools.show');
        Route::put('/', [SchoolController::class, 'update'])->name('schools.update');
        Route::get('editar', [SchoolController::class, 'edit'])->name('schools.edit');
        Route::post('upload-image', [SchoolController::class, 'uploadImage'])->name('schools.upload-image');
        Route::post('delete-image', [SchoolController::class, 'deleteImage'])->name('schools.delete-image');

        // Courses Routes
        Route::get('{schoolLevel}/cursos', [CourseController::class, 'index'])->name('courses.index');
        Route::get('{schoolLevel}/cursos/crear', [CourseController::class, 'create'])->name('courses.create');
        Route::post('{schoolLevel}/cursos', [CourseController::class, 'store'])->name('courses.store');
        Route::get('{schoolLevel}/cursos/{course}/editar', [CourseController::class, 'edit'])->name('courses.edit');
        Route::get('{schoolLevel}/cursos/{course}', [CourseController::class, 'show'])->name('courses.show');
        Route::put('{schoolLevel}/cursos/{course}', [CourseController::class, 'update'])->name('courses.update');
        Route::delete('{schoolLevel}/cursos/{course}', [CourseController::class, 'destroy'])->name('courses.destroy');
    });
});
