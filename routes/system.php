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

Route::prefix('sistema')->group(function () {
    Route::middleware('auth')->group(function () {
        Route::get('/perfil', [ProfileAdminController::class, 'edit'])->name('profile.edit');
        Route::patch('/perfil', [ProfileAdminController::class, 'update'])->name('profile.update');
        Route::delete('/perfil', [ProfileAdminController::class, 'destroy'])->name('profile.destroy');

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
            Route::get('usuarios/eliminados', [UserAdminController::class, 'trashed'])->name('users.trashed');
            Route::post('usuarios/{id}/restaurar', [UserAdminController::class, 'restore'])->name('users.restore');
            Route::delete('usuarios/{id}/eliminar-permanentemente', [UserAdminController::class, 'forceDelete'])->name('users.force-delete');
            Route::get('usuarios', [UserAdminController::class, 'index'])->name('users.index');
            Route::get('usuarios/crear', [UserAdminController::class, 'create'])->name('users.create');
            Route::get('usuarios/{user}', [UserAdminController::class, 'show'])->name('users.show');
            Route::get('usuarios/{user}/editar', [UserAdminController::class, 'edit'])->name('users.edit');
            Route::put('usuarios/{user}', [UserAdminController::class, 'update'])->name('users.update');
            Route::delete('usuarios/{user}', [UserAdminController::class, 'destroy'])->name('users.destroy');
            Route::get('usuarios/{user}/nuevo-rol', [UserAdminController::class, 'addRole'])->name('users.add-role');
            Route::put('usuarios/{user}/store-role', [UserAdminController::class, 'storeRole'])->name('users.roles.store');
            Route::post('usuarios/{user}/upload-image', [UserAdminController::class, 'uploadImage'])->name('users.upload-image');
            Route::delete('usuarios/{user}/delete-image', [UserAdminController::class, 'deleteImage'])->name('users.delete-image');
            Route::resource('usuarios', UserAdminController::class)->except(['index', 'show', 'create', 'edit', 'destroy']);
        });

        // Schools Routes
        Route::middleware('permission:school.create|school.edit|school.delete')->group(function () {
            Route::get('escuelas/eliminadas', [SchoolAdminController::class, 'trashed'])->name('schools.trashed');
            Route::post('escuelas/{school}/restaurar', [SchoolAdminController::class, 'restore'])->name('schools.restore');
            Route::delete('escuelas/{school}/eliminar-permanentemente', [SchoolAdminController::class, 'forceDelete'])->name('schools.force-delete');
            Route::get('escuelas', [SchoolAdminController::class, 'index'])->name('schools.index');
            Route::get('escuelas/crear', [SchoolAdminController::class, 'create'])->name('schools.create');
            Route::post('escuelas', [SchoolAdminController::class, 'store'])->name('schools.store');
            Route::get('escuelas/{school}', [SchoolAdminController::class, 'show'])->name('schools.show');
            Route::get('escuelas/{school}/editar', [SchoolAdminController::class, 'edit'])->name('schools.edit');
            Route::put('escuelas/{school}', [SchoolAdminController::class, 'update'])->name('schools.update');
            Route::post('escuelas/{school}/upload-image', [SchoolAdminController::class, 'uploadImage'])->name('schools.upload-image');
            Route::post('escuelas/{school}/delete-image', [SchoolAdminController::class, 'deleteImage'])->name('schools.delete-image');
            Route::delete('escuelas/{school}', [SchoolAdminController::class, 'destroy'])->name('schools.destroy');
        });

        // Academic Years Routes
        Route::middleware('permission:academic-year.manage')->group(function () {
            Route::get('ciclo-escolar/eliminadas', [AcademicYearAdminController::class, 'trashed'])->name('academic-years.trashed');
            Route::post('ciclo-escolar/{academicYear}/restaurar', [AcademicYearAdminController::class, 'restore'])->name('academic-years.restore');
            Route::delete('ciclo-escolar/{academicYear}/eliminar-permanentemente', [AcademicYearAdminController::class, 'forceDelete'])->name('academic-years.force-delete');
            Route::get('ciclo-escolar', [AcademicYearAdminController::class, 'index'])->name('academic-years.index');
            Route::get('ciclo-escolar/crear', [AcademicYearAdminController::class, 'create'])->name('academic-years.create');
            Route::post('ciclo-escolar', [AcademicYearAdminController::class, 'store'])->name('academic-years.store');
            Route::get('ciclo-escolar/{academicYear}', [AcademicYearAdminController::class, 'show'])->name('academic-years.show');
            Route::get('ciclo-escolar/{academicYear}/editar', [AcademicYearAdminController::class, 'edit'])->name('academic-years.edit');
            Route::put('ciclo-escolar/{academicYear}', [AcademicYearAdminController::class, 'update'])->name('academic-years.update');
            Route::delete('ciclo-escolar/{academicYear}', [AcademicYearAdminController::class, 'destroy'])->name('academic-years.destroy');
        });

        // File Types Routes
        Route::middleware('permission:file-type.manage')->group(function () {
            Route::get('tipos-archivo', [FileTypeAdminController::class, 'index'])
                ->name('file-types.index');
        });

        Route::resource('file-types', FileTypeAdminController::class)
            ->middleware('permission:file-type.manage');

        // File Subtypes Routes
        Route::middleware('permission:file-subtype.manage')->group(function () {
            Route::get('subtipos-archivo', [FileSubtypeAdminController::class, 'index'])
                ->name('file-subtypes.index');
        });

        Route::resource('file-subtypes', FileSubtypeAdminController::class)
            ->middleware('permission:file-subtype.manage');

        // Province Routes
        Route::middleware('permission:province.manage')->group(function () {
            Route::get('provincias', [ProvinceAdminController::class, 'index'])->name('provinces.index');
            Route::get('provincias/{province}/editar', [ProvinceAdminController::class, 'edit'])->name('provinces.edit');
            Route::put('provincias/{province}', [ProvinceAdminController::class, 'update'])->name('provinces.update');
            Route::delete('/provincias/{province}', [ProvinceAdminController::class, 'destroy'])->name('provinces.destroy');
            Route::get('provincias/{province}', [ProvinceAdminController::class, 'show'])->name('provinces.show');
            Route::post('provincias/{province}/upload-image', [ProvinceAdminController::class, 'uploadImage'])->name('provinces.upload-image');
            Route::delete('provincias/{province}/delete-image', [ProvinceAdminController::class, 'deleteImage'])->name('provinces.delete-image');
        });
    });
});
