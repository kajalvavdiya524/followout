<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class FollowoutCoupon extends Model
{
    protected $collection = 'followout_coupons';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'followout_id',
        'is_active',
    ];

    public function used_coupons()
    {
        return $this->hasMany('App\UsedCoupon', 'followout_coupon_id');
    }

    public function coupon()
    {
        return $this->belongsTo('App\Coupon', 'coupon_id');
    }

    public function followout()
    {
        return $this->belongsTo('App\Followout', 'followout_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    public function use($userId)
    {
        $user = User::find($userId);

        if (is_null($user)) {
            return null;
        }

        if (!$this->canBeUsed($userId)) {
            return null;
        }

        $usedCoupon = new UsedCoupon;
        $usedCoupon->followout_coupon()->associate($this);
        $usedCoupon->user()->associate($user);
        $usedCoupon->save();

        return $usedCoupon;
    }

    public function canBeUsed($userId = null)
    {
        if ($userId) {
            $user = User::find($userId);

            if (is_null($user)) {
                return false;
            }
        }

        if (!$this->isActive()) {
            return false;
        }

        if ($this->used_coupons()->where('user_id', $userId)->exists()) {
            return false;
        }

        return true;
    }

    public function enableCoupon()
    {
        $this->is_active = true;
        $this->save();

        return true;
    }

    public function disableCoupon()
    {
        $this->is_active = false;
        $this->save();

        return true;
    }

    public function isActive()
    {
        return $this->is_active === true;
    }

    public function pictureURL()
    {
        return $this->coupon->pictureURL();
    }

    public function useCount()
    {
        return $this->used_coupons()->count();
    }

    public function deleteCoupon()
    {
        $this->used_coupons()->delete();
        $this->delete();

        return true;
    }

    public function getTitleAttribute($value)
    {
        return $this->coupon->title;
    }

    public function getDescriptionAttribute($value)
    {
        return $this->coupon->description;
    }
}
