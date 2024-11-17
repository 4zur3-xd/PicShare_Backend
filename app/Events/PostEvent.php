<?php

namespace App\Events;

use App\Http\Resources\PostDetailResource;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PostEvent  implements ShouldBroadcast, ShouldDispatchAfterCommit
{
    use Dispatchable, InteractsWithSockets, SerializesModels;


    public $postDetailResource;
    public $sharedWith;

    /**
     *
     * @param PostDetailResource $postDetailResource
     * @param array $sharedWith
     */
    public function __construct(PostDetailResource $postDetailResource, array $sharedWith)
    {
        $this->postDetailResource = $postDetailResource;
        $this->sharedWith = $sharedWith;
    }

    /**
     *
     * @return \Illuminate\Broadcasting\Channel|\Illuminate\Broadcasting\Channel[]
     */
    public function broadcastOn()
    {
        $sharedWith = array_unique($this->sharedWith);
        // Broadcast to channels for each user in the share list
        return array_map(function($userId) {
            return new Channel('post.shared.' . $userId);
        }, $sharedWith);
    }

    public function broadcastAs()
    {
        return 'post.created';
    }

    public function broadcastWith()
    {
        return $this->postDetailResource->toArray(request());
    }
}
