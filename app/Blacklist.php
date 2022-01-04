<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class Blacklist extends Model
{
    protected $collection = 'blacklist';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'blocked_user_id',
    ];

    public function user()
    {
        return $this->belongsTo('App\User', 'user_id');
    }

    public function blocked_user()
    {
        return $this->belongsTo('App\User', 'blocked_user_id');
    }
}
