<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

/**
 * @property string $_id
 * @property string $code
 * @property double $amount
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class PromoCode extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code',
        'amount',
    ];

    // Users that have used the promo code
    public function users()
    {
        return $this->belongsToMany('App\User', null, 'promo_codes_used', 'used_by_users');
    }

    public function getAmountAttribute($value)
    {
        return $value ? (float) number_format($value, 2, '.', '') : null;
    }

    public function setAmountAttribute($value)
    {
        $this->attributes['amount'] = $value ? (float) number_format($value, 2, '.', '') : null;
    }
}
