<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $table = 'attendance';

    protected $fillable = [
        'name',
        'division',
        'position',
        'token',
        'time',
        'signature',
        'unit_source',
        'is_weekly',     // WAJIB ADA
        'is_backdate',
        'backdate_reason',
        'source_ip',
        'user_agent',
    ];

    protected $casts = [
        'time' => 'datetime',
        'is_backdate' => 'boolean',
        'is_weekly' => 'integer', // WAJIB ADA
    ];
}
