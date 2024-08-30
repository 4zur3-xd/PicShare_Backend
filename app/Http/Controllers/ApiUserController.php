<?php

namespace App\Http\Controllers;

use App\Helper\ResponseHelper;
use GuzzleHttp\Client;
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
}
