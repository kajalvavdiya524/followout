<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FollowoutCouponResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        $authUser = auth()->user() ? auth()->user() : auth()->guard('api')->user();

        return [
            '_id' => $this->id,
            'followout_id' => $this->followout_id,
            'followout' => new FollowoutResource($this->whenLoaded('followout')),
            'coupon_id' => $this->coupon_id,
            'is_active' => $this->is_active,
            'use_count' => $this->useCount(),
            'is_usable' => $authUser && $this->canBeUsed($authUser->id),
            'coupon' => $this->coupon->load('author', 'picture', 'qr_code'),
            'created_at' => (string) $this->created_at,
            'updated_at' => (string) $this->updated_at,
        ];
    }
}
