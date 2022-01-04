<?php

namespace App;

use Carbon;
use PaymentHelper;
use Jenssegers\Mongodb\Eloquent\Model;

require_once(base_path('vendor/chargebee/chargebee-php/lib/ChargeBee.php'));

\ChargeBee_Environment::configure(env('CHARGEBEE_SITE'), env('CHARGEBEE_KEY'));

/**
 * @property string $_id
 * @property string $user_id
 * @property Carbon $expires_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Subscription extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'type',
        'is_canceled',
        'is_resumable',
        'expires_at',
        'next_billing_at',
        'chargebee_plan_id',
        'subscription_code_id',
        'chargebee_subscription_id',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'expires_at',
        'next_billing_at',
    ];

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function subscription_code()
    {
        return $this->belongsTo('App\SubscriptionCode', 'subscription_code_id');
    }

    public function scopeActive($query)
    {
        return $query->where('expires_at', '>', Carbon::now());
    }

    public function scopeExpiringSoon($query)
    {
        return $query->active()->where('expires_at', '<', Carbon::now()->addDays(3));
    }

    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<', Carbon::now());
    }

    public function scopePendingDeletion($query)
    {
        return $query->expired()->where('next_billing_at', null)->whereIn('is_canceled', [true, null]);
    }

    public function scopePendingRenewal($query)
    {
        return $query->where('next_billing_at', '<', Carbon::now())->whereIn('is_canceled', [false, null]);
    }

    public function renew()
    {
        $user = $this->user;

        $chargebeeSubscription = PaymentHelper::getChargebeeSubscription($user->id);

        if ($chargebeeSubscription === null) {
            $this->cancelAndDelete();

            return false;
        }

        if ($chargebeeSubscription['status'] === 'cancelled' && $this->isExpired()) {
            return $this->cancelAndDelete();
        }

        PaymentHelper::updateOrCreateChargebeeSubscription($this->user->id, $chargebeeSubscription);

        return true;
    }

    public function resume()
    {
        if (!$this->isCanceled()) {
            return true;
        }

        if ($this->isChargebeeSubscription()) {
            try {
                $result = \ChargeBee_Subscription::reactivate($this->chargebee_subscription_id);

                $chargebeeSubscription = PaymentHelper::parseChargebeeSubscriptionFromResponse($result->subscription());

                PaymentHelper::updateOrCreateChargebeeSubscription($this->user->id, $chargebeeSubscription);
            } catch (\Exception $e) {
                throw $e;
            }
        }

        $this->is_canceled = false;
        $this->save();

        return true;
    }

    public function cancel()
    {
        $user = $this->user;

        $chargebeeCustomer = PaymentHelper::getChargebeeCustomer($user->id);

        $chargebeeSubscription = PaymentHelper::getChargebeeSubscription($user->id);

        if ($this->isExpired()) {
            $this->cancelAndDelete();

            return true;
        }

        if (!$this->isCanceled()) {
            if ($this->isChargebeeSubscription()) {
                try {
                    $result = \ChargeBee_Subscription::cancel($this->chargebee_subscription_id);
                } catch (\Exception $e) {
                    throw $e;
                }
            }

            $this->is_canceled = true;
            $this->save();
        }

        return true;
    }

    public function cancelAndDelete($notify = true)
    {
        PaymentHelper::deleteChargebeeSubscriptionForUser($this->user->id);

        if ($notify) {
            $this->user->notify(new \App\Notifications\SubscriptionExpired);
        }

        $this->delete();

        return true;
    }

    public function isChargebeeSubscription()
    {
        return is_null($this->chargebee_subscription_id) ? false : true;
    }

    public function isBasic()
    {
        return $this->type === 'subscription_basic';
    }

    public function isMonthly()
    {
        return $this->chargebee_plan_id === 'followouts-pro-monthly';
    }

    public function isYearly()
    {
        return $this->chargebee_plan_id === 'followouts-pro-yearly';
    }

    public function isActive()
    {
        return $this->isBasic() || $this->expires_at > Carbon::now();
    }

    public function isExpired()
    {
        return !$this->isActive();
    }

    public function isCanceled()
    {
        return $this->is_canceled === true;
    }

    public function isResumable()
    {
        return !$this->isBasic() && $this->is_resumable !== false;
    }

    /**
     * Return true if subscription was created via subscription code.
     *
     * @return bool
     */
    public function isGifted()
    {
        return $this->subscription_code !== null;
    }

    public function onGracePeriod()
    {
        return $this->isActive() && $this->isCanceled();
    }
}
