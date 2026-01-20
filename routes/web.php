<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\ScanController;

/*
|--------------------------------------------------------------------------
| ROUTE ABSENSI (PUBLIC - VIA QR)
|--------------------------------------------------------------------------
| HANYA untuk project external
| Bisa diakses dari INTERNET (HTTPS)
| TANPA AUTH
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/absen/{token}', [AttendanceController::class,'form']);
