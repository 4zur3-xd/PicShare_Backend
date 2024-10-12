<?php

namespace App\Http\Controllers;

use App\Models\Appeal;
use Illuminate\Http\Request;
use App\Helper\ResponseHelper;

class AppealController extends Controller
{
    public function store(Request $request)
    {
        try {
            $user = $request->user();

            $appealText = $request->msg;

            Appeal::create([
                'user_id' => $user->id,
                'msg' => $appealText,
            ]);

            return ResponseHelper::success(message: 'Appeal sent.');
        } catch (\Throwable $th) {
            return ResponseHelper::error(message: $th->getMessage());
        }
    }
}
