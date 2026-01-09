<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceToken extends Model
{
    protected $table = 'attendance_tokens';

    protected $fillable = [
        'token',
        'expires_at',
        'used_at',
        'user_id',
        'unit_source',
        'backdate_data',
        'is_backdate'
    ];

    protected $dates = [
        'expires_at',
        'used_at'
    ];
}
