<?php

namespace App;

use Str;
use Storage;
use Illuminate\Http\UploadedFile;
use Jenssegers\Mongodb\Eloquent\Model;

class RewardProgram extends Model
{
    /**
     * The guarded attributes on the model.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'redeem_code',
    ];

    public static $withAll = [
        'author',
        'followout',
        'jobs',
        'jobs.followout',
        'jobs.user',
        'jobs.parent_followout',
        'jobs.reward_program',
        'picture',
    ];

    public function followout()
    {
        return $this->belongsTo('App\Followout', 'followout_id');
    }

    public function author()
    {
        return $this->belongsTo('App\User', 'author_id');
    }

    public function jobs()
    {
        return $this->hasMany('App\RewardProgramJob', 'reward_program_id');
    }

    public function picture()
    {
        return $this->hasOne('App\File', 'reward_program_id')->where('type', 'reward_program');
    }

    public function hasPicture()
    {
        return $this->picture !== null;
    }

    public function defaultPictureURL()
    {
        return url('/img/coupon-pic-default.png');
    }

    public function pictureURL()
    {
        if (!$this->hasPicture()) {
            return $this->defaultPictureURL();
        }

        $picture = $this->picture;

        return Storage::url($picture->path);
    }

    public function savePicture(UploadedFile $file)
    {
        if ($this->hasPicture()) {
            $this->deletePicture();
        }

        $randomString = Str::random(64);
        $extension = $file->guessExtension();

        $picture = File::makeRewardProgramPicture($file);

        $path = 'reward_programs/' . $this->id . '/' . $randomString . '.' . $extension;

        Storage::put($path, (string) $picture, 'public');

        $picture = new File([ 'type' => 'reward_program', 'path' => $path ]);

        $this->picture()->save($picture);

        return true;
    }

    public function deletePicture()
    {
        if (!$this->hasPicture()) {
            return true;
        }

        $picture = $this->picture;

        Storage::delete($picture->path);

        $picture->delete();

        return true;
    }

    /**
     * @return bool
     */
    public function canBeUpdated()
    {
        return $this->followout->isUpcomingOrOngoing() && !$this->hasClaimedJobs();
    }

    public function isActive()
    {
        return $this->enabled === true && $this->followout->isUpcomingOrOngoing();
    }

    public function isPaused()
    {
        return $this->enabled === false;
    }

    public function scopeActive($query)
    {
        return $query->enabled()->whereHas('followout', function ($query) {
            $query->ongoingOrUpcoming();
        });
    }

    public function scopeEnabled($query)
    {
        return $query->where('enabled', true);
    }

    public function scopeHasClaimedJobs($query)
    {
        return $query->whereHas('jobs', function ($query) {
            $query->notPending();
        });
    }

    public function scopeHasRedeemedJobs($query)
    {
        return $query->whereHas('jobs', function ($query) {
            $query->redeemed();
        });
    }

    public function getTotalCheckinsCount()
    {
        $result = [];

        foreach ($this->jobs as $job) {
            if ($job->followout) {
                $result = array_merge($result, $job->followout->getAllAttendeesIds());
            }
        }

        return count(array_unique($result));
    }

    public function getJobByUser($userId)
    {
        return $this->jobs()->where('user_id', $userId)->first();
    }

    public function hasJobByUser($userId)
    {
        return $this->jobs()->where('user_id', $userId)->exists();
    }

    public function pendingByUser($userId)
    {
        return $this->jobs()->where('user_id', $userId)->pending()->exists();
    }

    public function claimedByUser($userId)
    {
        // If requested by Followee or accepted the invite by Followhost
        return $this->jobs()->where('user_id', $userId)->notPending()->exists();
    }

    public function redeemedByUser($userId)
    {
        return $this->jobs()->where('user_id', $userId)->redeemed()->exists();
    }

    public function hasJobs($query)
    {
        return $this->jobs()->count() > 0;
    }

    public function hasClaimedJobs()
    {
        return $this->jobs()->notPending()->count() > 0;
    }

    public function canBeRedeemedByUser($userId)
    {
        if (!$this->isActive()) return false;

        $job = $this->jobs()->where('user_id', $userId)->first();

        if (is_null($job)) return false;

        if ($job->isRedeemed() || !$job->isClaimed()) return false;

        // If job is in dispute or reward received that means that it's already redeemable
        if ($job->rewardIsReceived() || $job->inDispute()) return true;

        return $this->claimedByUser($userId) && $this->redeem_count <= $job->getAvailableCheckinsCount();
    }

    public function setRedeemCodeAttribute($value)
    {
        $code = trim($value);
        $code = str_replace(' ', '-', $value);

        $this->attributes['redeem_code'] = $code;
    }
}
