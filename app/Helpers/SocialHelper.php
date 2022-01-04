<?php

namespace App\Helpers;

use Str;
use Carbon;
use Socialite;
use Exception;
use App\User;
use App\SocialAccount;
use Illuminate\Auth\Events\Registered;
use Laravel\Socialite\Contracts\User as ProviderUser;

class SocialHelper
{
    public static function user($provider)
    {
        try {
            $providerUser = Socialite::driver($provider)->stateless()->user();
        } catch (Exception $e) {
            return null;
        }

        self::updateUserData($providerUser, $provider);

        return $providerUser;
    }

    public static function providerUserFromToken($token, $provider)
    {
        try {
            $providerUser = Socialite::driver($provider)->stateless()->userFromToken($token);
        } catch (Exception $e) {
            return null;
        }

        return $providerUser;
    }

    public static function userFromToken($token, $provider)
    {
        try {
            $providerUser = Socialite::driver($provider)->stateless()->userFromToken($token);
        } catch (Exception $e) {
            return null;
        }

        $account = SocialAccount::where('provider', $provider)->where('provider_user_id', $providerUser->getId())->first();

        if (is_null($account)) {
            return null;
        }

        self::updateUserData($providerUser, $provider);

        return $account->user;
    }

    public static function facebookConnected(User $user)
    {
        return self::providerConnected($user, 'facebook');
    }

    public static function providerConnected(User $user, $provider)
    {
         return $user->social_accounts()->where('provider', $provider)->exists();
    }

    public static function createOrGetUserFromToken($token, $provider)
    {
        $providerUser = self::providerUserFromToken($token, $provider);

        if (is_null($providerUser)) {
            throw new Exception("Provider user doesn't exist.", 400);
        }

        return self::createOrGetUser($providerUser, $provider);
    }

    public static function createOrGetUser(ProviderUser $providerUser, $provider)
    {
        $account = SocialAccount::where('provider', $provider)->where('provider_user_id', $providerUser->getId())->first();

        if ($account) {
            self::updateUserData($providerUser, $provider);

            return User::with(User::$withAll)->find($account->user->id);
        }

        $account = self::createSocialAccount($providerUser, $provider);

        $user = User::where('email', mb_strtolower($providerUser->getEmail()))->first();

        if (!$user) {
            $user = self::createUser(mb_strtolower($providerUser->getEmail()), $providerUser->getName());
        }

        $account->user()->associate($user);
        $account->save();

        return User::with(User::$withAll)->find($user->id);
    }

    public static function updateUserData(ProviderUser $providerUser, $provider)
    {
        $account = SocialAccount::where('provider', $provider)->where('provider_user_id', $providerUser->getId())->first();

        if ($account) {
            $account->token = $providerUser->token;
            $account->refresh_token = $providerUser->refreshToken;
            $account->expires_in = $providerUser->expiresIn;
            $account->expires_at = $providerUser->expiresIn ? Carbon::now()->addSeconds($providerUser->expiresIn) : null;
            $account->save();
        }

        return true;
    }

    public static function reassignSocialAccount(ProviderUser $providerUser, $provider, User $assignee)
    {
        $oldAccount = SocialAccount::where('provider', $provider)->where('provider_user_id', $providerUser->getId())->first();
        $oldAccount->delete();

        $account = self::createSocialAccount($providerUser, $provider);

        $account->user()->associate($assignee);
        $account->save();

        return $account;
    }

    public static function createSocialAccount(ProviderUser $providerUser, $provider)
    {
        $account = new SocialAccount([
            'provider' => $provider,
            'provider_user_id' => $providerUser->getId(),
            'token' => $providerUser->token,
            'refresh_token' => $providerUser->refreshToken,
            'expires_in' => $providerUser->expiresIn,
            'expires_at' => $providerUser->expiresIn ? Carbon::now()->addSeconds($providerUser->expiresIn) : null,
        ]);

        return $account;
    }

    public static function createUser($email, $name)
    {
        $user = new User;
        $user->email = mb_strtolower($email);
        $user->name = preg_replace('/\s+/', ' ', $name);
        $user->is_unregistered = true;
        $user->is_activated = $email ? true : false;
        $user->role = 'friend';
        $user->privacy_type = 'private';
        $user->api_token = Str::random(100);
        $user->last_seen = Carbon::now();
        $user->save();

        event(new Registered($user));

        return $user;
    }
}
