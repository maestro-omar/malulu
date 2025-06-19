<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\System\SchoolController;

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

require __DIR__ . '/auth.php';
require __DIR__ . '/json-options.php';
require __DIR__ . '/system-admin.php';
require __DIR__ . '/school-admin.php';

Route::post('/schools/{school}/upload-image', [SchoolController::class, 'uploadImage'])->name('schools.upload-image');
Route::delete('/schools/{school}/delete-image', [SchoolController::class, 'deleteImage'])->name('schools.delete-image');
