<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FollowerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $authUser = auth()->user() ? auth()->user() : auth()->guard('api')->user();

        return [
            '_id' => $this->id,
            'from_id' => $this->from_id,
            'to_id' => $this->to_id,
            'follower' => new UserResource($this->whenLoaded('follower')),
            'subscriber' => new UserResource($this->whenLoaded('subscriber')),
            'follows' => new UserResource($this->whenLoaded('follows')),
            'is_mutual_subscription' => $this->is_mutual_subscription,
            'created_at' => (string) $this->created_at,
            // 'updated_at' => (string) $this->updated_at,
        ];
    }
}
