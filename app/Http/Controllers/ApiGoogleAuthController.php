<?php

namespace App\Http\Controllers;

use App\Helper\ImageHelper;
use App\Helper\ResponseHelper;
use App\Models\User;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Http;
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
        $imageFile = $this->downloadGoogleImage($googleUser['picture']);
        
        $newUser = User::create([
            'name' => $googleUser['given_name'],
            'email' => $googleUser['email'],
            'google_id' => $googleUser['id'],
            'url_avatar' => $imageFile,
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

    public function downloadGoogleImage($url)
{
    $response = Http::get($url);

    if ($response->ok()) {
        // Create a temporary file for the image
        $tempFile = tempnam(sys_get_temp_dir(), 'google-profile-');
        file_put_contents($tempFile, $response->body());

        // Convert the temporary file into an UploadedFile instance
        $uploadedFile = new \Illuminate\Http\UploadedFile(
            $tempFile,
            'google-profile-' . uniqid() . '.jpg',
            null,
            null,
            true // Set "true" for test mode (bypasses file validation)
        );

        // Use the saveAndGenerateUrl helper to save and generate the URL
        $newImageUrl = ImageHelper::saveAndGenerateUrl($uploadedFile, 'public/images');

        // Clean up the temporary file
        unlink($tempFile);

        return $newImageUrl;
    }

    return null;
}
}
