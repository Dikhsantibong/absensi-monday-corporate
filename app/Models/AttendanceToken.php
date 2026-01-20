<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceToken extends Model
{
    protected $fillable = ['token','expires_at'];
}

