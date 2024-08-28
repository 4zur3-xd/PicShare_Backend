<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Validator;

class ApiAuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            $validateUser = Validator::make($request->all(), [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
            ]);

            if($validateUser->fails()){
                return [
                    'status' => false,
                    'errors' => $validateUser->errors(),
                ];
            }else{
                $user = User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => $request->password,
                ]);

                $user = $user->fresh();

                event(new Registered($user));

                $authToken = $user->createToken('auth_token')->plainTextToken;

                return [
                    'status' => true,
                    'user' => $user,
                    'access_token' => $authToken,
                ];
            }
        } catch (\Throwable $th) {
            return [
                'status' => false,
                'errors' => $th->getMessage(),
            ];
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
                return [
                    'status' => false,
                    'errors' => $validateUser->errors(),
                ];
            }else{
                $user = User::where('email', $request->email)->first();

                if(!$user || !Hash::check($request->password, $user->password)){
                    return [
                        'status' => false,
                        'errors' => 'Wrong password.'
                    ];
                }else{
                    $authToken = $user->createToken('auth_token')->plainTextToken;

                    return [
                        'status' => true,
                        'user' => $user,
                        'access_token' => $authToken,
                    ];
                }
            }
        } catch (\Throwable $th) {
            return [
                'status' => false,
                'errors' => $th->getMessage(),
            ];
        }
    }

    public function logout()
    {
        try {
            auth()->user()->tokens()->delete();

            return [
                'status' => true,
                'message' => "Logout successfully"
            ];
        } catch (\Throwable $th) {
            return [
                'status' => false,
                'errors' => $th->getMessage(),
            ];
        }
    }
}
