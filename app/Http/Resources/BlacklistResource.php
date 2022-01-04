<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BlacklistResource extends JsonResource
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
            'user_id' => $this->user_id,
            'user' => UserResource::collection($this->whenLoaded('user')),
            'blocked_user_id' => $this->blocked_user_id,
            'blocked_user' => UserResource::collection($this->whenLoaded('blocked_user')),
            'created_at' => (string) $this->created_at,
            // 'updated_at' => (string) $this->updated_at,
        ];
    }
}
