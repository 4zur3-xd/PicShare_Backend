<?php

namespace App\Http\Controllers;

use App\Models\User;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Helper\ResponseHelper;
use Illuminate\Support\Facades\App;

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
                $msg = __('invalidIDToken');
                return ResponseHelper::error(message: $msg);
            }

            $usedEmail = User::where('email', $googleUser['email'])->where('google_id', null)->first();
            if($usedEmail){
                $msg = __('alreadyRegisteredAccount');
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

                // create UserLog
                $userLogController = app(UserLogController::class);
                $userLogController->createUserLog($user);

                // fresh
                $newUser = $newUser->fresh();

                $authToken = $newUser->createToken('auth_token')->plainTextToken;
                $userArray = $newUser->toArray();
                $userArray['access_token'] = $authToken;
                $locale= $user->language;
                App::setLocale($locale);
                return ResponseHelper::success(data: $userArray,message: __('loginSuccessfully'));
            }else{
                $authToken = $user->createToken('auth_token')->plainTextToken;
                $userArray = $user->toArray();
                $userArray['access_token'] = $authToken;
                $locale= $user->language;
                App::setLocale($locale);
                return ResponseHelper::success(data: $userArray,message: __('loginSuccessfully'));
            }
        } catch (\Throwable $th) {
            return ResponseHelper::error(message: __('somethingWentWrongWithMsg') . $th->getMessage());
        }
    }
}
