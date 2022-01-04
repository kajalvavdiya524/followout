<?php

namespace App\Http\Controllers\API;

use FollowoutHelper;
use App\RewardProgram;
use App\RewardProgramJob;
use App\Http\Resources\RewardProgramResource;
use App\Http\Resources\RewardProgramCollection;
use App\Http\Resources\RewardProgramJobResource;
use App\Http\Resources\RewardProgramJobCollection;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RewardProgramJobsController extends Controller
{
    public function index(Request $request)
    {
        $authUser = auth()->guard('api')->user();

        // $excludeRedeemed = (bool) $request->input('exclude_redeemed', false);
        $excludeRedeemed = false;

        if ($authUser->isFollowhost()) {
            $rewardPrograms = new RewardProgramCollection(
                RewardProgramResource::collection(
                    RewardProgram::where('author_id', auth()->user()->id)->active()->orderByDesc('created_at')->get()
                )
            );
        } else {
            $rewardPrograms = RewardProgram::active()->orderByDesc('created_at')->get();

            $rewardPrograms = FollowoutHelper::filterRewardProgramsForUser($rewardPrograms, $authUser);

            $rewardPrograms = new RewardProgramCollection( RewardProgramResource::collection( $rewardPrograms ) );
        }

        if ($excludeRedeemed) {
            $rewardProgramJobs = new RewardProgramJobCollection(
                RewardProgramJobResource::collection(
                    RewardProgramJob::notRedeemed()->where('user_id', $authUser->id)->orderByDesc('created_at')->get()
                )
            );
        } else {
            $rewardProgramJobs = new RewardProgramJobCollection(
                RewardProgramJobResource::collection(
                    RewardProgramJob::where('user_id', $authUser->id)->orderByDesc('created_at')->get()
                )
            );
        }

        return response()->json([
            'status' => 'OK',
            'data' => [
                'reward_programs' => $rewardPrograms,
                'reward_program_jobs' => $rewardProgramJobs,
            ]
        ]);
    }

    public function redeem(Request $request, $rewardProgramJob)
    {
        $authUser = auth()->guard('api')->user();

        $rewardProgramJob = RewardProgramJob::with('user', 'reward_program')->find($rewardProgramJob);

        if (is_null($rewardProgramJob)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Reward program job not found.',
            ], 404);
        }

        if ($rewardProgramJob->user->id !== $authUser->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Access denied.',
            ], 403);
        }

        $rewardProgram = $rewardProgramJob->reward_program;

        if ($request->input('code') !== $rewardProgram->redeem_code) {
            return response()->json([
                'status' => 'error',
                'message' => 'The code is not valid.',
            ], 422);
        }

        if (!$rewardProgram->canBeRedeemedByUser($authUser->id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'This reward program job cannot be redeemed.',
            ], 422);
        }

        $rewardProgramJob->markAsRedeemed();

        return response()->json(['status' => 'OK']);
    }

    /**
     * Mark reward as received for the job.
     *
     * Note: followhost can do this without followee's approval if followee reaches the required checkin count.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\RewardProgramJob  $rewardProgramJob
     * @return \Illuminate\Http\Response
     */
    public function markAsReceived(Request $request, $rewardProgramJob)
    {
        $authUser = auth()->guard('api')->user();

        $rewardProgramJob = RewardProgramJob::with('user', 'reward_program')->find($rewardProgramJob);

        if ($rewardProgramJob->user->id !== $authUser->id && $rewardProgramJob->reward_program->author->id !== $authUser->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Access denied.',
            ], 403);
        }

        if ($rewardProgramJob->user->id === $authUser->id && $rewardProgramJob->inDispute() && !$rewardProgramJob->userCanCloseDispute()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Access denied.',
            ], 403);
        }

        if ($rewardProgramJob->reward_program->author->id === $authUser->id && $rewardProgramJob->inDispute() && !$rewardProgramJob->hostCanCloseDispute()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Access denied.',
            ], 403);
        }

        if (!$rewardProgramJob->canBeRedeemed()) {
            return response()->json([
                'status' => 'error',
                'message' => 'This reward program job cannot be received.',
            ], 403);
        }

        $rewardProgramJob->markRewardAsReceived();

        return response()->json(['status' => 'OK']);
    }
}
