<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class NotificationSetting extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'platform',
        'is_enabled',
        'notification_type',
    ];

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function scopePlatform($query, $platform)
    {
        return $query->where('platform', $platform);
    }

    public function isEnabled()
    {
        return $this->is_enabled === true;
    }
}
