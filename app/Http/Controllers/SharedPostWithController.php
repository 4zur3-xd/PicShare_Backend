<?php

namespace App\Http\Controllers;

use App\Helper\ResponseHelper;
use App\Models\SharedPostWith;
use App\Http\Requests\StoreSharedPostWithRequest;
use App\Http\Requests\UpdateSharedPostWithRequest;
use Illuminate\Http\Request;

class SharedPostWithController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        try {
            $postId = $request->input('post_id');
            $sharedUsers = $request->input('shared_users', []);

            foreach ($sharedUsers as $userId) {
                SharedPostWith::create([
                    'post_id' => $postId,
                    'user_id' => $userId,
                ]);
            }

            return ResponseHelper::success(message: "Shared post successfully");
        } catch (\Throwable $th) {
            return ResponseHelper::error(message:  __('somethingWentWrongWithMsg') . $th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(SharedPostWith $sharedPostWith)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSharedPostWithRequest $request, SharedPostWith $sharedPostWith)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SharedPostWith $sharedPostWith)
    {
        //
    }
}
