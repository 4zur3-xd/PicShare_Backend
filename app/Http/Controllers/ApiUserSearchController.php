<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Helper\ResponseHelper;

class ApiUserSearchController extends Controller
{
    public function searchByName(Request $request, ?string $name = null)
    {
        try {
            $currUser = $request->user();

            if($name == null){
                $msg = 'Please enter a name.';
                return ResponseHelper::error(message: $msg);
            }
    
            $result = User::where('name', 'like', '%' . $name . '%')->where('id', '!=', $currUser->id);
            $result = $result->get();
    
            if($result->isEmpty()){
                $msg = 'No users found.';
                return ResponseHelper::success(message: $msg);
            }
    
            return ResponseHelper::success(data: $result);
        } catch (\Throwable $th) {
            return ResponseHelper::error(message: $th->getMessage());
        }
    }

    public function searchByCode(Request $request, ?string $code = null)
    {
        try {
            $currUser = $request->user();

            if($code == null){
                $msg = 'Please enter a user code.';
                return ResponseHelper::error(message: $msg);
            }
    
            $result = User::where('user_code', $code)->where('user_code', '!=', $currUser->user_code);
            $result = $result->get();
    
            if($result->isEmpty()){
                $msg = 'No users found.';
                return ResponseHelper::success(message: $msg);
            }
    
            return ResponseHelper::success(data: $result);
        } catch (\Throwable $th) {
            return ResponseHelper::error(message: $th->getMessage());
        }
    }
}
