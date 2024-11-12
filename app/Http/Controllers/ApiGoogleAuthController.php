<?php

namespace App\Http\Controllers;

use App\Helper\ResponseHelper;
use App\Models\User;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Tymon\JWTAuth\Facades\JWTAuth;

class ApiGoogleAuthController extends Controller
{
    public function callback(Request $request)
    {
        try {

            $token = $request->access_token;

            $googleUser = $this->getGoogleUserInfo($token);

            if (!$googleUser) {
                return ResponseHelper::error(message: __('invalidIDToken'));
            }

            $usedEmail = User::where('email', $googleUser['email'])->where('google_id', null)->first();
            if ($usedEmail) {
                return ResponseHelper::error(message: __('alreadyRegisteredAccount'));
            }

            $user = User::where('google_id', $googleUser['id'])->first();
            $refreshTokenTTL = config('jwt.refresh_ttl');
            if (!$user) {
                $user = $this->createNewUser($googleUser);
            }
            $userArray = $this->generateTokens($user, $refreshTokenTTL);
            App::setLocale($user->language);
            return ResponseHelper::success(data: $userArray, message: __('loginSuccessfully'));

        } catch (\Throwable $th) {
            return ResponseHelper::error(message: __('somethingWentWrongWithMsg') . $th->getMessage());
        }
    }

    private function getGoogleUserInfo($token)
    {
        try {
            $client = new Client();
            $response = $client->get('https://www.googleapis.com/oauth2/v1/userinfo?access_token=' . $token);
            $googleUser = json_decode($response->getBody()->getContents(), true);

            return isset($googleUser['error']) ? null : $googleUser;
        } catch (\Exception $e) {
            return null;
        }
    }

    private function createNewUser($googleUser)
    {
        $newUser = User::create([
            'name' => $googleUser['given_name'],
            'email' => $googleUser['email'],
            'google_id' => $googleUser['id'],
            'url_avatar' => $googleUser['picture'],
            'email_verified_at' => now(),
        ]);

        $userLogController = app(UserLogController::class);
        $userLogController->createUserLog($newUser);

        return $newUser->fresh();
    }

    private function generateTokens($user, $refreshTokenTTL)
    {
        $accessToken = JWTAuth::fromUser($user);
        $refreshToken = JWTAuth::customClaims([
            'exp' => now()->addMinutes($refreshTokenTTL)->getTimestamp(),
        ])->fromUser($user);

        $userArray = $user->toArray();
        $userArray['access_token'] = $accessToken;
        $userArray['refresh_token'] = $refreshToken;

        return $userArray;
    }
}
