<?php

namespace App\Events;

use App\Http\Resources\ConversationResource;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;

class ConversationCreatedEvent implements ShouldBroadcast, ShouldDispatchAfterCommit
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $conversation;
    public $receiverId;
    public $currentUser;
    /**
     * Create a new event instance.
     */
    public function __construct(Conversation $conversation, $receiverId,$currentUser)
    {
        $this->conversation = $conversation;
        $this->receiverId = $receiverId;
        $this->currentUser = $currentUser;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     *  \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn()
    {
        return new Channel('chat.user.'. $this->receiverId);

    }
    public function broadcastAs()
    {
        return 'conversation.created';
    }

    public function broadcastWith()
    {
        $lastMessage = $this->conversation->messages->last();
        $friend = $this->conversation->users->where('user_id', '!=', $this->currentUser->id)->first();

        return (new ConversationResource($this->conversation, $lastMessage, $this->currentUser, $friend))->toArray(request());
    }
}
