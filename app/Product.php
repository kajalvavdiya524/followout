<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

/**
 * @property string $_id
 * @property string $name
 * @property string $description
 * @property double $price
 * @property string $type
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Product extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'action_name',
        'description',
        'price',
        'type',
    ];

    /**
     * The list of all product types. For reference only.
     *
     * @var array
     */
    protected $productTypes = [
        'coupon_uses',
        'followee_services',
        'subscription_monthly',
        'subscription_yearly',
        'subscription_basic',
    ];

    public function scopeCouponUses($query)
    {
        return $query->whereIn('type', ['coupon_uses'])->orderBy('name');
    }

    public function scopeServices($query)
    {
        return $query->whereIn('type', ['followee_services'])->orderBy('name');
    }

    public function scopeFolloweeServices($query)
    {
        return $query->where('type', 'followee_services');
    }

    public function scopeSubscriptions($query)
    {
        return $query->whereIn('type', ['subscription_monthly', 'subscription_yearly'])->orderBy('name');
    }

    public function scopeSubscriptionMonthly($query)
    {
        return $query->where('type', 'subscription_monthly');
    }

    public function scopeSubscriptionYearly($query)
    {
        return $query->where('type', 'subscription_yearly');
    }

    public function scopeSubscriptionBasic($query)
    {
        return $query->where('type', 'subscription_basic');
    }

    public function scopeSubscriptionSetupFee($query)
    {
        return $query->where('type', 'subscription_setup_fee');
    }

    public function isSubscription()
    {
        return $this->isSubscriptionMonthly() || $this->isSubscriptionYearly();
    }

    public function isCouponUses()
    {
        return $this->type === 'coupon_uses';
    }

    public function isFolloweeServices()
    {
        return $this->type === 'followee_services';
    }

    public function isSubscriptionBasic()
    {
        return $this->type === 'subscription_basic';
    }

    public function isSubscriptionMonthly()
    {
        return $this->type === 'subscription_monthly';
    }

    public function isSubscriptionYearly()
    {
        return $this->type === 'subscription_yearly';
    }

    public function getPriceAttribute($value)
    {
        return number_format((float) $value, 2, '.', '');
    }

    public function setPriceAttribute($value)
    {
        $this->attributes['price'] = $value ? (float) number_format($value, 2, '.', '') : null;
    }
}
