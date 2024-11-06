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

            return ResponseHelper::success(message: __('userLogCreatedSuccessfully'),data: $userLog);
        } catch (\Throwable $th) {
            return ResponseHelper::error(message:__('somethingWentWrongWithMsg') . $th->getMessage());
        }
    }
    public function getUserLogs(Request $request)
    {
        try {
            // Get the user_id from the query string, default to null if not provided
            $userId = $request->query('user_id');
    
            // If user_id is not provided, use the currently authenticated user's ID
            if (!$userId) {
                $user = $request->user();
                $userId = $user ? $user->id : null;
            }
    
            // If user_id is still null (i.e., no user authenticated), return an error
            if (!$userId) {
                return ResponseHelper::error(message: __('userNotAuthenticatedOrNoUIDProvided'));
            }
    
            // Look for the UserLog based on the user_id
            $userLog = UserLog::where('user_id', $userId)->first();
    
            // If no UserLog is found, return an error response
            if (!$userLog) {
                return ResponseHelper::error(message: __('userLogNotFound'));
            }
    
            // If UserLog is found, return a success response with the UserLog data
            return ResponseHelper::success(message: __('userLogRetrievedSuccessfully'), data: $userLog);
    
        } catch (\Throwable $th) {
            // Handle any exceptions and return an error message
            return ResponseHelper::error(message: __('somethingWentWrongWithMsg') . $th->getMessage());
        }
    }
    
}
