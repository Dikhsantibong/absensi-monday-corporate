<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;

/*
|--------------------------------------------------------------------------
| ROUTE ABSENSI (PUBLIC - VIA QR)
|--------------------------------------------------------------------------
| HANYA untuk project external
| Bisa diakses dari INTERNET (HTTPS)
| TANPA AUTH
*/

// 1. FORM ABSENSI (hasil scan QR)
Route::get('/attendance/{token}', [AttendanceController::class, 'showForm'])
    ->name('attendance.form');

// 2. SUBMIT ABSENSI (AJAX / FETCH)
Route::post('/attendance/store', [AttendanceController::class, 'store'])
    ->name('attendance.store');

// 3. HALAMAN SUKSES
Route::get('/attendance/success', [AttendanceController::class, 'success'])
    ->name('attendance.success');
