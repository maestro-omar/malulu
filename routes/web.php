<?php

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

require __DIR__ . '/json-options.php';
require __DIR__ . '/auth.php';
require __DIR__ . '/system.php';
require __DIR__ . '/school.php';
require __DIR__ . '/public.php';//important to be the last one because path {school slug} is for searching any other first slug to match a school 
