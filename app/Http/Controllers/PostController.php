<?php

namespace App\Http\Controllers;

use App\Helper\ImageHelper;
use App\Helper\ResponseHelper;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class PostController extends Controller
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
    public function store(StorePostRequest $request)
    {
        try {
            // Get the image file from the request
            $imageFile = $request->file('url_image');

            // Save the file and get the public URL using the helper
            $fullUrl = ImageHelper::saveAndGenerateUrl($imageFile);

            $dataCreate = $request->all();
            $dataCreate['user_id'] = auth()->user()->id;
            $dataCreate['url_image'] = $fullUrl;

            $post = Post::create($dataCreate);

            if (!$post || !$post->wasRecentlyCreated) {
                return ResponseHelper::error(message: "Failed to create post. Please try again.");
            }

            return ResponseHelper::success(message: "Create post successfully");
        } catch (\Throwable $th) {
            return ResponseHelper::error(message: $th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Post $post)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePostRequest $request, $id)
    {
        //
        try {
            // Make sure that only the true owner can update
            $post = Post::findOrFail($id);
            Gate::authorize('modifyPost', $post);
            $post->update($request->all());
            return ResponseHelper::success(message: "Update post successfully");
        } catch (\Throwable $th) {
            return ResponseHelper::error(message: $th->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
        try {
            $post = Post::findOrFail($id);
            Gate::authorize('modifyPost', $post);
            $post->delete();
            return ResponseHelper::success(message: "Delete post successfully");
        } catch (\Throwable $th) {
            return ResponseHelper::error(message: $th->getMessage());
        }
    }

    public function getPostHistories(Request $request)
    {
        try {
            $posts = Post::where('user_id', Auth::id())->get();
            $dataCollection = PostResource::collection($posts);
            $responseData = [
                'user_id' => Auth::id(),
                'posts' => $dataCollection,
                'totalItems' => $posts->count(),
            ];
            return ResponseHelper::success(data: $responseData);
        } catch (\Throwable $th) {
            return ResponseHelper::error(message: $th->getMessage());
        }
    }

    public function getUserView(Request $request, $id)
    {
        try {
            $post = Post::findOrFail($id);
            Gate::authorize('modifyPost', $post);

        } catch (\Throwable $th) {
            return ResponseHelper::error(message: $th->getMessage());
        }
    }
}
