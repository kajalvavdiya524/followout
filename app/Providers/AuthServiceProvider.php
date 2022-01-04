<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Gate::define('introduce-yourself', function ($user, $followhost) {
            return !$user->isFollowhost() && $followhost->isFollowhost() && !$followhost->blocked($user->id);
        });

        Gate::define('set-profile-privacy-type-public', function ($user) {
            return true; // Currently allowed for all users
        });

        Gate::define('set-followout-privacy-type-public', function ($user) {
            return ($user->isFollowhost() && $user->subscribed()) || $user->isAdmin();
        });

        Gate::define('manage-reward-programs', function ($user) {
            return $user->isFollowhost() && $user->subscribed();
        });

        Gate::define('invite-followee', function ($user, $invitedUser) {
            $userCanInvite = (($user->isFollowhost() || $user->isFollowee()) && $user->subscribed()) || $user->isAdmin();
            $distinctUser = $user->id !== $invitedUser->id;
            $userNotBlocked = !$invitedUser->blocked($user->id);

            return $userCanInvite && $distinctUser && $userNotBlocked;
        });

        Gate::define('invite-followee-by-email', function ($user) {
            return false; // Currently disabled
            // return ($user->isFollowhost() && $user->subscribed()) || $user->isFollowee() || $user->isAdmin();
        });

        Gate::define('request-to-present-followout', function ($user, $followout) {
            $followhost = $followout->author;
            $isUpcomingOrOngoing = $followout->isUpcomingOrOngoing();
            $isSubscribedFollowhost = $followhost->isFollowhost() && $followhost->subscribed();
            $notReposted = !$followout->isReposted();
            $distinctUser = $user->id !== $followhost->id;
            $userNotBlocked = !$followhost->blocked($user->id);
            $hasActiveRewardProgram = $followout->hasActiveRewardProgram();
            $hasPendingInvite = $followout->hasPendingFollowee($user->id);

            return $isUpcomingOrOngoing && $isSubscribedFollowhost && $notReposted && $distinctUser && $userNotBlocked && $hasActiveRewardProgram && !$hasPendingInvite;
        });
    }
}
