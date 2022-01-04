<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FollowoutResource extends JsonResource
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
            'privacy_type' => $this->privacy_type,
            'author_id' => $this->author_id,
            'author' => new UserResource($this->author),
            'is_virtual' => $this->isVirtual(),
            $this->mergeWhen($this->isVirtual(), [
                'virtual_address' => $this->virtual_address,
            ]),
            $this->mergeWhen(!$this->isVirtual(), [
                'address' => $this->address,
                'city' => $this->city,
                'state' => $this->state,
                'zip_code' => $this->zip_code,
                'lat' => $this->lat,
                'lng' => $this->lng,
                'radius' => $this->radius,
                'location' => $this->location,
            ]),
            'coupon_id' => $this->coupon_id,
            'coupon' => new CouponResource($this->whenLoaded('coupon')),
            'tickets_url' => $this->tickets_url,
            'external_info_url' => $this->external_info_url,
            'starts_at' => (string) $this->starts_at,
            'ends_at' => (string) $this->ends_at,
            'followout_category_ids' => $this->followout_category_ids,
            'experience_categories' => FollowoutCategoryResource::collection($this->experience_categories),
            'followees' => FolloweeResource::collection($this->whenLoaded('followees')),
            'accepted_followees' => FolloweeResource::collection($this->whenLoaded('accepted_followees')),
            'pending_followees' => FolloweeResource::collection($this->whenLoaded('pending_followees')),
            'checkins' => CheckinResource::collection($this->whenLoaded('checkins')),
            'coupons' => FollowoutCouponResource::collection($this->whenLoaded('coupons')),
            'used_coupons' => UsedCouponResource::collection($this->whenLoaded('used_coupons')),
            'favorited' => FavoriteResource::collection($this->whenLoaded('favorited')),
            'flyer' => $this->hasFlyer() ? new FileResource($this->flyer) : new FileResource($this->author->default_flyer),
            'flyer_url' => $this->flyerURL(),
            'video' => new VideoResource($this->whenLoaded('video')),
            'pictures' => FileResource::collection($this->whenLoaded('pictures')),
            $this->mergeWhen($this->isReposted(), [
                'top_parent_followout_id' => $this->top_parent_followout_id,
                'parent_followout_id' => $this->parent_followout_id,
                'parent_followout' => new FollowoutResource($this->whenLoaded('parent_followout')),
                'top_parent_followout' => new FollowoutResource($this->whenLoaded('top_parent_followout')),
            ]),
            'hash' => $this->when($this->userHasAccess($authUser), $this->hash),
            'views_count' => $this->views_count,
            'is_default' => $this->is_default,
            'is_edited' => $this->isEdited(),
            'is_editable' => !$this->hasCompletedCheckins(),
            'geohash' => $this->geohash,
            'created_at' => (string) $this->created_at,
            'updated_at' => (string) $this->updated_at,
        ];
    }
}
