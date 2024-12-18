<?php

namespace App\Http\Controllers;

use App\Enum\NotificationPayloadType;
use App\Events\ChatMessageEvent;
use App\Events\ConversationCreatedEvent;
use App\Helper\NotificationHelper;
use App\Helper\ResponseHelper;
use App\Http\Requests\CreateMessageRequest;
use App\Http\Resources\MessageResource;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MessageController extends Controller
{

    protected $firebasePushController;

    public function __construct(FirebasePushController $firebasePushController)
    {
        $this->firebasePushController = $firebasePushController;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateMessageRequest $request)
    {
        DB::beginTransaction();
        try {
            $validatedData = $request->validated();

            $receiverId = $validatedData['user_id'] ?? null;
            $currentUserId = Auth::id();
            $conversationId = $validatedData['conversation_id'] ?? null;
            $user = Auth::user();
            if ($conversationId) {
                $conversation = Conversation::findOrFail($conversationId);

                if (is_null($receiverId)) {
                    $users = $conversation->users()->pluck('id')->toArray();
                    // Filter out receiverId (who is not currentUserId)
                    $receiverId = collect($users)->reject(fn($id) => $id === $currentUserId)->first();
                }
            } else {
                // Check if there is a conversation between userA and userB
                $conversation = Conversation::whereHas('users', function ($query) use ($currentUserId) {
                    $query->where('user_id', $currentUserId);
                })->whereHas('users', function ($query) use ($receiverId) {
                    $query->where('user_id', $receiverId);
                })->first();

                // If there is no conversation, create a new one.
                if (!$conversation) {
                    $conversation = Conversation::create();
                    // Add both sender and receiver to the conversation
                    $conversation->users()->attach([$currentUserId, $receiverId]);
                    $this->broadcastConversationCreated($conversation, $receiverId, $user);
                }
            }

            //Check if the conversation is not found
            if (!$conversation) {
                return ResponseHelper::error(message: __('conversationNotFound'), status: 404);
            }

            // encrypt text 
            $msg =  $validatedData['text'];
            $encryptedMessage =  $msg ? Crypt::encrypt($msg) : null; 

            $message = Message::create([
                'user_id' => Auth::id(),
                'conversation_id' => $conversation->id,
                'text' => $encryptedMessage,
                'url_image' => $validatedData['url_image'] ?? null,
                'message_type' => $validatedData['message_type'],
                'height' => $validatedData['height'] ?? null,
                'width' => $validatedData['width'] ?? null,
            ]);

            $conversation->touch();  // update field 'updated_at' in conversation record

           


             // send notification
             $this->sendNotification($receiverId, $msg, $user->name, $conversation->id);


             // Broadcast events for real-time updates via Pusher
            // $message->created_at = $message->created_at->setTimezone(config('app.timezone'));
            // $message->updated_at = $message->updated_at->setTimezone(config('app.timezone'));
            $this->broadcastChatMessage($message);

           

            DB::commit();
            return ResponseHelper::success(message: __('sendMessageSuccessfully'),
                data: new MessageResource($message),
                status: 201
            );
        } catch (\Throwable $th) {
            DB::rollBack();
            return ResponseHelper::error(message: __('sendMessageFail'), );
        }

    }

    protected function broadcastChatMessage($message)
    {
        try {
            broadcast(new ChatMessageEvent($message))->toOthers();
        } catch (\Exception $e) {
            Log::error('Broadcast event failed: ' . $e->getMessage());
        }
    }
    protected function broadcastConversationCreated($conversation, $receiverId, $user)
    {
        try {
            broadcast(new ConversationCreatedEvent($conversation, $receiverId, $user))->toOthers();
        } catch (\Exception $e) {
            Log::error('Broadcast ConversationCreatedEvent failed: ' . $e->getMessage());
        }
    }


    /**
     * Send friend request notification
     */
    private function sendNotification($friendUserId, $message, $title,$conversationId)
    {
        $currentUser = auth()->user();
        $friendUser = User::find($friendUserId);
        if (!$friendUser) {
            return;
        }

        $content = $currentUser->name . $message;
        $fcmToken = $friendUser->fcm_token;
        $avatar = $currentUser->url_avatar;

        if ($fcmToken) {
            $notificationData = $this->prepareNotificationData($fcmToken, $title, $content, $avatar,$conversationId);
            $this->firebasePushController->sendNotification(new Request($notificationData));
        }

    }

    /**
     * Prepare notification data for Firebase
     */
    private function prepareNotificationData($fcmToken, $title, $body, $imageUrl,$conversationId)
    {

        return NotificationHelper::createNotificationData(
            fcmToken: $fcmToken,
            title: $title,
            body: $body,
            imageUrl: $imageUrl,
            postId: null,
            commentId: null,
            replyId: null,
            type: NotificationPayloadType::CHAT,
            notificationId: null,
            friendType: null,
            conversationId: $conversationId,
            userId: auth()->user()->id,
            userName: auth()->user()->name,
            userAvatar: auth()->user()->url_avatar,
        );
    }

}
