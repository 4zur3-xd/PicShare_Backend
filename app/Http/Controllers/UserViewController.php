<?php

namespace App\Http\Controllers;

use App\Helper\ResponseHelper;
use App\Models\UserView;
use App\Http\Requests\StoreUserViewRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Http\Requests\UpdateUserViewRequest;
use App\Models\Post;

class UserViewController extends Controller
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
    public function store(StoreUserViewRequest $request)
    {
        //
        try {
            // Tạo một UserLike mới
            $userLike = UserView::create([
                'user_id' => auth()->user()->id,
                'post_id' => $request->post_id,
            ]);
            if (!$userLike || $userLike->wasRecentlyCreated === false) {
                return ResponseHelper::error(message: "Failed to create user view. Please try again.");
            }
            return ResponseHelper::success(message: "User like created successfully");
        } catch (\Throwable $th) {
            return ResponseHelper::error(message: $th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(UserView $userView)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserViewRequest $request, UserView $userView)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(UserView $userView)
    {
        //
    }
}
