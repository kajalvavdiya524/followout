<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class Favorite extends Model
{
    /**
     * Get all of the owning favoriteable models.
     */
    public function favoriteable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo('App\User', 'user_id');
    }

    public function scopeFollowouts($query)
    {
        return $query->where('favoriteable_type', 'App\Followout');
    }

    public function isFollowout()
    {
        return $this->favoriteable_type === 'App\Followout';
    }

    public function isShared()
    {
        return $this->is_shared === true;
    }
}
