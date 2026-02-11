<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Root route is handled in public.php

require __DIR__ . '/json-options.php';
require __DIR__ . '/auth.php';
require __DIR__ . '/system.php';
require __DIR__ . '/school.php';
require __DIR__ . '/system-admin-tools.php'; // Admin tools (IP whitelisted, no auth required)
require __DIR__ . '/public.php';//important to be the last one because path {school slug} is for searching any other first slug to match a school 
