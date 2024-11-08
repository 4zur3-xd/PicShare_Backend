<?php

namespace App\Http\Controllers;

use App\Helper\ResponseHelper;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
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

            if($validateUser->fails()){
                return ResponseHelper::error(message: __('failToValidation') .  $validateUser->errors());
            }else{
                $user = User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => $request->password,
                ]);

                $user = $user->fresh();

                $userLogController = app(UserLogController::class);
                $userLogController->createUserLog($user);

                event(new Registered($user));

                DB::commit();

                $authToken = $user->createToken('auth_token')->plainTextToken;
                $userArray = $user->toArray();
                $userArray['access_token'] = $authToken;
                // return [
                //     'status' => true,
                //     'user' => $userArray,
                // ];
                $locale= $user->language;
                App::setLocale($locale);
                return ResponseHelper::success(data: $userArray,message: __('accRegisteredSuccessfully'));
            }
        } catch (\Throwable $th) {
            DB::rollback();
            return ResponseHelper::error(message: __('somethingWentWrongWithMsg') .  $th->getMessage());
        }
    }

    public function login(Request $request)
    {
        try {
            $validateUser = Validator::make($request->all(), [
                'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'exists:users,email'],
                'password' => ['required', Rules\Password::defaults()],
            ]);

            if($validateUser->fails()){
                return ResponseHelper::error(message: __('somethingWentWrongWithMsg') .  $validateUser->errors());
            }else{
                $user = User::where('email', $request->email)->first();

                if(!$user || !Hash::check($request->password, $user->password)){
                    return ResponseHelper::error(message: __('wrongPassword'));
                }else{
                    $authToken = $user->createToken('auth_token')->plainTextToken;
                    $userArray = $user->toArray();
                    $userArray['access_token'] = $authToken;
                    if ($user->status == 0) {
                        Auth::logout();
                        $msg =  __('thisAccHasBeenBanned') .  __('mailTo') . env('ADMIN_EMAIL', 'admin@picshare.com').   __('protestBan') ; 
                        return ResponseHelper::error(message: $msg, statusCode: 403);
                    }
                    $locale= $user->language;
                    App::setLocale($locale);
                    return ResponseHelper::success(data: $userArray,message: __('loginSuccessfully'));
                }
            }
        } catch (\Throwable $th) {
            return ResponseHelper::error(message:  __('somethingWentWrongWithMsg') .  $th->getMessage());
        }
    }

    public function logout()
    {
        try {
            auth()->user()->tokens()->delete();

            // return [
            //     'status' => true,
            //     'message' =>  __('logoutSuccessfully')
            // ];
            return ResponseHelper::success(message: __('logoutSuccessfully'));
        } catch (\Throwable $th) {
            return ResponseHelper::error(message:  __('somethingWentWrongWithMsg') . $th->getMessage());
        }
    }
}
