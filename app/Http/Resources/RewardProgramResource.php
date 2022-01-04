<?php

namespace App\Http\Resources;

use Gate;
use Illuminate\Http\Resources\Json\JsonResource;

class RewardProgramResource extends JsonResource
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
        $rewardProgramJob = $this->getJobByUser($authUser->id);

        return [
            '_id' => $this->id,
            'author_id' => $this->author_id,
            'author' => new UserResource($this->whenLoaded('author')),
            'followout_id' => $this->followout_id,
            'followout' => new FollowoutResource($this->whenLoaded('followout')),
            'picture' => new FileResource($this->whenLoaded('picture')),
            'picture_url' => $this->pictureURL(),
            'jobs' => RewardProgramJobResource::collection($this->whenLoaded('jobs')),
            'title' => $this->title,
            'description' => $this->description,
            'redeem_count' => $this->redeem_count,
            'enabled' => $this->isActive(),
            'require_coupon' => (bool) $this->require_coupon,
            'is_editable' => $this->canBeUpdated(),
            'redeem_code' => $this->when($authUser && $authUser->id === $this->author_id, $this->redeem_code),
            'job_id' => $this->when($authUser && $rewardProgramJob, $rewardProgramJob->id ?? null),
            $this->mergeWhen($authUser && !empty($rewardProgramJob), [
                'job_status' => $rewardProgramJob ? $rewardProgramJob->getApiStatus() : null,
                'reward_program_job_exists' => true,
            ]),
            $this->mergeWhen($authUser && empty($rewardProgramJob) && Gate::forUser($authUser)->allows('request-to-present-followout', $this->followout), [
                'job_status' => 0, // 0 = Claimable
                'reward_program_job_exists' => false,
            ]),
            'created_at' => (string) $this->created_at,
            'updated_at' => (string) $this->updated_at,
        ];
    }
}
