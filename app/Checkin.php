<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

/**
 * @property string $_id
 * @property string $user_id
 * @property string $followout_id
 * @property string $status
 * @property bool   $coupon_entered
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Checkin extends Model
{
    protected $collection = 'checkins';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'status', 'coupon_entered'
    ];

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function followout()
    {
        return $this->belongsTo('App\Followout');
    }

    public function scopeCompleted($query)
    {
        return $query->whereIn('status', ['enter', 'exit']);
    }

    public function scopePresentedCoupon($query)
    {
        return $query->where('coupon_entered', true);
    }
}
