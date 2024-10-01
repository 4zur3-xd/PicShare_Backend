<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConversationResource extends JsonResource
{
    protected $lastMessage;
    protected $currentUser;
    protected $friend;

    public function __construct($resource, $lastMessage = null, $currentUser = null, $friend = null){
        parent::__construct($resource);
        $this->lastMessage = $lastMessage;
        $this->currentUser = $currentUser;
        $this->friend = $friend;
    }
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'last_message' => $this->lastMessage, 
            'unread_count' => $this->unread_count, 
            'created_time' => $this->created_at, 
            'updated_time' => $this->updated_at,
            'current_user' => new UserSummaryResource($this->currentUser), 
            'friend' => new UserSummaryResource($this->friend),
        ];
    }

    private function getLastMessage()
    {
        return $this->messages->first() ? new MessageResource($this->messages->first()) : null;
    }
}
