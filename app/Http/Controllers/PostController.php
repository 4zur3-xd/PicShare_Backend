<?php

namespace App\Http\Controllers;

use App\Enum\SharedPostType;
use App\Helper\ImageHelper;
use App\Helper\ResponseHelper;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Http\Resources\PostCollection;
use App\Http\Resources\PostDetailResource;
use App\Http\Resources\PostResource;
use App\Models\Post;
use App\Models\SharedPostWith;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
        DB::beginTransaction();
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

            $sharedWith = $dataCreate['shared_with'] ?? [];
            if ($dataCreate['type'] === SharedPostType::GROUP_MEMBERS && !empty($sharedWith)) {
                foreach ($sharedWith as $friendId) {
                   SharedPostWith::create([
                    'post_id' => $post->id,
                    'user_id' => $friendId,
                   ]);
                }
            }
            DB::commit();
            return ResponseHelper::success(message: "Create post successfully", data: $post);
        } catch (\Throwable $th) {
            DB::rollback();
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
            return ResponseHelper::success(message: "Update post successfully", data: $post);
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
            $perPage = $request->get('per_page', 18); 
    
            $posts = Post::where('user_id', Auth::id())
                         ->paginate($perPage);
        
            $dataCollection = PostResource::collection($posts);
        
             $dataCollection = new PostCollection($posts);

             return ResponseHelper::success(data: $dataCollection->toArray($request),message: "Get post histories successfully");
        } catch (\Throwable $th) {
            return ResponseHelper::error(message: $th->getMessage());
        }
    }

    public function detail(Request $request,$id)
    {
        try {
            $post = Post::with(['comments.user', 'comments.replies.user'])->find($id);
    
         
            if (!$post) {
                return ResponseHelper::error(message: 'Post not found.', statusCode: 404);
            }
    
            return ResponseHelper::success(
                message: 'Post details retrieved successfully.',
                data: new PostDetailResource($post)
            );
    
        } catch (\Throwable $th) {
            return ResponseHelper::error(message: 'An error occurred: ' . $th->getMessage());
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


    public function getUserLike(Request $request, $id)
    {
        //
    }   


}
