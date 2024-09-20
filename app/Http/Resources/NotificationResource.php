<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user' =>  new UserSummaryResource($this->whenLoaded('user')),
            'sender' => new UserSummaryResource($this->whenLoaded('sender')),
            'content' => $this->content,
            'title' => $this->title,
            'is_seen' => $this->is_seen,
            'link_to' => $this->link_to,
            'notification_type' => $this->notification_type,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
