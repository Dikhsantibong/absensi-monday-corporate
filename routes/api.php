<?php

use App\Http\Controllers\AttendanceController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('attendance')->group(function () {
    Route::get('/', [AttendanceController::class, 'index'])->name('api.attendance.index');
    Route::get('/{id}', [AttendanceController::class, 'show'])->name('api.attendance.show');
});
