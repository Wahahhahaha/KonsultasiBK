<?php

use Illuminate\Support\Facades\Route;

Route::fallback('App\Http\Controllers\Ctrl@notfound');

Route::get('/login', 'App\Http\Controllers\Ctrl@login');
Route::post('/login/process', 'App\Http\Controllers\Ctrl@loginact');
Route::get('/logout', 'App\Http\Controllers\Ctrl@logout');

Route::get('/forgot-password', 'App\Http\Controllers\Ctrl@forgotPassword');
Route::post('/forgot-password/email', 'App\Http\Controllers\Ctrl@forgotPasswordByEmail');
Route::post('/forgot-password/phone', 'App\Http\Controllers\Ctrl@forgotPasswordByPhone');
Route::get('/reset-password', 'App\Http\Controllers\Ctrl@resetPasswordForm');
Route::get('/reset-password/phone', 'App\Http\Controllers\Ctrl@resetPasswordPhoneForm');
Route::post('/reset-password', 'App\Http\Controllers\Ctrl@resetPasswordSubmit');
Route::post('/reset-password/phone', 'App\Http\Controllers\Ctrl@resetPasswordPhoneSubmit');

Route::get('/home', 'App\Http\Controllers\Ctrl@home');

Route::get('/userdata', 'App\Http\Controllers\Ctrl@userdata');
Route::post('/userdata/save', 'App\Http\Controllers\Ctrl@saveuser');
Route::get('/userdata/delete/{id}', 'App\Http\Controllers\Ctrl@deleteuser');
Route::post('/userdata/reset/{id}', 'App\Http\Controllers\Ctrl@userresetpassword');
Route::get('/userdata/export', 'App\Http\Controllers\Ctrl@exportUsers');
Route::post('/userdata/import', 'App\Http\Controllers\Ctrl@importUsers');

Route::get('/teacherlist', 'App\Http\Controllers\Ctrl@teacherlist');
Route::get('/get-available-times', 'App\Http\Controllers\Ctrl@getAvailableTimes');
Route::post('/book-consult', 'App\Http\Controllers\Ctrl@bookConsult');
Route::post('/teacher/add', 'App\Http\Controllers\Ctrl@addTeacher');

Route::get('/myprofile', 'App\Http\Controllers\Ctrl@profile');
Route::post('/myprofile/update', 'App\Http\Controllers\Ctrl@updateprofile');
Route::post('/myprofile/update-schedule', 'App\Http\Controllers\Ctrl@updateSchedule');
Route::post('/myprofile/request-phone-otp', 'App\Http\Controllers\Ctrl@requestPhoneOtp');
Route::post('/myprofile/verify-phone-otp', 'App\Http\Controllers\Ctrl@verifyPhoneOtp');
Route::post('/myprofile/request-email-change', 'App\Http\Controllers\Ctrl@requestEmailChange');
Route::post('/myprofile/changepw', 'App\Http\Controllers\Ctrl@changePassword');
Route::get('/myprofile/verify-email', 'App\Http\Controllers\Ctrl@verifyEmailChange');

Route::get('/setting', 'App\Http\Controllers\Ctrl@setting');
Route::post('/setting/update', 'App\Http\Controllers\Ctrl@savesetting');

Route::get('/classdata', 'App\Http\Controllers\Ctrl@classdata');
Route::post('/classdata/add', 'App\Http\Controllers\Ctrl@addClass');
Route::post('/classdata/update', 'App\Http\Controllers\Ctrl@updateClass');
Route::post('/classdata/delete', 'App\Http\Controllers\Ctrl@deleteClass');
Route::get('/classdata/export', 'App\Http\Controllers\Ctrl@exportClasses');
Route::post('/classdata/import', 'App\Http\Controllers\Ctrl@importClasses');
Route::get('/gradedata/export', 'App\Http\Controllers\Ctrl@exportGrades');
Route::post('/gradedata/import', 'App\Http\Controllers\Ctrl@importGrades');
Route::get('/gradedata', 'App\Http\Controllers\Ctrl@gradedata');
Route::post('/gradedata/add', 'App\Http\Controllers\Ctrl@addGrade');
Route::post('/gradedata/update', 'App\Http\Controllers\Ctrl@updateGrade');
Route::post('/gradedata/delete', 'App\Http\Controllers\Ctrl@deleteGrade');
Route::get('/majordata', 'App\Http\Controllers\Ctrl@majordata');
Route::post('/majordata/add', 'App\Http\Controllers\Ctrl@addMajor');
Route::post('/majordata/update', 'App\Http\Controllers\Ctrl@updateMajor');
Route::post('/majordata/delete', 'App\Http\Controllers\Ctrl@deleteMajor');
Route::get('/majordata/export', 'App\Http\Controllers\Ctrl@exportMajors');
Route::post('/majordata/import', 'App\Http\Controllers\Ctrl@importMajors');

Route::get('/database', 'App\Http\Controllers\Ctrl@databasePage');
Route::get('/database/export', 'App\Http\Controllers\Ctrl@exportDatabase');
Route::post('/database/import', 'App\Http\Controllers\Ctrl@importDatabase');

Route::get('/chat', 'App\Http\Controllers\Ctrl@chat');
Route::get('/chat/search', 'App\Http\Controllers\Ctrl@searchConsultations');
Route::get('/chat/messages/{id}', 'App\Http\Controllers\Ctrl@getMessages');
Route::post('/chat/send', 'App\Http\Controllers\Ctrl@sendMessage');
Route::post('/chat/end', 'App\Http\Controllers\Ctrl@endConsultation');
Route::post('/chat/cancel', 'App\Http\Controllers\Ctrl@cancelConsultation');
Route::post('/chat/approve', 'App\Http\Controllers\Ctrl@approveConsultation');
Route::post('/chat/reject', 'App\Http\Controllers\Ctrl@rejectConsultation');
Route::post('/chat/report', 'App\Http\Controllers\Ctrl@submitConsultReport');
Route::get('/followups', 'App\Http\Controllers\Ctrl@followups');
Route::get('/permission', 'App\Http\Controllers\Ctrl@permissionPage');
Route::post('/permission/save', 'App\Http\Controllers\Ctrl@savePermissions');
Route::get('/activity-logs', 'App\Http\Controllers\Ctrl@activityLogsPage');
Route::post('/set-geo', 'App\Http\Controllers\Ctrl@setGeo');
Route::get('/notifications', 'App\Http\Controllers\Ctrl@notificationsPage');
Route::post('/notifications/delete', 'App\Http\Controllers\Ctrl@deleteNotifications');
Route::post('/notifications/mark-read', 'App\Http\Controllers\Ctrl@markNotificationRead');
Route::post('/notifications/mark-all-read', 'App\Http\Controllers\Ctrl@markAllNotificationsRead');
Route::get('/trash', 'App\Http\Controllers\Ctrl@trashPage');
Route::post('/trash/restore', 'App\Http\Controllers\Ctrl@restoreTrash');
Route::post('/trash/delete-permanent', 'App\Http\Controllers\Ctrl@deleteTrashPermanent');
Route::post('/trash/list', 'App\Http\Controllers\Ctrl@listTrash');
Route::post('/activity-logs/list', 'App\Http\Controllers\Ctrl@listActivityLogs');
