<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class Payment extends Model
{
    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function scopeViaChargebee($query)
    {
        return $query->where('payment_method', 'chargebee');
    }

    public function getAmountAttribute($value)
    {
        $value = $value ?: 0;

        return number_format($value, 2, '.', '');
    }

    public function getPromoCodeAmountAttribute($value)
    {
        return $value ? number_format($value, 2, '.', '') : null;
    }
}
