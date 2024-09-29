<?php

namespace App\Http\Controllers;

use App\Events\ChatMessageEvent;
use App\Helper\ResponseHelper;
use App\Http\Requests\CreateMessageRequest;
use App\Http\Requests\UpdateMessageRequest;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MessageController extends Controller
{

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateMessageRequest $request)
    {
        DB::beginTransaction();
        try {
            $validatedData = $request->validated();

            $receiverId = $validatedData['user_id'];
            $currentUserId = Auth::id();

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
            }

            $message = Message::create([
                'user_id' => Auth::id(),
                'conversation_id' => $conversation->id,
                'text' => $validatedData['text'] ?? null,
                'url_image' => $validatedData['url_image'] ?? null,
                'message_type' => $validatedData['message_type'],
                'height' => $validatedData['height'] ?? null,
                'width' => $validatedData['width'] ?? null,
            ]);

            // Broadcast events for real-time updates via Pusher
            broadcast(new ChatMessageEvent($message))->toOthers();

            DB::commit();
            return ResponseHelper::success(message: "Create conversation successfully", data: $message, status: 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            return ResponseHelper::error(message: "Create conversation failed", );
        }

    }

}
