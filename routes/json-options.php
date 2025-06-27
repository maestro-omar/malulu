<?php

use App\Models\Catalogs\SchoolManagementType;
use App\Models\Catalogs\SchoolShift;
use App\Models\Catalogs\SchoolLevel;
use App\Models\Catalogs\Role;
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
