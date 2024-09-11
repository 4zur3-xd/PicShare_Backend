<?php

namespace App\Http\Controllers;

use App\Helper\ResponseHelper;
use App\Models\UserLog;
use Illuminate\Http\Request;

class UserLogController extends Controller
{

    public function createUserLog($user)
    {
        try {
          $userLog=  UserLog::create([
                'user_id' => $user->id,
                'total_post' => 0,
                'total_view' => 0,
                'total_deleted' => 0,
                'total_like' => 0,
            ]);

            return ResponseHelper::success(message: 'UserLog created successfully.',data: $userLog);
        } catch (\Throwable $th) {
            return ResponseHelper::error(message: $th->getMessage());
        }
    }
    public function getUserLogs(Request $request)
    {
        try {
            $user = $request->user();

            if ($user) {
                $userLog = UserLog::where('user_id', $user->id)->first();

                if (!$userLog) {
                    return ResponseHelper::error(message: 'UserLog not found');
                }

                return ResponseHelper::success(message: 'UserLog retrieved successfully.', data: $userLog);
            }

        } catch (\Throwable $th) {
            return ResponseHelper::error(message: $th->getMessage());
        }
    }
}
