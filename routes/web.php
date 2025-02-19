<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/attendance', [AttendanceController::class, 'attendance']);
Route::get('/attendanceDeviceLog', [AttendanceController::class, 'testDB']);