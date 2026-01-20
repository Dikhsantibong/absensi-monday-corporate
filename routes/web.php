<?php

use App\Http\Controllers\ScanController;
use Illuminate\Support\Facades\Route;

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

Route::get('/scan/{token}', [ScanController::class, 'form'])->name('scan.form');
Route::post('/scan/{token}', [ScanController::class, 'submit'])->name('scan.submit');
