<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\AttendanceToken;

class AttendanceController extends Controller
{
    public function form($token)
    {
        $tokenData = AttendanceToken::where('token',$token)
            ->where('expires_at','>',now())
            ->firstOrFail();

        return view('attendance.form', compact('token'));
    }

    public function submit(Request $request)
    {
        Attendance::create($request->all());
        return response()->json(['success'=>true]);
    }
}
