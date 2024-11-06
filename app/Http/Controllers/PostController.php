<?php

namespace App\Http\Controllers;

use App\Enum\FriendStatus;
use App\Enum\SharedPostType;
use App\Events\PostEvent;
use App\Helper\ImageHelper;
use App\Helper\ResponseHelper;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Http\Resources\PostCollection;
use App\Http\Resources\PostDetailResource;
use App\Http\Resources\PostResource;
use App\Http\Resources\UserSummaryResource;
use App\Models\Friend;
use App\Models\Post;
use App\Models\Report;
use App\Models\SharedPostWith;
use App\Models\User;
use App\Models\UserLog;
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
            $user = auth()->user();

            // Get the image file from the request
            $imageFile = $request->file('url_image');

            // Save the file and get the public URL using the helper
            $fullUrl = ImageHelper::saveAndGenerateUrl($imageFile);

            $dataCreate = $request->all();
            $dataCreate['user_id'] = $user->id;
            $dataCreate['url_image'] = $fullUrl;

            // Ensure latitude and longitude are converted to double if they exist
            if (isset($dataCreate['latitude'])) {
                $dataCreate['latitude'] = (double) $dataCreate['latitude'];
            }

            if (isset($dataCreate['longitude'])) {
                $dataCreate['longitude'] = (double) $dataCreate['longitude'];
            }

            $post = Post::create($dataCreate);

            if (!$post || !$post->wasRecentlyCreated) {
                return ResponseHelper::error(message: __('failToCreatePostAndTryAgain'));
            }

            // Find UserLog or create new if not exists
            $userLog = UserLog::firstOrCreate(
                ['user_id' => $user->id]
            );
            $userLog->increment('total_post');

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

            // send event
           $this->sentPostCreationEvent($dataCreate['type'], $post,$sharedWith);
            return ResponseHelper::success(message: __('createPostSuccessfully'), data: $post);
        } catch (\Throwable $th) {
            DB::rollback();
            return ResponseHelper::error(message: __('somethingWentWrongWithMsg') . $th->getMessage());
        }
    }


    private function getFriendsForPost($user)
    {
        return Friend::where(function ($query) use ($user) {
            $query->where('user_id', $user->id)
                ->orWhere('friend_id', $user->id);
        })->where('status', FriendStatus::FRIEND)
            ->pluck('friend_id') // Lấy danh sách ID bạn bè
            ->toArray();
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
    public function destroy(Request $request, $id)
    {
        //
        try {
            DB::beginTransaction();
            $post = Post::findOrFail($id);
            Gate::authorize('modifyPost', $post);
            $likeCount = $post->like_count;
            $post->delete();
            // Find UserLog or create new if not exists
            $userLog = UserLog::firstOrCreate(
                ['user_id' => $request->user()->id]
            );
            $userLog->decrement('total_post');
            $userLog->decrement('total_like', $likeCount);
            $userLog->increment('total_deleted');
            DB::commit();
            return ResponseHelper::success(message: __('deletePostSuccessfully'));
        } catch (\Throwable $th) {
            DB::rollback();
            return ResponseHelper::error(message: __('somethingWentWrongWithMsg') . $th->getMessage());
        }
    }

    public function getPostHistories(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 18);

            // Get user_id from the query string; if not provided, use Auth::id()
            $userId = $request->query('user_id', Auth::id());

            $posts = Post::where('user_id', $userId)
                ->paginate($perPage);

            $dataCollection = PostResource::collection($posts);

            $dataCollection = new PostCollection($posts);

            return ResponseHelper::success(data: $dataCollection->toArray($request), message: __('getPostHistorySuccessfully'));
        } catch (\Throwable $th) {
            return ResponseHelper::error(message: __('somethingWentWrongWithMsg') . $th->getMessage());
        }
    }

    public function detail(Request $request, $id)
    {
        try {
            $post = Post::with(['user', 'comments.user', 'comments.replies.user'])->find($id);

            if (!$post) {
                return ResponseHelper::error(message: __('postNotFound'), statusCode: 404);
            }

            return ResponseHelper::success(
                message: __('postDetailRetrievedSuccessfully'),
                data: new PostDetailResource($post)
            );

        } catch (\Throwable $th) {
            return ResponseHelper::error(message: __('somethingWentWrongWithMsg') . $th->getMessage());
        }
    }

    public function getUserView(Request $request, $id)
    {
        try {
            $post = Post::findOrFail($id);
            Gate::authorize('modifyPost', $post);

            $curUser = $request->user()->id;

            $viewers = User::join('user_views', 'users.id', '=', 'user_views.user_id')
                ->where('user_views.post_id', $id)
                ->where('users.id', '!=', $curUser)
                ->select('users.*')
                ->get();

            if ($viewers->isEmpty()) {
                $msg = 'No viewers.';
                return ResponseHelper::success(message: $msg);
            }

            $data = [];
            foreach ($viewers as $viewer) {
                $userData = [
                    'id' => $viewer['id'],
                    'url_avatar' => $viewer['url_avatar'],
                    'name' => $viewer['name'],
                ];

                array_push($data, $userData);
            }

            return ResponseHelper::success(data: [
                'totalItems' => $viewers->count(),
                'user_views' => $data,
            ]);
        } catch (\Throwable $th) {
            return ResponseHelper::error(message: __('somethingWentWrongWithMsg') . $th->getMessage());
        }
    }

    public function getUserLike(Request $request, $id)
    {
        try {
            $post = Post::findOrFail($id);
            // Gate::authorize('modifyPost', $post);

            $curUser = $request->user()->id;

            $likers = User::join('user_likes', 'users.id', '=', 'user_likes.user_id')
                ->where('user_likes.post_id', $id)
            // ->where('users.id', '!=', $curUser)
                ->select('users.*')
                ->get();

            if ($likers->isEmpty()) {
                $msg = 'Noone likes =)).';
                return ResponseHelper::success(message: $msg);
            }

            $data = [];
            foreach ($likers as $liker) {
                $userData = [
                    'id' => $liker['id'],
                    'url_avatar' => $liker['url_avatar'],
                    'name' => $liker['name'],
                ];

                array_push($data, $userData);
            }

            return ResponseHelper::success(data: [
                'totalItems' => $likers->count(),
                'user_likes' => $data,
            ]);
        } catch (\Throwable $th) {
            return ResponseHelper::error(message: __('somethingWentWrongWithMsg') . $th->getMessage());
        }
    }

    public function postReport(Request $request, $id)
    {
        try {
            $data = $request->all();

            if (!isset($data['reason'])) {
                $msg = __('noReasonDelivered');
                return ResponseHelper::error(message: $msg);
            }

            if ($data['reason'] == null) {
                $msg = __('noReasonDelivered');
                return ResponseHelper::error(message: $msg);
            }

            $post = Post::findOrFail($id);
            $user = $request->user();

            $checkDup = Report::where('post_id', $post->id)->where('user_reporting', $user->id);

            if ($checkDup->count() > 0) {
                $msg = __('alreadyReported');
                return ResponseHelper::error(message: $msg);
            }

            $report = Report::create([
                'post_id' => $post->id,
                'reason' => $data['reason'],
                'reported_user' => $post->user_id,
                'user_reporting' => $user->id,
            ]);

            $report = $report->fresh();

            $msg = __('reportSuccessfully');

            return ResponseHelper::success(message: $msg, data: $report);
        } catch (\Throwable $th) {
            ResponseHelper::error(message:  __('somethingWentWrongWithMsg') . $th->getMessage());
        }
    }

    public function postsForUser(Request $request)
    {
        try {
            $currentUserId = $request->user()->id;
            $friendId = $request->query('friend_id');

            $postsQuery = Post::query()
                ->select('posts.id', 'posts.user_id', 'posts.url_image', 'posts.caption', 'posts.cmt_count', 'posts.like_count', 'posts.is_deleted', 'posts.type', 'posts.created_at', 'posts.updated_at', 'posts.latitude', 'posts.longitude')
                ->with('user') // Eager load user information
                ->where(function ($query) use ($currentUserId) {

                    // Get posts from current user
                    $query->where('posts.user_id', $currentUserId);

                    // Get posts with type 'all_friends' and current user is friend of the poster
                    $query->orWhere(function ($query) use ($currentUserId) {
                        $query->where('posts.type', SharedPostType::ALL_FRIENDS)
                            ->whereExists(function ($subQuery) use ($currentUserId) {
                                $subQuery->select(DB::raw(1))
                                    ->from('friends')
                                    ->where(function ($friendQuery) use ($currentUserId) {
                                        $friendQuery->whereColumn('friends.user_id', 'posts.user_id')
                                            ->where('friends.friend_id', $currentUserId)
                                            ->where('friends.status', FriendStatus::FRIEND)
                                            ->orWhere(function ($friendQuery) use ($currentUserId) {
                                                $friendQuery->whereColumn('friends.friend_id', 'posts.user_id')
                                                    ->where('friends.user_id', $currentUserId)
                                                    ->where('friends.status', FriendStatus::FRIEND);
                                            });
                                    });
                            });
                    });

                    // Get posts with type 'group_member' and shared with current user
                    $query->orWhere(function ($query) use ($currentUserId) {
                        $query->where('posts.type', SharedPostType::GROUP_MEMBERS)
                            ->whereExists(function ($subQuery) use ($currentUserId) {
                                $subQuery->select(DB::raw(1))
                                    ->from('shared_post_withs')
                                    ->whereColumn('shared_post_withs.post_id', 'posts.id')
                                    ->where('shared_post_withs.user_id', $currentUserId);
                            });
                    });
                })
                ->orderBy('posts.created_at', 'desc');

            // If there is a filter for a specific friend
            if ($friendId) {
                $postsQuery->where('posts.user_id', $friendId);
            }

            $posts = $postsQuery->distinct()->get();

            // format posts
            $formattedPosts = $posts->map(function ($post) use ($currentUserId) {
                $formattedPost = [
                    'id' => $post->id,
                    'user' => new UserSummaryResource($post->user),
                    'url_image' => $post->url_image,
                    'caption' => $post->caption,
                    'user_id' => $post->user_id,
                    'cmt_count' => $post->cmt_count,
                    'like_count' => $post->like_count,
                    'is_deleted' => $post->is_deleted,
                    'type' => $post->type,
                    'created_at' => $post->created_at,
                    'updated_at' => $post->updated_at,
                    'latitude' => $post->latitude,
                    'longitude' => $post->longitude,
                ];

                // If the current user is the owner of the post, add user_views
                if ($post->user_id == $currentUserId) {
                    $formattedPost['user_views'] = $this->getUserViewsData($post->id, $currentUserId);
                }

                // Add user_likes to the post
                $formattedPost['user_likes'] = $this->getUserLikes($post->id);

                return $formattedPost;
            });

            // format response
            $response = [
                'totalItems' => $formattedPosts->count(),
                'posts' => $formattedPosts,
            ];
            return ResponseHelper::success(data: $response);
        } catch (\Throwable $th) {
            return ResponseHelper::error(message:  __('somethingWentWrongWithMsg') . $th->getMessage());
        }

    }
 
    public function getPostsWithLocation(Request $request)
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return ResponseHelper::error(message: 'Unauthorized', statusCode: 401);
            }

            $posts = Post::with('user')
                ->where('user_id', $user->id)
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->get();

            $response = PostResource::collection($posts);
            return ResponseHelper::success(data: $response);
        } catch (\Throwable $th) {
            return ResponseHelper::error(message:  __('somethingWentWrongWithMsg') . $th->getMessage());
        }

    }

    // helper methods
    public function sentPostCreationEvent($type, $post,$sharedWith){
        $user = auth()->user();
              
        if (empty($sharedWith) && $type == SharedPostType::ALL_FRIENDS) {
            $sharedWith = $this->getFriendsForPost($user);
        }

     
        $postWithRelations = Post::with(['user', 'comments.user', 'comments.replies.user'])->find($post->id);
        $postDetailResource = new PostDetailResource($postWithRelations);
        
        event(new PostEvent($postDetailResource, $sharedWith));
    }
    public function getUserViewsData($postId, $currentUserId)
    {
        // Fetch user views for a specific post
        $viewers = User::join('user_views', 'users.id', '=', 'user_views.user_id')
            ->where('user_views.post_id', $postId)
            ->where('users.id', '!=', $currentUserId)
            ->select('users.id', 'users.url_avatar', 'users.name')
            ->get();

        $formattedUserViews = [];
        foreach ($viewers as $viewer) {
            $formattedUserViews[] = [
                'id' => $viewer->id,
                'url_avatar' => $viewer->url_avatar,
                'name' => $viewer->name,
            ];
        }

        return $formattedUserViews;
    }

    private function getUserLikes($postId)
    {
        $likers = User::join('user_likes', 'users.id', '=', 'user_likes.user_id')
            ->where('user_likes.post_id', $postId)
            ->select('users.*')
            ->get();

        if ($likers->isEmpty()) {
            return [];
        }

        $data = [];
        foreach ($likers as $liker) {
            $data[] = [
                'id' => $liker['id'],
                'url_avatar' => $liker['url_avatar'],
                'name' => $liker['name'],
            ];
        }

        return $data;
    }

}
