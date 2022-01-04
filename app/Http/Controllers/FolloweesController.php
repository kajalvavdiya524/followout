<?php

namespace App\Http\Controllers;

use Gate;
use Mail;
use Carbon;
use App\User;
use App\Followee;
use App\Followout;
use App\RewardProgram;
use Illuminate\Http\Request;

class FolloweesController extends Controller
{
    // Followee sends introductory letter to Followhost
    public function sendFolloweeIntro(User $user)
    {
        if (Gate::denies('introduce-yourself', $user)) {
            return abort(403, 'Access denied.');
        }

        $user->notify(new \App\Notifications\FolloweeIntro(auth()->user()));

        session()->flash('toastr.success', 'Your request has been sent.');

        return redirect()->back();
    }

    public function presentFollowoutRequest(Request $request)
    {
        if (auth()->user()->hasOpenDisputes()) {
            return abort(403, 'Please resolve open transactions first.');
        }

        $request->validate([
            'reward_program_id' => 'required|exists:reward_programs,_id',
        ]);

        $rewardProgram = RewardProgram::findOrFail($request->input('reward_program_id'));

        $followout = $rewardProgram->followout;

        // If user is already a followee, it means he's claiming the job and doesn't need followhost's approval
        $silentRequest = $followout->hasFollowee(auth()->user()->id);

        if ($followout->hasPendingFollowee(auth()->user()->id)) {
            session()->flash('toastr.error', 'You have a pending invite for ' . $followout->title . ' that you need to accept or decline first.');
            return redirect()->back();
        }

        $result = $followout->requestToPresentFollowout(auth()->user()->id, $request->input('reward_program_id'));

        if (!$result) {
            return abort(403, 'Access denied.');
        }

        if ($silentRequest) {
            session()->flash('toastr.success', 'Reward program job has been claimed. You can now start promoting the followout.');
        } else {
            session()->flash('toastr.success', 'Your request has been sent.');
        }

        return redirect()->route('followouts.show', ['followout' => $followout->id]);
    }

    public function inviteFollowee(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,_id',
            'followout_id' => [auth()->user()->isFollowhost() ? 'nullable' : 'required', 'exists:followouts,_id'],
            'reward_program_id' => [auth()->user()->isFollowhost() ? 'required' : 'nullable', 'exists:reward_programs,_id'],
        ]);

        $user = User::find($request->input('user_id'));
        $rewardProgram = RewardProgram::find($request->input('reward_program_id'));

        if (auth()->user()->isFollowhost()) {
            $followout = $rewardProgram->followout;
        } else {
            $followout = Followout::find($request->input('followout_id'));
        }

        if ($followout->author->id !== auth()->user()->id) {
            return abort(403, 'Access denied.');
        }

        if (Gate::denies('invite-followee', $user)) {
            session()->flash('toastr.error', 'You can\'t invite this user.');
            return redirect()->back();
        }

        if ($followout->hasFollowee($user->id)) {
            session()->flash('toastr.error', 'This user is already invited.');
            return redirect()->back();
        }

        if ($rewardProgram) {
            $invited = $followout->inviteFollowee($user->id, $rewardProgram->id);
        } else {
            $invited = $followout->inviteFollowee($user->id);
        }

        if ($invited) {
            session()->flash('toastr.success', 'Your invitation has been sent.');
        } else {
            session()->flash('toastr.error', 'Can\'t invite this user.');
        }

        return redirect()->back();
    }

    public function inviteFolloweeByEmail(Request $request)
    {
        // Currently disabled
        return abort(403);

        $request->validate([
            'followout_id' => 'required|exists:followouts,_id',
            'email' => 'required|email',
        ]);

        $followout = Followout::find($request->input('followout_id'));

        $email = mb_strtolower($request->input('email'));

        if ($followout->author->id !== auth()->user()->id) {
            return abort(403, 'Access denied.');
        }

        if (Gate::denies('invite-followee-by-email')) {
            session()->flash('toastr.error', 'You can\'t invite ' . $email);
            return redirect()->back();
        }

        if ($followout->hasFolloweeByEmail($email)) {
            session()->flash('toastr.error', 'You\'ve already invited ' . $email);
            return redirect()->back();
        }

        $invited = $followout->inviteFolloweeByEmail($email);

        if ($invited) {
            session()->flash('toastr.success', 'Your invitation has been sent to ' . $email);
        } else {
            session()->flash('toastr.error', 'Can\'t invite invite ' . $email);
        }

        return redirect()->back();
    }

    public function manageFolloweeInvitation(Request $request, Followout $followout)
    {
        $hash = $request->input('hash', null);

        if ($hash) {
            return redirect()->route('followouts.show', ['followout' => $followout->id, 'hash' => $hash]);
        }

        return redirect()->route('followouts.show', ['followout' => $followout->id]);
    }

    public function acceptFolloweeInvitation(Followout $followout)
    {
        $followee = $followout->followees()->pending()->notRequestedByUser()->where('user_id', auth()->user()->id)->firstOrFail();
        $followee->update(['status' => 'accepted']);

        $repostedFollowout = auth()->user()->repostFollowout($followout->id, $followee->reward_program_id);

        session()->flash('toastr.success', 'You\'ve accepted the invitation.');

        // TODO: Notification about accepted invitation

        if ((auth()->user()->isFriend() || auth()->user()->isFollowee()) && $followout->author->subscribed()) {
            auth()->user()->setRole('followee', Carbon::now()->addDays(config('followouts.release_followee_role_after')));
        }

        if ($repostedFollowout) {
            session()->flash('SHOW_FOLLOWOUT_REPOSTED_TUTORIAL', true);
            return redirect()->route('followouts.show', ['followout' => $repostedFollowout->id]);
        }

        return redirect()->route('followouts.show', ['followout' => $followout->id]);
    }

    public function declineFolloweeInvitation(Followout $followout)
    {
        $followee = $followout->followees()->pending()->notRequestedByUser()->where('user_id', auth()->user()->id)->firstOrFail();
        $followee->update(['status' => 'declined']);

        session()->flash('toastr.success', 'You\'ve declined the invitation.');

        // TODO: Notification about declined invitation

        return redirect()->route('followouts.show', ['followout' => $followout->id]);
    }

    public function acceptPresentFollowoutRequest(Followout $followout, User $user)
    {
        if ($followout->author->id !== auth()->user()->id) {
            return abort(403, 'Access denied.');
        }

        $followee = $followout->followees()->pending()->requestedByUser()->where('user_id', $user->id)->firstOrFail();
        $followee->update(['status' => 'accepted']);

        if (($user->isFriend() || $user->isFollowee()) && $followout->author->subscribed()) {
            $user->setRole('followee', Carbon::now()->addDays(config('followouts.release_followee_role_after')));
        }

        $user->repostFollowout($followout->id, $followee->reward_program_id);

        session()->flash('toastr.success', 'You\'ve accepted the request.');
        // TODO: Notification about accepted request

        return redirect()->route('followouts.show', ['followout' => $followout->id]);
    }

    public function declinePresentFollowoutRequest(Followout $followout, User $user)
    {
        if ($followout->author->id !== auth()->user()->id) {
            return abort(403, 'Access denied.');
        }

        $followee = $followout->followees()->pending()->requestedByUser()->where('user_id', $user->id)->firstOrFail();
        $followee->update(['status' => 'declined']);

        session()->flash('toastr.success', 'You\'ve declined the request.');

        // TODO: Notification about declined request

        return redirect()->route('followouts.show', ['followout' => $followout->id]);
    }
}
