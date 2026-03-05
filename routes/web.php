<?php

use Illuminate\Support\Facades\Route;

Route::fallback('App\Http\Controllers\Ctrl@notfound');

Route::get('/login', 'App\Http\Controllers\Ctrl@login');
Route::post('/login/process', 'App\Http\Controllers\Ctrl@loginact');
Route::get('/logout', 'App\Http\Controllers\Ctrl@logout');

Route::get('/home', 'App\Http\Controllers\Ctrl@home');

Route::get('/userdata', 'App\Http\Controllers\Ctrl@userdata');
Route::post('/userdata/save', 'App\Http\Controllers\Ctrl@saveuser');
Route::get('/userdata/delete/{id}', 'App\Http\Controllers\Ctrl@deleteuser');
Route::post('/userdata/reset/{id}', 'App\Http\Controllers\Ctrl@userresetpassword');

Route::get('/teacherlist', 'App\Http\Controllers\Ctrl@teacherlist');
Route::get('/get-available-times', 'App\Http\Controllers\Ctrl@getAvailableTimes');
Route::post('/book-consult', 'App\Http\Controllers\Ctrl@bookConsult');
Route::post('/teacher/add', 'App\Http\Controllers\Ctrl@addTeacher');

Route::get('/myprofile', 'App\Http\Controllers\Ctrl@profile');
Route::post('/myprofile/update', 'App\Http\Controllers\Ctrl@updateprofile');
Route::post('/myprofile/changepw', 'App\Http\Controllers\Ctrl@changepw');

Route::get('/setting', 'App\Http\Controllers\Ctrl@setting');
Route::post('/setting/update', 'App\Http\Controllers\Ctrl@savesetting');

Route::get('/database', 'App\Http\Controllers\Ctrl@databasePage');
Route::get('/database/export', 'App\Http\Controllers\Ctrl@exportDatabase');
Route::post('/database/import', 'App\Http\Controllers\Ctrl@importDatabase');

Route::get('/chat', 'App\Http\Controllers\Ctrl@chat');
Route::get('/chat/messages/{id}', 'App\Http\Controllers\Ctrl@getMessages');
Route::post('/chat/send', 'App\Http\Controllers\Ctrl@sendMessage');
Route::post('/chat/end', 'App\Http\Controllers\Ctrl@endConsultation');
Route::post('/chat/cancel', 'App\Http\Controllers\Ctrl@cancelConsultation');
Route::post('/chat/approve', 'App\Http\Controllers\Ctrl@approveConsultation');
Route::post('/chat/reject', 'App\Http\Controllers\Ctrl@rejectConsultation');
Route::post('/chat/report', 'App\Http\Controllers\Ctrl@submitConsultReport');
Route::get('/followups', 'App\Http\Controllers\Ctrl@followups');
