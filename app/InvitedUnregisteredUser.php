<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class InvitedUnregisteredUser extends Model
{
    /**
     * The guarded attributes on the model.
     *
     * @var array
     */
    protected $guarded = [];

    public function attachToUser()
    {
        $user = User::where('email', $this->email)->first();

        if (is_null($user)) {
            return false;
        }

        if ($this->type === 'followee_invitation') {
            $followout = Followout::find($this->parameters['followout_id']);

            if ($followout) {
                $followout->inviteFollowee($user->id);
            }

            $this->delete();
        }
    }

    public function setEmailAttribute($value)
    {
        $this->attributes['email'] = $value ? mb_strtolower($value) : null;
    }
}
