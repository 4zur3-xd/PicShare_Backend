<?php

namespace App\Helper;

use Tymon\JWTAuth\Facades\JWTAuth;

class TokenHelper
{
    public static function generateRefreshToken($user)
    {
        $refreshTokenTTL = config('jwt.refresh_ttl'); // Time to live for refresh tokens
        return JWTAuth::customClaims([
            'exp' => now()->addMinutes($refreshTokenTTL)->getTimestamp(),
        ])->fromUser($user);
    }

    
    
}
