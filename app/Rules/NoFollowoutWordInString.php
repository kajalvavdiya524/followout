<?php

namespace App\Rules;

use App\User;
use Illuminate\Contracts\Validation\Rule;
use Str;

class NoFollowoutWordInString implements Rule
{
    /**
     * @var User|null
     */
    public $user;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(User $user = null)
    {
        $this->user = $user;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if ($this->user) {
            if ($this->user->isAdmin()) {
                return true;
            }

            // TODO: also promotional accounts can have it
        }

        $containsForbiddenWords = Str::contains(mb_strtolower($value), ['followout', 'follow out']);

        return ! $containsForbiddenWords;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute cannot contain "Followout" word.';
    }
}
