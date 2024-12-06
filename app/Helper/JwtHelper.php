<?php

namespace App\Helper;

use App\Models\UserTokens;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Crypt;
use Tymon\JWTAuth\Token;
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




    public static function createRefreshToken(string $refreshToken,$userId,$deviceId,$deviceName)
    {   
        $refreshTokenTTL = config('jwt.refresh_ttl'); // Time to live for refresh tokens
        UserTokens::create([
            'user_id' => $userId,
            'refresh_token' => $refreshToken,
            'device_name' => $deviceName,
            'device_id' => $deviceId,
            'expires_at' =>now()->addMinutes($refreshTokenTTL)->getTimestamp(),
        ]);
    }

    public static function getRefreshTokenFromDb(string $refreshToken){
            $refreshTokenRecord = UserTokens::where('refresh_token', $refreshToken)->first();
            return $refreshTokenRecord;
    }
    public static function deleteRefreshTokenWithDevice($userId,$deviceId)
    {
        UserTokens::where('user_id',$userId)->where('device_id',$deviceId)->delete();
    }
    public static function deleteRefreshTokenWithUserId($userId)
    {
        UserTokens::where('user_id',$userId)->where('expires_at', '>', now()->getTimestamp())->delete();
    }
    public static function deleteRefreshToken($refreshToken)
    {
        UserTokens::where('refresh_token',$refreshToken)->delete();
    }

    public static function deleteAllRefreshTokenOfUser($userId)
    {
        UserTokens::where('user_id',$userId)->delete();
    }


}
