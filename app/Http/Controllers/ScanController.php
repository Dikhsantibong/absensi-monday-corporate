<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ScanController extends Controller
{
    public function form($token, Request $request)
    {
        // 1. Ambil Parameter
        $unitSource = $request->query('unit', 'mysql');
        $isWeeklyInput = $request->query('weekly'); 
        
        // 2. Tentukan is_weekly (Default 0)
        $isWeekly = 0;
        
        // Cek 1: Dari URL parameter
        if ($isWeeklyInput == '1' || $isWeeklyInput == 'true') {
            $isWeekly = 1;
        }

        // Cek 2: FORCE dari Token (Paling Kuat)
        if (str_contains($token, 'WEEKLY')) {
            $isWeekly = 1;
        }

        // 3. Cek/Buat Token
        $attendanceToken = AttendanceToken::where('token', $token)->first();

        if (!$attendanceToken) {
            // Auto-create token baru
            $attendanceToken = AttendanceToken::create([
                'token' => $token,
                'expires_at' => now()->addDays(1),
                'unit_source' => $unitSource,
                'is_weekly' => $isWeekly,
                'is_backdate' => 0,
            ]);
        } else {
            // Update token existing jika perlu
            if ($attendanceToken->is_weekly != $isWeekly) {
                $attendanceToken->update(['is_weekly' => $isWeekly]);
            }
        }

        // 4. Cek Expiry (Safe Parsing)
        if ($attendanceToken->expires_at) {
            $expiryDate = is_string($attendanceToken->expires_at) 
                ? \Carbon\Carbon::parse($attendanceToken->expires_at) 
                : $attendanceToken->expires_at;
            
            if ($expiryDate < now()) {
                abort(410, 'Token sudah expired pada ' . $expiryDate->format('d/m/Y H:i:s'));
            }
        }

        return view('scan', compact('token', 'unitSource', 'isWeekly'));
    }

    public function submit(Request $request, $token): \Illuminate\Http\JsonResponse
    {
        // 1. Validasi Input
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'division' => 'required|string|max:255',
                'position' => 'required|string|max:255',
                'signature' => 'required|string',
                'unit_source' => 'required|string',
                // Terima string '0','1', atau integer
                'is_weekly' => 'nullable', 
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], 422);
        }

        // 2. Logika Penentuan Nilai (PARANOID MODE)
        
        // Ambil unit
        $unitSource = $request->input('unit_source') ?? 'mysql';

        // Tentukan is_weekly (Default 0)
        $isWeekly = 0;

        // Cek input
        $inputWeekly = $request->input('is_weekly');
        if ($inputWeekly == '1' || $inputWeekly == 1 || $inputWeekly == 'true') {
            $isWeekly = 1;
        }

        // FORCE CHECK: Jika token mengandung kata 'WEEKLY', PASTI 1
        if (str_contains($token, 'WEEKLY')) {
            $isWeekly = 1;
        }

        // Debug Log
        Log::info("Submitting Attendance for {$token}", [
            'is_weekly_result' => $isWeekly,
            'token_contains_weekly' => str_contains($token, 'WEEKLY')
        ]);

        // 3. Proses Database
        $attendanceToken = AttendanceToken::where('token', $token)->first();

        if (!$attendanceToken) {
            $attendanceToken = AttendanceToken::create([
                'token' => $token,
                'expires_at' => now()->addDays(1),
                'unit_source' => $unitSource,
                'is_weekly' => $isWeekly,
                'is_backdate' => 0,
            ]);
        } else {
            // Update token existing dengan status yang benar
            $attendanceToken->update([
                'is_weekly' => $isWeekly,
                'unit_source' => $unitSource
            ]);
        }

        // Cek Expiry
        if ($attendanceToken->expires_at) {
            $expiryDate = is_string($attendanceToken->expires_at) 
                ? \Carbon\Carbon::parse($attendanceToken->expires_at) 
                : $attendanceToken->expires_at;
            
            if ($expiryDate < now()) {
                return response()->json([
                    'success' => false,
                    'error_type' => 'token_expired',
                    'message' => 'Token sudah expired pada ' . $expiryDate->format('d/m/Y H:i:s'),
                ], 422);
            }
        }

        // Cek Duplikasi
        $todayStart = now()->startOfDay();
        $todayEnd = now()->endOfDay();
        
        $existingAttendance = Attendance::where('name', $request->name)
            ->whereBetween('time', [$todayStart, $todayEnd])
            ->where('unit_source', $unitSource)
            ->where('is_weekly', $isWeekly) // Cek duplikat juga berdasarkan weekly
            ->first();

        if ($existingAttendance) {
            $weeklyText = $isWeekly ? ' weekly' : '';
            return response()->json([
                'success' => false,
                'error_type' => 'already_attended',
                'message' => 'Anda sudah melakukan absensi' . $weeklyText . ' hari ini pada ' . $existingAttendance->time->format('H:i:s'),
            ], 422);
        }

        // Simpan Absensi
        try {
            DB::transaction(function () use ($request, $token, $attendanceToken, $unitSource, $isWeekly) {
                Attendance::create([
                    'name' => $request->name,
                    'division' => $request->division,
                    'position' => $request->position,
                    'token' => $token,
                    'time' => now(),
                    'signature' => $request->signature,
                    'unit_source' => $unitSource,
                    // PASTIKAN NILAI INI MENGGUNAKAN VARIABEL YANG SUDAH DI-FORCE
                    'is_weekly' => $isWeekly, 
                    'is_backdate' => $attendanceToken->is_backdate ?? 0,
                    'backdate_reason' => $attendanceToken->backdate_reason ?? null,
                    'source_ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);

                $attendanceToken->update([
                    'used_at' => now(),
                ]);
            });

            $weeklyText = $isWeekly ? ' weekly' : '';
            return response()->json([
                'success' => true,
                'message' => 'Absensi' . $weeklyText . ' berhasil disimpan',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan absensi: '.$e->getMessage(),
            ], 500);
        }
    }
}
