<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceToken extends Model
{
    protected $table = 'attendance_tokens';

    protected $fillable = [
    'token',
    'user_id',
    'expires_at',
    'used_at',
    'unit_source',
    'is_weekly',  // Pastikan ada
    'is_backdate',
    'backdate_data',
    ];

    protected $casts = [
    'expires_at' => 'datetime',
    'used_at' => 'datetime',
    'is_backdate' => 'boolean',
    'is_weekly' => 'integer', // TAMBAHKAN cast ke integer
    ];

}
