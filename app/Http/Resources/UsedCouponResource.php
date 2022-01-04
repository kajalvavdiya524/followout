<?php

namespace App\Http\Resources;

use App\Coupon;
use App\FollowoutCoupon;
use Illuminate\Http\Resources\Json\JsonResource;

class UsedCouponResource extends JsonResource
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
            'followout_coupon' => new FollowoutCouponResource($this->whenLoaded('followout_coupon')),
            'followout_coupon_id' => $this->followout_coupon_id,
            'user' => new UserResource($this->whenLoaded('user')),
            'user_id' => $this->user_id,
            'coupon' => new CouponResource($this->coupon),
            'created_at' => (string) $this->created_at,
            'updated_at' => (string) $this->updated_at,
        ];
    }
}
