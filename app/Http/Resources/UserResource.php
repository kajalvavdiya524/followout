<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'email' => $this->email,
            'role' => $this->role,
            'privacy_type' => $this->privacy_type,
            'phone_number' => $this->phone_number,
            'gender' => $this->gender,
            'address' => $this->address,
            'city' => $this->city,
            'state' => $this->state,
            'zip_code' => $this->zip_code,
            'about' => $this->about,
            'about' => $this->about,
            'followout_category_ids' => $this->followout_category_ids,
            'promo_codes_used' => $this->promo_codes_used,
            'website' => $this->website,
            'education' => $this->education,
            'keywords' => $this->keywords,
            'lat' => $this->lat,
            'lng' => $this->lng,
            'avatars' => FileResource::collection($this->whenLoaded('avatars')),
            'avatar_url' => $this->avatarURL(),
            'profile_cover' => new FileResource($this->whenLoaded('profile_cover')),
            'accepted_followees' => FolloweeResource::collection($this->whenLoaded('accepted_followees')),
            'account_categories' => FollowoutCategoryResource::collection($this->whenLoaded('account_categories')),
            'blocked_users' => BlacklistResource::collection($this->whenLoaded('blocked_users')),
            'blocked_by_users' => BlacklistResource::collection($this->whenLoaded('blocked_by_users')),
            'checkins' => CheckinResource::collection($this->whenLoaded('checkins')),
            'country_id' => $this->country_id,
            'country' => new CountryResource($this->whenLoaded('country')),
            'follows' => FollowerResource::collection($this->whenLoaded('follows')),
            'followers' => FollowerResource::collection($this->whenLoaded('followers')),
            'subscribers' => FollowerResource::collection($this->whenLoaded('subscribers')),
            'followees' => FolloweeResource::collection($this->whenLoaded('followees')),
            'followouts' => FollowoutResource::collection($this->whenLoaded('followouts')),
            'saved_followouts' => FavoriteResource::collection($this->whenLoaded('saved_followouts')),
            'social_accounts' => SocialAccountResource::collection($this->whenLoaded('social_accounts')),
            'used_coupons' => UsedCouponResource::collection($this->whenLoaded('used_coupons')),
            'cart' => $this->cart,
            'birthday' => $this->birthday ? (string) $this->birthday : null,
            'subscription' => new SubscriptionResource($this->subscription),
            'is_unregistered' => $this->is_unregistered,
            'is_activated' => $this->is_activated,
            'is_subscribed' => $this->subscribed(),
            $this->mergeWhen($authUser && ($authUser->isAdmin() || $authUser->id === $this->id), [
                'api_token' => $this->api_token,
                'apns_device_token' => $this->apns_device_token,
                'account_activation_token' => $this->when($this->account_activation_token !== null, $this->account_activation_token),
                'requested_account_deletion_reason' => $this->when($this->requested_account_deletion_reason !== null, $this->requested_account_deletion_reason),
                'requested_account_deletion_at' => $this->when($this->requested_account_deletion_at !== null, (string) $this->requested_account_deletion_at),
                'password_reset_token' => $this->when($this->password_reset_token !== null, $this->password_reset_token),
                'password_reset_token_expires_at' => $this->when($this->password_reset_token_expires_at !== null, (string) $this->password_reset_token_expires_at),
            ]),
            $this->mergeWhen(!$this->isFollowhost(), [
                'autosubcribe_to_followhosts' => (bool) $this->autosubcribe_to_followhosts,
                'available_for_promotion' => (bool) $this->available_for_promotion,
            ]),
            'last_seen' => (string) $this->last_seen,
            'created_at' => (string) $this->created_at,
            'updated_at' => (string) $this->updated_at,
        ];
    }
}
