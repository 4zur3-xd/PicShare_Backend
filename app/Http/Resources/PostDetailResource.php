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
         $userLikes = $this->resource->likes()->with('user')->get()->map(function ($like) {
            return [
                'id' => $like->user_id,
                'name' => $like->user->name,
                'url_avatar' => $like->user->url_avatar,
            ];
        });

        $userViews = $this->resource->userViews()->with('user')->get()->map(function ($view) {
            return [
                'id' => $view->user_id,
                'name' => $view->user->name,
                'url_avatar' => $view->user->url_avatar,
            ];
        });
        return [
            'id' => $this->id,
            'url_image' => $this->url_image,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
            'caption' => $this->caption,
            'like_count' => $this->like_count,
            'is_deleted' => $this->is_deleted,
            'cmt_count' => $this->comments->count(),
            'user_id' => $this->user_id,
            'type'=> $this->type,
            'user' => new UserSummaryResource($this->whenLoaded('user')),
            'listCmt' => CommentResource::collection($this->whenLoaded('comments')),
            'user_likes' => $userLikes, 
            'user_views' => $userViews, 
        ];
    }
}
