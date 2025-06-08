<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SchoolController;
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
            Route::get('users/trashed', [UserController::class, 'trashed'])->name('users.trashed');
            Route::post('users/{id}/restore', [UserController::class, 'restore'])->name('users.restore');
            Route::delete('users/{id}/force-delete', [UserController::class, 'forceDelete'])->name('users.force-delete');
            Route::resource('users', UserController::class);
        });

        // Schools Routes
        Route::middleware('permission:view schools')->group(function () {
            Route::resource('schools', SchoolController::class);
        });
    });
});

require __DIR__ . '/auth.php';
