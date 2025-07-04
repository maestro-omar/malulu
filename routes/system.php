<?php

use App\Http\Controllers\System\ProfileAdminController;
use App\Http\Controllers\System\UserAdminController;
use App\Http\Controllers\System\SchoolAdminController;
use App\Http\Controllers\System\AcademicYearAdminController;
use App\Http\Controllers\System\FileTypeAdminController;
use App\Http\Controllers\System\FileSubtypeAdminController;
use App\Http\Controllers\System\ProvinceAdminController;
use Illuminate\Support\Facades\Route;
// use Inertia\Inertia;

Route::prefix(__('routes.system'))->group(function () {
    Route::middleware('auth')->group(function () {
        Route::get('/' . __('routes.profile'), [ProfileAdminController::class, 'edit'])->name('profile.edit');
        Route::patch('/' . __('routes.profile'), [ProfileAdminController::class, 'update'])->name('profile.update');
        Route::delete('/' . __('routes.profile'), [ProfileAdminController::class, 'destroy'])->name('profile.destroy');

        // // Test route to check permissions
        // Route::get('/test-permissions', function () {
        //     $user = auth()->user();
        //     return [
        //         'user' => $user->name,
        //         'roles' => $user->getRoleNames(),
        //         'permissions' => $user->getAllPermissions()->pluck('name'),
        //         'can_view_users' => $user->can('view users'),
        //     ];
        // });

        // User Management Routes
        Route::middleware('permission:user.manage')->group(function () {
            Route::get(__('routes.users') . '/' . __('routes.trashed'), [UserAdminController::class, 'trashed'])->name('users.trashed');
            Route::post(__('routes.users') . '/{id}/' . __('routes.restore'), [UserAdminController::class, 'restore'])->name('users.restore');
            Route::delete(__('routes.users') . '/{id}/' . __('routes.force-delete'), [UserAdminController::class, 'forceDelete'])->name('users.force-delete');
            Route::get(__('routes.users'), [UserAdminController::class, 'index'])->name('users.index');
            Route::get(__('routes.users') . '/' . __('routes.create'), [UserAdminController::class, 'create'])->name('users.create');
            Route::get(__('routes.users') . '/{user}', [UserAdminController::class, 'show'])->name('users.show');
            Route::get(__('routes.users') . '/{user}/' . __('routes.edit'), [UserAdminController::class, 'edit'])->name('users.edit');
            Route::put(__('routes.users') . '/{user}', [UserAdminController::class, 'update'])->name('users.update');
            Route::delete(__('routes.users') . '/{user}', [UserAdminController::class, 'destroy'])->name('users.destroy');
            Route::get(__('routes.users') . '/{user}/' . __('routes.new-role'), [UserAdminController::class, 'addRole'])->name('users.add-role');
            Route::put(__('routes.users') . '/{user}/' . __('routes.store-role'), [UserAdminController::class, 'storeRole'])->name('users.roles.store');
            Route::post(__('routes.users') . '/{user}/' . __('routes.upload-image'), [UserAdminController::class, 'uploadImage'])->name('users.upload-image');
            Route::delete(__('routes.users') . '/{user}/' . __('routes.delete-image'), [UserAdminController::class, 'deleteImage'])->name('users.delete-image');
            Route::resource('usuarios', UserAdminController::class)->except(['index', 'show', 'create', 'edit', 'destroy']);
        });

        // Schools Routes
        Route::middleware('permission:school.create|school.edit|school.delete')->group(function () {
            Route::get(__('routes.schools') . '/' . __('routes.trashed'), [SchoolAdminController::class, 'trashed'])->name('schools.trashed');
            Route::post(__('routes.schools') . '/{school}/' . __('routes.restore'), [SchoolAdminController::class, 'restore'])->name('schools.restore');
            Route::delete(__('routes.schools') . '/{school}/' . __('routes.force-delete'), [SchoolAdminController::class, 'forceDelete'])->name('schools.force-delete');
            Route::get(__('routes.schools'), [SchoolAdminController::class, 'index'])->name('schools.index');
            Route::get(__('routes.schools') . '/' . __('routes.create'), [SchoolAdminController::class, 'create'])->name('schools.create');
            Route::post(__('routes.schools'), [SchoolAdminController::class, 'store'])->name('schools.store');
            Route::get(__('routes.schools') . '/{school}', [SchoolAdminController::class, 'show'])->name('schools.show');
            Route::get(__('routes.schools') . '/{school}/' . __('routes.edit'), [SchoolAdminController::class, 'edit'])->name('schools.edit');
            Route::put(__('routes.schools') . '/{school}', [SchoolAdminController::class, 'update'])->name('schools.update');
            Route::post(__('routes.schools') . '/{school}/' . __('routes.upload-image'), [SchoolAdminController::class, 'uploadImage'])->name('schools.upload-image');
            Route::post(__('routes.schools') . '/{school}/' . __('routes.delete-image'), [SchoolAdminController::class, 'deleteImage'])->name('schools.delete-image');
            Route::delete(__('routes.schools') . '/{school}', [SchoolAdminController::class, 'destroy'])->name('schools.destroy');
        });

        // Academic Years Routes
        Route::middleware('permission:academic-year.manage')->group(function () {
            Route::get(__('routes.academic-year') . '/' . __('routes.trashed'), [AcademicYearAdminController::class, 'trashed'])->name('academic-years.trashed');
            Route::post(__('routes.academic-year') . '/{academicYear}/' . __('routes.restore'), [AcademicYearAdminController::class, 'restore'])->name('academic-years.restore');
            Route::delete(__('routes.academic-year') . '/{academicYear}/' . __('routes.force-delete'), [AcademicYearAdminController::class, 'forceDelete'])->name('academic-years.force-delete');
            Route::get(__('routes.academic-year'), [AcademicYearAdminController::class, 'index'])->name('academic-years.index');
            Route::get(__('routes.academic-year') . '/' . __('routes.create'), [AcademicYearAdminController::class, 'create'])->name('academic-years.create');
            Route::post(__('routes.academic-year'), [AcademicYearAdminController::class, 'store'])->name('academic-years.store');
            Route::get(__('routes.academic-year') . '/{academicYear}', [AcademicYearAdminController::class, 'show'])->name('academic-years.show');
            Route::get(__('routes.academic-year') . '/{academicYear}/' . __('routes.edit'), [AcademicYearAdminController::class, 'edit'])->name('academic-years.edit');
            Route::put(__('routes.academic-year') . '/{academicYear}', [AcademicYearAdminController::class, 'update'])->name('academic-years.update');
            Route::delete(__('routes.academic-year') . '/{academicYear}', [AcademicYearAdminController::class, 'destroy'])->name('academic-years.destroy');
        });

        // File Types Routes
        Route::middleware('permission:file-type.manage')->group(function () {
            Route::get(__('routes.file-types'), [FileTypeAdminController::class, 'index'])
                ->name('file-types.index');
        });

        Route::resource('file-types', FileTypeAdminController::class)
            ->middleware('permission:file-type.manage');

        // File Subtypes Routes
        Route::middleware('permission:file-subtype.manage')->group(function () {
            Route::get(__('routes.file-subtypes'), [FileSubtypeAdminController::class, 'index'])
                ->name('file-subtypes.index');
        });

        Route::resource('file-subtypes', FileSubtypeAdminController::class)
            ->middleware('permission:file-subtype.manage');

        // Province Routes
        Route::middleware('permission:province.manage')->group(function () {
            Route::get(__('routes.provinces'), [ProvinceAdminController::class, 'index'])->name('provinces.index');
            Route::get(__('routes.provinces') . '/{province}/' . __('routes.edit'), [ProvinceAdminController::class, 'edit'])->name('provinces.edit');
            Route::put(__('routes.provinces') . '/{province}', [ProvinceAdminController::class, 'update'])->name('provinces.update');
            Route::delete('/' . __('routes.provinces') . '/{province}', [ProvinceAdminController::class, 'destroy'])->name('provinces.destroy');
            Route::get(__('routes.provinces') . '/{province}', [ProvinceAdminController::class, 'show'])->name('provinces.show');
            Route::post(__('routes.provinces') . '/{province}/' . __('routes.upload-image'), [ProvinceAdminController::class, 'uploadImage'])->name('provinces.upload-image');
            Route::delete(__('routes.provinces') . '/{province}/' . __('routes.delete-image'), [ProvinceAdminController::class, 'deleteImage'])->name('provinces.delete-image');
        });
    });
});
