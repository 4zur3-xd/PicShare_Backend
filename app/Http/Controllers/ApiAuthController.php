<?php

namespace App\Http\Controllers;

use App\Helper\ResponseHelper;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class ApiAuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            DB::beginTransaction();
            $validateUser = Validator::make($request->all(), [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
            ]);

            if ($validateUser->fails()) {
                return ResponseHelper::error(message: __('failToValidation') . $validateUser->errors());
            } else {
                $user = User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => bcrypt($request->password),
                ]);

                $user = $user->fresh();

                $userLogController = app(UserLogController::class);
                $userLogController->createUserLog($user);

                event(new Registered($user));

                DB::commit();

                // $authToken = $user->createToken('auth_token')->plainTextToken;
                $accessToken = JWTAuth::fromUser($user);
                $refreshTokenTTL = config('jwt.refresh_ttl');
                $refreshToken = JWTAuth::customClaims([
                    'exp' => now()->addMinutes($refreshTokenTTL)->getTimestamp(),
                ])->fromUser($user);
                $userArray = $user->toArray();
                $userArray['access_token'] = $accessToken;
                $userArray['refresh_token'] = $refreshToken;
                $locale = $user->language;
                App::setLocale($locale);
                return ResponseHelper::success(data: $userArray, message: __('accRegisteredSuccessfully'));
            }
        } catch (\Throwable $th) {
            DB::rollback();
            return ResponseHelper::error(message: __('somethingWentWrongWithMsg') . $th->getMessage());
        }
    }

    public function login(Request $request)
    {
        try {
            $validateUser = Validator::make($request->all(), [
                'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'exists:users,email'],
                'password' => ['required', Rules\Password::defaults()],
            ]);

            if ($validateUser->fails()) {
                return ResponseHelper::error(message: __('somethingWentWrongWithMsg') . $validateUser->errors());
            }
            // second approach
            $credentials = $request->only('email', 'password');
            if (!$token = JWTAuth::attempt($credentials)) {
                return ResponseHelper::error(message: __('wrongPassword'));
            }
            $user = auth()->user();
            if ($user->status === 0) {
                JWTAuth::invalidate($token);
                $msg = __('thisAccHasBeenBanned') . __('mailTo') . env('ADMIN_EMAIL', 'admin@picshare.com') . __('protestBan');
                return ResponseHelper::error(message: $msg, statusCode: 403);
            }
            // Set user language
            App::setLocale($user->language);
            $refreshTokenTTL = config('jwt.refresh_ttl');

           
            $userArray = $user->toArray();
            $accessToken = $token;
            $refreshToken = JWTAuth::customClaims([
                'exp' => now()->addMinutes($refreshTokenTTL)->getTimestamp(),
            ])->fromUser($user);

            $userArray['access_token'] = $accessToken;
            $userArray['refresh_token'] = $refreshToken;

            return ResponseHelper::success(data: $userArray, message: __('loginSuccessfully'));

        } catch (\Throwable $th) {
            return ResponseHelper::error(message: __('somethingWentWrongWithMsg') . $th->getMessage());
        }
    }

    public function logout()
    {
        try {
            // auth()->user()->tokens()->delete();
            JWTAuth::invalidate(JWTAuth::getToken());
            return ResponseHelper::success(message: __('logoutSuccessfully'));
        } catch (\Throwable $th) {
            return ResponseHelper::error(message: __('somethingWentWrongWithMsg') . $th->getMessage());
        }
    }

    public function refreshNewAccessToken(Request $request)
    {
        $refreshToken = $request->header('Authorization');

        // Check if there is no refresh token
        if (!$refreshToken) {
            return ResponseHelper::error(message: __('refreshTokenIsRequired'), statusCode: 400);   
        }

        // Handle removing "Bearer " in Authorization header
        if (substr($refreshToken, 0, 7) === 'Bearer ') {
            $refreshToken = substr($refreshToken, 7);
        }

        try {
            // Set the current token to the refresh token passed in
            JWTAuth::setToken($refreshToken);

            // Check if refresh token is still valid
            if (!JWTAuth::check()) {
                return ResponseHelper::error(message: __('tokenExpired'), statusCode: 401);   
            }

            // Create new access token without refreshing refresh token
            $user = JWTAuth::authenticate($refreshToken);
            $accessToken = JWTAuth::fromUser($user);
            
            return ResponseHelper::success(data: ['access_token' => $accessToken], message: __('updateSuccessfully'));
          
        } catch (TokenInvalidException $e) {
            return ResponseHelper::error(message: __('tokenNotValid'), statusCode: 400);   
        }
    }
}
