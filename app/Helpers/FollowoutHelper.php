<?php

namespace App\Helpers;

use App\Coupon;
use App\Followee;
use App\Followout;
use App\FollowoutCategory;
use App\File;
use App\RewardProgramJob;
use App\User;
use Carbon;
use Gate;
use Storage;
use Str;

class FollowoutHelper
{
    public static function getPreviewDataFromCoupon(Coupon $coupon)
    {
        $user = $coupon->author;

        if (is_null($user)) {
            return null;
        }

        $followout = new Followout;
        $followout->hash = hash('sha256', $user->id.time());
        $followout->title = $coupon->title;
        $followout->description = $coupon->description;
        $followout->tickets_url = null;
        $followout->external_info_url = null;
        $followout->starts_at = now();
        $followout->ends_at = $coupon->expires_at;
        $followout->is_virtual = false;
        $followout->city = $user->city;
        $followout->state = $user->state;
        $followout->address = $user->city;
        $followout->zip_code = $user->zip_code;
        $followout->lat = doubleval($user->lat);
        $followout->lng = doubleval($user->lng);
        $followout->location = FollowoutHelper::makeLocation($followout->lat, $followout->lng);
        $followout->radius = null;
        $followout->is_default = false;
        $followout->privacy_type = Gate::forUser($user)->allows('set-followout-privacy-type-public') && !$user->hasOngoingOrUpcomingPublicGeoFollowout() ? 'public' : 'followers';

        // Custom attributes for preview
        $followout->author()->associate($user);
        $followout->coupon()->associate($coupon);
        $followout->flyer_url = $coupon->hasPicture() ? $coupon->pictureURL() : null;

        return $followout;
    }

    public static function createFollowoutFromCoupon(Coupon $coupon)
    {
        $user = $coupon->author;

        if (is_null($user)) {
            return null;
        }

        if (!$user->isFollowhost()) {
            return null;
        }

        $followout = new Followout;
        $followout->hash = hash('sha256', $user->id . time());
        $followout->title = $coupon->title;
        $followout->description = $coupon->description;
        $followout->tickets_url = null;
        $followout->external_info_url = null;
        $followout->starts_at = now();
        $followout->ends_at = $coupon->expires_at;
        $followout->is_virtual = false;
        $followout->city = $user->city;
        $followout->state = $user->state;
        $followout->address = $user->city;
        $followout->zip_code = $user->zip_code;
        $followout->lat = doubleval($user->lat);
        $followout->lng = doubleval($user->lng);
        $followout->location = FollowoutHelper::makeLocation($followout->lat, $followout->lng);
        $followout->radius = null;
        $followout->is_default = false;
        $followout->privacy_type = Gate::forUser($user)->allows('set-followout-privacy-type-public') && !$user->hasOngoingOrUpcomingPublicGeoFollowout() ? 'public' : 'followers';

        $followout = $user->followouts()->save($followout);
        $followout->experience_categories()->attach($user->account_categories->pluck('id')->toArray());
        $followout->author()->associate($user);
        $followout->coupon()->associate($coupon);
        $followout->save();

        $coupon->followout_coupons()->create([
            'followout_id' => $followout->id,
            'is_active' => true,
        ]);

        if ($coupon->hasPicture()) {
            $randomString = Str::random(64);

            $extension = \Illuminate\Support\Facades\File::extension($coupon->pictureURL());

            $path = 'followouts/' . $followout->id . '/' . $randomString . '.' . $extension;

            Storage::copy($coupon->picture->path, $path);

            $picture = new File(['type' => 'followout_flyer', 'path' => $path]);

            $followout->flyer()->save($picture);
        }

        self::autosubscribeUsersToFollowhost($followout->author);

        return $followout;
    }

    public static function autosubscribeUsersToFollowhost(User $followhost)
    {
        if (!($followhost->isFollowhost() && $followhost->subscribed())) {
            return;
        }

        $users = User::where('_id', '!=', $followhost->id)
                    ->where('autosubcribe_to_followhosts', true)
                    ->notFollowhosts()
                    ->whereDoesntHave('follows', function ($query) use ($followhost) {
                        $query->where('to_id', $followhost->id);
                    })
                    ->orderBy('_id')
                    ->take(1000)
                    ->get();

        $users = $users->count() >= 5 ? $users->random(5) : $users;

        foreach ($users as $user) {
            $user->follow($followhost->id, true);
        }

        if ($users->count() > 0) {
            $followhost->notify(new \App\Notifications\NewAutosubscribers($users->count()));
        }
    }

    public static function updateOrCreateDefaultFollowout($userId)
    {
        $user = User::find($userId);

        if (is_null($user)) {
            return false;
        }

        if ($user->hasDefaultFollowout()) {
            return self::updateDefaultFollowout($userId);
        }

        return self::createDefaultFollowout($userId);
    }

    public static function getEmptyFollowoutTemplateData($userId, $additionalData = [], $singleDateField = false)
    {
        $user = User::findOrFail($userId);

        $socialCategory = FollowoutCategory::where('name', 'Social')->firstOrFail();

        $data = [
            'privacy_type' => 'followers',
            'description' => '#followout',
            'experience_categories' => [$socialCategory->getKey()],
        ];

        $starts = now()->tz(session_tz());
        $ends = now()->tz(session_tz())->addDay();

        if ($singleDateField) {
            $data['starts_at'] = $starts->format(config('followouts.time_format')) . ' ' . $starts->format(config('followouts.date_format'));
            $data['ends_at'] = $ends->format(config('followouts.time_format')) . ' ' . $ends->format(config('followouts.date_format'));
        } else {
            $data['starts_at_time'] = $starts->format(config('followouts.time_format'));
            $data['starts_at_date'] = $starts->format(config('followouts.date_format'));
            $data['ends_at_time'] = $ends->format(config('followouts.time_format'));
            $data['ends_at_date'] = $ends->format(config('followouts.date_format'));
        }

        if ($user->address && $user->city && $user->zip_code && $user->lat && $user->lng) {
            $data['city'] = $user->city;
            $data['state'] = $user->state;
            $data['address'] = $user->address;
            $data['zip_code'] = $user->zip_code;
            $data['lat'] = doubleval($user->lat);
            $data['lng'] = doubleval($user->lng);
        } else {
            $data['city'] = 'İskilip';
            $data['state'] = 'Çorum';
            $data['address'] = 'Beyoğlan';
            $data['zip_code'] = '19400';
            $data['lat'] = 40.866667;
            $data['lng'] = 34.566667;
        }

        // We may want to overwrite some data via $additionalData param
        foreach ($additionalData as $key => $value) {
            $data[$key] = $value;
        }

        $data['location'] = FollowoutHelper::makeLocation($data['lat'], $data['lng']);
        $data['title'] = 'My ' . $data['city'] . (!empty($data['state']) ? ', ' . $data['state'] : '') . ' Followout';

        return $data;
    }

    public static function createDefaultFollowout($userId)
    {
        $user = User::find($userId);

        if (is_null($user)) {
            return false;
        }

        $followout = $user->followouts()->where('is_default', true)->first();

        if ($followout) {
            return $followout;
        }

        $followout = new Followout;
        $followout->hash = hash('sha256', $user->id.time());
        $followout->title = $user->name;
        $followout->description = '#followout';
        $followout->tickets_url = null;
        $followout->external_info_url = null;
        $startsAt = now()->format(config('followouts.datetime_format'));
        $endsAt = now()->addYears(100)->format(config('followouts.datetime_format'));
        $followout->starts_at = Carbon::createFromFormat(config('followouts.datetime_format'), $startsAt)->tz('UTC')->setTime(0, 0, 0);
        $followout->ends_at = Carbon::createFromFormat(config('followouts.datetime_format'), $endsAt)->tz('UTC')->setTime(23, 59, 59);
        $followout->is_virtual = false;
        $followout->city = $user->city;
        $followout->state = $user->state;
        $followout->address = $user->city;
        $followout->zip_code = $user->zip_code;
        $followout->lat = doubleval($user->lat);
        $followout->lng = doubleval($user->lng);
        $followout->location = FollowoutHelper::makeLocation($followout->lat, $followout->lng);
        $followout->radius = null;
        $followout->is_default = true;

        if (Gate::forUser($user)->denies('set-followout-privacy-type-public')) {
            $followout->privacy_type = 'followers';
        } else {
            $followout->privacy_type = 'public';
        }

        $followout = $user->followouts()->save($followout);
        $followout->experience_categories()->attach($user->account_categories->pluck('id')->toArray());
        $followout->author()->associate($user);
        $followout->save();

        if (!$user->isFollowhost()) {
            $followee = new Followee(['status' => 'accepted']);
            $followee = $followout->followees()->save($followee);
            $followee->user()->associate(auth()->user());
            $followee->save();
        }

        $user->saveDefaultFlyerFromUsername();

        return $followout;
    }

    public static function updateDefaultFollowout($userId)
    {
        $user = User::find($userId);

        if (is_null($user)) {
            return false;
        }

        $followout = $user->followouts()->where('is_default', true)->first();

        if (is_null($followout)) {
            return false;
        }

        // Restart followout every 30 days
        if ($followout->starts_at->lt(now()->subDays(30))) {
            $startsAt = now()->format(config('followouts.datetime_format'));
            $followout->starts_at = Carbon::createFromFormat(config('followouts.datetime_format'), $startsAt)->tz('UTC')->setTime(0, 0, 0);
        }

        $followout->title = $user->name;
        $followout->city = $user->city;
        $followout->state = $user->state;
        $followout->address = $user->city;
        $followout->zip_code = $user->zip_code;
        $followout->lat = doubleval($user->lat);
        $followout->lng = doubleval($user->lng);
        $followout->location = FollowoutHelper::makeLocation($followout->lat, $followout->lng);

        $followout->experience_categories()->detach();
        $followout->experience_categories()->attach($user->account_categories->pluck('id')->toArray());

        $followout->save();

        $user->saveDefaultFlyerFromUsername();

        return true;
    }

    public static function showDefaultFollowout($userId)
    {
        $user = User::find($userId);

        if (is_null($user)) {
            return false;
        }

        $followout = $user->followouts()->where('is_default', true)->first();

        if (is_null($followout)) {
            return false;
        }

        if (Gate::forUser($user)->allows('set-followout-privacy-type-public')) {
            $followout->setPrivacyPublic();
        } else {
            $followout->setPrivacyFollowers();
        }

        return true;
    }

    public static function hideDefaultFollowout($userId)
    {
        $user = User::find($userId);

        if (is_null($user)) {
            return false;
        }

        $followout = $user->followouts()->where('is_default', true)->first();

        if (is_null($followout)) {
            return false;
        }

        $followout->setPrivacyHidden();

        return true;
    }

    public static function makeOngoingOrUpcomingPublicFollowoutsFollowersOnly($userId)
    {
        $user = User::find($userId);

        if (is_null($user)) {
            return false;
        }

        $followouts = $user->followouts()->notGeoCoupon()->notReposted()->ongoingOrUpcoming()->public()->get();

        foreach ($followouts as $followout) {
            $followout->setPrivacyFollowers();
        }

        return true;
    }

    public static function makeOngoingOrUpcomingPublicGeoFollowoutsFollowersOnly($userId)
    {
        $user = User::find($userId);

        if (is_null($user)) {
            return false;
        }

        $followouts = $user->followouts()->geoCoupon()->notReposted()->ongoingOrUpcoming()->public()->get();

        foreach ($followouts as $followout) {
            $followout->setPrivacyFollowers();
        }

        return true;
    }

    public static function makeDefaultFollowoutPublicIfPossible($userId)
    {
        $user = User::find($userId);

        if (is_null($user)) {
            return false;
        }

        $followout = $user->followouts()->where('is_default', true)->first();

        if (is_null($followout)) {
            return false;
        }

        if (Gate::forUser($user)->allows('set-followout-privacy-type-public') && !$user->hasOngoingOrUpcomingPublicFollowout() && $followout->isFollowersOnly()) {
            $followout->setPrivacyPublic();

            return true;
        }

        return false;
    }

    public static function syncAttributesForRepostedFollowouts(Followout $followout)
    {
        $followout = $followout->getTopParentFollowout();

        foreach ($followout->reposted_followouts as $repostedFollowout) {
            // Attributes that can be copied directly
            $attributes = [
                'title', 'description', 'is_virtual', 'virtual_address', 'city', 'state', 'address', 'zip_code', 'lat', 'lng', 'radius', 'location', 'tickets_url', 'external_info_url', 'starts_at', 'ends_at',
            ];

            foreach ($attributes as $attribute) {
                $repostedFollowout->{$attribute} = $followout->{$attribute};
            }

            // Attributes that need to be copied in a special way
            $repostedFollowout->experience_categories()->detach();
            $repostedFollowout->experience_categories()->attach($followout->experience_categories->pluck('_id')->all());

            $repostedFollowout->privacy_type = $followout->isPublic() ? 'followers' : $followout->privacy_type;

            $repostedFollowout->save();
        }

        return true;
    }

    public static function getRandomFollowoutWithFlyer($user = null, $category = null)
    {
        if ($category) {
            $randomFollowouts = Followout::ongoingOrUpcoming()->whereIn('followout_category_ids', [$category])->with('flyer')->orderBy('starts_at')->take(500)->get();
        } else {
            $randomFollowouts = Followout::ongoingOrUpcoming()->orderBy('starts_at')->with('flyer')->take(500)->get();
        }

        // Make sure followouts have a flyer
        $randomFollowouts = $randomFollowouts->filter(function ($followout, $key) {
            return $followout->hasFlyer() || $followout->isDefault();
        });

        $randomFollowouts = self::filterFollowoutsForUser($randomFollowouts, $user);

        $randomFollowouts = $randomFollowouts->shuffle();

        $found = false;
        $randomFollowout = null;

        do {
            if ($randomFollowout !== null || $randomFollowouts->isEmpty()) {
                $found = true;
            } else {
                $randomFollowout = $randomFollowouts->shift();
            }
        } while (!$found);

        return $randomFollowout;
    }

    public static function filterRewardProgramsForUser($rewardPrograms, $user = null)
    {
        if (is_null($rewardPrograms)) {
            return collect();
        }

        $rewardPrograms = $rewardPrograms->filter(function ($rewardProgram, $key) use ($user) {
            return $rewardProgram->followout->userHasAccess($user);
        });

        return $rewardPrograms;
    }

    public static function filterFollowoutsForUser($followouts, $user = null)
    {
        if (is_null($followouts)) {
            return collect();
        }

        $followouts = $followouts->filter(function ($followout, $key) use ($user) {
            return $followout->userHasAccess($user);
        });

        return $followouts;
    }

    /**
     * Used for checking if reward program job has been completed.
     *
     * @return void
     */
    public static function handleNewCheckin(Followout $followout)
    {
        if ($followout->isUsedInRewardProgramJob()) {
            // This will automatically get the correct followee because we're using reposted $followout ID
            $rewardProgramJobs = RewardProgramJob::where('followout_id', $followout->id)->notRedeemed()->get();

            foreach ($rewardProgramJobs as $rewardProgramJob) {
                $rewardProgram = $rewardProgramJob->reward_program;
                $followee = $rewardProgramJob->user;

                if ($rewardProgramJob->canBeRedeemed() && !$rewardProgramJob->rewardIsReceived() && !$rewardProgramJob->inDispute()) {
                    $rewardProgramJob->markRewardAsReceived();

                    if (!$rewardProgramJob->followhost_redeem_notification_sent) {
                        $rewardProgramJob->followhost_redeem_notification_sent = true;
                        $rewardProgramJob->save();

                        $rewardProgram->author->notify(new \App\Notifications\RewardProgramJobBecameRedeemable($rewardProgramJob, 'followhost'));
                    }

                    if (!$rewardProgramJob->followee_redeem_notification_sent) {
                        $rewardProgramJob->followee_redeem_notification_sent = true;
                        $rewardProgramJob->save();

                        $rewardProgramJob->user->notify(new \App\Notifications\RewardProgramJobBecameRedeemable($rewardProgramJob, 'followee'));
                    }
                }
            }
        }
    }

    public static function makeLocation($lat, $lng) {
        return [
            doubleval($lng),
            doubleval($lat),
        ];
    }
}
