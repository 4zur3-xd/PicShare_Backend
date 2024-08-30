<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FriendResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $userId = auth()->id();
        
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'friend_id' => $this->friend_id,
            'status' => $this->status,
            'avatar' => $this->user_id == $userId ? $this->friend->avatar : $this->user->avatar,
            'name' => $this->user_id == $userId ? $this->friend->name : $this->user->name,
            'updated_at'=>  $this->updated_at,
            'created_at'=>  $this->created_at,
        ];
    }
}
