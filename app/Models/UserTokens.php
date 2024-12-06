<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserTokens extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'device_name',
        'device_id',
        'refresh_token',
        'expires_at'
    ];
}
