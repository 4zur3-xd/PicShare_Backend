<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostDetailResource extends JsonResource
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
            'url_image' => $this->url_image,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
            'caption' => $this->caption,
            'like_count' => $this->like_count,
            'is_deleted' => $this->is_deleted,
            'cmt_count' => $this->comments->count(),
            'userID' => $this->user_id,
            'listCmt' => CommentResource::collection($this->whenLoaded('comments')),
        ];
    }
}