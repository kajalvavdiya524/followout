<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class Message extends Model
{
    /**
     * The guarded attributes on the model.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'read_at',
    ];

    public function from()
    {
        return $this->belongsTo('App\User', 'from_id');
    }

    public function to()
    {
        return $this->belongsTo('App\User', 'to_id');
    }

    public function scopeFrom($query, $from)
    {
        return $query->where('from_id', $from);
    }

    public function scopeTo($query, $to)
    {
        return $query->where('to_id', $to);
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function markAsRead()
    {
        if (is_null($this->read_at)) {
            $this->update(['read_at' => now()]);
        }
    }

    public function isRead()
    {
        return $this->read_at !== null;
    }

    public function isUnread()
    {
        return $this->read_at === null;
    }

    public function setMessageAttribute($value)
    {
        $value = trim($value);
        $value = preg_replace('/\s\s+/', ' ', $value);

        $this->attributes['message'] = $value;
    }
}
