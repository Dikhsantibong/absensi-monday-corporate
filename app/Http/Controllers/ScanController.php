<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ScanController extends Controller
{
    public function form($token)
    {
        // Validasi token sebelum menampilkan form
        // Jika token tidak ada, buat otomatis (dari QR code web intranet)
        $attendanceToken = AttendanceToken::where('token', $token)->first();

        if (!$attendanceToken) {
            // Auto-create token jika belum ada (dari QR code web intranet)
            $attendanceToken = AttendanceToken::create([
                'token' => $token,
                'expires_at' => now()->addDays(1), // Default expire 1 hari
                'is_backdate' => false,
            ]);
        }

        // REMOVED: Token sudah digunakan check - biarkan digunakan berkali-kali
        // if ($attendanceToken->used_at) {
        //     abort(410, 'Token sudah digunakan pada '.$attendanceToken->used_at->format('d/m/Y H:i:s'));
        // }

        // Cek hanya expiry
        if ($attendanceToken->expires_at && $attendanceToken->expires_at < now()) {
            abort(410, 'Token sudah expired pada '.$attendanceToken->expires_at->format('d/m/Y H:i:s'));
        }

        return view('scan', compact('token'));
    }

    public function submit(Request $request, $token): \Illuminate\Http\JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'division' => 'required|string|max:255',
                'position' => 'required|string|max:255',
                'signature' => 'required|string',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], 422);
        }

        // Cek token
        $attendanceToken = AttendanceToken::where('token', $token)->first();

        if (!$attendanceToken) {
            // Auto-create token jika belum ada (dari QR code web intranet)
            $attendanceToken = AttendanceToken::create([
                'token' => $token,
                'expires_at' => now()->addDays(1), // Default expire 1 hari
                'unit_source' => $request->get('unit_source'),
                'is_backdate' => false,
            ]);
        }

        // REMOVED: Token sudah digunakan check
        // if ($attendanceToken->used_at) {
        //     return response()->json([
        //         'success' => false,
        //         'error_type' => 'token_already_used',
        //         'message' => 'Token sudah digunakan pada '.$attendanceToken->used_at->format('d/m/Y H:i:s'),
        //     ], 422);
        // }

        // Cek hanya expiry
        if ($attendanceToken->expires_at && $attendanceToken->expires_at < now()) {
            return response()->json([
                'success' => false,
                'error_type' => 'token_expired',
                'message' => 'Token sudah expired pada '.$attendanceToken->expires_at->format('d/m/Y H:i:s'),
            ], 422);
        }

        // Cek duplikasi absensi untuk orang yang sama di hari yang sama
        $todayStart = now()->startOfDay();
        $todayEnd = now()->endOfDay();
        
        $existingAttendance = Attendance::where('name', $request->name)
            ->whereBetween('time', [$todayStart, $todayEnd])
            ->first();

        if ($existingAttendance) {
            return response()->json([
                'success' => false,
                'error_type' => 'already_attended',
                'message' => 'Anda sudah melakukan absensi hari ini pada ' . $existingAttendance->time->format('H:i:s'),
            ], 422);
        }

        try {
            DB::transaction(function () use ($request, $token, $attendanceToken) {
                Attendance::create([
                    'name' => $request->name,
                    'division' => $request->division,
                    'position' => $request->position,
                    'token' => $token,
                    'time' => now(),
                    'signature' => $request->signature,
                    'unit_source' => $attendanceToken->unit_source,
                    'is_backdate' => $attendanceToken->is_backdate ?? false,
                    'backdate_reason' => $attendanceToken->backdate_data,
                    'source_ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);

                // Update used_at untuk tracking (tapi tidak digunakan untuk validasi)
                $attendanceToken->update([
                    'used_at' => now(),
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Absensi berhasil disimpan',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan absensi: '.$e->getMessage(),
            ], 500);
        }
    }
}