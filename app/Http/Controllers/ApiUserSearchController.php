<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Helper\ResponseHelper;
use App\Http\Resources\UserSummaryResource;

class ApiUserSearchController extends Controller
{
    public function searchByName(Request $request, ?string $name = null)
    {
        try {
            $currUser = $request->user();

            if($name == null){
                $msg = __('pleaseEnterName');
                return ResponseHelper::error(message: $msg);
            }
    
            $result = User::where('name', 'like', '%' . $name . '%')->where('id', '!=', $currUser->id);
            $result = $result->get();

            if($result->isEmpty()){
                $msg = __('noUserFound');
                return ResponseHelper::success(message: $msg);
            }

            $data = UserSummaryResource::collection($result);

             return ResponseHelper::success(data: [
                 'totalItems' => $result->count(),
                 'users' => $data,
             ]);
    
        } catch (\Throwable $th) {
            return ResponseHelper::error(message:  __('somethingWentWrongWithMsg') . $th->getMessage());
        }
    }

    public function searchByCode(Request $request, ?string $code = null)
    {
        try {
            $currUser = $request->user();

            if($code == null){
                $msg = __('pleaseEnterUserCode');
                return ResponseHelper::error(message: $msg);
            }
    
            $result = User::where('user_code', $code)->where('user_code', '!=', $currUser->user_code);
            $result = $result->get();
    
            if($result->isEmpty()){
                $msg = __('noUserFound');
                return ResponseHelper::success(message: $msg);
            }

            $data = UserSummaryResource::collection($result);

            return ResponseHelper::success(data: [
                'totalItems' => $result->count(),
                'users' => $data,
            ]);
    
        } catch (\Throwable $th) {
            return ResponseHelper::error(message:  __('somethingWentWrongWithMsg') . $th->getMessage());
        }
    }
}
