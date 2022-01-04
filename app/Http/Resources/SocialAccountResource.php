<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SocialAccountResource extends JsonResource
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
            'provider' => $this->provider,
            'provider_user_id' => $this->provider_user_id,
            'user_id' => $this->user_id,
            'user' => new UserResource($this->whenLoaded('user')),
            $this->mergeWhen($authUser && ($authUser->isAdmin() || $authUser->id === $this->user_id), [
                'token' => $this->token,
                'refresh_token' => $this->refresh_token,
                'expires_in' => $this->expires_in,
                'expires_at' => (string) $this->expires_at,
            ]),
            'created_at' => (string) $this->created_at,
            'updated_at' => (string) $this->updated_at,
        ];
    }
}
