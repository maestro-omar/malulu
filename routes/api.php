<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:sanctum')->group(function () {
    // Schools API Routes
    Route::apiResource('schools', \App\Http\Controllers\Api\SchoolController::class);
    Route::get('schools/trashed', [\App\Http\Controllers\Api\SchoolController::class, 'trashed']);
    Route::post('schools/{id}/restore', [\App\Http\Controllers\Api\SchoolController::class, 'restore']);
    Route::delete('schools/{id}/force-delete', [\App\Http\Controllers\Api\SchoolController::class, 'forceDelete']);

    // Academic Years API Routes
    Route::apiResource('academic-years', \App\Http\Controllers\Api\AcademicYearController::class);
    Route::get('academic-years/trashed', [\App\Http\Controllers\Api\AcademicYearController::class, 'trashed']);
    Route::post('academic-years/{id}/restore', [\App\Http\Controllers\Api\AcademicYearController::class, 'restore']);
    Route::delete('academic-years/{id}/force-delete', [\App\Http\Controllers\Api\AcademicYearController::class, 'forceDelete']);
});
