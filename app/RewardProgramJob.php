<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class RewardProgramJob extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'reward_program_jobs';

    /**
     * The guarded attributes on the model.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'redeemed_at',
    ];

    public static $withAll = [
        'user',
        'followout',
        'parent_followout',
        'reward_program',
    ];

    public function user()
    {
        return $this->belongsTo('App\User', 'user_id');
    }

    public function followout()
    {
        return $this->belongsTo('App\Followout', 'followout_id');
    }

    public function parent_followout()
    {
        return $this->belongsTo('App\Followout', 'parent_followout_id');
    }

    public function reward_program()
    {
        return $this->belongsTo('App\RewardProgram', 'reward_program_id');
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isClaimed()
    {
        return $this->status === 'claimed';
    }

    public function isRedeemed()
    {
        return $this->status === 'redeemed';
    }

    public function rewardIsReceived()
    {
        return $this->transaction_status === 'close';
    }

    public function inDispute()
    {
        return $this->transaction_status === 'open';
    }

    public function transactionIsClosed()
    {
        return $this->transaction_status === 'close';
    }

    public function scopeReceived($query)
    {
        return $query->where('transaction_status', 'close');
    }

    public function scopeReceivedOrInDispute($query)
    {
        return $query->whereIn('transaction_status', ['close', 'open'])->claimed();
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeNotPending($query)
    {
        return $query->where('status', '!=', 'pending');
    }

    public function scopeClaimed($query)
    {
        return $query->where('status', 'claimed');
    }

    public function scopeRedeemed($query)
    {
        return $query->where('status', 'redeemed');
    }

    public function scopeNotRedeemed($query)
    {
        return $query->where('status', '!=', 'redeemed');
    }

    public function userCanOpenDispute()
    {
        if ($this->followout->hasEnded()) return false;

        if ($this->inDispute()) return false;

        if ($this->isRedeemed()) return false;

        return true;
    }

    /**
     * Return true if reward program followee can close the dispute.
     *
     * Note: followee can close at any time.
     *
     * @return bool
     */
    public function userCanCloseDispute()
    {
        return true;
    }

    public function hostCanCloseDispute()
    {
        if ($this->inDispute()) return true;

        return false;
    }

    public function markRewardAsReceived()
    {
        $this->transaction_status = 'close';
        $this->save();
    }

    public function markAsRedeemed()
    {
        $this->status = 'redeemed';
        $this->transaction_status = 'close';
        $this->redeemed_at = now();
        $this->save();
    }

    public function canBeRedeemed()
    {
        return $this->reward_program->canBeRedeemedByUser($this->user->id);
    }

    public function getApiStatus()
    {
        if ($this->isRedeemed()) return 4;

        if ($this->canBeRedeemed()) return 3;

        if ($this->isClaimed()) return 2;

        return 1; // Job is pending approval
    }

    /**
     * Calculates and returns checkin count that can be used for redeeming the job.
     *
     * @return int
     */
    public function getAvailableCheckinsCount()
    {
        $availableCount = 0;

        // TODO: support multiple reward programs with checkin fractions

        if ($this->followout) {
            if ($this->reward_program->require_coupon) {
                $availableCount += $this->followout->getAttendeesWithPresentedCouponCount();
            } else {
                $availableCount += $this->followout->getAttendeesCount();
            }

            // Get all followout's reward program jobs with given user
            $redeemedJobs = $this->parent_followout->reward_program_jobs()->receivedOrInDispute()->where('user_id', $this->user->id)->get();

            foreach ($redeemedJobs as $job) {
                if ($this->reward_program->require_coupon) {
                    // Only subtract $availableCount from redeemed jobs that required coupon
                    if ($job->reward_program->require_coupon) {
                        $availableCount -= $job->reward_program->redeem_count;
                    }
                } else {
                    $availableCount -= $job->reward_program->redeem_count;
                }
            }
        }

        return $availableCount < 0 ? 0 : $availableCount;
    }
}
