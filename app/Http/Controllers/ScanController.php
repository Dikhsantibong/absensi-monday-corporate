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

        if (! $attendanceToken) {
            // Auto-create token jika belum ada (dari QR code web intranet)
            $attendanceToken = AttendanceToken::create([
                'token' => $token,
                'expires_at' => now()->addDays(1), // Default expire 1 hari
                'is_backdate' => false,
            ]);
        }

        if ($attendanceToken->used_at) {
            abort(410, 'Token sudah digunakan pada '.$attendanceToken->used_at->format('d/m/Y H:i:s'));
        }

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

        // Cek dan lock token dengan detail untuk error message yang lebih informatif
        // Jika token tidak ada, buat otomatis (dari web intranet)
        $attendanceToken = AttendanceToken::where('token', $token)
            ->lockForUpdate()
            ->first();

        if (! $attendanceToken) {
            // Auto-create token jika belum ada (dari QR code web intranet)
            $attendanceToken = AttendanceToken::create([
                'token' => $token,
                'expires_at' => now()->addDays(1), // Default expire 1 hari
                'unit_source' => $request->get('unit_source'),
                'is_backdate' => false,
            ]);
        }

        // Validasi token status
        if ($attendanceToken->used_at) {
            return response()->json([
                'success' => false,
                'error_type' => 'token_already_used',
                'message' => 'Token sudah digunakan pada '.$attendanceToken->used_at->format('d/m/Y H:i:s'),
            ], 422);
        }

        if ($attendanceToken->expires_at && $attendanceToken->expires_at < now()) {
            return response()->json([
                'success' => false,
                'error_type' => 'token_expired',
                'message' => 'Token sudah expired pada '.$attendanceToken->expires_at->format('d/m/Y H:i:s'),
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
                    'is_backdate' => $attendanceToken->is_backdate,
                    'backdate_reason' => $attendanceToken->backdate_data,
                    'source_ip' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);

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
