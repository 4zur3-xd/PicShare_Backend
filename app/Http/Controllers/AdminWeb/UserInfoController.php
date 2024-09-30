<?php

namespace App\Http\Controllers\AdminWeb;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UserInfoController extends Controller
{
    public function index ($id = null)
    {
        try {
            if($id == null){
                $msg = 'Please provide user id!';
                return view('errors.500')->with('error_info', $msg);
            }

            $userData = User::where('id', $id)->first();

            if(!$userData){
                $msg = 'User not found!';
                return view('errors.500')->with('error_info', $msg);
            }

            return view('userinfo')->with('userData', $userData);
        } catch (\Throwable $th) {
            return view('errors.500')->with('error_info', $th->getMessage());
        }
    }
}
