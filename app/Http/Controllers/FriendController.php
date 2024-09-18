<?php

namespace App\Http\Controllers;

use App\Enum\FriendStatus;
use App\Enum\FriendType;
use App\Enum\NotificationPayloadType;
use App\Helper\NotificationHelper;
use App\Helper\ResponseHelper;
use App\Models\Friend;
use App\Http\Requests\StoreFriendRequest;
use App\Http\Requests\UpdateFriendRequest;
use App\Http\Resources\FriendResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FriendController extends Controller
{

    protected $firebasePushController;



    public function __construct(FirebasePushController $firebasePushController)
    {
        $this->firebasePushController = $firebasePushController;
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
                return ResponseHelper::error(message: "Failed to add friend. Please try again.");
            }

            $currentUser = auth()->user();    
            $friendUser = User::find($dataCreate['friend_id']);
            if($friendUser){
                $fcmToken = $friendUser->fcm_token;
                $avatar = $currentUser->url_avatar;
                if($fcmToken != null){
                    $notificationData = NotificationHelper::createNotificationData(
                        fcmToken: $fcmToken,
                        title: 'New Friend Request', 
                        body: 'You have a new friend request!', 
                        imageUrl: $avatar,
                        postId: null,
                        commentId: null,
                        replyId: null,
                        friendType: FriendType::REQUESTED,
                        type: NotificationPayloadType::FRIEND_REQUEST 
                    );
                    $this->firebasePushController->sendNotificationToMultipleDevice(new Request($notificationData));
                }
              
            }
            
            $friend = Friend::with(['user', 'friend'])->find($friend->id);
            DB::commit();
            return ResponseHelper::success(
                message: "Add friend successfully", 
                data: new FriendResource($friend)
            );
        } catch (\Throwable $th) {
            DB::rollBack();
           return ResponseHelper::error(message: $th->getMessage());
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
            $friend=Friend::findOrFail($id);
            Gate::authorize('modify',$friend);
            $friend->update($request->all());
            $friendUser = User::find($friend->user_id);
            $currentUser = auth()->user();    
            if($friendUser){
                $fcmToken = $friendUser->fcm_token;
                $avatar = $currentUser->url_avatar;
                if($fcmToken != null){
                    $notificationData = NotificationHelper::createNotificationData(
                        fcmToken: $fcmToken,
                        title: 'Friend Request',
                        body: $currentUser->name . ' accepted your friend request!', 
                        imageUrl: $avatar,
                        postId: null,
                        commentId: null,
                        replyId: null,
                        friendType: FriendType::FRIEND,
                        type: NotificationPayloadType::FRIEND_REQUEST 
                    );
                    $this->firebasePushController->sendNotification(new Request($notificationData));
                }
            }
            DB::commit();
            return ResponseHelper::success(message: "Update friend successfully");
        } catch (\Throwable $th) {
            DB::rollBack();
           return ResponseHelper::error(message: $th->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $friend=Friend::findOrFail($id);
            Gate::authorize('modify',$friend);
            $friend->delete();
            return ResponseHelper::success(message: "Delete friend successfully");
        } catch (\Throwable $th) {
            return ResponseHelper::error(message: $th->getMessage());
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
            $dataCollection=  FriendResource::collection($friends);
            $responseData = [
                'user_id' =>  Auth::id(),
                'list_friend' => $dataCollection,
                'totalItems' => $friends->count(),
            ];
            return ResponseHelper::success(data: $responseData);
        } catch (\Throwable $th) {
            return ResponseHelper::error(message: $th->getMessage());
        }
    }
    public function getRequestedFriends()
    {
        try {
            $friends = Friend::where('friend_id', Auth::id())
            ->where('status', FriendStatus::PENDING) 
            ->with(['user', 'friend'])
            ->get();
            $dataCollection=  FriendResource::collection($friends);
            $responseData = [
                'user_id' =>  Auth::id(),
                'list_request' => $dataCollection,
                'totalItems' => $friends->count(),
            ];
            return ResponseHelper::success(data: $responseData);
        } catch (\Throwable $th) {
            return ResponseHelper::error(message: $th->getMessage());
        }
    }
    public function getSentFriends()
    {
        try {
            $friends = Friend::where('user_id', Auth::id())
            ->where('status', FriendStatus::PENDING) 
            ->with(['user', 'friend'])
            ->get();
            $dataCollection=  FriendResource::collection($friends);
            $responseData = [
                'user_id' =>  Auth::id(),
                'list_request' => $dataCollection,
                'totalItems' => $friends->count(),
            ];
            return ResponseHelper::success(data: $responseData);
        } catch (\Throwable $th) {
            return ResponseHelper::error(message: $th->getMessage());
        }
    }

}
