<?php

use App\Http\Controllers\School\CourseController;
use App\Http\Controllers\School\SchoolController;
use App\Http\Controllers\School\SchoolPageController;
use App\Http\Controllers\School\UserController;
use Illuminate\Support\Facades\Route;
// use Inertia\Inertia;

Route::prefix(__('routes.system') . '/escuela/{school}')->group(function () {
    Route::middleware('auth')->group(function () {
        // School Routes

        Route::get('/', [SchoolController::class, 'show'])->name('schools.show')->middleware('school.permission:school.view');
        Route::put('/', [SchoolController::class, 'update'])->name('schools.update')->middleware('school.permission:school.edit');
        Route::get(__('routes.edit'), [SchoolController::class, 'edit'])->name('schools.edit')->middleware('school.permission:school.edit');
        Route::post(__('routes.upload-image'), [SchoolController::class, 'uploadImage'])->name('schools.upload-image')->middleware('school.permission:school.edit');
        Route::post(__('routes.delete-image'), [SchoolController::class, 'deleteImage'])->name('schools.delete-image')->middleware('school.permission:school.edit');

        Route::get(__('routes.staff'), [UserController::class, 'staff'])->name('school.staff')->middleware('school.permission:staff.view');

        Route::get(__('routes.students'), [UserController::class, 'students'])->name('school.students')->middleware('school.permission:student.view');
        Route::get(__('routes.students') . '/' . __('routes.create'), [UserController::class, 'studentCreate'])->name('school.students.create')->middleware('school.permission:student.create');
        Route::get(__('routes.student') . '/{idAndName}', [UserController::class, 'student'])->name('school.student.show')->middleware('school.permission:student.view');
        Route::get(__('routes.student') . '/{idAndName}/' . __('routes.edit'), [UserController::class, 'studentEdit'])->name('school.student.edit')->middleware('school.permission:student.edit');
        Route::put(__('routes.student') . '/{idAndName}/' . __('routes.edit'), [UserController::class, 'studentUpdate'])->name('school.student.update')->middleware('school.permission:student.edit');

        Route::get(__('routes.guardians'), [UserController::class, 'guardians'])->name('school.guardians')->middleware('school.permission:guardians.view');




        // School Pages Routes
        Route::get(__('routes.pages'), [SchoolPageController::class, 'index'])->name('school-pages.index')->middleware('school.permission:school-page.manage');
        Route::get(__('routes.pages') . '/' . __('routes.create'), [SchoolPageController::class, 'create'])->name('school-pages.create')->middleware('school.permission:school-page.manage');
        Route::post(__('routes.pages'), [SchoolPageController::class, 'store'])->name('school-pages.store')->middleware('school.permission:school-page.manage');
        Route::get(__('routes.pages') . '/{schoolPage}', [SchoolPageController::class, 'show'])->name('school-pages.show')->middleware('school.permission:school-page.manage');
        Route::get(__('routes.pages') . '/{schoolPage}/' . __('routes.edit'), [SchoolPageController::class, 'edit'])->name('school-pages.edit')->middleware('school.permission:school-page.manage');
        Route::put(__('routes.pages') . '/{schoolPage}', [SchoolPageController::class, 'update'])->name('school-pages.update')->middleware('school.permission:school-page.manage');
        Route::delete(__('routes.pages') . '/{schoolPage}', [SchoolPageController::class, 'destroy'])->name('school-pages.destroy')->middleware('school.permission:school-page.manage');




        // Courses Routes
        Route::get('{schoolLevel}/' . __('routes.courses') . '/{course}', [CourseController::class, 'show'])->name('courses.show')->middleware('school.permission:course.manage|student.me|child.course.view');

        Route::get('{schoolLevel}/' . __('routes.courses'), [CourseController::class, 'index'])->name('courses.index')->middleware('school.permission:course.manage');
        Route::get('{schoolLevel}/' . __('routes.courses') . '/' . __('routes.create'), [CourseController::class, 'create'])->name('courses.create')->middleware('school.permission:course.manage');
        Route::post('{schoolLevel}/' . __('routes.courses'), [CourseController::class, 'store'])->name('courses.store')->middleware('school.permission:course.manage');
        Route::get('{schoolLevel}/' . __('routes.courses') . '/{course}/' . __('routes.edit'), [CourseController::class, 'edit'])->name('courses.edit')->middleware('school.permission:course.manage');
        Route::put('{schoolLevel}/' . __('routes.courses') . '/{course}', [CourseController::class, 'update'])->name('courses.update')->middleware('school.permission:course.manage');
        Route::delete('{schoolLevel}/' . __('routes.courses') . '/{course}', [CourseController::class, 'destroy'])->name('courses.destroy')->middleware('school.permission:course.manage');
    });
});
