<?php

namespace App\Http\Controllers;

use App\Models\User;
use Auth;
use Illuminate\Http\Request;
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

            $user = User::where('google_id', $googleUser->getId())->first();

            if(!$user){
                $newUser = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'url_avatar' => $googleUser->getAvatar(),
                ]);

                Auth::login($newUser);

                return redirect(route('dashboard', absolute: false));
            }else{
                Auth::login($user);

                return redirect(route('dashboard', absolute: false));
            }

        } catch (\Throwable $th) {
            dd('Something wrong! Info: '.$th->getMessage());
        }
        
    }

}
