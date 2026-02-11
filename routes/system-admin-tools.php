<?php
/**
 * SEPARATE FILE for Admin Tools Routes
 * Include this file separately to avoid breaking main routes
 * 
 * To use: Add this line to routes/web.php:
 * require __DIR__ . '/system-admin-tools.php';
 */

use App\Http\Controllers\System\AdminToolsController;
use Illuminate\Support\Facades\Route;

// Admin Tools Routes (IP whitelisted, NO authentication required)
// This allows running migrations when database is empty
Route::prefix(__('routes.system'))->group(function () {
    Route::middleware('ip.whitelist')->group(function () {
        Route::get('/admin-tools', [AdminToolsController::class, 'index'])->name('admin-tools.index');
        Route::post('/admin-tools/command', [AdminToolsController::class, 'executeCommand'])->name('admin-tools.command');
        Route::post('/admin-tools/query', [AdminToolsController::class, 'executeQuery'])->name('admin-tools.query');
    });
});
