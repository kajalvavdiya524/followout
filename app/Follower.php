<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class Follower extends Model
{
    protected $collection = 'followers';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'from_id',
        'to_id',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'is_mutual_subscription',
    ];

    public function follower()
    {
        return $this->belongsTo('App\User', 'from_id');
    }

    public function subscriber()
    {
        return $this->belongsTo('App\User', 'from_id');
    }

    public function follows()
    {
        return $this->belongsTo('App\User', 'to_id');
    }

    public function getIsMutualSubscriptionAttribute($value)
    {
        return $this->follows->following($this->follower->id);
    }
}
