<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\AttendanceToken;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    public function show($token)
    {
        $attendanceToken = AttendanceToken::where('token', $token)
            ->where('expires_at', '>=', now())
            ->whereNull('used_at')
            ->firstOrFail();

        return view('attendance.form', [
            'token' => $attendanceToken->token
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'token'     => 'required|string',
            'name'      => 'required|string|max:255',
            'division'  => 'required|string|max:255',
            'position'  => 'required|string|max:255',
            'signature' => 'required|string'
        ]);

        $token = AttendanceToken::where('token', $request->token)
            ->where('expires_at', '>=', now())
            ->whereNull('used_at')
            ->lockForUpdate()
            ->first();

        if (!$token) {
            return response()->json([
                'success' => false,
                'error_type' => 'invalid_token',
                'message' => 'Token tidak valid atau sudah digunakan'
            ], 422);
        }

        DB::transaction(function () use ($request, $token) {
            Attendance::create([
                'name'        => $request->name,
                'division'    => $request->division,
                'position'    => $request->position,
                'token'       => $request->token,
                'time'        => now(),
                'signature'   => $request->signature,
                'unit_source' => $token->unit_source,
                'is_backdate' => $token->is_backdate,
                'source_ip'   => request()->ip(),
                'user_agent'  => request()->userAgent(),
            ]);

            $token->update([
                'used_at' => now()
            ]);
        });

        return response()->json([
            'success' => true
        ]);
    }

    public function success()
    {
        return view('attendance.success');
    }
}
