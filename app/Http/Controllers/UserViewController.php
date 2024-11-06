<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\UserView;
use App\Helper\ResponseHelper;
use Illuminate\Support\Facades\Gate;
use App\Http\Requests\UpdatePostRequest;
use App\Http\Requests\StoreUserViewRequest;
use App\Http\Requests\UpdateUserViewRequest;

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
    public function store(StoreUserViewRequest $request, $id)
    {
        try {
            $userLike = UserView::create([
                'user_id' => $request->user()->id,
                'post_id' => $id,
            ]);

            if (!$userLike || $userLike->wasRecentlyCreated === false) {
                return ResponseHelper::error(message: "Failed to create viewer. Please try again.");
            }

            return ResponseHelper::success(message: "Viewer created successfully");
        } catch (\Throwable $th) {
            return ResponseHelper::error(message: __('somethingWentWrongWithMsg') . $th->getMessage());
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
