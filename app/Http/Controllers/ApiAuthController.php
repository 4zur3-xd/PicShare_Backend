<?php

namespace App\Http\Controllers;

use App\Helper\ResponseHelper;
use App\Mail\LoginEventMail;
use App\Helper\TokenHelper;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules;
use PragmaRX\Google2FAQRCode\Google2FA;
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
                'device_id' => ['nullable', 'string',],
                'device_name' => ['nullable', 'string', ],
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

            $userArray = $user->toArray();
            $accessToken = $token;

            $userArray['access_token'] = $accessToken;


            $message = __('continueToCheck2FA');
            // if user does not enable 2FA
            if (!$user->google2fa_enable && $user->google2fa_secret === null) {
                $refreshToken = TokenHelper::generateRefreshToken($user);
                $userArray['refresh_token'] = $refreshToken;
                $message = __('loginSuccessfully');
            }


            // send login event to mail
            if($user->is_login_email_enabled){
                Mail::to($request->user())->send(new LoginEventMail(deviceId: $request->device_id, deviceName: $request->device_name));
            }
            

            return ResponseHelper::success(data: $userArray, message: $message);

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
    public function toggle2FA(Request $request)
    {
        try {
            DB::beginTransaction();
            // Validate the incoming request data (password and otp if enabling 2FA)
            $request->validate([
                'password' => 'required|string',
            ]);

            $user = auth()->user();
            $appName = config('app.name', 'MyApp');

            // Check if the provided password matches the user's password
            if (!Hash::check($request->input('password'), $user->password)) {
                return ResponseHelper::error(message: __('wrongPassword'), statusCode: 400);
            }

            // Handle enabling or disabling 2FA based on the current state
            if ($user->google2fa_enable) {
                // Disable 2FA
                $user->update([
                    'google2fa_enable' => false,
                    'google2fa_secret' => null,
                ]);
                DB::commit();

                return ResponseHelper::success(message: __('updateSuccessfully'));
            } else {
                // Enable 2FA
                $google2fa = new Google2FA();

                // Generate a unique secret for the user
                $secret = $google2fa->generateSecretKey();

                // Generate the QR Code image
                $qrCodeUrl = $google2fa->getQRCodeUrl(
                    $appName, 
                    $user->email, // User email for the label
                    $secret // The user's unique secret key
                );

                // Store the secret temporarily 
                $tempKey = "2fa_secret_{$user->id}";
                Cache::put($tempKey, encrypt($secret), now()->addMinutes(15)); // Store for 15 minutes

                DB::commit();

                return ResponseHelper::success(message: __('updateSuccessfully'),
                    data: [
                        'qr_code_url' => $qrCodeUrl,
                        'code' => $secret,
                    ],
                );

            }

        } catch (\Throwable $th) {
            DB::rollBack();
            return ResponseHelper::error(message: __('somethingWentWrongWithMsg') . $th->getMessage());
        }
    }

    public function verify2FA(Request $request)
    {
        DB::beginTransaction();
        try {
            if (!$request->has('code')) {
                return ResponseHelper::error(message: __('2FACodeRequired'), statusCode: 400);
            }

            $google2fa = new Google2FA();

            // Decrypt the stored secret
            $secret = decrypt($request->user()->google2fa_secret);

            // Verify the OTP
            $isValid = $google2fa->verifyKey($secret, $request->input('code'));

            DB::commit();
            if ($isValid) {
                $refreshToken = TokenHelper::generateRefreshToken($request->user());
                return ResponseHelper::success(message: __('verify2FASuccessfully'),
                    data: [
                        'refresh_token' => $refreshToken,
                    ], );
            } else {
                return ResponseHelper::error(message: __('invalid2FACode'), statusCode: 400);
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            return ResponseHelper::error(message: __('somethingWentWrongWithMsg') . $th->getMessage());
        }
    }

    public function confirmEnable2FA(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'code' => 'required|string',
            ]);

            $user = auth()->user();
            $tempKey = "2fa_secret_{$user->id}";

            // Retrieve the temporary secret from cache
            $encryptedSecret = Cache::get($tempKey);
            if (!$encryptedSecret) {
                return ResponseHelper::error(message: __('2FASetupExpired'), statusCode: 400);
            }

            // Decrypt the secret
            $google2fa = new Google2FA();
            $secret = decrypt($encryptedSecret);

            // Verify the OTP
            if ($google2fa->verifyKey($secret, $request->input('code'))) {
                // OTP is valid, save 2FA details to the database
                $user->update([
                    'google2fa_secret' => encrypt($secret),
                    'google2fa_enable' => true,
                ]);

                // Clean up the cache
                Cache::forget($tempKey);
                DB::commit();
                return ResponseHelper::success(message: __('2faEnabledSuccessfully'));
            } else {
                DB::commit();
                // Invalid OTP, return error
                return ResponseHelper::error(message: __('invalid2FACode'), statusCode: 400);
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            return ResponseHelper::error(message: __('somethingWentWrongWithMsg') . $th->getMessage());
        }
    }

}
