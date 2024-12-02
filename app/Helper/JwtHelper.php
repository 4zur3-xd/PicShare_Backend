<?php

namespace App\Helper;

use Tymon\JWTAuth\Facades\JWTAuth;

class JwtHelper
{
    public static function getPayloadData($refreshToken)
    {
        JWTAuth::setToken($refreshToken);

        $payload = JWTAuth::getPayload();

        return [
            'iss' => $payload->get('iss'), // Issuer
            'sub' => $payload->get('sub'), // Subject (user_id)
            'exp' => $payload->get('exp'), // Expiry time
            'iat' => $payload->get('iat'), // Issued time
            'aud' => $payload->get('aud'), // Audience
            'nbf' => $payload->get('nbf'), // Not Before
            'jti' => $payload->get('jti'), // JWT ID
        ];
    }

    public static function getUserIdFromRefreshToken($refreshToken)
    {
        $payload = JWTAuth::setToken($refreshToken)->getPayload();
        return $payload->get('sub'); 
    }

    public static function isRefreshTokenNearExpiry($refreshToken)
    {
        $payload = JWTAuth::setToken($refreshToken)->getPayload();
        $expiry = $payload->get('exp');
        return ($expiry - time()) < 600; 
    }
}
