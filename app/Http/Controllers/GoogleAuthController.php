<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{

    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback()
    {

        try {

            $googleUser = Socialite::driver('google')->user();

            $usedEmail = User::where('email', $googleUser->getEmail())->where('google_id', null)->first();
            if($usedEmail){
                $error_info = __('alreadyRegisteredAccount');

                return view('errors.500')->with('error_info', $error_info);
            }

            $user = User::where('google_id', $googleUser->getId())->first();

            if(!$user){
                $newUser = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'url_avatar' => $googleUser->getAvatar(),
                    'email_verified_at' => now(),
                ]);

                Auth::login($newUser);

                return redirect(route('dashboard', absolute: false));
            }else{
                Auth::login($user);

                return redirect(route('dashboard', absolute: false));
            }

        } catch (\Throwable $th) {
            return view('errors.500')->with('error_info', $th->getMessage());
        }
        
    }

}
