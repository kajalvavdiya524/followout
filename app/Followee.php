<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

/**
 * @property string $_id
 * @property string $user_id
 * @property string $followout_id
 * @property bool   $requested_by_user
 * @property string $status
 */
class Followee extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function followout()
    {
        return $this->belongsTo('App\Followout');
    }

    public function reward_program()
    {
        return $this->belongsTo('App\RewardProgram', 'reward_program_id');
    }

    public function scopeAccepted($query)
    {
        return $query->where('status', 'accepted');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeRequestedByUser($query)
    {
        return $query->where('requested_by_user', true);
    }

    public function scopeNotRequestedByUser($query)
    {
        return $query->whereIn('requested_by_user', [null, false]);
    }

    public function scopeDeclined($query)
    {
        return $query->where('status', 'declined');
    }

    public function scopeNotDeclined($query)
    {
        return $query->where('status', '!=', 'declined');
    }

    public function url($withHash = false)
    {
        if ($withHash) {
            return route('followouts.invitation.manage', ['followout' => $this->followout->id, 'hash' => $this->followout->hash]);
        }

        return route('followouts.invitation.manage', ['followout' => $this->followout->id]);
    }

    public function isAccepted()
    {
        return $this->status === 'accepted';
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isPendingApproval()
    {
        return $this->status === 'pending' && $this->requested_by_user === true;
    }

    public function isDeclined()
    {
        return $this->status === 'declined';
    }
}
