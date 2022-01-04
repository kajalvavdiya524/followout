<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class SocialAccount extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'provider_user_id',
        'provider',
        'token',
        'refresh_token',
        'expires_in',
        'expires_at',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'expires_at',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'token',
        'refresh_token',
        'expires_in',
    ];

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function scopeFacebook($query)
    {
        return $query->where('provider', 'facebook');
    }

    public function disconnect()
    {
        $this->delete();

        return true;
    }
}
