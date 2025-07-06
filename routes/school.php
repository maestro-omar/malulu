<?php

use App\Http\Controllers\School\CourseController;
use App\Http\Controllers\School\SchoolController;
use App\Http\Controllers\School\SchoolPageController;
use Illuminate\Support\Facades\Route;
// use Inertia\Inertia;

Route::prefix(__('routes.system') . '/escuela/{school}')->group(function () {
    Route::middleware('auth')->group(function () {
        // School Routes
        Route::get('/', [SchoolController::class, 'show'])->name('schools.show')->middleware('permission:school.view');
        Route::get('/', [SchoolController::class, 'show'])->name('schools.show');
        Route::put('/', [SchoolController::class, 'update'])->name('schools.update')->middleware('permission:school.edit');
        Route::get(__('routes.edit'), [SchoolController::class, 'edit'])->name('schools.edit')->middleware('permission:school.edit');
        Route::post(__('routes.upload-image'), [SchoolController::class, 'uploadImage'])->name('schools.upload-image')->middleware('permission:school.edit');
        Route::post(__('routes.delete-image'), [SchoolController::class, 'deleteImage'])->name('schools.delete-image')->middleware('permission:school.edit');

        // School Pages Routes
        Route::get(__('routes.pages'), [SchoolPageController::class, 'index'])->name('school-pages.index')->middleware('permission:school-page.manage');
        Route::get(__('routes.pages') . '/' . __('routes.create'), [SchoolPageController::class, 'create'])->name('school-pages.create')->middleware('permission:school-page.manage');
        Route::post(__('routes.pages'), [SchoolPageController::class, 'store'])->name('school-pages.store')->middleware('permission:school-page.manage');
        Route::get(__('routes.pages') . '/{schoolPage}', [SchoolPageController::class, 'show'])->name('school-pages.show')->middleware('permission:school-page.manage');
        Route::get(__('routes.pages') . '/{schoolPage}/' . __('routes.edit'), [SchoolPageController::class, 'edit'])->name('school-pages.edit')->middleware('permission:school-page.manage');
        Route::put(__('routes.pages') . '/{schoolPage}', [SchoolPageController::class, 'update'])->name('school-pages.update')->middleware('permission:school-page.manage');
        Route::delete(__('routes.pages') . '/{schoolPage}', [SchoolPageController::class, 'destroy'])->name('school-pages.destroy')->middleware('permission:school-page.manage');

        // Courses Routes
        Route::get('{schoolLevel}/' . __('routes.courses'), [CourseController::class, 'index'])->name('courses.index')->middleware('permission:course.manage');
        Route::get('{schoolLevel}/' . __('routes.courses') . '/' . __('routes.create'), [CourseController::class, 'create'])->name('courses.create')->middleware('permission:course.manage');
        Route::post('{schoolLevel}/' . __('routes.courses'), [CourseController::class, 'store'])->name('courses.store')->middleware('permission:course.manage');
        Route::get('{schoolLevel}/' . __('routes.courses') . '/{course}/' . __('routes.edit'), [CourseController::class, 'edit'])->name('courses.edit')->middleware('permission:course.manage');
        Route::get('{schoolLevel}/' . __('routes.courses') . '/{course}', [CourseController::class, 'show'])->name('courses.show')->middleware('permission:course.manage');
        Route::put('{schoolLevel}/' . __('routes.courses') . '/{course}', [CourseController::class, 'update'])->name('courses.update')->middleware('permission:course.manage');
        Route::delete('{schoolLevel}/' . __('routes.courses') . '/{course}', [CourseController::class, 'destroy'])->name('courses.destroy')->middleware('permission:course.manage');
    });
});
