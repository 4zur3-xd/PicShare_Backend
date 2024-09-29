<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
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
            'sender' => new UserSummaryResource($this->user), 
            'text' => $this->text,
            'url_image' => $this->url_image,
            'message_type' => $this->message_type,
            'height' => $this->height,
            'width' => $this->width,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'is_read' => $this->is_read
        ];
    }
}
