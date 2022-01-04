<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FileResource extends JsonResource
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
            'type' => $this->type,
            'path' => $this->path,
            'video_id' => $this->when($this->video_id !== null, $this->video_id),
            'video' => $this->when($this->video_id !== null, new VideoResource($this->whenLoaded('video'))),
            'user_id' => $this->when($this->user_id !== null, $this->user_id),
            'user' => $this->when($this->user_id !== null, new UserResource($this->whenLoaded('user'))),
            'coupon_id' => $this->when($this->coupon_id !== null, $this->coupon_id),
            'coupon' => $this->when($this->coupon_id !== null, new CouponResource($this->whenLoaded('coupon'))),
            'followout_id' => $this->when($this->followout_id !== null, $this->followout_id),
            'followout' => $this->when($this->followout_id !== null, new FollowoutResource($this->whenLoaded('followout'))),
            'created_at' => (string) $this->created_at,
            'updated_at' => (string) $this->updated_at,
        ];
    }
}
