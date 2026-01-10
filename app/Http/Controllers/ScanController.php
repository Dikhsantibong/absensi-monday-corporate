<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ScanController extends Controller
{
    public function form($token)
    {
        return view('scan', compact('token'));
    }

    public function submit(Request $request, $token)
    {
        Http::withHeaders([
            'X-API-KEY' => env('INTRANET_API_KEY')
        ])->post(env('INTRANET_API_URL'), [
            'token' => $token,
            'name' => $request->name,
            'division' => $request->division,
            'position' => $request->position,
        ]);

        return view('succes');
    }
}
