<?php

use App\Http\Controllers\System\ProvinceAdminController;
use App\Http\Controllers\School\CourseController;
use App\Http\Controllers\School\SchoolController;
use App\Http\Controllers\School\SchoolPageController;
use App\Http\Controllers\School\UserController;
use App\Http\Controllers\School\FileController;
use App\Http\Controllers\School\AcademicEventController;
use App\Http\Controllers\School\CourseStudentController;
use App\Http\Controllers\School\CourseTeacherController;
use Illuminate\Support\Facades\Route;
// use Inertia\Inertia;

Route::prefix(__('routes.system') . '/escuela/{school}')->group(function () {
    Route::middleware('auth')->group(function () {
        // School Routes

        Route::get('/', [SchoolController::class, 'show'])->name('school.show')->middleware('school.permission:school.view');
        Route::put('/', [SchoolController::class, 'update'])->name('school.update')->middleware('school.permission:school.edit');
        Route::get(__('routes.edit'), [SchoolController::class, 'edit'])->name('school.edit')->middleware('school.permission:school.edit');
        Route::post(__('routes.upload-image'), [SchoolController::class, 'uploadImage'])->name('school.upload-image')->middleware('school.permission:school.edit');
        Route::delete(__('routes.delete-image'), [SchoolController::class, 'deleteImage'])->name('school.delete-image')->middleware('school.permission:school.edit');

        Route::get(__('routes.province'), [SchoolController::class, 'provinceShow'])->name('school.province.show');

        Route::get(__('routes.staff'), [UserController::class, 'staff'])->name('school.staff')->middleware('school.permission:staff.view');
        Route::get(__('routes.staff') . '/' . __('routes.create'), [UserController::class, 'staffCreate'])->name('school.staff.create')->middleware('school.permission:teacher.manage');
        Route::get(__('routes.staff') . '/{idAndName}', [UserController::class, 'staffView'])->name('school.staff.show')->middleware('school.permission:partner.view');
        Route::get(__('routes.staff') . '/{idAndName}/' . __('routes.edit'), [UserController::class, 'staffEdit'])->name('school.staff.edit')->middleware('school.permission:school.edit');
        Route::put(__('routes.staff') . '/{idAndName}/' . __('routes.edit'), [UserController::class, 'staffUpdate'])->name('school.staff.update')->middleware('school.permission:school.edit');
        Route::get(__('routes.staff') . '/{idAndName}/' . __('routes.edit-diagnoses'), [UserController::class, 'staffEditDiagnoses'])->name('school.staff.edit-diagnoses')->middleware('school.permission:school.edit');
        Route::put(__('routes.staff') . '/{idAndName}/' . __('routes.edit-diagnoses'), [UserController::class, 'staffUpdateDiagnoses'])->name('school.staff.update-diagnoses')->middleware('school.permission:school.edit');
        Route::post(__('routes.staff') . '/{idAndName}/' . __('routes.upload-image'), [UserController::class, 'uploadImage'])->name('school.staff.upload-image')->middleware('school.permission:school.edit');

        Route::get(__('routes.students'), [UserController::class, 'students'])->name('school.students')->middleware('school.permission:student.view');
        Route::get(__('routes.students') . '/' . __('routes.create'), [UserController::class, 'studentCreate'])->name('school.students.create')->middleware('school.permission:student.create');
        Route::get(__('routes.student') . '/{idAndName}', [UserController::class, 'student'])->name('school.student.show')->middleware('school.permission:student.view');
        Route::get(__('routes.student') . '/{idAndName}/' . __('routes.edit'), [UserController::class, 'studentEdit'])->name('school.student.edit')->middleware('school.permission:student.edit');
        Route::put(__('routes.student') . '/{idAndName}/' . __('routes.edit'), [UserController::class, 'studentUpdate'])->name('school.student.update')->middleware('school.permission:student.edit');
        Route::get(__('routes.student') . '/{idAndName}/' . __('routes.edit-diagnoses'), [UserController::class, 'studentEditDiagnoses'])->name('school.student.edit-diagnoses')->middleware('school.permission:student.edit');
        Route::put(__('routes.student') . '/{idAndName}/' . __('routes.edit-diagnoses'), [UserController::class, 'studentUpdateDiagnoses'])->name('school.student.update-diagnoses')->middleware('school.permission:student.edit');
        Route::post(__('routes.student') . '/{idAndName}/' . __('routes.upload-image'), [UserController::class, 'uploadImage'])->name('school.student.upload-image')->middleware('school.permission:student.edit');

        // School User Files Routes (use school controllers with school breadcrumbs)
        Route::get(__('routes.student') . '/{user}/' . __('routes.files') . '/' . __('routes.create'), [FileController::class, 'createForStudent'])->name('school.student.file.create')->middleware('school.permission:student.edit');
        Route::post(__('routes.student') . '/{user}/' . __('routes.files'), [FileController::class, 'storeForStudent'])->name('school.student.file.store')->middleware('school.permission:student.edit');
        Route::get(__('routes.student') . '/{user}/' . __('routes.file') . '/{file}', [FileController::class, 'showForStudent'])->name('school.student.file.show')->middleware('school.permission:student.view');
        Route::get(__('routes.student') . '/{user}/' . __('routes.file') . '/{file}/' . __('routes.edit'), [FileController::class, 'editForStudent'])->name('school.student.file.edit')->middleware('school.permission:student.edit');
        Route::put(__('routes.student') . '/{user}/' . __('routes.file') . '/{file}', [FileController::class, 'updateForStudent'])->name('school.student.file.update')->middleware('school.permission:student.edit');
        Route::get(__('routes.student') . '/{user}/' . __('routes.file') . '/{file}/' . __('routes.replace'), [FileController::class, 'replaceForStudent'])->name('school.student.file.replace')->middleware('school.permission:student.edit');
        Route::post(__('routes.student') . '/{user}/' . __('routes.file') . '/{file}/' . __('routes.replace'), [FileController::class, 'replaceForStudent'])->name('school.student.file.replace')->middleware('school.permission:student.edit');

        Route::get(__('routes.guardians'), [UserController::class, 'guardians'])->name('school.guardians')->middleware('school.permission:guardian.view');

        // School Files Routes
        Route::get(__('routes.files') . '/' . __('routes.create'), [FileController::class, 'createForSchoolDirect'])->name('school.file.create')->middleware('school.permission:school.edit');
        Route::post(__('routes.files'), [FileController::class, 'storeForSchoolDirect'])->name('school.file.store')->middleware('school.permission:school.edit');
        Route::get(__('routes.file') . '/{file}', [FileController::class, 'showForSchoolDirect'])->name('school.file.show')->middleware('school.permission:school.view');
        Route::get(__('routes.file') . '/{file}/' . __('routes.edit'), [FileController::class, 'editForSchoolDirect'])->name('school.file.edit')->middleware('school.permission:school.edit');
        Route::put(__('routes.file') . '/{file}', [FileController::class, 'updateForSchoolDirect'])->name('school.file.update')->middleware('school.permission:school.edit');
        Route::get(__('routes.file') . '/{file}/' . __('routes.replace'), [FileController::class, 'replaceForSchoolDirect'])->name('school.file.replace')->middleware('school.permission:school.edit');
        Route::post(__('routes.file') . '/{file}/' . __('routes.replace'), [FileController::class, 'replaceForSchoolDirect'])->name('school.file.replace')->middleware('school.permission:school.edit');

        // School Pages Routes
        Route::get(__('routes.pages'), [SchoolPageController::class, 'index'])->name('school-pages.index')->middleware('school.permission:school-page.manage');
        Route::get(__('routes.pages') . '/' . __('routes.create'), [SchoolPageController::class, 'create'])->name('school-pages.create')->middleware('school.permission:school-page.manage');
        Route::post(__('routes.pages'), [SchoolPageController::class, 'store'])->name('school-pages.store')->middleware('school.permission:school-page.manage');
        Route::get(__('routes.pages') . '/{schoolPage}', [SchoolPageController::class, 'show'])->name('school-pages.show')->middleware('school.permission:school-page.manage');
        Route::get(__('routes.pages') . '/{schoolPage}/' . __('routes.edit'), [SchoolPageController::class, 'edit'])->name('school-pages.edit')->middleware('school.permission:school-page.manage');
        Route::put(__('routes.pages') . '/{schoolPage}/' . __('routes.edit'), [SchoolPageController::class, 'update'])->name('school-pages.update')->middleware('school.permission:school-page.manage');
        Route::delete(__('routes.pages') . '/{schoolPage}', [SchoolPageController::class, 'destroy'])->name('school-pages.destroy')->middleware('school.permission:school-page.manage');

        // Academic Events Routes
        Route::get(__('routes.events'), [AcademicEventController::class, 'index'])->name('school.academic-events.index')->middleware('school.permission:academic-event.manage');
        Route::get(__('routes.events') . '/' . __('routes.create'), [AcademicEventController::class, 'create'])->name('school.academic-events.create')->middleware('school.permission:academic-event.manage');
        Route::post(__('routes.events'), [AcademicEventController::class, 'store'])->name('school.academic-events.store')->middleware('school.permission:academic-event.manage');
        Route::get(__('routes.events') . '/{academicEvent}', [AcademicEventController::class, 'show'])->name('school.academic-events.show')->middleware('school.permission:academic-event.manage');
        Route::get(__('routes.events') . '/{academicEvent}/' . __('routes.edit'), [AcademicEventController::class, 'edit'])->name('school.academic-events.edit')->middleware('school.permission:academic-event.manage');
        Route::put(__('routes.events') . '/{academicEvent}', [AcademicEventController::class, 'update'])->name('school.academic-events.update')->middleware('school.permission:academic-event.manage');
        Route::delete(__('routes.events') . '/{academicEvent}', [AcademicEventController::class, 'destroy'])->name('school.academic-events.destroy')->middleware('school.permission:academic-event.manage');




        // Courses Routes
        Route::get('{schoolLevel}/' . __('routes.courses'), [CourseController::class, 'index'])->name('school.courses')->middleware('school.permission:course.manage');

        Route::get('{schoolLevel}/' . __('routes.course') . '/{idAndLabel}', [CourseController::class, 'show'])->name('school.course.show')->middleware('school.permission:course.manage|student.me|child.course.view');
        Route::get('{schoolLevel}/' . __('routes.courses') . '/' . __('routes.create'), [CourseController::class, 'create'])->name('school.course.create')->middleware('school.permission:course.manage');
        Route::get('{schoolLevel}/' . __('routes.courses') . '/' . __('routes.create-next'), [CourseController::class, 'createNext'])->name('school.course.create-next')->middleware('school.permission:course.manage');
        Route::post('{schoolLevel}/' . __('routes.courses') . '/' . __('routes.create-next'), [CourseController::class, 'storeNext'])->name('school.course.store-next')->middleware('school.permission:course.manage');
        Route::post('{schoolLevel}/' . __('routes.course'), [CourseController::class, 'store'])->name('school.course.store')->middleware('school.permission:course.manage');
        Route::get('{schoolLevel}/' . __('routes.course') . '/{idAndLabel}/' . __('routes.edit'), [CourseController::class, 'edit'])->name('school.course.edit')->middleware('school.permission:course.manage');
        Route::put('{schoolLevel}/' . __('routes.course') . '/{idAndLabel}/' . __('routes.edit'), [CourseController::class, 'update'])->name('school.course.update')->middleware('school.permission:course.manage');
        Route::match(['get', 'post'], '{schoolLevel}/' . __('routes.course') . '/{idAndLabel}/' . __('routes.export'), [CourseController::class, 'export'])->name('school.course.export')->middleware('school.permission:course.manage');
        Route::delete('{schoolLevel}/' . __('routes.course') . '/{idAndLabel}', [CourseController::class, 'destroy'])->name('school.course.destroy')->middleware('school.permission:course.manage');
        Route::get('{schoolLevel}/' . __('routes.course') . '/{idAndLabel}/' . __('routes.student') . '/{userIdAndName}', [CourseController::class, 'viewStudent'])->name('school.course.student.view'); //OMAR PENDIENTE ver permiso para esto
        Route::get('{schoolLevel}/' . __('routes.course') . '/{idAndLabel}/' . __('routes.student') . '/{userIdAndName}/' . __('routes.remove'), [CourseController::class, 'removeStudent'])->name('school.course.student.remove'); //OMAR PENDIENTE ver permiso para esto
        Route::get('{schoolLevel}/' . __('routes.course') . '/{idAndLabel}/' . __('routes.student') . '/{userIdAndName}', [CourseController::class, 'viewStudent'])->name('school.course.student.view'); //OMAR PENDIENTE ver permiso para esto

        // Course student enrollment (inscribir)
        Route::get('{schoolLevel}/' . __('routes.course') . '/{idAndLabel}/' . __('routes.inscribir'), [CourseStudentController::class, 'create'])->name('school.course.student.enroll')->middleware('school.permission:course.manage');
        Route::post('{schoolLevel}/' . __('routes.course') . '/{idAndLabel}/' . __('routes.inscribir') . '/' . __('routes.search'), [CourseStudentController::class, 'searchStudents'])->name('school.course.students.search')->middleware('school.permission:course.manage');
        Route::post('{schoolLevel}/' . __('routes.course') . '/{idAndLabel}/' . __('routes.inscribir'), [CourseStudentController::class, 'store'])->name('school.course.student.store')->middleware('school.permission:course.manage');

        // Course teacher assignment (asignar docente)
        Route::get('{schoolLevel}/' . __('routes.course') . '/{idAndLabel}/' . __('routes.asignar-docente'), [CourseTeacherController::class, 'create'])->name('school.course.teacher.enroll')->middleware('school.permission:course.manage');
        Route::post('{schoolLevel}/' . __('routes.course') . '/{idAndLabel}/' . __('routes.asignar-docente') . '/' . __('routes.search'), [CourseTeacherController::class, 'searchTeachers'])->name('school.course.teachers.search')->middleware('school.permission:course.manage');
        Route::post('{schoolLevel}/' . __('routes.course') . '/{idAndLabel}/' . __('routes.asignar-docente'), [CourseTeacherController::class, 'store'])->name('school.course.teacher.store')->middleware('school.permission:course.manage');

        Route::get('{schoolLevel}/' . __('routes.course') . '/{idAndLabel}/' . __('routes.files') . '/' . __('routes.create'), [FileController::class, 'createForCourse'])->name('school.course.file.create')->middleware('school.permission:course.files.manage');
        Route::post('{schoolLevel}/' . __('routes.course') . '/{idAndLabel}/' . __('routes.files'), [FileController::class, 'storeForCourse'])->name('school.course.file.store')->middleware('school.permission:course.files.manage');
        Route::get('{schoolLevel}/' . __('routes.course') . '/{idAndLabel}/' . __('routes.file') . '/{file}', [FileController::class, 'showForCourse'])->name('school.course.file.show')->middleware('school.permission:course.files.manage');
        Route::get('{schoolLevel}/' . __('routes.course') . '/{idAndLabel}/' . __('routes.file') . '/{file}/' . __('routes.edit'), [FileController::class, 'editForCourse'])->name('school.course.file.edit')->middleware('school.permission:course.files.manage');
        Route::put('{schoolLevel}/' . __('routes.course') . '/{idAndLabel}/' . __('routes.file') . '/{file}', [FileController::class, 'updateForCourse'])->name('school.course.file.update')->middleware('school.permission:course.files.manage');
        Route::get('{schoolLevel}/' . __('routes.course') . '/{idAndLabel}/' . __('routes.file') . '/{file}/' . __('routes.replace'), [FileController::class, 'replaceForCourse'])->name('school.course.file.replace')->middleware('school.permission:course.files.manage');
        Route::post('{schoolLevel}/' . __('routes.course') . '/{idAndLabel}/' . __('routes.file') . '/{file}/' . __('routes.replace'), [FileController::class, 'replaceForCourse'])->name('school.course.file.replace')->middleware('school.permission:course.files.manage');

        Route::get('{schoolLevel}/' . __('routes.course') . '/{idAndLabel}/' . __('routes.attendance') . '/' . __('routes.edit'), [CourseController::class, 'attendanceDayEdit'])->name('school.course.attendance.edit')->middleware('school.permission:course.manage');
        Route::post('{schoolLevel}/' . __('routes.course') . '/{idAndLabel}/' . __('routes.attendance') . '/' . __('routes.update'), [CourseController::class, 'attendanceDayUpdate'])->name('school.course.attendance.update')->middleware('school.permission:course.manage');

        // Course Search for Popover
        Route::post('{schoolLevel}/' . __('routes.course') . '/' . __('routes.search'), [CourseController::class, 'search'])->name('school.courses.search')->middleware('school.permission:course.manage');
    });
});
