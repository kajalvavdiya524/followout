<?php

namespace App\Http\Controllers;

use FollowoutHelper;
use App\RewardProgram;
use App\RewardProgramJob;
use Illuminate\Http\Request;

class RewardProgramJobsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $rewardPrograms = RewardProgram::with('author')->active()->orderByDesc('created_at')->get();
        $rewardPrograms = FollowoutHelper::filterRewardProgramsForUser($rewardPrograms, auth()->user());

        $data = ['availableRewardPrograms' => $rewardPrograms];

        if (auth()->user()->isFollowhost()) {
            // Get all reward programs created by auth user
            $ownRewardPrograms = RewardProgram::with('author')->where('author_id', auth()->user()->id)->active()->orderByDesc('created_at')->get();
            $ownRewardPrograms = FollowoutHelper::filterRewardProgramsForUser($ownRewardPrograms, auth()->user());

            // Get all jobs from followees that applied
            $data['claimedRewardProgramJobs'] = collect();
            foreach ($ownRewardPrograms as $rewardProgram) {
                foreach ($rewardProgram->jobs as $rewardProgramJob) {
                    $data['claimedRewardProgramJobs']->push($rewardProgramJob);
                }
            }

            // Higher scores come first
            $data['claimedRewardProgramJobs'] = $data['claimedRewardProgramJobs']->sort(function ($a, $b) {
                if ($a->isRedeemed()) {
                    $scoreA = 1;
                } elseif ($a->inDispute()) {
                    $scoreA = 3;
                } else {
                    $scoreA = 2;
                }

                if ($b->isRedeemed()) {
                    $scoreB = 1;
                } elseif ($b->inDispute()) {
                    $scoreB = 3;
                } else {
                    $scoreB = 2;
                }

                return ($scoreA * $a->created_at->timestamp) - ($scoreB - $b->created_at->timestamp);
            });
        }

        return view('reward_program_jobs.index', $data);
    }

    /**
     * Redeem the job.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\RewardProgramJob  $rewardProgramJob
     * @return \Illuminate\Http\Response
     */
    public function redeem(Request $request, $rewardProgramJob)
    {
        $rewardProgramJob = RewardProgramJob::with('user', 'reward_program')->findOrFail($rewardProgramJob);

        if ($rewardProgramJob->user->id !== auth()->user()->id) {
            return abort(403, 'Access denied.');
        }

        $rewardProgram = $rewardProgramJob->reward_program;

        if ($request->input('code') !== $rewardProgram->redeem_code) {
            session()->flash('toastr.error', 'The code is not valid.');
            return redirect()->back();
        }

        if (!$rewardProgram->canBeRedeemedByUser(auth()->user()->id)) {
            session()->flash('toastr.error', 'This reward program job cannot be redeemed.');
            return redirect()->route('reward_program_jobs.index');
        }

        $rewardProgramJob->markAsRedeemed();

        session()->flash('toastr.success', 'The reward program job has been redeemed.');

        return redirect()->route('reward_program_jobs.index');
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
    public function markAsReceived(Request $request, RewardProgramJob $rewardProgramJob)
    {
        if ($rewardProgramJob->user->id !== auth()->user()->id && $rewardProgramJob->reward_program->author->id !== auth()->user()->id) return abort(403, 'Access denied.');

        if ($rewardProgramJob->user->id !== auth()->user()->id && $rewardProgramJob->inDispute() && !$rewardProgramJob->userCanCloseDispute()) return abort(403, 'Access denied.');

        if ($rewardProgramJob->reward_program->author->id !== auth()->user()->id && $rewardProgramJob->inDispute() && !$rewardProgramJob->hostCanCloseDispute()) return abort(403, 'Access denied.');

        if (!$rewardProgramJob->canBeRedeemed()) {
            session()->flash('toastr.error', 'This reward program job cannot be received.');
            return redirect()->route('reward_program_jobs.index');
        }

        $rewardProgramJob->markRewardAsReceived();

        session()->flash('toastr.success', 'Reward has been marked as received.');

        return redirect()->back();
    }

    /**
     * Toggle the dispute for the job.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\RewardProgramJob  $rewardProgramJob
     * @return \Illuminate\Http\Response
     */
    public function toggleDispute(Request $request, $rewardProgramJob)
    {
        $rewardProgramJob = RewardProgramJob::with('user', 'reward_program')->findOrFail($rewardProgramJob);

        if ($rewardProgramJob->user->id === auth()->user()->id) { // request coming from followee
            if ($rewardProgramJob->inDispute()) { // followee closes dispute
                if ($rewardProgramJob->userCanCloseDispute()) {
                    $rewardProgramJob->transaction_status = 'close';

                    session()->flash('toastr.success', 'Reward has been marked as received.');
                }
            } else { // followee opens dispute
                if ($rewardProgramJob->userCanOpenDispute()) {
                    $rewardProgramJob->transaction_status = 'open';

                    $rewardProgramJob->reward_program->author->notify(new \App\Notifications\RewardNotReceived($rewardProgramJob));

                    session()->flash('toastr.success', 'Request has been sent.');
                }
            }
        } elseif ($rewardProgramJob->reward_program->author->id === auth()->user()->id) { // request coming from followhost
            if ($rewardProgramJob->inDispute()) { // followhost closing dispute
                if ($rewardProgramJob->hostCanCloseDispute()) {
                    $rewardProgramJob->transaction_status = 'close';

                    session()->flash('toastr.success', 'Reward has been marked as received.');
                }
            } else {
                // Followhost cannot open disputes
                return abort(403, 'Access denied.');
            }
        }

        $rewardProgramJob->save();

        return redirect()->back();
    }

    /**
     * Resolve all open transactions both for own reward programs and redeemed reward program jobs.
     *
     * @return \Illuminate\Http\Response
     */
    public function resolveAllDisputes()
    {
        // As followhost
        $rewardProgramIds = auth()->user()->reward_programs()->whereHas('jobs', function ($query) {
            $query->where('transaction_status', 'open');
        })->pluck('_id');

        $rewardProgramJobs = RewardProgramJob::whereIn('reward_program_id', $rewardProgramIds)->where('transaction_status', 'open')->get();

        foreach ($rewardProgramJobs as $rewardProgramJob) {
            if ($rewardProgramJob->inDispute() && $rewardProgramJob->hostCanCloseDispute()) {
                $rewardProgramJob->transaction_status = 'close';
                $rewardProgramJob->save();
            }
        }

        // As followee
        $rewardProgramJobs = auth()->user()->reward_program_jobs()->where('transaction_status', 'open')->get();

        foreach ($rewardProgramJobs as $rewardProgramJob) {
            if ($rewardProgramJob->inDispute() && $rewardProgramJob->userCanCloseDispute()) {
                $rewardProgramJob->transaction_status = 'close';
                $rewardProgramJob->save();
            }
        }

        session()->flash('toastr.success', 'All rewards has been marked as received.');

        return redirect()->back();
    }
}
