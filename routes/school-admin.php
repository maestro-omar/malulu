<?php

use App\Http\Controllers\School\CourseController;
use App\Http\Controllers\School\SchoolController;
use Illuminate\Support\Facades\Route;
// use Inertia\Inertia;

Route::prefix('sistema/mi-escuela/{school}')->group(function () {
    Route::middleware('auth')->group(function () {
        // School Routes
        Route::get('/', [SchoolController::class, 'show'])->name('schools.show')->middleware('permission:school.view');
        Route::put('/', [SchoolController::class, 'update'])->name('schools.update')->middleware('permission:school.edit');
        Route::get('editar', [SchoolController::class, 'edit'])->name('schools.edit')->middleware('permission:school.edit');
        Route::post('upload-image', [SchoolController::class, 'uploadImage'])->name('schools.upload-image')->middleware('permission:school.edit');
        Route::post('delete-image', [SchoolController::class, 'deleteImage'])->name('schools.delete-image')->middleware('permission:school.edit');

        // Courses Routes
        Route::get('{schoolLevel}/cursos', [CourseController::class, 'index'])->name('courses.index')->middleware('permission:course.manage');
        Route::get('{schoolLevel}/cursos/crear', [CourseController::class, 'create'])->name('courses.create')->middleware('permission:course.manage');
        Route::post('{schoolLevel}/cursos', [CourseController::class, 'store'])->name('courses.store')->middleware('permission:course.manage');
        Route::get('{schoolLevel}/cursos/{course}/editar', [CourseController::class, 'edit'])->name('courses.edit')->middleware('permission:course.manage');
        Route::get('{schoolLevel}/cursos/{course}', [CourseController::class, 'show'])->name('courses.show')->middleware('permission:course.manage');
        Route::put('{schoolLevel}/cursos/{course}', [CourseController::class, 'update'])->name('courses.update')->middleware('permission:course.manage');
        Route::delete('{schoolLevel}/cursos/{course}', [CourseController::class, 'destroy'])->name('courses.destroy')->middleware('permission:course.manage');
    });
});
