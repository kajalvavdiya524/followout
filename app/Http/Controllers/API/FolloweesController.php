<?php

namespace App\Http\Controllers\API;

use Gate;
use Mail;
use Carbon;
use Validator;
use App\User;
use App\Followee;
use App\Followout;
use App\RewardProgram;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Notifications\FolloweeIntro;

class FolloweesController extends Controller
{
    public function show(Request $request, $followee)
    {
        $followee = Followee::with(['followout', 'followout.flyer', 'followout.pictures', 'followout.experience_categories', 'reward_program'])->find($followee);

        if (is_null($followee)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Followee not found.',
            ], 404);
        }

        $followout = $followee->followout;

        $authUser = auth()->guard('api')->user();

        if (!$followout->userHasAccess($authUser, $request->input('hash', null))) {
            return response()->json([
                'status' => 'error',
                'message' => 'Access denied.',
            ], 403);
        }

        return response()->json([
            'status' => 'OK',
            'data' => [
                'followee' => $followee,
            ],
        ]);
    }

    /**
     * Send introductory letter to Followhost from Followee.
     *
     * @param  int $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendFolloweeIntro($user)
    {
        $authUser = auth()->guard('api')->user();

        $user = User::find($user);

        if (is_null($user)) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found.',
            ], 404);
        }

        if (Gate::forUser($authUser)->denies('introduce-yourself', $user)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Access denied.',
            ], 403);
        }

        $user->notify(new FolloweeIntro($authUser));

        return response()->json([ 'status' => 'OK' ]);
    }

    public function presentFollowoutRequest(Request $request)
    {
        $authUser = auth()->guard('api')->user();

        if ($authUser->hasOpenDisputes()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Please resolve open transactions first.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'reward_program_id' => 'required|exists:reward_programs,_id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $rewardProgram = RewardProgram::findOrFail($request->input('reward_program_id'));

        $followout = $rewardProgram->followout;

        if ($followout->hasPendingFollowee($authUser->id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You have a pending invite for ' . $followout->title . ' that you need to accept or decline first.',
            ], 403);
        }

        $result = $followout->requestToPresentFollowout($authUser->id, $request->input('reward_program_id'));

        if (!$result) {
            return response()->json([
                'status' => 'error',
                'message' => 'Access denied.',
            ], 403);
        }

        return response()->json([ 'status' => 'OK' ]);
    }

    public function inviteFollowee(Request $request)
    {
        $authUser = auth()->guard('api')->user();

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,_id',
            'followout_id' => [($authUser->isFollowhost() ? 'nullable' : 'required'), 'exists:followouts,_id'],
            'reward_program_id' => [($authUser->isFollowhost() ? 'required' : 'nullable'), 'exists:reward_programs,_id'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = User::find($request->input('user_id'));

        $rewardProgram = RewardProgram::find($request->input('reward_program_id'));

        if ($authUser->isFollowhost()) {
            $followout = $rewardProgram->followout;
        } else {
            $followout = Followout::find($request->input('followout_id'));
        }

        if ($followout->author->id !== $authUser->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'You are not the author of this Followout.'
            ], 403);
        }

        if (Gate::denies('invite-followee', $user)) {
            return response()->json([
                'status' => 'error',
                'message' => 'You can\'t invite this user.'
            ], 403);
        }

        if ($followout->hasFollowee($user->id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'This user is already invited.'
            ], 403);
        }

        if ($authUser->isFollowhost()) {
            $invited = $followout->inviteFollowee($user->id, $rewardProgram->id);
        } else {
            $invited = $followout->inviteFollowee($user->id);
        }

        if (!$invited) {
            return response()->json([
                'status' => 'error',
                'message' => 'Can\'t invite this user.'
            ], 422);
        }

        return response()->json([ 'status' => 'OK' ]);
    }

    public function acceptFolloweeInvitation($followout)
    {
        $authUser = auth()->guard('api')->user();

        $followout = Followout::find($followout);

        if (is_null($followout)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Followout not found.',
            ], 404);
        }

        $followee = $followout->followees()->pending()->notRequestedByUser()->where('user_id', $authUser->id)->first();

        if (is_null($followee)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Followee not found.',
            ], 404);
        }

        $followee->update([ 'status' => 'accepted' ]);

        // TODO: Notification about accepted invitation

        if (($authUser->isFriend() || $authUser->isFollowee()) && $followout->author->subscribed()) {
            $authUser->setRole('followee', Carbon::now()->addDays(config('followouts.release_followee_role_after')));
        }

        $repostedFollowout = $authUser->repostFollowout($followout->id, $followee->reward_program_id);

        return response()->json([
            'status' => 'OK',
            'data' => [
                'reposted_followout' => $repostedFollowout ?? null,
            ],
        ]);
    }

    public function declineFolloweeInvitation($followout)
    {
        $authUser = auth()->guard('api')->user();

        $followout = Followout::find($followout);

        if (is_null($followout)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Followout not found.',
            ], 404);
        }

        $followee = $followout->followees()->pending()->notRequestedByUser()->where('user_id', $authUser->id)->first();

        if (is_null($followee)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Followee not found.',
            ], 404);
        }

        $followee->update([ 'status' => 'declined' ]);

        // TODO: Notification about declined invitation

        return response()->json([ 'status' => 'OK' ]);
    }

    public function acceptPresentFollowoutRequest($followout, $user)
    {
        $authUser = auth()->guard('api')->user();

        $followout = Followout::find($followout);

        if (is_null($followout)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Followout not found.',
            ], 404);
        }

        $user = User::find($followout);

        if (is_null($user)) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found.',
            ], 404);
        }

        if ($followout->author->id !== $authUser->id) {
            return abort(403, 'Access denied.');
        }

        $followee = $followout->followees()->pending()->requestedByUser()->where('user_id', $user->id)->first();

        if (is_null($followee)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Followee not found.',
            ], 404);
        }

        $followee->update(['status' => 'accepted']);

        if (($user->isFriend() || $user->isFollowee()) && $followout->author->subscribed()) {
            $user->setRole('followee', Carbon::now()->addDays(config('followouts.release_followee_role_after')));
        }

        $user->repostFollowout($followout->id);

        // TODO: Notification about accepted request

        return response()->json([ 'status' => 'OK' ]);
    }

    public function declinePresentFollowoutRequest($followout, $user)
    {
        $authUser = auth()->guard('api')->user();

        $followout = Followout::find($followout);

        if (is_null($followout)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Followout not found.',
            ], 404);
        }

        $user = User::find($followout);

        if (is_null($user)) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found.',
            ], 404);
        }

        if ($followout->author->id !== $authUser->id) {
            return abort(403, 'Access denied.');
        }

        $followee = $followout->followees()->pending()->requestedByUser()->where('user_id', $user->id)->first();

        if (is_null($followee)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Followee not found.',
            ], 404);
        }

        $followee->update(['status' => 'declined']);

        // TODO: Notification about declined request

        return response()->json([ 'status' => 'OK' ]);
    }
}
