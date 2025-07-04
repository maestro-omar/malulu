<?php

use App\Http\Controllers\PublicSchoolController;
use App\Http\Controllers\PublicSchoolPageController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Diglactic\Breadcrumbs\Breadcrumbs;
use Diglactic\Breadcrumbs\Trail;
use App\Models\Catalogs\Province;
use App\Models\Catalogs\District;
use App\Models\Catalogs\Locality;

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

// Schools listing routes
Route::get(__('routes.schools'), [PublicSchoolController::class, 'index'])->name('schools.public-index');
Route::get(__('routes.schools') . '/{province}', [PublicSchoolController::class, 'byProvince'])->name('schools.public-byProvince');

// Individual school routes (these should come after the escuelas routes to avoid conflicts)
Route::prefix('{school}')->group(function () {
    // School main page (shows school info and navigation) - handled by PublicSchoolPageController
    Route::get('/', [PublicSchoolPageController::class, 'showSchool'])->name('schools.public-show');

    // School Pages Routes (direct access without "paginas" slug)
    Route::get('{slug}', [PublicSchoolPageController::class, 'show'])->name('school.public-page');
});


/* Test route for default pages (remove in production)
Route::get('/test-default-pages/{school}', function ($school) {
    $school = \App\Models\Entities\School::where('slug', $school)->firstOrFail();
    $service = new \App\Services\SchoolPageService();

    echo "<h1>Testing Default Pages for: {$school->name}</h1>";
    echo "<h2>Default Slugs:</h2>";
    echo "<ul>";
    foreach ($service->getDefaultPageSlugs() as $slug) {
        echo "<li><a href='/{$school->slug}/{$slug}'>{$slug}</a></li>";
    }
    echo "</ul>";

    echo "<h2>All Available Pages:</h2>";
    $pages = $service->getAllAvailablePagesForSchool($school->id, $school);
    echo "<ul>";
    foreach ($pages as $page) {
        $isDefault = $page['is_default'] ? ' (Default)' : '';
        echo "<li><a href='/{$school->slug}/{$page['slug']}'>{$page['name']}{$isDefault}</a></li>";
    }
    echo "</ul>";

    echo "<h2>Main School Page:</h2>";
    echo "<p><a href='/{$school->slug}'>Main School Page</a></p>";
})->name('test.default.pages');
*/
