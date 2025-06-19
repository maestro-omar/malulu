<?php

use App\Models\SchoolLevel;
use Illuminate\Support\Facades\Route;
use App\Models\Role;
// use Inertia\Inertia;

Route::prefix('json-options')->group(function () {
    Route::get('school-level', function () {
        return response()->json(SchoolLevel::vueOptions());
    });
    Route::get('roles', function () {
        return response()->json(Role::vueOptions());
    });
});
