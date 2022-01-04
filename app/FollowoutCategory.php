<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class FollowoutCategory extends Model
{
    protected $collection = 'followout_categories';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
    ];

    public static function getSocialCategory()
    {
        return self::where('name', 'Social')->first();
    }
}
