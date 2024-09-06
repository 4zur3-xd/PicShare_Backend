<?php

namespace App\Http\Controllers;

use App\Helper\ResponseHelper;
use App\Models\UserLike;
use App\Http\Requests\StoreUserLikeRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Http\Requests\UpdateUserLikeRequest;
use App\Models\Post;

class UserLikeController extends Controller
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
    public function store(StoreUserLikeRequest $request, $id)
    {
        try {
            $userLike = UserLike::create([
                'user_id' => $request->user()->id,
                'post_id' => $id,
            ]);

            if (!$userLike || $userLike->wasRecentlyCreated === false) {
                return ResponseHelper::error(message: "Failed to create liker. Please try again.");
            }

            $post = Post::findOrFail($id);
            $post->increment('like_count');

            // $updateRequest = new UpdatePostRequest();
            // $updateRequest->merge(['like_count' => Post::findOrFail($id)->like_count + 1]);
            // app(PostController::class)->update($updateRequest, $id);

            return ResponseHelper::success(message: "Liker created and post updated successfully");
        } catch (\Throwable $th) {
            return ResponseHelper::error(message: $th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(UserLike $userLike)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserLikeRequest $request, UserLike $userLike)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(UserLike $userLike)
    {
        //
    }
}
