<?php

use App\Http\Controllers\School\CourseController;
use App\Http\Controllers\School\SchoolController;
use App\Http\Controllers\School\SchoolPageController;
use Illuminate\Support\Facades\Route;
// use Inertia\Inertia;

Route::prefix('sistema/escuela/{school}')->group(function () {
    Route::middleware('auth')->group(function () {
        // School Routes
        Route::get('/', [SchoolController::class, 'show'])->name('schools.show')->middleware('permission:school.view');
        Route::put('/', [SchoolController::class, 'update'])->name('schools.update')->middleware('permission:school.edit');
        Route::get('editar', [SchoolController::class, 'edit'])->name('schools.edit')->middleware('permission:school.edit');
        Route::post('upload-image', [SchoolController::class, 'uploadImage'])->name('schools.upload-image')->middleware('permission:school.edit');
        Route::post('delete-image', [SchoolController::class, 'deleteImage'])->name('schools.delete-image')->middleware('permission:school.edit');

        // School Pages Routes
        Route::get('paginas', [SchoolPageController::class, 'index'])->name('school-pages.index')->middleware('permission:school-page.manage');
        Route::get('paginas/crear', [SchoolPageController::class, 'create'])->name('school-pages.create')->middleware('permission:school-page.manage');
        Route::post('paginas', [SchoolPageController::class, 'store'])->name('school-pages.store')->middleware('permission:school-page.manage');
        Route::get('paginas/{schoolPage}', [SchoolPageController::class, 'show'])->name('school-pages.show')->middleware('permission:school-page.manage');
        Route::get('paginas/{schoolPage}/editar', [SchoolPageController::class, 'edit'])->name('school-pages.edit')->middleware('permission:school-page.manage');
        Route::put('paginas/{schoolPage}', [SchoolPageController::class, 'update'])->name('school-pages.update')->middleware('permission:school-page.manage');
        Route::delete('paginas/{schoolPage}', [SchoolPageController::class, 'destroy'])->name('school-pages.destroy')->middleware('permission:school-page.manage');

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
