<?php

use App\Http\Controllers\PublicSchoolController;
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

Route::get('escuelas', function () {
    Route::get('/', [PublicSchoolController::class, 'index'])->name('schools.public.index');
    Route::get('{province}', [PublicSchoolController::class, 'byProvince'])->name('schools.public.byProvince');
});

Route::prefix('{school}')->group(function () {
    // School Routes
    Route::get('/', [PublicSchoolController::class, 'show'])->name('schools.public.show');
    Route::get('{schoolPage}', [PublicSchoolController::class, 'page'])->name('schools.public.page');
});
