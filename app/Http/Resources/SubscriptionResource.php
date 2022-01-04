<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionResource extends JsonResource
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
            'type' => $this->type,
            'is_active' => $this->isActive(),
            'is_expired' => $this->isExpired(),
            'is_canceled' => $this->isCanceled(),
            'on_grace_period' => $this->onGracePeriod(),
            'chargebee_plan_id' => $this->chargebee_plan_id,
            'chargebee_subscription_id' => $this->chargebee_subscription_id,
            'expires_at' => (string) $this->expires_at,
            'next_billing_at' => (string) $this->next_billing_at,
            'created_at' => (string) $this->created_at,
            'updated_at' => (string) $this->updated_at,
        ];
    }
}
