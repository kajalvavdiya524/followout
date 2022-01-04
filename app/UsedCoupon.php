<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class UsedCoupon extends Model
{
    protected $collection = 'used_coupons';

    public function coupon()
    {
        return $this->followout_coupon->coupon;
    }

    public function followout_coupon()
    {
        return $this->belongsTo('App\FollowoutCoupon', 'followout_coupon_id');
    }

    public function user()
    {
        return $this->belongsTo('App\User', 'user_id');
    }
}
