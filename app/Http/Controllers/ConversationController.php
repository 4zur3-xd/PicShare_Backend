<?php

namespace App\Http\Controllers;

use App\Helper\ResponseHelper;
use App\Http\Requests\StoreConversationRequest;
use App\Http\Resources\ConversationResource;
use App\Http\Resources\MessageResource;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\UserConversation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConversationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();

        //Get a list of conversations a user participates in
        $conversations = Conversation::whereHas('users', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
            ->with(['messages' => function ($query) {
                $query->latest()->take(1);
            }, 'users'])
            ->orderBy('updated_at', 'desc')
            ->get();

        // Calculate the number of unread messages for each conversation
        $conversationsWithUnreadCount = $conversations->map(function ($conversation) use ($user) {
            // Count unread messages
            $unreadCount = $conversation->messages()
                ->where('is_read', false)
                ->where('user_id', '!=', $user->id) // Only count other people's messages
                ->count();

            $conversation->unread_count = $unreadCount;

            // Identify friends (other people in the conversation)
            $friend = $conversation->users->filter(function ($u) use ($user) {
                return $u->id !== $user->id; // Filter out people who are not currentUser
            })->first();

            // Convert Last_message to MessageResource
            $lastMessage = $conversation->messages->first() ? new MessageResource($conversation->messages->first()) : null;

            return new ConversationResource($conversation, $lastMessage, $user, $friend);
        });
        // Count the number of conversations that have at least one unread message
        $unreadConversationsCount = $conversationsWithUnreadCount->filter(function ($conversation) {
            return $conversation->unread_count > 0;
        })->count();

        return ResponseHelper::success(data: [
            'conversations' => $conversationsWithUnreadCount,
            'unread_chats' => $unreadConversationsCount,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreConversationRequest $request)
    {
        $validated = $request->validate([
            'participants' => 'required|array|min:1',
            'participants.*' => 'exists:users,id',
        ]);

        $conversation = Conversation::create();

        UserConversation::create([
            'user_id' => Auth::id(),
            'conversation_id' => $conversation->id,
        ]);

        foreach ($validated['participants'] as $userId) {
            UserConversation::create([
                'user_id' => $userId,
                'conversation_id' => $conversation->id,
            ]);
        }
        return ResponseHelper::success(message: __('createConversationSuccessfully'), data: $conversation, status: 201);
    }

    public function getMessages($conversationId)
    {
        $conversation = Conversation::with('messages')->findOrFail($conversationId);
        $dataResponse = MessageResource::collection($conversation->messages);
        return ResponseHelper::success(data: $dataResponse);
    }

    public function markAllMessagesAsRead(Request $request, $conversationId)
    {

        try {
            $user = Auth::user();

            $messages = Message::where('conversation_id', $conversationId)
                ->where('is_read', false)
                ->where('user_id', '!=', $user->id) // Only get messages from others
                ->get();

            foreach ($messages as $message) {
                $message->update(['is_read' => true]);
            }

            return ResponseHelper::success(message: "All messages marked as read successfully.");
        } catch (\Throwable $th) {
            return ResponseHelper::error(message: __('somethingWentWrongWithMsg')  . $th->getMessage());
        }

    }

}
