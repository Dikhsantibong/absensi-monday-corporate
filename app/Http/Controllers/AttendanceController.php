<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    public function form($token)
    {
        $attendanceToken = AttendanceToken::where('token', $token)
            ->where('expires_at', '>=', now())
            ->whereNull('used_at')
            ->firstOrFail();

        return view('attendance.form', [
            'token' => $attendanceToken->token,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'name' => 'required|string|max:255',
            'division' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'signature' => 'required|string',
        ]);

        $token = AttendanceToken::where('token', $request->token)
            ->where('expires_at', '>=', now())
            ->whereNull('used_at')
            ->lockForUpdate()
            ->first();

        if (! $token) {
            return response()->json([
                'success' => false,
                'error_type' => 'invalid_token',
                'message' => 'Token tidak valid atau sudah digunakan',
            ], 422);
        }

        DB::transaction(function () use ($request, $token) {
            Attendance::create([
                'name' => $request->name,
                'division' => $request->division,
                'position' => $request->position,
                'token' => $request->token,
                'time' => now(),
                'signature' => $request->signature,
                'unit_source' => $token->unit_source,
                'is_backdate' => $token->is_backdate,
                'source_ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            $token->update([
                'used_at' => now(),
            ]);
        });

        return response()->json([
            'success' => true,
        ]);
    }

    public function success()
    {
        return view('attendance.success');
    }

    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        $query = Attendance::query()->orderBy('time', 'desc');

        // Filter by date range
        if ($request->has('start_date')) {
            $query->whereDate('time', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->whereDate('time', '<=', $request->end_date);
        }

        // Filter by token
        if ($request->has('token')) {
            $query->where('token', $request->token);
        }

        // Filter by unit_source
        if ($request->has('unit_source')) {
            $query->where('unit_source', $request->unit_source);
        }

        // Filter by name (search)
        if ($request->has('name')) {
            $query->where('name', 'like', '%'.$request->name.'%');
        }

        // Filter by division
        if ($request->has('division')) {
            $query->where('division', 'like', '%'.$request->division.'%');
        }

        // Pagination
        $perPage = $request->get('per_page', 15);
        $maxPerPage = 100; // Limit maksimal per page
        $perPage = min($perPage, $maxPerPage);

        $attendances = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $attendances->items(),
            'pagination' => [
                'current_page' => $attendances->currentPage(),
                'last_page' => $attendances->lastPage(),
                'per_page' => $attendances->perPage(),
                'total' => $attendances->total(),
                'from' => $attendances->firstItem(),
                'to' => $attendances->lastItem(),
            ],
        ]);
    }

    public function show($id): \Illuminate\Http\JsonResponse
    {
        $attendance = Attendance::find($id);

        if (! $attendance) {
            return response()->json([
                'success' => false,
                'message' => 'Attendance not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $attendance,
        ]);
    }
}
