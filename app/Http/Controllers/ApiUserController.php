<?php

namespace App\Http\Controllers;

use App\Helper\ResponseHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ApiUserController extends Controller
{
    public function destroy(Request $request)
    {
        try {
            $validatePass = Validator::make($request->all(), [
                'password' => ['required', 'current_password'],
            ]);

            if($validatePass->fails()){
                $msg = 'Wrong password.';
                return ResponseHelper::error(message: $msg);
            }

            auth()->user()->tokens()->delete();

            $request->user()->delete();

            $msg = "User deleted.";

            return ResponseHelper::success(message: $msg);
        } catch (\Throwable $th) {
            return ResponseHelper::error(message: $th->getMessage());
        }
    }

    public function update(Request $request)
    {
        try {
            $validation = Validator::make($request->all(), [
                'name' => ['string', 'max:255'],
                'url_avatar' => ['string', 'max:255'],
            ]);

            if($validation->fails()){
                $msg = 'Validation fails.';
                return ResponseHelper::error(message: $msg);
            }

            $request->user()->fill($request->all());
            $request->user()->save();

            $msg = 'User updated.';

            return ResponseHelper::success(message: $msg);
        } catch (\Throwable $th) {
            return ResponseHelper::error(message: $th->getMessage());
        }
    }
}
