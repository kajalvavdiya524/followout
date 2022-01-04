<?php

namespace App;

use App\Jobs\ProcessFollowoutFlyerVideo;
use Debugbar;
use Gate;
use Jenssegers\Mongodb\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Mail;
use Storage;
use Str;

/**
 * @property string         $_id
 * @property string         $author_id
 * @property string         $title
 * @property string         $description
 * @property string         $password
 * @property \Carbon\Carbon $starts_at
 * @property \Carbon\Carbon $ends_at
 * @property string         $address
 * @property string         $city
 * @property string         $state
 * @property string         $zip_code
 * @property double         $lat
 * @property double         $lng
 * @property string         $virtual_address
 * @property bool           $is_virtual
 * @property bool           $is_default
 * @property string         $privacy_type
 * @property string         $tickets_url
 * @property string         $external_info_url
 * @property string         $coupon_id
 * @property string         $based_on_followhost_id
 * @property string         $top_parent_followout_id
 * @property string         $parent_followout_id
 * @property int            $views_count
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Followout extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'description',
        'views_count',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'starts_at',
        'ends_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'lat' => 'double',
        'lng' => 'double',
    ];

    public static $withAll = [
        'author',
        'author.country',
        'author.avatars',
        'author.account_categories',
        'followees',
        'followees.user',
        'followees.user.avatars',
        'followees.user.account_categories',
        'followees.reward_program',
        'accepted_followees',
        'accepted_followees.user',
        'accepted_followees.user.avatars',
        'accepted_followees.user.account_categories',
        'accepted_followees.reward_program',
        'pending_followees',
        'pending_followees.user',
        'pending_followees.user.avatars',
        'pending_followees.user.account_categories',
        'pending_followees.reward_program',
        'coupon',
        'checkins',
        'checkins.user',
        'checkins.user.avatars',
        'checkins.user.account_categories',
        'favorited',
        'flyer',
        'flyer.video',
        'pictures',
        'experience_categories',
        'parent_followout',
        'parent_followout.author',
        'parent_followout.author.avatars',
        'top_parent_followout',
        'top_parent_followout.author',
        'top_parent_followout.author.avatars',
    ];

    // Relations
    public function author()
    {
        return $this->belongsTo('App\User', 'author_id');
    }

    public function based_on_followhost()
    {
        return $this->belongsTo('App\User', 'based_on_followhost_id');
    }

    public function top_parent_followout()
    {
        return $this->belongsTo('App\Followout', 'top_parent_followout_id');
    }

    public function parent_followout()
    {
        return $this->belongsTo('App\Followout', 'parent_followout_id');
    }

    public function child_followouts()
    {
        return $this->hasMany('App\Followout', 'parent_followout_id');
    }

    public function reposted_followouts()
    {
        return $this->hasMany('App\Followout', 'top_parent_followout_id');
    }

    public function favorited()
    {
        return $this->morphMany('App\Favorite', 'favoriteable');
    }

    public function followees()
    {
        return $this->hasMany('App\Followee');
    }

    public function accepted_followees()
    {
        return $this->hasMany('App\Followee')->where('status', 'accepted');
    }

    public function pending_followees()
    {
        return $this->hasMany('App\Followee')->where('status', 'pending');
    }

    public function checkins()
    {
        return $this->hasMany('App\Checkin');
    }

    public function experience_categories()
    {
        return $this->belongsToMany('App\FollowoutCategory');
    }

    public function flyer()
    {
        return $this->hasOne('App\File')->where('type', 'followout_flyer');
    }

    public function pictures()
    {
        return $this->hasMany('App\File', 'followout_id')->where('type', 'followout_picture')->orderBy('created_at');
    }

    public function reward_programs()
    {
        return $this->hasMany('App\RewardProgram');
    }

    public function reward_program_jobs()
    {
        return $this->hasMany('App\RewardProgramJob', 'parent_followout_id');
    }

    public function coupon()
    {
        return $this->belongsTo('App\Coupon', 'coupon_id');
    }

    public function coupons()
    {
        return $this->hasMany('App\FollowoutCoupon', 'followout_id');
    }

    public function used_coupons()
    {
        return $this->hasMany('App\UsedCoupon', 'followout_id');
    }

    public function picture($number = 0)
    {
        if (!$this->hasPicture($number)) {
            return null;
        }

        $pictures = $this->pictures()->orderBy('created_at')->get();

        return $pictures->get($number);
    }

    public function hasFlyer()
    {
        return !is_null($this->flyer);
    }

    public function hasVideoFlyer()
    {
        if (!$this->hasFlyer()) {
            return false;
        }

        return !is_null($this->flyer->video);
    }

    public function hasPicture($number = 0)
    {
        $pictures = $this->pictures()->orderBy('created_at')->get();

        return !is_null($pictures->get($number));
    }

    public function defaultFlyerURL($preferVideo = false)
    {
        if ($this->isReposted()) {
            return $this->getTopParentFollowout()->author->defaultFlyerURL($preferVideo);
        }

        return $this->author->defaultFlyerURL($preferVideo);
    }

    public function defaultFlyerIsVideo()
    {
        if ($this->isReposted()) {
            return $this->getTopParentFollowout()->author->hasDefaultVideoFlyer();
        }

        return $this->author->hasDefaultVideoFlyer();
    }

    // This method is used for flyers with unprocessed videos
    public function defaultVideoFlyerURL()
    {
        return url('/img/flyer-video-pic-default.png');
    }

    public function defaultPictureURL()
    {
        return url('/img/pic-default.png');
    }

    /**
     * Alias functiion to be consistent with other models.
     *
     * @return string
     */
    public function avatarURL()
    {
        return $this->flyerURL();
    }

    /**
     * Alias functiion to be consistent with other models.
     *
     * @return string
     */
    public function defaultAvatarURL()
    {
        return $this->defaultFlyerURL();
    }

    public function url($withHash = false)
    {
        if ($withHash) {
            return route('followouts.show', ['followout' => $this->id, 'hash' => $this->hash]);
        }

        return route('followouts.show', ['followout' => $this->id]);
    }

    public function flyerURL()
    {
        if (!$this->hasFlyer()) {
            return $this->defaultFlyerURL();
        }

        if ($this->hasVideoFlyer()) {
            if ($this->flyer->video->isProcessed()) {
                return $this->flyer->video->thumbURL();
            }

            return $this->defaultVideoFlyerURL();
        }

        return Storage::url($this->flyer->path);
    }

    public function videoFlyerURL()
    {
        if (!$this->hasFlyer()) {
            return $this->defaultFlyerURL(true);
        }

        if ($this->hasVideoFlyer()) {
            if ($this->flyer->video->isProcessed()) {
                return $this->flyer->video->url();
            }

            return $this->defaultVideoFlyerURL();
        }

        $picture = $this->flyer()->first();

        return Storage::url($picture->path);
    }

    public function pictureURL($number = 0)
    {
        if (!$this->hasPicture()) {
            return $this->defaultPictureURL();
        }

        $pictures = $this->pictures()->orderBy('created_at')->get();

        $picture = $pictures->get($number);

        if (is_null($picture)) {
            return $this->defaultPictureURL();
        }

        return Storage::url($picture->path);
    }

    public function saveFlyer(UploadedFile $file)
    {
        if ($this->hasFlyer()) {
            $this->deleteFlyer();
        }

        $randomString = Str::random(64);

        $extension = $file->guessExtension();

        $isImage = $extension === 'gif' || $extension === 'png' || $extension === 'jpg' || $extension === 'jpeg';
        $isVideo = $extension === 'mp4' || $extension === 'mov' || $extension === 'm4v' || $extension === 'qt';

        // AWS doesn't accept some file extensions
        $extension = in_array($extension, ['qt']) ? 'mp4' : $extension;

        if ($isImage) {
            if ($this->isGeoCoupon()) {
                $picture = File::makeCouponFlyer($file);
            } else {
                $picture = File::makeFlyer($file);
            }

            $path = 'followouts/' . $this->id . '/' . $randomString.'.'.$extension;

            Storage::put($path, (string) $picture, 'public');

            $picture = new File([ 'type' => 'followout_flyer', 'path' => $path ]);

            $this->flyer()->save($picture);
        } else if ($isVideo) {
            $authUser = auth()->user() ? auth()->user() : auth()->guard('api')->user();
            $uploader = $authUser ?? $this->author;

            $basename = Str::random(64);
            $filename = $basename . '.' . $extension;

            $path_raw = 'videos/raw/' . $filename;
            $path_mp4 = 'followouts/' . $this->id . '/' . $basename.'.mp4';
            $path_m3u8 = 'followouts/' . $this->id . '/' . $basename.'.m3u8';
            $path_thumb = 'followouts/' . $this->id . '/' . $basename.'.0000000.jpg';

            Storage::put($path_raw, file_get_contents($file->getRealPath()), 'public');

            $video = new Video;
            $video->processed_at = null;
            $video->basename = $basename;
            $video->filename = $filename;
            $video->path_raw = $path_raw;
            $video->path_thumb = $path_thumb;
            $video->path_mp4 = $path_mp4;
            $video->path_m3u8 = $path_m3u8;
            $video->path_thumb = $path_thumb;
            $video->uploader()->associate($uploader);
            $video->save();

            $picture = new File([ 'type' => 'followout_flyer' ]);
            $picture->video()->associate($video);
            $this->flyer()->save($picture);

            $video->file()->associate($picture);
            $video->save();

            ProcessFollowoutFlyerVideo::dispatch($video->id, 'followouts/'.$this->id);
        } else {
            throw new \Exception($extension.' format is not supported yet.', 422);
        }

        return true;
    }

    public function saveLocationFlyer()
    {
        if ($this->hasFlyer()) {
            return false;
        }

        $randomString = Str::random(64);
        $extension = 'jpg';

        $address = $this->city . ($this->state ? ', ' . $this->state : '');

        $picture = File::makeFlyerFromText($address);

        $path = 'followouts/' . $this->id . '/' . $randomString . '.' . $extension;

        Storage::put($path, (string) $picture, 'public');

        $picture = new File([ 'type' => 'followout_flyer', 'path' => $path ]);

        $this->flyer()->save($picture);

        return true;
    }

    public function cloneFlyerFromFollowout(Followout $followout)
    {
        if (!$followout->hasFlyer()) {
            return false;
        }

        if ($this->hasFlyer()) {
            $this->deleteFlyer();
        }

        $authUser = auth()->user() ? auth()->user() : auth()->guard('api')->user();
        $uploader = $authUser ?? $this->author;

        if ($followout->hasVideoFlyer()) {
            if ($followout->flyer->video->isProcessed()) {
                $extension = 'mp4';
                $basename = Str::random(64);
                $filename = $basename . '.' . $extension;

                $path_raw = 'videos/raw/' . $filename;
                $path_mp4 = 'followouts/' . $this->id . '/' . $basename . '.mp4';
                $path_m3u8 = 'followouts/' . $this->id . '/' . $basename . '.m3u8';
                $path_m3u8_hls = 'followouts/' . $this->id . '/' . $basename . '-hls.m3u8';
                $path_m3u8_hls_ts = 'followouts/' . $this->id . '/' . $basename . '-hls.ts';
                $path_thumb = 'followouts/' . $this->id . '/' . $basename . '.0000000.jpg';

                // Copy processed video
                Storage::copy($followout->flyer->video->path_thumb, $path_thumb);
                Storage::copy($followout->flyer->video->path_mp4, $path_mp4);
                Storage::copy($followout->flyer->video->path_m3u8, $path_m3u8);
                Storage::copy($followout->flyer->video->path_m3u8_hls, $path_m3u8_hls);
                Storage::copy($followout->flyer->video->path_m3u8_hls_ts, $path_m3u8_hls_ts);

                $video = new Video;
                $video->processed_at = now();
                $video->basename = $basename;
                $video->filename = $filename;
                $video->path_raw = null;
                $video->path_mp4 = $path_mp4;
                $video->path_m3u8 = $path_m3u8;
                $video->path_thumb = $path_thumb;
                $video->uploader()->associate($uploader);
                $video->save();

                $picture = new File([ 'type' => 'followout_flyer' ]);
                $picture->video()->associate($video);
                $this->flyer()->save($picture);

                $video->file()->associate($picture);
                $video->save();
            } else {
                $extension = str_replace($followout->flyer->video->basename . '.', '', $followout->flyer->video->filename);
                $basename = Str::random(64);
                $filename = $basename . '.' . $extension;

                $path_raw = 'videos/raw/' . $filename;
                $path_mp4 = 'followouts/' . $this->id . '/' . $basename . '.mp4';
                $path_m3u8 = 'followouts/' . $this->id . '/' . $basename . '.m3u8';
                $path_thumb = 'followouts/' . $this->id . '/' . $basename . '.0000000.jpg';

                // Copy raw video and process it
                Storage::copy($followout->flyer->video->path_raw, $path_raw);

                $video = new Video;
                $video->processed_at = null;
                $video->basename = $basename;
                $video->filename = $filename;
                $video->path_raw = $path_raw;
                $video->path_mp4 = $path_mp4;
                $video->path_m3u8 = $path_m3u8;
                $video->path_thumb = $path_thumb;
                $video->uploader()->associate($uploader);
                $video->save();

                $picture = new File(['type' => 'followout_flyer']);
                $picture->video()->associate($video);
                $this->flyer()->save($picture);

                $video->file()->associate($picture);
                $video->save();

                ProcessFollowoutFlyerVideo::dispatch($video->id, 'followouts/' . $this->id);
            }
        } else {
            $randomString = Str::random(64);

            $extension = \Illuminate\Support\Facades\File::extension($followout->flyerURL());

            $path = 'followouts/' . $this->id . '/' . $randomString . '.' . $extension;

            Storage::put($path, file_get_contents($followout->flyerURL()), 'public');

            $picture = new File(['type' => 'followout_flyer', 'path' => $path]);

            $this->flyer()->save($picture);
        }

        return true;
    }

    public function savePicture(UploadedFile $file, $number = null)
    {
        if ($number && $this->hasPicture($number)) {
            $this->deletePictureByNumber($number);
        } else if ($this->hasPicture(2)) {
            $this->deletePictureByNumber(2);
        }

        $randomString = Str::random(64);
        $extension = $file->guessExtension();

        $picture = File::makeFollowoutPicture($file);

        $path = 'followouts/' . $this->id . '/' . $randomString . '.' . $extension;

        Storage::put($path, (string) $picture, 'public');

        $picture = new File([ 'type' => 'followout_picture', 'path' => $path ]);

        $this->pictures()->save($picture);

        return true;
    }

    public function deleteFlyer()
    {
        if (!$this->hasFlyer()) {
            return true;
        }

        $flyer = $this->flyer;

        if ($this->hasVideoFlyer()) {
            $video = $flyer->video;

            if ($video) {
                $video->deleteVideo(true);
            } else {
                $flyer->delete();
            }
        } else {
            Storage::delete($flyer->path);
            $flyer->delete();
        }

        return true;
    }

    public function deletePictureByNumber($number)
    {
        $picture = $this->picture($number);

        if ($picture) {
            Storage::delete($picture->path);
            $picture->delete();
        }

        return true;
    }

    public function deletePicturesById(array $pictures)
    {
        foreach ($pictures as $id) {
            $picture = $this->pictures()->where('_id', $id)->first();

            if (!is_null($picture)) {
                Storage::delete($picture->path);
                $picture->delete();
            }
        }

        return true;
    }

    public function deletePictures()
    {
        $pictures = $this->pictures;

        foreach ($pictures as $picture) {
            Storage::delete($picture->path);
            $picture->delete();
        }

        return true;
    }

    public function userHasAccess($user = null, $hash = null)
    {
        if ($this->isHidden()) {
            return false;
        }

        // If $user is not a model but a user ID, get the model
        if ($user && !$user instanceof User) {
            $user = User::find($user);

            if ($user && $this->getTopParentFollowout()->author->blocked($user->id)) {
                return false;
            }
        }

        if ($this->isPublic()) {
            return true;
        }

        if ($hash === $this->hash) {
            return true;
        }

        // At this point Followout is not public and user must be authorized
        if (is_null($user)) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        if ($this->author->id === $user->id) {
            return true;
        }

        if ($this->hasFollowee($user->id, false)) {
            return true;
        }

        if ($this->isFollowersOnly() && $user->following($this->author->id)) {
            return true;
        }

        return false;
    }

    public function scopeDefault($query, $showDefault = true)
    {
        if ($showDefault === false) {
            return $query->whereIn('is_default', [false, null]);
        }

        return $query->where('is_default', true);
    }

    public function scopePublic($query)
    {
        return $query->where('privacy_type', 'public');
    }

    public function scopePrivate($query)
    {
        return $query->where('privacy_type', 'private');
    }

    public function scopeFollowersOnly($query)
    {
        return $query->where('privacy_type', 'followers');
    }

    public function scopeHidden($query)
    {
        return $query->where('privacy_type', 'hidden');
    }

    public function scopeOngoing($query)
    {
        return $query->where('starts_at', '<', now())->where('ends_at', '>', now());
    }

    public function scopeUpcoming($query)
    {
        return $query->where('starts_at', '>', now());
    }

    public function scopeOngoingOrUpcoming($query)
    {
        return $query->where([
            ['starts_at', '<', now()],
            ['ends_at', '>', now()],
        ])->orWhere('starts_at', '>', now());
    }

    public function scopeOngoingOrUpcomingOrEndedRecently($query, $days = 90)
    {
        return $query->where([
                ['starts_at', '<', now()],
                ['ends_at', '>', now()],
            ])->orWhere('starts_at', '>', now())
              ->orWhere([
                  ['starts_at', '>', now()->subDays($days)],
                  ['ends_at', '<', now()],
            ]);
    }

    public function scopeReposted($query)
    {
        return $query->whereHas('parent_followout');
    }

    public function scopeNotReposted($query)
    {
        return $query->doesntHave('parent_followout');
    }

    public function scopeGeoCoupon($query)
    {
        return $query->has('coupon');
    }

    public function scopeNotGeoCoupon($query)
    {
        return $query->doesntHave('coupon');
    }

    public function scopeUpcomingNextThreeMonths($query)
    {
        return $query->where('starts_at', '>', now())->where('starts_at', '<', now()->addMonths(3));
    }

    public function inviteFollowee($userId, $rewardProgramId = null)
    {
        $user = User::find($userId);
        $rewardProgram = RewardProgram::find($rewardProgramId);

        if (is_null($user)) {
            Debugbar::info('Cannot invite followee: user not found.');
            return false;
        }

        if ($this->author->isFollowhost() && is_null($rewardProgram)) {
            Debugbar::info('Cannot invite followee: reward program not found.');
            return false;
        }

        // This followout must be the same as in reward program
        if ($this->author->isFollowhost() && $rewardProgram->followout_id !== $this->id) {
            Debugbar::info('Cannot invite followee: reward program followout is different.');
            return false;
        }

        if (!$this->isUpcomingOrOngoing()) {
            Debugbar::info('Cannot invite followee: followout is not upcoming or ongoing.');
            return false;
        }

        if ($this->hasFollowee($user->id)) {
            Debugbar::info('Cannot invite followee: followout already has this user as followee.');
            return false;
        }

        if (Gate::forUser($this->author)->denies('invite-followee', $user)) {
            Debugbar::info('Cannot invite followee: gate denied \'invite-followee\' action.');
            return false;
        }

        $followee = new Followee;
        $followee->status = 'pending';
        $followee->user_id = $user->id;
        if ($this->author->isFollowhost()) {
            $followee->reward_program_id = $rewardProgram->id;
        }
        $followee = $this->followees()->save($followee);

        $user->notify(new \App\Notifications\FolloweeInvitation($this, $followee));

        return true;
    }

    public function inviteFolloweeByEmail($email, $rewardProgramId = null)
    {
        $email = mb_strtolower($email);

        $user = User::where('email', $email)->first();

        if ($user) {
            return $this->inviteFollowee($user->id, $rewardProgramId);
        }

        if (!$this->isUpcomingOrOngoing()) {
            return false;
        }

        if ($this->hasFolloweeByEmail($email)) {
            return false;
        }

        if (Gate::forUser($this->author)->denies('invite-followee-by-email')) {
            return false;
        }

        $invite = new InvitedUnregisteredUser;
        $invite->email = $email;
        $invite->type = 'followee_invitation';
        $invite->parameters = ['followout_id' => $this->id];
        $invite->save();

        Mail::to($email)->send(new \App\Mail\FolloweeInvitation($this));

        return true;
    }

    /**
     * Reward program can be optional (for Followeee inviting others to help present reposted Followout).
     */
    public function requestToPresentFollowout($userId, $rewardProgramId = null)
    {
        $user = User::find($userId);
        $rewardProgram = RewardProgram::with('followout')->find($rewardProgramId);
        $rewardProgramId = $rewardProgram->id ?? null;

        if (is_null($user)) {
            Debugbar::info('Cannot request to present: user not found.');
            return false;
        }

        if ($this->author->isFollowhost() && is_null($rewardProgram)) {
            Debugbar::info('Cannot request to present: reward program not found.');
            return false;
        }

        // This also ensures that author of followout is the same as author of reward program
        if ($this->author->isFollowhost() && $rewardProgram->followout->id !== $this->id) {
            Debugbar::info('Cannot request to present: reward program followout is different.');
            return false;
        }

        if ($rewardProgram && $rewardProgram->hasJobByUser($user->id)) {
            Debugbar::info('Cannot request to present: user has claimed the reward program job.');
            return false;
        }

        if (Gate::forUser($user)->denies('request-to-present-followout', $this)) {
            Debugbar::info('Cannot invite followee: gate denied \'request-to-present-followout\' action.');
            return false;
        }

        // If user previously declined invite to present, we'll reverse that
        if ($this->hasDeclinedFollowee($user->id)) {
            $followee = $this->followees()->where('user_id', $user->id)->first();

            if ($this->author->isFollowhost()) {
                $followee->reward_program_id = $rewardProgram->id;
            }

            $followee->status = 'accepted';
            $followee->save();

            $repostedFollowout = $user->repostFollowout($this->id, $rewardProgramId);
        }

        if (!$this->hasFollowee($user->id)) {
            $followee = new Followee;
            $followee->status = 'pending';
            $followee->requested_by_user = true;

            if ($this->author->isFollowhost()) {
                $followee->reward_program_id = $rewardProgramId;
            }

            $followee->user_id = $user->id;
            $followee = $this->followees()->save($followee);

            if ($this->author->isFollowhost()) {
                $rewardProgramJob = new RewardProgramJob;
                $rewardProgramJob->status = 'pending';
                $rewardProgramJob->user()->associate($user);
                $rewardProgramJob->parent_followout()->associate($this);
                $rewardProgramJob = $rewardProgram->jobs()->save($rewardProgramJob);
            }

            $this->author->notify(new \App\Notifications\PresentFollowoutRequest($this, $user));
        } elseif ($rewardProgram) {
            $repostedFollowout = $user->followouts()->where('top_parent_followout_id', $this->id)->first();

            // If user is already presents followout via another reward program
            // then there's no reason to ask followhost if user can present this followout
            // since followhost allowed it previously
            if ($this->hasAcceptedFollowee($user->id) && $repostedFollowout) {
                if ($rewardProgram) {
                    $rewardProgramJob = new RewardProgramJob;
                    $rewardProgramJob->status = 'claimed';
                    $rewardProgramJob->user()->associate($user);
                    $rewardProgramJob->followout()->associate($repostedFollowout);
                    $rewardProgramJob->parent_followout()->associate($this);
                    $rewardProgramJob = $rewardProgram->jobs()->save($rewardProgramJob);
                }
            } else {
                return false;
            }
        } else {
            Debugbar::info('Cannot invite followee: followout already has this user as followee.');
            return false;
        }

        return true;
    }

    // Invite someone to attend Followout, includes Followout's URL with hash
    public function inviteAttendee($to, $method = 'email')
    {
        if ($method === 'email') {
            $to = mb_strtolower(trim($to));

            Mail::to($to)->queue(new \App\Mail\AttendeeInvitation($this));
        }

        if ($method === 'user') {
            if ($to instanceof User) {
                $user = $to;
            } else {
                $user = User::find($to);
            }

            if (is_null($user)) {
                return false;
            }

            if ($user->blocked($this->author->id)) {
                return false;
            }

            if (!$user->following($this->author->id)) {
                return false;
            }

            $user->notify(new \App\Notifications\AttendeeInvitation($this));
        }

        if ($method === 'sms') {
            return false;
        }

        return true;
    }

    public function deleteFollowout()
    {
        foreach ($this->reposted_followouts as $repostedFollowout) {
            $repostedFollowout->deleteFollowout();
        }

        if ($this->isReposted()) {
            $this->parent_followout->followees()->where('user_id', $this->author->id)->delete();
        }

        RewardProgramJob::where('followout_id', $this->id)->delete();

        $this->deleteFlyer();
        $this->deletePictures();
        $this->followees()->delete();
        $this->favorited()->delete();
        $this->reward_program_jobs()->delete();
        $this->reward_programs()->delete();
        $this->delete();

        return true;
    }

    /**
     * Return true if followout has at least one checkin with "enter" or "exit" status.
     *
     * @return bool
     */
    public function hasCompletedCheckins()
    {
        return $this->checkins()->completed()->exists();
    }

    public function hasCheckin($userId, $status = null)
    {
        if ($status) {
            return $this->checkins()->where('user_id', $userId)->where('status', $status)->exists();
        }

        return $this->checkins()->where('user_id', $userId)->exists();
    }

    public function hasActiveRewardProgram()
    {
        return $this->reward_programs()->active()->exists();
    }

    public function userHasAttended($userId) {
        $checkin = $this->checkins()->where('user_id', $userId)->whereIn('status', ['enter', 'exit'])->first();

        return !is_null($checkin);
    }

    public function hasFollowee($userId, $includeDeclined = true) {
        if ($this->isReposted()) {
            $followout = $this->getTopParentFollowout();
        } else {
            $followout = $this;
        }

        return $followout->hasFolloweeIncludingChildren($userId, $includeDeclined);
    }

    public function hasFolloweeByEmail($email)
    {
        $email = mb_strtolower($email);

        $invites = InvitedUnregisteredUser::where('email', $email)->get();

        foreach ($invites as $invite) {
            $followoutId = $invite->parameters['followout_id'] ?? null;

            if ($followoutId && $followoutId === $this->id) {
                return true;
            }
        }
    }

    // This function assumes that we start from top level parent Followout
    private function hasFolloweeIncludingChildren($userId, $includeDeclined = true)
    {
        if (!$includeDeclined) {
            $found = $this->followees()->where('user_id', $userId)->notDeclined()->exists();
        } else {
            $found = $this->followees()->where('user_id', $userId)->exists();
        }

        if ($found) {
            return true;
        }

        foreach ($this->reposted_followouts as $repostedFollowout) {
            if (!$includeDeclined) {
                $found = $repostedFollowout->followees()->where('user_id', $userId)->notDeclined()->exists();
            } else {
                $found = $repostedFollowout->followees()->where('user_id', $userId)->exists();
            }

            if ($found) {
                return true;
            }
        }

        return false;
    }

    public function hasPendingFollowee($userId)
    {
        return $this->pending_followees()->where('user_id', $userId)->exists();
    }

    public function hasAcceptedFollowee($userId)
    {
        return $this->accepted_followees()->where('user_id', $userId)->exists();
    }

    public function hasDeclinedFollowee($userId)
    {
        return $this->followees()->declined()->where('user_id', $userId)->exists();
    }

    public function getTopParentFollowout()
    {
        if ($this->isReposted()) {
            return $this->top_parent_followout;
        }

        return $this;
    }

    public function isEdited()
    {
        return $this->is_edited === true;
    }

    /**
     * @param  User $authUser
     * @return bool
     */
    public function onlyPrivacyIsEditable($authUser = null)
    {
        if ($authUser && $authUser->isAdmin()) {
            return false;
        }

        if ($this->author->isAdmin()) {
            return false;
        }

        if ($this->isUpcomingOrOngoing() && !$this->hasCompletedCheckins()) {
            return false;
        }

        return true;
    }

    public function isTopParentFollowout()
    {
        return $this->id === $this->getTopParentFollowout()->id;
    }

    public function isOngoing()
    {
        return $this->starts_at < now() && $this->ends_at > now();
    }

    public function isUpcoming()
    {
        return $this->starts_at > now();
    }

    public function isUpcomingOrOngoing()
    {
        return $this->isOngoing() || $this->isUpcoming();
    }

    public function hasEnded()
    {
        return $this->ends_at < now();
    }

    public function isUpcomingNextThreeMonths()
    {
        return $this->starts_at > now() && $this->starts_at < now()->addMonths(3);
    }

    public function isReposted()
    {
        return $this->parent_followout;
    }

    public function isUsedInRewardProgramJob()
    {
        return $this->isReposted() && RewardProgramJob::where('followout_id', $this->id)->exists();
    }

    public function isPublic()
    {
        return $this->privacy_type === 'public';
    }

    public function isPrivate()
    {
        return $this->privacy_type === 'private';
    }

    public function isHidden()
    {
        return $this->privacy_type === 'hidden';
    }

    public function isFollowersOnly()
    {
        return $this->privacy_type === 'followers';
    }

    public function isVirtual()
    {
        return $this->is_virtual === true;
    }

    public function isDefault()
    {
        return $this->is_default === true;
    }

    public function isGeoCoupon()
    {
        return $this->coupon;
    }

    public function setPrivacyPublic()
    {
        $this->privacy_type = 'public';
        $this->save();

        return true;
    }

    public function setPrivacyFollowers()
    {
        $this->privacy_type = 'followers';
        $this->save();

        return true;
    }

    public function setPrivacyPrivate()
    {
        $this->privacy_type = 'private';
        $this->save();

        return true;
    }

    public function setPrivacyHidden()
    {
        $this->privacy_type = 'hidden';
        $this->save();

        return true;
    }

    public function getFolloweesCount($excludeAuthor = false)
    {
        if ($excludeAuthor) {
            return $this->accepted_followees()->whereNotIn('user_id', [$this->author->id])->count();
        }

        return $this->accepted_followees()->count();
    }

    public function getAttendeesIds()
    {
        $ids = $this->checkins()->completed()->pluck('user_id')->toArray();

        // Add followees as attendee, to show how many people really followed out
        $ids = array_merge($ids, $this->followees->pluck('user_id')->toArray());

        $ids = array_unique($ids);

        // Remove followout author as an attendee
        if (($key = array_search($this->author_id, $ids)) !== false) {
            unset($ids[$key]);
        }

        return $ids;
    }

    public function getAttendeesIdsWithPresentedCoupon()
    {
        return $this->checkins()->completed()->presentedCoupon()->pluck('user_id')->toArray();
    }

    public function getAllAttendeesIds()
    {
        $result = $this->getAttendeesIds();

        if ($this->isReposted()) {
            $result = array_merge($result, $this->top_parent_followout->getAttendeesIds());
        }

        foreach ($this->child_followouts as $childFollowout) {
            $result = array_merge($result, $childFollowout->getAttendeesIds());
        }

        foreach ($this->reposted_followouts as $repostedFollowout) {
            $result = array_merge($result, $repostedFollowout->getAttendeesIds());
        }

        // Remove followout author as an attendee
        if (($key = array_search($this->author_id, $result)) !== false) {
            unset($result[$key]);
        }

        return array_unique($result);
    }

    public function getAttendeesCount()
    {
        $result = $this->getAttendeesIds();

        if ($this->isReposted()) {
            $result = array_diff($result, $this->top_parent_followout->getAttendeesIds());
        }

        return count(array_unique($result));
    }

    public function getAttendeesWithPresentedCouponCount()
    {
        $result = $this->getAttendeesIdsWithPresentedCoupon();

        if ($this->isReposted()) {
            $result = array_diff($result, $this->top_parent_followout->getAttendeesIdsWithPresentedCoupon());
        }

        return count(array_unique($result));
    }

    public function getRepostedFollowoutsAttendeesCount()
    {
        $result = [];

        if ($this->isTopParentFollowout()) {
            foreach ($this->reposted_followouts as $repostedFollowout) {
                $result = array_merge($result, $repostedFollowout->getAttendeesIds());
            }
        } else {
            foreach ($this->child_followouts as $childFollowout) {
                $result = array_merge($result, $childFollowout->getAttendeesIds());
            }
        }

        return count(array_unique($result));
    }

    public function getTotalAttendeesCount()
    {
        return count($this->getAllAttendeesIds());
    }

    public function getRepostedFollowoutsFolloweesCount()
    {
        $count = 0;

        foreach ($this->reposted_followouts as $repostedFollowout) {
            $count += $repostedFollowout->getFolloweesCount(true);
            $count += $repostedFollowout->getRepostedFollowoutsFolloweesCount();
        }

        return $count;
    }

    public function getTotalFolloweesCount()
    {
        return $this->getFolloweesCount() + $this->getRepostedFollowoutsFolloweesCount();
    }

    public function getRepostedFollowoutsViewsCount()
    {
        $count = 0;

        if ($this->isTopParentFollowout()) {
            $repostedFollowouts = $this->reposted_followouts;
        } else {
            $repostedFollowouts = $this->child_followouts;
        }

        foreach ($repostedFollowouts as $repostedFollowout) {
            $count += $repostedFollowout->views_count;
            $count += $repostedFollowout->getRepostedFollowoutsViewsCount();
        }

        return $count;
    }

    public function getRepostsCount()
    {
        return $this->reposted_followouts()->count();
    }

    public function getRepostedFollowoutsRepostsCount()
    {
        $count = 0;

        foreach ($this->reposted_followouts as $repostedFollowout) {
            $count += $repostedFollowout->getRepostsCount();
            $count += $repostedFollowout->getRepostedFollowoutsRepostsCount();
        }

        return $count;
    }

    public function getTotalRepostsCount()
    {
        return $this->getRepostsCount() + $this->getRepostedFollowoutsRepostsCount();
    }

    public function fullAddress()
    {
        $state = $this->state ? $this->state . ', ' : '';

        return $this->address . ', ' . $this->city . ', ' . $state . $this->zip_code;
    }

    public function incrementViews($amount = 1)
    {
        return $this->increment('views_count', $amount);
    }

    public function getTitleAttribute($value)
    {
        if ($this->isVirtual()) {
            return Str::finish($value, ' Virtual Followout');
        }

        return Str::finish($value, ' Followout');
    }

    public function getDescriptionAttribute($value)
    {
        if ($this->isReposted()) {
            return $this->getTopParentFollowout()->description;
        }

        if (!$this->isGeoCoupon()) {
            return $this->attributes['description'] ?? '';
        }

        if ($this->coupon->promo_code) {
            $value .= 'Promo code: ' . $this->coupon->promo_code;
            $value .= PHP_EOL;
        }

        $value = 'Value: ' . $this->coupon->discount_value_formatted;
        $value .= PHP_EOL;

        $value .= 'Expiration date: ' . $this->coupon->expires_at->tz('UTC')->format(config('followouts.date_format_date_time_string_long'));
        $value .= PHP_EOL;
        $value .= 'Details: ' . $this->coupon->description;

        return $value;
    }

    public function getViewsCountAttribute($value)
    {
        return is_null($value) ? 1 : $value;
    }

    public function getTotalViewsCountAttribute($value)
    {
        return $this->views_count + $this->getRepostedFollowoutsViewsCount();
    }

    public function setTicketsUrlAttribute($value)
    {
        $this->attributes['tickets_url'] = $value ? addHttpScheme(mb_strtolower(rtrim($value, '/'))) : null;
    }

    public function setExternalInfoUrlAttribute($value)
    {
        $this->attributes['external_info_url'] = $value ? addHttpScheme(mb_strtolower(rtrim($value, '/'))) : null;
    }
}
