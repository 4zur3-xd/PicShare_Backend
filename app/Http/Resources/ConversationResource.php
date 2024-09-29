<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConversationResource extends JsonResource
{
    protected $lastMessage;
    public function __construct($resource, $lastMessage = null){
        parent::__construct($resource);
        $this->lastMessage = $lastMessage;
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
        ];
    }

    private function getLastMessage()
    {
        return $this->messages->first() ? new MessageResource($this->messages->first()) : null;
    }
}
