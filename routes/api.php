<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;

Route::post('/attendance/submit', [AttendanceController::class,'submit']);
