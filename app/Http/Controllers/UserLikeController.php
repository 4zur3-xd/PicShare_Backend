<?php

namespace App\Http\Controllers;

use App\Helper\ResponseHelper;
use App\Http\Requests\StoreUserLikeRequest;
use App\Http\Requests\UpdateUserLikeRequest;
use App\Models\Post;
use App\Models\UserLike;
use App\Models\UserLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

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
            DB::beginTransaction();
            $userLike = UserLike::create([
                'user_id' => $request->user()->id,
                'post_id' => $id,
            ]);

            if (!$userLike || $userLike->wasRecentlyCreated === false) {
                return ResponseHelper::error(message: "Failed to create liker. Please try again.");
            }

            $post = Post::findOrFail($id);
            $post->increment('like_count');

            // Find UserLog or create new if not exists
            $userLog = UserLog::firstOrCreate(
                ['user_id' => $post->user_id]
            );
            $userLog->increment('total_like');

            DB::commit();
            return ResponseHelper::success(message: "Liker created and post updated successfully");
        } catch (\Throwable $th) {
            DB::rollback();
            return ResponseHelper::error(message:  __('somethingWentWrongWithMsg') . $th->getMessage());
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
    public function destroy(Request $request,$id)
    {
        try {
            $validateUser = Validator::make($request->all(), [
                'user_id' => ['required', 'integer'],
            ]);

            if ($validateUser->fails()) {
                return ResponseHelper::error(message: $validateUser->errors()->first());
            }

            $userId = $request->input('user_id');
            // Find and delete the UserLike based on user_id and post_id
            $userLike = UserLike::where('user_id', $request->user()->id)
                ->where('post_id', $id)
                ->firstOrFail();
            $userLike->delete();

            $post = Post::findOrFail($id);
            $post->decrement('like_count');

            // Find UserLog or create new if not exists
            $userLog = UserLog::firstOrCreate(
                ['user_id' => $userId]
            );
            $userLog->decrement('total_like');

            DB::commit();
            return ResponseHelper::success(message: "Updated successfully");
        } catch (\Throwable $th) {
            DB::rollback();
            return ResponseHelper::error(message:  __('somethingWentWrongWithMsg') . $th->getMessage());
        }
    }
}
