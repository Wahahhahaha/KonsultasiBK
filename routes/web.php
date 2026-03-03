<?php

use Illuminate\Support\Facades\Route;

Route::fallback('App\Http\Controllers\Ctrl@notfound');

Route::get('/login', 'App\Http\Controllers\Ctrl@login');
Route::post('/login/process', 'App\Http\Controllers\Ctrl@loginact');
Route::get('/logout', 'App\Http\Controllers\Ctrl@logout');

Route::get('/register', 'App\Http\Controllers\Ctrl@register');
Route::get('/register/activation', 'App\Http\Controllers\Ctrl@loadactivation');

Route::get('/home', 'App\Http\Controllers\Ctrl@home');

Route::get('/userdata', 'App\Http\Controllers\Ctrl@userdata');
Route::post('/userdata/save', 'App\Http\Controllers\Ctrl@saveuser');
Route::get('/userdata/delete/{id}', 'App\Http\Controllers\Ctrl@deleteuser');
Route::post('/userdata/reset/{id}', 'App\Http\Controllers\Ctrl@userresetpassword');

Route::get('/setting', 'App\Http\Controllers\Ctrl@setting');
Route::post('/setting/update', 'App\Http\Controllers\Ctrl@savesetting');

Route::get('/database', 'App\Http\Controllers\Ctrl@databasePage');
Route::get('/database/export', 'App\Http\Controllers\Ctrl@exportDatabase');
Route::post('/database/import', 'App\Http\Controllers\Ctrl@importDatabase');
