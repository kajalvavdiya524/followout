<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FolloweeResource extends JsonResource
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
            'status' => $this->status,
            'user_id' => $this->user_id,
            'user' => new UserResource($this->whenLoaded('user')),
            'followout_id' => $this->followout_id,
            'followout' => new FollowoutResource($this->whenLoaded('followout')),
            'requested_by_user' => $this->requested_by_user ?: false,
            'reward_program_id' => $this->reward_program_id,
            'reward_program' => new RewardProgramResource($this->whenLoaded('reward_program')),
            'created_at' => (string) $this->created_at,
            'updated_at' => (string) $this->updated_at,
        ];
    }
}
