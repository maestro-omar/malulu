<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SchoolController;
use App\Http\Controllers\AcademicYearController;
use App\Http\Controllers\FileTypeController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::prefix('sistema')->group(function () {
    Route::get('/', function () {
        if (auth()->check()) {
            return redirect()->route('dashboard');
        }
        return redirect()->route('login');
    });

    Route::get('/inicio', function () {
        return Inertia::render('Dashboard');
    })->middleware(['auth', 'verified'])->name('dashboard');

    Route::middleware('auth')->group(function () {
        Route::get('/perfil', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/perfil', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/perfil', [ProfileController::class, 'destroy'])->name('profile.destroy');

        // Test route to check permissions
        Route::get('/test-permissions', function () {
            $user = auth()->user();
            return [
                'user' => $user->name,
                'roles' => $user->getRoleNames(),
                'permissions' => $user->getAllPermissions()->pluck('name'),
                'can_view_users' => $user->can('view users'),
            ];
        });

        // User Management Routes
        Route::middleware('permission:view users')->group(function () {
            Route::get('usuarios/eliminados', [UserController::class, 'trashed'])->name('users.trashed');
            Route::post('usuarios/{id}/restaurar', [UserController::class, 'restore'])->name('users.restore');
            Route::delete('usuarios/{id}/eliminar-permanentemente', [UserController::class, 'forceDelete'])->name('users.force-delete');
            Route::get('usuarios', [UserController::class, 'index'])->name('users.index');
            Route::get('usuarios/crear', [UserController::class, 'create'])->name('users.create');
            Route::get('usuarios/{user}', [UserController::class, 'show'])->name('users.show');
            Route::get('usuarios/{user}/editar', [UserController::class, 'edit'])->name('users.edit');
            Route::delete('usuarios/{user}', [UserController::class, 'destroy'])->name('users.destroy');
            Route::resource('usuarios', UserController::class)->except(['index', 'show', 'create', 'edit', 'destroy']);
        });

        // Schools Routes
        Route::middleware('permission:view schools')->group(function () {
            Route::get('escuelas/eliminadas', [SchoolController::class, 'trashed'])->name('schools.trashed');
            Route::post('escuelas/{school}/restaurar', [SchoolController::class, 'restore'])->name('schools.restore');
            Route::delete('escuelas/{school}/eliminar-permanentemente', [SchoolController::class, 'forceDelete'])->name('schools.force-delete');
            Route::get('escuelas', [SchoolController::class, 'index'])->name('schools.index');
            Route::get('escuelas/crear', [SchoolController::class, 'create'])->name('schools.create');
            Route::post('escuelas', [SchoolController::class, 'store'])->name('schools.store');
            Route::get('escuelas/{school}', [SchoolController::class, 'show'])->name('schools.show');
            Route::get('escuelas/{school}/editar', [SchoolController::class, 'edit'])->name('schools.edit');
            Route::put('escuelas/{school}', [SchoolController::class, 'update'])->name('schools.update');
            Route::delete('escuelas/{school}', [SchoolController::class, 'destroy'])->name('schools.destroy');
        });

        // Academic Years Routes
        Route::middleware('permission:superadmin')->group(function () {
            Route::get('ciclo-escolar/eliminadas', [AcademicYearController::class, 'trashed'])->name('academic-years.trashed');
            Route::post('ciclo-escolar/{academicYear}/restaurar', [AcademicYearController::class, 'restore'])->name('academic-years.restore');
            Route::delete('ciclo-escolar/{academicYear}/eliminar-permanentemente', [AcademicYearController::class, 'forceDelete'])->name('academic-years.force-delete');
            Route::get('ciclo-escolar', [AcademicYearController::class, 'index'])->name('academic-years.index');
            Route::get('ciclo-escolar/crear', [AcademicYearController::class, 'create'])->name('academic-years.create');
            Route::post('ciclo-escolar', [AcademicYearController::class, 'store'])->name('academic-years.store');
            Route::get('ciclo-escolar/{academicYear}', [AcademicYearController::class, 'show'])->name('academic-years.show');
            Route::get('ciclo-escolar/{academicYear}/editar', [AcademicYearController::class, 'edit'])->name('academic-years.edit');
            Route::put('ciclo-escolar/{academicYear}', [AcademicYearController::class, 'update'])->name('academic-years.update');
            Route::delete('ciclo-escolar/{academicYear}', [AcademicYearController::class, 'destroy'])->name('academic-years.destroy');
        });

        // File Types Routes
        Route::middleware('permission:superadmin')->group(function () {
            Route::get('/tipos-archivo', [FileTypeController::class, 'index'])
                ->name('file-types.index');
        });

        Route::resource('file-types', FileTypeController::class)
            ->middleware('can:superadmin');
    });
});

require __DIR__ . '/auth.php';
