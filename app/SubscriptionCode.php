<?php

namespace App;

use Carbon;
use PaymentHelper;
use Jenssegers\Mongodb\Eloquent\Model;

/**
 * @property string              $_id
 * @property string              $email
 * @property string              $code
 * @property string              $plan_id
 * @property string              $subscription_id
 * @property \Carbon\Carbon      $created_at
 * @property \Carbon\Carbon      $updated_at
 * @property \Carbon\Carbon|null $activated_at
 */
class SubscriptionCode extends Model
{
    /**
     * The attributes that aren't mass assignable.
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
        'expires_at', 'activated_at'
    ];

    public function user()
    {
        return $this->belongsTo('App\User', 'email', 'email');
    }

    public function subscription()
    {
        return $this->hasOne('App\Subscription', 'subscription_code_id');
    }

    public function scopeUnactivated($query)
    {
        return $query->whereNull('activated_at');
    }
}
