<?php

use App\Models\SchoolManagementType;
use App\Models\SchoolShift;
use App\Models\SchoolLevel;
use App\Models\Role;
use Illuminate\Support\Facades\Route;
// use Inertia\Inertia;

Route::prefix('json-options')->group(function () {
    Route::get('school-levels', function () {
        return response()->json(SchoolLevel::vueOptions());
    });
    Route::get('school-shifts', function () {
        return response()->json(SchoolShift::vueOptions());
    });
    Route::get('school-management-types', function () {
        return response()->json(SchoolManagementType::vueOptions());
    });
    Route::get('roles', function () {
        return response()->json(Role::vueOptions());
    });
});
