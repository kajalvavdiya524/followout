<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CouponResource extends JsonResource
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
            'title' => $this->title,
            'description' => $this->description,
            'author_id' => $this->author_id,
            'followout_id' => $this->followout_id,
            'followout' => new FollowoutResource($this->whenLoaded('followout')),
            'author' => new UserResource($this->whenLoaded('author')),
            'followout_coupons' => FollowoutCouponResource::collection($this->whenLoaded('followout_coupons')),
            'picture' => new FileResource($this->whenLoaded('picture')),
            'qr_code' => new FileResource($this->whenLoaded('qr_code')),
            'code' => $this->code,
            'promo_code' => $this->promo_code,
            'discount' => $this->discount,
            'discount_type' => $this->discount_type,
            'expires_at' => (string) $this->expires_at,
            'created_at' => (string) $this->created_at,
            'updated_at' => (string) $this->updated_at,
        ];
    }
}
