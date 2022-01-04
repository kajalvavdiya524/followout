<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionCodeResource extends JsonResource
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
            'email' => $this->email,
            'code' => $this->code,
            'chargebee_subscription_id' => $this->chargebee_subscription_id,
            'created_at' => (string) $this->created_at,
            'updated_at' => (string) $this->updated_at,
            'activated_at' => $this->activated_at ? (string) $this->activated_at : null,
            'expires_at' => $this->expires_at ? (string) $this->expires_at : null,
        ];
    }
}
