<?php

namespace App\Http\Controllers;

use App\Models\User;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Helper\ResponseHelper;

class ApiGoogleAuthController extends Controller
{
    public function callback(Request $request)
    {
        try {
            $token = $request->access_token;

            $client = new Client();
            $response = $client->get('https://www.googleapis.com/oauth2/v1/userinfo?access_token='.$token);
            $googleUser = json_decode($response->getBody()->getContents(), true);

            if(isset($googleUser['error'])) {
                $msg = 'Invalid ID token.';
                return ResponseHelper::error(message: $msg);
            }

            $usedEmail = User::where('email', $googleUser['email'])->first();
            if($usedEmail){
                $msg = "Sorry, this email has been registered to an account (Try login with this email and password, not \"Continue with Google\"!).";
                return ResponseHelper::error(message: $msg);
            }

            $user = User::where('google_id', $googleUser['id'])->first();

            if(!$user){
                $newUser = User::create([
                    'name' => $googleUser['given_name'],
                    'email' => $googleUser['email'],
                    'google_id' => $googleUser['id'],
                    'url_avatar' => $googleUser['picture'],
                    'email_verified_at' => now(),
                ]);

                $newUser = $newUser->fresh();

                $authToken = $newUser->createToken('auth_token')->plainTextToken;
                $userArray = $newUser->toArray();
                $userArray['access_token'] = $authToken;

                return ResponseHelper::success(data: $userArray);
            }else{
                $authToken = $user->createToken('auth_token')->plainTextToken;
                $userArray = $user->toArray();
                $userArray['access_token'] = $authToken;

                return ResponseHelper::success(data: $userArray);
            }
        } catch (\Throwable $th) {
            return ResponseHelper::error(message: $th->getMessage());
        }
    }
}
