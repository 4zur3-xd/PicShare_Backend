<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class NotificationCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
       return [
        'user_id' => $request->user()->id, 
        'notifications' => NotificationResource::collection($this->collection), 
        'totalItems' => $this->total(), 
        'currentPage' => $this->currentPage(), 
        'lastPage' => $this->lastPage(), 
        'perPage' => $this->perPage(), 
       ];
    }
}
