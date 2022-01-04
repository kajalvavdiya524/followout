<?php

namespace App\Http\Controllers\API;

use Gate;
use Validator;
use FollowoutHelper;
use App\Followout;
use App\RewardProgram;
use App\Http\Resources\FollowoutCollection;
use App\Http\Resources\FollowoutResource;
use App\Http\Resources\RewardProgramCollection;
use App\Http\Resources\RewardProgramResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RewardProgramsController extends Controller
{
    public function index(Request $request)
    {
        $authUser = auth()->guard('api')->user();

        if (!$authUser->isFollowhost()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Access denied.',
            ], 403);
        }

        $rewardPrograms = RewardProgram::with(RewardProgram::$withAll)->where('author_id', $authUser->id)->orderByDesc('created_at')->get();

        $rewardPrograms = FollowoutHelper::filterRewardProgramsForUser($rewardPrograms, $authUser);

        $rewardPrograms = new RewardProgramCollection(
            RewardProgramResource::collection($rewardPrograms)
        );

        return response()->json([
            'status' => 'OK',
            'data' => [
                'reward_programs' => $rewardPrograms,
            ]
        ]);
    }

    public function followoutsAvailable()
    {
        $authUser = auth()->guard('api')->user();

        if (!$authUser->isFollowhost()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Access denied.',
            ], 403);
        }

        $followouts = $authUser->followouts()->public()->notReposted()->ongoingOrUpcoming()->doesntHave('reward_programs')->get();

        $followouts = new FollowoutCollection(FollowoutResource::collection($followouts));

        return response()->json([
            'status' => 'OK',
            'data' => [
                'followouts' => $followouts,
            ]
        ]);
    }

    public function store(Request $request)
    {
        $authUser = auth()->guard('api')->user();

        if (Gate::forUser($authUser)->denies('manage-reward-programs')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Access denied.',
            ], 403);
        }

        $followouts = $authUser->followouts()->public()->notReposted()->ongoingOrUpcoming()->doesntHave('reward_programs')->get();
        $followoutIds = $followouts->pluck('_id')->toArray();

        $validator = Validator::make($request->all(), [
            'picture' => 'nullable|image|mimetypes:image/jpeg,image/png|dimensions:min_width=100,min_height=100,max_width=5120,max_height=5120|max:10000',
            'title' => 'required|string|max:128',
            'description' => 'required|string|max:128',
            'redeem_count' => 'required|integer|min:1|max:1000000',
            'followout_id' => 'required|in:' . implode(',', $followoutIds),
            'enabled' => 'required|boolean',
            'redeem_code' => 'required|string|min:3|max:128',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $followout = Followout::findOrFail($request->input('followout_id'));

        $rewardProgram = new RewardProgram;
        $rewardProgram->title = $request->input('title');
        $rewardProgram->description = $request->input('description');
        $rewardProgram->redeem_count = (int) $request->input('redeem_count');
        $rewardProgram->redeem_code = $request->input('redeem_code');
        $rewardProgram->enabled = (bool) $request->input('enabled');
        $rewardProgram->require_coupon = (bool) $request->input('require_coupon');
        $rewardProgram = $authUser->reward_programs()->save($rewardProgram);

        $rewardProgram->author()->associate($authUser);
        $rewardProgram->followout()->associate($followout);
        $rewardProgram->save();

        if ($request->hasFile('picture')) {
            $rewardProgram->savePicture($request->file('picture'));
        }

        // If at least one attached coupon is required we'll disable the reward program until Followhost attaches the coupon
        if ($rewardProgram->require_coupon && $followout->coupons()->active()->count() === 0) {
            $rewardProgram->enabled = false;
            $rewardProgram->save();
        }

        return response()->json([
            'status' => 'OK',
            'data' => [
                'reward_program' => new RewardProgramResource(RewardProgram::find($rewardProgram->getKey())),
            ],
        ]);
    }

    public function update(Request $request, $rewardProgram)
    {
        $authUser = auth()->guard('api')->user();

        $rewardProgram = RewardProgram::find($rewardProgram);

        if (is_null($rewardProgram)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Reward program not found.',
            ], 404);
        }

        if (Gate::forUser($authUser)->denies('manage-reward-programs')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Access denied.',
            ], 403);
        }

        if ($authUser->id !== $rewardProgram->author_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Access denied.',
            ], 403);
        }

        if (!$rewardProgram->canBeUpdated()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Reward program can no longer be updated.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'picture' => 'nullable|image|mimetypes:image/jpeg,image/png|dimensions:min_width=100,min_height=100,max_width=5120,max_height=5120|max:10000',
            'title' => 'required|string|max:128',
            'description' => 'required|string|max:128',
            'redeem_count' => 'required|integer|min:1|max:1000000',
            'enabled' => 'required|boolean',
            'redeem_code' => 'required|string|min:3|max:128',
            'removed_pictures' => 'nullable|array|max:1',
            'removed_pictures.*' => 'nullable|string|distinct',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $rewardProgram->title = $request->input('title');
        $rewardProgram->description = $request->input('description');
        $rewardProgram->redeem_count = (int) $request->input('redeem_count');
        $rewardProgram->redeem_code = $request->input('redeem_code');
        $rewardProgram->enabled = (bool) $request->input('enabled');
        $rewardProgram->require_coupon = (bool) $request->input('require_coupon');
        $rewardProgram->save();

        if ($request->hasFile('picture')) {
            $rewardProgram->savePicture($request->file('picture'));
        }

        // If at least one attached coupon is required we'll disable the reward program until Followhost attaches the coupon
        if ($rewardProgram->require_coupon && $followout->coupons()->active()->count() === 0) {
            $rewardProgram->enabled = false;
            $rewardProgram->save();
        }

        return response()->json([
            'status' => 'OK',
            'data' => [
                'reward_program' => new RewardProgramResource(RewardProgram::find($rewardProgram->getKey())),
            ],
        ]);
    }

    public function pause($rewardProgram)
    {
        $authUser = auth()->guard('api')->user();

        $rewardProgram = RewardProgram::find($rewardProgram);

        if (is_null($rewardProgram)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Reward program not found.',
            ], 404);
        }

        if ($authUser->id !== $rewardProgram->author_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Access denied.',
            ], 403);
        }

        $rewardProgram->enabled = false;
        $rewardProgram->save();

        return response()->json([
            'status' => 'OK',
            'data' => [
                'reward_program' => new RewardProgramResource(RewardProgram::find($rewardProgram->getKey())),
            ],
        ]);
    }

    public function resume($rewardProgram)
    {
        $authUser = auth()->guard('api')->user();

        $rewardProgram = RewardProgram::find($rewardProgram);

        if (is_null($rewardProgram)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Reward program not found.',
            ], 404);
        }

        if ($authUser->id !== $rewardProgram->author_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Access denied.',
            ], 403);
        }

        // If at least one attached coupon is required we'll disable the reward program until Followhost attaches the coupon
        if ($rewardProgram->require_coupon && $rewardProgram->followout->coupons()->active()->count() === 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'Please attach a valid coupon to followout first.',
            ], 422);
        } else {
            $rewardProgram->enabled = true;
            $rewardProgram->save();
        }

        return response()->json([
            'status' => 'OK',
            'data' => [
                'reward_program' => new RewardProgramResource(RewardProgram::find($rewardProgram->getKey())),
            ],
        ]);
    }
}
