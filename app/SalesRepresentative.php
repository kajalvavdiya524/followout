<?php

namespace App;

use App\User;
use Jenssegers\Mongodb\Eloquent\Model;

class SalesRepresentative extends Model
{
    /**
     * The guarded attributes on the model.
     *
     * @var array
     */
    protected $guarded = [];

    public function hasInvitedUsers()
    {
        return $this->users_count > 0;
    }

    public function addReferredUser($userId, $viaPromoCode = false)
    {
        $user = User::find($userId);

        if (is_null($user)) {
            return false;
        }

        $user->sales_rep_code = $this->code;
        $user->sales_rep_promo_code = $viaPromoCode ? $this->promo_code : null;

        $user->save();

        return true;
    }

    public function getFullNameAttribute($value)
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function getUsersAttribute()
    {
        return User::where('sales_rep_code', $this->code)->get();
    }

    public function getUsersCountAttribute()
    {
        return User::where('sales_rep_code', $this->code)->count();
    }

    public function getPromoUsersAttribute()
    {
        return User::where('sales_rep_promo_code', $this->promo_code)->get();
    }
}
