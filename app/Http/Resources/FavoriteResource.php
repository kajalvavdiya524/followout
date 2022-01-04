<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FavoriteResource extends JsonResource
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
            'user' => new UserResource($this->whenLoaded('user')),
            'favoriteable_id' => $this->favoriteable_id,
            'favoriteable_type' => $this->favoriteable_type,
            'is_shared' => $this->isShared(),
            'created_at' => (string) $this->created_at,
            // 'updated_at' => (string) $this->updated_at,
        ];
    }
}
