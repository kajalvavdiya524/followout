<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FollowoutCategoryResource extends JsonResource
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
            'name' => $this->name,
        ];
    }
}
