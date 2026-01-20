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
        return view('scan', compact('token'));
    }

    public function submit(Request $request, $token): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'division' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'signature' => 'required|string',
        ]);

        $attendanceToken = AttendanceToken::where('token', $token)
            ->where('expires_at', '>=', now())
            ->whereNull('used_at')
            ->lockForUpdate()
            ->first();

        if (! $attendanceToken) {
            return response()->json([
                'success' => false,
                'error_type' => 'invalid_token',
                'message' => 'Token tidak valid atau sudah digunakan',
            ], 422);
        }

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
    }
}
