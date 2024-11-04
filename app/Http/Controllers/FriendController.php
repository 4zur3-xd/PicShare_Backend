<?php

namespace App\Http\Controllers;

use App\Enum\FriendStatus;
use App\Enum\FriendType;
use App\Enum\NotificationPayloadType;
use App\Enum\NotificationType;
use App\Helper\LinkToHelper;
use App\Helper\NotificationHelper;
use App\Helper\ResponseHelper;
use App\Http\Requests\StoreFriendRequest;
use App\Http\Requests\StoreNotificationRequest;
use App\Http\Requests\UpdateFriendRequest;
use App\Http\Resources\FriendResource;
use App\Http\Resources\UserSummaryResource;
use App\Models\Comment;
use App\Models\Friend;
use App\Models\Post;
use App\Models\User;
use App\Models\UserLike;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class FriendController extends Controller
{

    protected $firebasePushController;
    protected $notificationController;

    public function __construct(FirebasePushController $firebasePushController, NotificationController $notificationController)
    {
        $this->firebasePushController = $firebasePushController;
        $this->notificationController = $notificationController;
    }
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
    public function store(StoreFriendRequest $request)
    {
        //
        DB::beginTransaction();
        try {
            $dataCreate = $request->all();
            $dataCreate['user_id'] = auth()->user()->id;

            $friend = Friend::create($dataCreate);

            if (!$friend || $friend->wasRecentlyCreated === false) {
                DB::rollBack();
                return ResponseHelper::error(message: __('failToAddFriendTryAgain'));
            }

            $this->sendFriendRequestNotification($friend->friend_id, 'sentYouFriendRequest', 'newFriendRequest', FriendType::REQUESTED);

            $friend = Friend::with(['user', 'friend'])->find($friend->id);
            DB::commit();
            return ResponseHelper::success(
                message: __('addFriendSuccessfully'),
                data: new FriendResource($friend)
            );
        } catch (\Throwable $th) {
            DB::rollBack();
            return ResponseHelper::error(message:__('somethingWentWrongWithMsg') . $th->getMessage());
        }

    }

    /**
     * Display the specified resource.
     */
    public function show(Friend $friend)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateFriendRequest $request, $id)
    {
        //
        DB::beginTransaction();
        try {
            // Make sure that only the true owner can update
            $friend = Friend::findOrFail($id);
            Gate::authorize('modify', $friend);
            $friend->update($request->all());
            $this->sendFriendRequestNotification($friend->user_id,'acceptYourFriendRequest', 'friendRequest', FriendType::FRIEND);
            DB::commit();
            return ResponseHelper::success(message: __('updateFriendSuccessfully'));
        } catch (\Throwable $th) {
            DB::rollBack();
            return ResponseHelper::error(message: __('somethingWentWrongWithMsg') . $th->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $friend = Friend::findOrFail($id);
            Gate::authorize('modify', $friend);
            $friend->delete();
            return ResponseHelper::success(message: __('deleteFriendSuccessfully'));
        } catch (\Throwable $th) {
            return ResponseHelper::error(message: __('somethingWentWrongWithMsg') . $th->getMessage());
        }
    }

    public function getFriends()
    {
        try {

            $friends = Friend::where(function ($query) {
                $query->where('user_id', Auth::id())
                    ->orWhere('friend_id', Auth::id());
            })
                ->where('status', FriendStatus::FRIEND)
                ->with(['user', 'friend'])
                ->get();
            $dataCollection = FriendResource::collection($friends);
            $responseData = [
                'user_id' => Auth::id(),
                'list_friend' => $dataCollection,
                'totalItems' => $friends->count(),
            ];
            return ResponseHelper::success(data: $responseData);
        } catch (\Throwable $th) {
            return ResponseHelper::error(message: __('somethingWentWrongWithMsg') . $th->getMessage());
        }
    }
    public function getRequestedFriends()
    {
        try {
            $friends = Friend::where('friend_id', Auth::id())
                ->where('status', FriendStatus::PENDING)
                ->with(['user', 'friend'])
                ->get();
            $dataCollection = FriendResource::collection($friends);
            $responseData = [
                'user_id' => Auth::id(),
                'list_request' => $dataCollection,
                'totalItems' => $friends->count(),
            ];
            return ResponseHelper::success(data: $responseData);
        } catch (\Throwable $th) {
            return ResponseHelper::error(message: __('somethingWentWrongWithMsg') . $th->getMessage());
        }
    }
    public function getSentFriends()
    {
        try {
            $friends = Friend::where('user_id', Auth::id())
                ->where('status', FriendStatus::PENDING)
                ->with(['user', 'friend'])
                ->get();
            $dataCollection = FriendResource::collection($friends);
            $responseData = [
                'user_id' => Auth::id(),
                'list_request' => $dataCollection,
                'totalItems' => $friends->count(),
            ];
            return ResponseHelper::success(data: $responseData);
        } catch (\Throwable $th) {
            return ResponseHelper::error(message: __('somethingWentWrongWithMsg') . $th->getMessage());
        }
    }

    public function getMutualFriends($friendId)
    {
        try {
            $currentUserId = auth()->user()->id;
            // Get current user's friends list
            $friendsOfUserA = Friend::where(function ($query) use ($currentUserId) {
                $query->where('user_id', $currentUserId)
                    ->orWhere('friend_id', $currentUserId);
            })->where('status', FriendStatus::FRIEND)
                ->get(['user_id', 'friend_id']);

            // Get User friend's friends list
            $friendsOfUserB = Friend::where(function ($query) use ($friendId) {
                $query->where('user_id', $friendId)
                    ->orWhere('friend_id', $friendId);
            })->where('status', FriendStatus::FRIEND)
                ->get(['user_id', 'friend_id']);

            // Find both friends IDs
            $friendIdsOfUserA = $friendsOfUserA->map(function ($friend) use ($currentUserId) {
                return $friend->user_id == $currentUserId ? $friend->friend_id : $friend->user_id;
            });

            $friendIdsOfUserB = $friendsOfUserB->map(function ($friend) use ($friendId) {
                return $friend->user_id == $friendId ? $friend->friend_id : $friend->user_id;
            });

            // Find intersection of friend list
            $mutualFriendIds = $friendIdsOfUserA->intersect($friendIdsOfUserB);

            // Get details of mutual friends
            $mutualFriends = User::whereIn('id', $mutualFriendIds)->get();

            $reponse = UserSummaryResource::collection($mutualFriends);
            return ResponseHelper::success(data: $reponse);
        } catch (\Throwable $th) {
            return ResponseHelper::error(message: __('somethingWentWrongWithMsg') . $th->getMessage());
        }
    }

    /**
     * Send friend request notification
     */
    private function sendFriendRequestNotification($friendUserId, $messageKey, $titleKey, FriendType $friendType)
    {
        $currentUser = auth()->user();
        $friendUser = User::find($friendUserId);
        if (!$friendUser) {
            return;
        }

        $fcmToken = $friendUser->fcm_token;
        $avatar = $currentUser->url_avatar;

        $contentParams = [
            'name' => $currentUser->name, 
        ];

        $originalLocale = App::getLocale();

        $friendLocale = $friendUser->language ?? 'en'; // Default to 'en' if no locale is set
        App::setLocale($friendLocale); // Set the app locale to the friend's language temporarily
    
        $title=__($titleKey);
        $contentDB = json_encode([
            'key' => $messageKey,
            'params' => $contentParams
        ]);

        // Create notification record
        $linkTo = LinkToHelper::createLinkTo(NotificationPayloadType::FRIEND_REQUEST, $friendType);
        $request = new StoreNotificationRequest([
            'title' => $titleKey,
            'user_id' => $friendUser->id,
            'content' => $contentDB,
            'link_to' => $linkTo,
            'notification_type' => NotificationType::USER,
        ]);
        $notification = $this->notificationController->store($request);
        $notificationId = $notification ? $notification->id : null;
        if ($fcmToken) {
            $translatedContent = __($messageKey, $contentParams);
            $notificationData = $this->prepareNotificationData($fcmToken, $title, $translatedContent, $avatar, $friendType, $notificationId);
            $this->firebasePushController->sendNotification(new Request($notificationData));
        }

        // Restore the original locale 
        App::setLocale(locale: $originalLocale);
    }

    /**
     * Prepare notification data for Firebase
     */
    private function prepareNotificationData($fcmToken, $title, $body, $imageUrl, FriendType $friendType, $notificationId)
    {
        return NotificationHelper::createNotificationData(
            fcmToken: $fcmToken,
            title: $title,
            body: $body,
            imageUrl: $imageUrl,
            postId: null,
            commentId: null,
            replyId: null,
            friendType: $friendType,
            type: NotificationPayloadType::FRIEND_REQUEST,
            notificationId: $notificationId,
            conversationId: null,
            userId: auth()->user()->id,
            userName: auth()->user()->name,
            userAvatar: auth()->user()->url_avatar,
        );
    }
    


    public function getMutualFriendsScore(User $user, User $potentialFriend) {
    // Get the list of friend IDs that the user has sent friend requests to
    $userFriendIdsSent = $user->friendsSent()->pluck('friend_id')->toArray();

    // Get the list of friend IDs that the user has accepted friend requests from
    $userFriendIdsReceived = $user->friendsReceived()->pluck('user_id')->toArray();

    // Combine both user's friend lists and remove duplicates
    $allUserFriendIds = array_unique(array_merge($userFriendIdsSent, $userFriendIdsReceived));

    // Get the list of potentialFriend's friend IDs similarly
    $potentialFriendIdsSent = $potentialFriend->friendsSent()->pluck('friend_id')->toArray();
    $potentialFriendIdsReceived = $potentialFriend->friendsReceived()->pluck('user_id')->toArray();

   // Combine both potentialFriend's friend lists and remove duplicates
    $allPotentialFriendIds = array_unique(array_merge($potentialFriendIdsSent, $potentialFriendIdsReceived));

    // Find the intersection of two lists to determine common friends
    $mutualFriendsCount = count(array_intersect($allUserFriendIds, $allPotentialFriendIds));

    return $mutualFriendsCount * 4;
    }


    public function getLikesScore(User $user, User $potentialFriend)
    {
        // Get all your friends' posts from both directions
        $friendPostIds = Post::whereIn('user_id', array_merge(
                            $user->friendsSent->pluck('id')->toArray(),
                            $user->friendsReceived->pluck('id')->toArray()
                        ))->pluck('id');
    
        // Count potentialFriend's likes on these posts
        $likeCount = UserLike::whereIn('post_id', $friendPostIds)
                    ->where('user_id', $potentialFriend->id)
                    ->count();
    
        return $likeCount * 1;
    }
    
    public function getCommentsScore(User $user, User $potentialFriend)
    {
       // Get all your friends' posts from both directions
        $friendPostIds = Post::whereIn('user_id', array_merge(
                            $user->friendsSent->pluck('id')->toArray(),
                            $user->friendsReceived->pluck('id')->toArray()
                        ))->pluck('id');
    
       // Count potentialFriend's comments on these posts
        $commentCount = Comment::whereIn('post_id', $friendPostIds)
                       ->where('user_id', $potentialFriend->id)
                       ->count();
    
        return $commentCount * 2;
    }
    
    public function calculateSuggestionScore(User $user, User $potentialFriend) {
        $score = 0;
        $score += $this->getMutualFriendsScore($user, $potentialFriend);
        $score += $this->getLikesScore($user, $potentialFriend);
        $score += $this->getCommentsScore($user, $potentialFriend);
        return $score;
    }
    
    public function getSuggestedFriends($user) {
        $allUsers = User::where('id', '!=', $user->id)
        ->whereDoesntHave('friendsSentPending', function ($query) use ($user) {
            $query->where('friend_id', $user->id);
        })
        ->whereDoesntHave('friendsReceivedPending', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
        ->whereDoesntHave('friendsSent', function ($query) use ($user) {
            $query->where('friend_id', $user->id);
        })
        ->whereDoesntHave('friendsReceived', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
        ->get();
    
        $suggestions = [];
        foreach ($allUsers as $potentialFriend) {
            $score = $this->calculateSuggestionScore($user, $potentialFriend);
            if ($score > 0) {
                $suggestions[] = [
                    'user' => new UserSummaryResource($potentialFriend),
                    'score' => $score
                ];
            }
        }
        
        usort($suggestions, fn($a, $b) => $b['score'] <=> $a['score']);
        
        return $suggestions;
    }
    
    public function suggestFriends(Request $request) {
        try {
            $user = auth()->user();
            $suggestedFriends = $this->getSuggestedFriends($user);
            return ResponseHelper::success(data: $suggestedFriends);
        } catch (\Throwable $th) {
            return ResponseHelper::error(message: $th->getMessage());
        }
    }
}
