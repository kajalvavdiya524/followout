<?php

namespace App;

use Arr;
use Carbon;
use FollowoutHelper;
use Gate;
use Mail;
use Storage;
use Str;
use App\Jobs\ProcessFollowoutFlyerVideo;
use Jenssegers\Mongodb\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Notifications\Notifiable;

/**
 * @property string $_id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string $remember_token
 * @property string $phone_number
 * @property Carbon $last_seen
 * @property Carbon $birthday
 * @property string $address
 * @property string $city
 * @property string $zip_code
 * @property string $website
 * @property string $education
 * @property string $about
 * @property string $role
 * @property Carbon $role_expires_at
 * @property string $gender
 * @property double $lat
 * @property double $lng
 * @property string $privacy_type
 * @property string $keywords
 * @property string $account_activation_token
 * @property string $password_reset_token
 * @property Carbon $password_reset_token_expires_at
 * @property string $requested_account_deletion_reason
 * @property Carbon $requested_account_deletion_at
 * @property bool   $is_activated
 * @property bool   $is_unregistered
 * @property bool   $auto_show_default_followouts
 * @property string $api_token
 * @property string $apns_device_token
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class User extends Model implements AuthenticatableContract, AuthorizableContract, CanResetPasswordContract
{
    use Notifiable, Authenticatable, Authorizable, CanResetPassword;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'phone_number',
        'password',
        'gender',
        'birthday',
        'last_seen',
        'zip_code',
        'address',
        'city',
        'state',
        'education',
        'about',
        'lat',
        'lng',
        'is_activated',
        'is_unregistered',
        'privacy_type',
        'auto_show_default_followouts',
        'video_url'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'account_activation_token',
        'password_reset_token',
        'password_reset_token_expires_at',
        'remember_token',
        'api_token',
        'apns_device_token',
    ];
    protected $appends = ['video_link'];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'birthday',
        'last_seen',
        'password_reset_token_expires_at',
        'requested_account_deletion_at',
        'role_expires_at',
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

    public static $withBasic = [
        'account_categories',
        'avatars',
        'profile_cover',
        'country',
        'subscription',
    ];

    public static $withProfile = [
        'accepted_followees',
        'account_categories',
        'avatars',
        'profile_cover',
        'country',
        'followouts',
        'followouts.author',
        'followouts.experience_categories',
        'followouts.flyer',
        'followouts.pictures',
        'follows',
        'followers',
        'subscribers',
        'subscription',
        'blocked_users',
        'blocked_by_users',
    ];

    public static $withAll = [
        'accepted_followees',
        'account_categories',
        'checkins',
        'avatars',
        'profile_cover',
        'country',
        'follows',
        'followers',
        'subscribers',
        'followees',
        'followouts',
        'followouts.author',
        'followouts.experience_categories',
        'followouts.flyer',
        'followouts.pictures',
        'saved_followouts',
        'social_accounts',
        'subscription',
        'blocked_users',
        'blocked_by_users',
    ];


    // Relations
    public function getVideoLinkAttribute()
    {
        $link = $this->attributes['video_url'];
        $domain =  $this->getDomainFromUrl($link);
        if($domain == 'youtube.com'){
            preg_match('/[\?\&]v=([^\?\&]+)/',$link,$matches);
            if(!empty($matches[1])){
                $YtCode = $matches[1];
            }else{
                $YtCode = $link;
            }
            return "https://www.youtube.com/embed/".$YtCode;
        }else if($domain == 'vimeo.com'){
            $vimmeoId = (int) substr(parse_url($link, PHP_URL_PATH), 1);
            return "https://player.vimeo.com/video/".$vimmeoId;
        }else{
            return $link;
        }

    }

    public function account_categories()
    {
        return $this->belongsToMany('App\FollowoutCategory');
    }

    public function social_accounts()
    {
        return $this->hasMany('App\SocialAccount');
    }

    public function payments()
    {
        return $this->hasMany('App\Payment');
    }

    public function subscription()
    {
        return $this->hasOne('App\Subscription');
    }

    public function country()
    {
        return $this->belongsTo('App\Country');
    }

    public function reward_programs()
    {
        return $this->hasMany('App\RewardProgram', 'author_id');
    }

    public function reward_program_jobs()
    {
        return $this->hasMany('App\RewardProgramJob', 'user_id');
    }

    public function coupons()
    {
        return $this->hasMany('App\Coupon', 'author_id');
    }

    public function used_coupons()
    {
        return $this->hasMany('App\UsedCoupon', 'user_id');
    }

    // Followouts where user is author
    public function followouts()
    {
        return $this->hasMany('App\Followout', 'author_id');
    }

    public function checkins()
    {
        return $this->hasMany('App\Checkin', 'user_id');
    }

    // Followee models where user is followee
    public function followees()
    {
        return $this->hasMany('App\Followee');
    }

    // Followee models where user is followee that accepted his invite
    public function accepted_followees()
    {
        return $this->hasMany('App\Followee')->where('user_id', $this->id)->where('status', 'accepted');
    }

    public function follows()
    {
        return $this->hasMany('App\Follower', 'from_id');
    }

    public function followers()
    {
        return $this->hasMany('App\Follower', 'to_id');
    }

    public function subscribers()
    {
        return $this->hasMany('App\Follower', 'to_id');
    }

    public function messages_sent()
    {
        return $this->hasMany('App\Message', 'from_id');
    }

    public function messages_received()
    {
        return $this->hasMany('App\Message', 'to_id');
    }

    public function notifications()
    {
        return $this->hasMany('App\Notification', 'user_id')->orderBy('created_at', 'DESC');
    }

    public function unread_notifications()
    {
        return $this->hasMany('App\Notification', 'user_id')->unread()->orderBy('created_at', 'DESC');
    }

    public function notification_settings()
    {
        return $this->hasMany('App\NotificationSetting', 'user_id');
    }

    public function blocked_users()
    {
        return $this->hasMany('App\Blacklist', 'user_id');
    }

    public function blocked_by_users()
    {
        return $this->hasMany('App\Blacklist', 'blocked_user_id');
    }

    public function pictures()
    {
        return $this->hasMany('App\File', 'user_id')->orderBy('created_at');
    }

    public function avatars()
    {
        return $this->hasMany('App\File', 'user_id')->where('type', 'avatar')->orderBy('created_at');
    }

    public function default_flyer()
    {
        return $this->hasOne('App\File', 'user_id')->where('type', 'default_flyer');
    }

    public function avatar($number = 0)
    {
        if (!$this->hasAvatar($number)) {
            return null;
        }

        $avatars = $this->avatars()->orderBy('created_at')->get();

        return $avatars->get($number);
    }

    public function profile_cover()
    {
        return $this->hasOne('App\File')->where('type', 'profile_cover');
    }

    public function favorites()
    {
        return $this->hasMany('App\Favorite', 'user_id');
    }

    public function saved_followouts()
    {
        return $this->hasMany('App\Favorite', 'user_id')->followouts();
    }

    // Scopes

    public function scopeWithAvatar($query)
    {
        return $query->has('avatars');
    }

    public function scopeFriends($query)
    {
        return $query->where('role', 'friend');
    }

    public function scopeAdmins($query)
    {
        return $query->where('role', 'admin');
    }

    public function scopeNotAdmins($query)
    {
        return $query->where('role', '!=', 'admin');
    }

    public function scopeAnonymous($query)
    {
        return $query->whereNotNull('anonymous_user_id');
    }

    public function scopeNotAnonymous($query)
    {
        return $query->whereNull('anonymous_user_id');
    }

    public function scopeFollowhosts($query)
    {
        return $query->where('role', 'followhost');
    }

    public function scopeNotFollowhosts($query)
    {
        return $query->where('role', '!=', 'followhost');
    }

    public function scopeFollowees($query)
    {
        return $query->where('role', 'followee');
    }

    public function scopeActivated($query)
    {
        return $query->where('is_activated', true);
    }

    public function scopeToBeDeleted($query)
    {
        return $query->whereNotNull('requested_account_deletion_at');
    }

    public function scopePublic($query)
    {
        return $query->whereIn('privacy_type', ['public', null]);
    }

    public function scopePrivate($query)
    {
        return $query->where('privacy_type', 'private');
    }

    public function scopeSubscribed($query)
    {
        return $query->whereHas('subscription', function ($query) {
            $query->active();
        });
    }

    public function scopeHaveOngoingOrUpcomingPublicFollowout($query)
    {
        return $query->whereHas('followouts', function ($query) {
            $query->notGeoCoupon()->notReposted()->ongoingOrUpcoming()->public();
        });
    }

    // Helpers

    public function isOnline()
    {
        return $this->last_seen->lte(Carbon::now()->subMinutes(15)) ? false : true;
    }

    public function lastSeen()
    {
        return $this->last_seen->lte(Carbon::now()->subMinutes(15)) ? 'Last seen '.$this->last_seen->diffForHumans() : 'Online';
    }

    public function hasLocation()
    {
        return !empty($this->lat) && !empty($this->lng);
    }

    public function hasAddress()
    {
        return $this->city && $this->address && $this->zip_code;
    }

    public function hasOpenDisputesAsFollowhost()
    {
        return $this->reward_programs()->whereHas('jobs', function ($query) {
            $query->where('transaction_status', 'open');
        })->exists();
    }

    public function hasOpenDisputesAsFollowee()
    {
        return $this->reward_program_jobs()->where('transaction_status', 'open')->exists();
    }

    public function hasOpenDisputes()
    {
        if ($this->hasOpenDisputesAsFollowhost()) return true;

        if ($this->hasOpenDisputesAsFollowee()) return true;

        return false;
    }

    public function hasProfileCover()
    {
        return !is_null($this->profile_cover);
    }

    public function hasAvatar($number = 0)
    {
        $avatars = $this->avatars()->orderBy('created_at')->get();

        return !is_null($avatars->get($number));
    }

    public function hasDefaultFlyer()
    {
        return $this->default_flyer()->exists();
    }

    public function hasDefaultVideoFlyer()
    {
        if (is_null($this->default_flyer)) {
            return false;
        }

        return !is_null($this->default_flyer->video);
    }

    public function defaultAvatarURL()
    {
        if ($this->isAnonymous()) {
            if ($settings = StaticContent::where('name', 'users')->first()) {
                return $settings->anonymous_user_avatar_url;
            }
        }

        return url('/img/user-pic-default.png');
    }

    public function defaultFlyerURL($preferVideo = false)
    {
        if ($this->hasDefaultFlyer()) {
            if ($this->hasDefaultVideoFlyer()) {
                if ($this->default_flyer->video->isProcessed()) {
                    if ($preferVideo) {
                        return $this->default_flyer->video->url();
                    }
                    return $this->default_flyer->video->thumbURL();
                }

                return $this->defaultVideoFlyerURL();
            }

            return Storage::url($this->default_flyer->path);
        }

        return url('/img/flyer-pic-default.png');
    }

    // This method is used for flyers with unprocessed videos
    public function defaultVideoFlyerURL()
    {
        return url('/img/flyer-video-pic-default.png');
    }

    public function saveDefaultFlyer(UploadedFile $file)
    {
        if ($this->hasDefaultFlyer()) {
            $this->deleteDefaultFlyer();
        }

        $randomString = Str::random(64);
        $extension = $file->guessExtension();

        $isImage = $extension === 'gif' || $extension === 'png' || $extension === 'jpg' || $extension === 'jpeg';
        $isVideo = $extension === 'mp4' || $extension === 'qt' || $extension === 'm4v';

        // AWS doesn't accept .qt file extension
        $extension = $extension === 'qt' ? 'mp4' : $extension;

        if ($isImage) {
            $picture = File::makeFlyer($file);

            $path = 'users/' . $this->id . '/' . $randomString.'.'.$extension;

            Storage::put($path, (string) $picture, 'public');

            $picture = new File([ 'type' => 'default_flyer', 'path' => $path, 'is_autogenerated' => false ]);

            $this->default_flyer()->save($picture);
        } else if ($isVideo) {
            $basename = Str::random(64);
            $filename = $basename.'.'.$extension;

            $path_raw = 'videos/raw/' . $filename;
            $path_mp4 = 'users/' . $this->id . '/' . $basename.'.mp4';
            $path_m3u8 = 'users/' . $this->id . '/' . $basename.'.m3u8';
            $path_thumb = 'users/' . $this->id . '/' . $basename.'.0000000.jpg';

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
            $video->uploader()->associate($this);
            $video->save();

            $picture = new File([ 'type' => 'default_flyer', 'is_autogenerated' => false ]);
            $picture->video()->associate($video);
            $this->default_flyer()->save($picture);

            $video->file()->associate($picture);
            $video->save();

            ProcessFollowoutFlyerVideo::dispatch($video->id, 'users/'.$this->id);
        } else {
            throw new \Exception($extension.' format is not supported yet.', 422);
        }

        return true;
    }

    public function saveDefaultFlyerFromUsername()
    {
        if ($this->hasDefaultFlyer()) {
            if ($this->default_flyer->is_autogenerated === true) {
                $this->deleteDefaultFlyer();
            } else {
                return false;
            }
        }

        $randomString = Str::random(64);
        $extension = 'jpg';

        $picture = File::makeFlyerFromText($this->name);

        $path = 'users/' . $this->id . '/' . $randomString.'.'.$extension;

        Storage::put($path, (string) $picture, 'public');

        $picture = new File([ 'type' => 'default_flyer', 'path' => $path, 'is_autogenerated' => true ]);

        $this->default_flyer()->save($picture);

        return true;
    }

    public function deleteDefaultFlyer()
    {
        $flyer = $this->default_flyer;

        if (is_null($flyer)) {
            return true;
        }

        if ($this->hasDefaultVideoFlyer()) {
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

    public function avatarURL($number = 0)
    {
        if (!$this->hasAvatar()) {
            return $this->defaultAvatarURL();
        }

        $avatars = $this->avatars()->orderBy('created_at')->get();

        $avatar = $avatars->get($number);

        if (is_null($avatar)) {
            return $this->defaultAvatarURL();
        }

        return Storage::url($avatar->path);
    }

    public function saveAvatar(UploadedFile $file, $number = null)
    {
        if ($number && $this->hasAvatar($number)) {
            $this->deleteAvatarByNumber($number);
        } else if ($this->hasAvatar(2)) {
            // Delete the third (last) avatar otherwise
            $this->deleteAvatarByNumber(2);
        }

        $randomString = Str::random(64);
        $extension = $file->guessExtension();

        $picture = File::makeAvatar($file);

        $path = 'users/' . $this->id . '/' . $randomString . '.' . $extension;

        Storage::put($path, (string) $picture, 'public');

        $avatar = new File([ 'type' => 'avatar', 'path' => $path ]);

        $this->avatars()->save($avatar);

        return true;
    }

    public function saveProfileCover(UploadedFile $file)
    {
        if ($this->hasProfileCover()) {
            $this->deleteProfileCover();
        }

        $randomString = Str::random(64);
        $extension = $file->guessExtension();

        $picture = File::makeProfileCover($file);

        $path = 'users/' . $this->id . '/' . $randomString.'.'.$extension;

        Storage::put($path, (string) $picture, 'public');

        $cover = new File([ 'type' => 'profile_cover', 'path' => $path ]);

        $this->profile_cover()->save($cover);

        return true;
    }

    public function deletePicturesById(array $pictures = [])
    {
        $pictures = $this->pictures()->whereIn('_id', $pictures)->get();

        foreach ($pictures as $picture) {
            Storage::delete($picture->path);
            $picture->delete();
        }

        return true;
    }

    public function deleteAvatarByNumber($number)
    {
        $avatar = $this->avatar($number);

        if ($avatar) {
            Storage::delete($avatar->path);
            $avatar->delete();
        }

        return true;
    }

    public function deleteAvatars()
    {
        if (!$this->hasAvatar()) {
            return true;
        }

        foreach ($this->avatars as $avatar) {
            Storage::delete($avatar->path);
            $avatar->delete();
        }

        return true;
    }

    public function deleteProfileCover()
    {
        if (!$this->hasProfileCover()) {
            return true;
        }

        $cover = $this->profile_cover;

        Storage::delete($cover->path);
        $cover->delete();

        return true;
    }

    public function getEmailActivationURL()
    {
        if ($this->isActivated()) {
            return null;
        }

        $this->account_activation_token = $this->account_activation_token ?? Str::random(48);
        $this->save();

        return action('UsersController@activateAccount', ['token' => $this->account_activation_token]);
    }

    public function getPasswordResetToken($forceNew = false)
    {
        $token = $this->generatePasswordResetToken($forceNew);

        return $token;
    }

    public function getPasswordResetURL()
    {
        $token = $this->generatePasswordResetToken();

        return route('password.reset', ['token' => $token]);
    }

    public function generatePasswordResetToken($forceNew = false)
    {
        if (!$forceNew && $this->password_reset_token && $this->password_reset_token_expires_at > Carbon::now()) {
            return $this->password_reset_token;
        }

        $this->password_reset_token = Str::random(10);
        $this->password_reset_token_expires_at = Carbon::now()->addMinutes(15);
        $this->save();

        return $this->password_reset_token;
    }

    public function sendAccountActivationEmail()
    {
        if (is_null($this->email)) {
            return false;
        }

        if ($this->isActivated()) {
            return false;
        }

        Mail::to($this->email)->queue(new \App\Mail\ActivateYourAccount($this->getEmailActivationURL()));
    }

    public function sendPasswordResetEmail()
    {
        Mail::to($this->email)->queue(new \App\Mail\ResetPassword($this->getPasswordResetToken(true)));
    }

    public function hasUnreadNotifications()
    {
        return $this->unreadNotificationsCount() > 0;
    }

    public function unreadNotificationsCount()
    {
         return $this->unread_notifications()->count();
    }

    public function notificationEnabled($type, $platform)
    {
        // TODO: disabled until we use https://github.com/laravel-notification-channels/apn
        if ($platform === 'mobile_push') return false;

        $notificationSetting = $this->notification_settings()->where('notification_type', $type)->platform($platform)->first();

        if (is_null($notificationSetting)) {
            return true;
        }

        return $notificationSetting->isEnabled();
    }

    public function enableNotification($type, $platform)
    {
        if (!$this->notificationEnabled($type, $platform)) {
            $this->notification_settings()->updateOrCreate(
                [
                    'notification_type' => $type,
                    'platform' => $platform
                ],
                [
                    'notification_type' => $type,
                    'is_enabled' => true,
                ]
            );
        }

        return true;
    }

    public function disableNotification($type, $platform)
    {
        $this->notification_settings()->updateOrCreate(
            [
                'notification_type' => $type,
                'platform' => $platform
            ],
            [
                'notification_type' => $type,
                'is_enabled' => false,
            ]
        );

        return true;
    }

    /**
     * Specifies the user's APNs token.
     *
     * @return string
     */
    public function routeNotificationForApn()
    {
        return $this->apns_device_token;
    }

    public function hasUnreadMessages()
    {
        return $this->unreadMessagesCount() > 0;
    }

    public function getLastMessageFromChat($chatId)
    {
        $lastSentMessage = $this->messages_sent()->to($chatId)->orderBy('created_at', 'DESC')->first();
        $lastReceivedMessage = $this->messages_received()->from($chatId)->orderBy('created_at', 'DESC')->first();

        if (is_null($lastSentMessage) && is_null($lastReceivedMessage)) {
            $lastMessage = null;
        } else if (is_null($lastSentMessage)) {
            $lastMessage = $lastReceivedMessage;
        } else if (is_null($lastReceivedMessage)) {
            $lastMessage = $lastSentMessage;
        } else {
            $lastMessage = $lastReceivedMessage->created_at->gte($lastSentMessage->created_at) ? $lastReceivedMessage : $lastSentMessage;
        }

        return $lastMessage;
    }

    public function hasUnreadMessagesFromUser($userId)
    {
        return $this->unreadMessagesCountFromUser($userId) > 0;
    }

    public function unreadMessagesCountFromUser($userId)
    {
        return $this->messages_received()->from($userId)->unread()->count();
    }

    public function unreadMessagesCount()
    {
        return $this->messages_received()->unread()->count();
    }

    public function repostFollowout($followoutId, $rewardProgramId)
    {
        $followout = Followout::find($followoutId);
        $allowRepost = true;

        if (is_null($followout)) {
            return false;
        }

        // Cannot repost your own Followout
        if ($followout->author->id === $this->id) {
            return false;
        }

        // Cannot repost Followout twice
        if ($this->followouts()->where('top_parent_followout_id', $followoutId)->exists()) {
            $repostedFollowout = $this->followouts()->where('top_parent_followout_id', $followoutId)->first();
        } else {
            $repostedFollowout = new Followout;
            $repostedFollowout->hash = hash('sha256', $this->id.time());
            $repostedFollowout->privacy_type = $followout->isPrivate() ? 'private' : 'followers';
            $repostedFollowout->author()->associate($this);
            $repostedFollowout->parent_followout_id = $followout->id;
            $repostedFollowout->top_parent_followout_id = $followout->getTopParentFollowout()->id;
            $repostedFollowout->save();
            $repostedFollowout->cloneFlyerFromFollowout($followout);

            FollowoutHelper::syncAttributesForRepostedFollowouts($followout);

            $followee = new Followee;
            $followee->status = 'accepted';
            $followee->user_id = $this->id;
            $followee = $repostedFollowout->followees()->save($followee);
        }

        // This is to prevent checkin of user that reposted followout count towards reward program job
        if ($followout->isVirtual() && !$followout->hasCheckin($this->id, 'exit')) {
            $checkin = $followout->checkins()->create(['status' => 'exit']);
            $checkin->user()->associate($this);
            $checkin->save();
        }

        if ($rewardProgramId) {
            $rewardProgram = RewardProgram::find($rewardProgramId);
            if ($rewardProgram->followout_id === $followoutId) {
                if ($rewardProgramJob = $rewardProgram->getJobByUser($this->id)) {
                    // User sent the request to present followout himself, so we'll update existing RewardProgramJob model
                    if ($rewardProgramJob->isPending()) {
                        $rewardProgramJob->status = 'claimed';
                        $rewardProgramJob->followout()->associate($repostedFollowout);
                        $rewardProgramJob->save();
                    }
                } else {
                    // User was invited by followhost, so we'll create new instance of RewardProgramJob
                    $rewardProgramJob = new RewardProgramJob;
                    $rewardProgramJob->status = 'claimed';
                    $rewardProgramJob->user()->associate($this);
                    $rewardProgramJob->followout()->associate($repostedFollowout);
                    $rewardProgramJob->parent_followout()->associate($followout);
                    $rewardProgramJob = $rewardProgram->jobs()->save($rewardProgramJob);
                }
            }
        }

        return $repostedFollowout;
    }

    public function shortAddress()
    {
        $address = '';

        if ($this->city) {
            $address .= $this->city . ', ';
        }

        if ($this->state) {
            $address .= $this->state . ', ';
        }

        if ($this->country) {
            $address .= $this->country->name . ', ';
        }

        if (is_null($this->city) && is_null($this->state) && is_null($this->country)) {
            $address .= 'Zip ';
        }

        $address .= $this->zip_code;

        return $address;
    }

    public function fullAddress()
    {
        $address = '';

        if ($this->address) {
            $address .= $this->address . ', ';
        }

        if ($this->city) {
            $address .= $this->city . ', ';
        }

        if ($this->state) {
            $address .= $this->state . ', ';
        }

        if ($this->country) {
            $address .= $this->country->name . ', ';
        }

        if (is_null($this->address) && is_null($this->city) && is_null($this->state) && is_null($this->country)) {
            $address .= 'Zip ';
        }

        $address .= $this->zip_code;

        return $address;
    }

    public function isMale()
    {
        return $this->gender !== 'female';
    }

    public function isFemale()
    {
        return $this->gender === 'female';
    }

    public function isFriend()
    {
        return $this->role === 'friend';
    }

    public function isFollowee()
    {
        return $this->role === 'followee';
    }

    public function isFollowhost()
    {
        return $this->role === 'followhost';
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isAnonymous()
    {
        return $this->anonymous_user_id !== null;
    }

    public function isActivated()
    {
        return $this->is_activated === true;
    }

    public function isRegistered()
    {
        return !$this->isUnregistered();
    }

    public function isPublic()
    {
        return is_null($this->privacy_type) || $this->privacy_type === 'public';
    }

    public function isPrivate()
    {
        return $this->privacy_type === 'private';
    }

    public function isUnregistered()
    {
        return $this->is_unregistered === true;
    }

    public function isMissingProfileInfo()
    {
        if ($this->zip_code === null) {
            return true;
        }

        if ($this->isFollowhost()) {
            if ($this->lat === null || $this->lng === null) {
                return true;
            }
        }

        return false;
    }

    public function hasDefaultFollowout()
    {
        return $this->followouts()->where('is_default', true)->exists();
    }

    public function hasOngoingOrUpcomingPublicFollowout()
    {
        return $this->followouts()->notGeoCoupon()->notReposted()->ongoingOrUpcoming()->public()->exists();
    }

    public function hasOngoingOrUpcomingPublicGeoFollowout()
    {
        return $this->followouts()->geoCoupon()->notReposted()->ongoingOrUpcoming()->public()->exists();
    }

    public function hasRole($role)
    {
        return $this->role === $role;
    }

    public function subscribed()
    {
        if ($this->subscription && $this->subscription->isActive()) {
            return true;
        }

        return false;
    }

    public function subscribedToBasic()
    {
        if ($this->subscription && $this->subscription->isActive() && $this->subscription->isBasic()) {
            return true;
        }

        return false;
    }

    public function subscribedToPro()
    {
        if ($this->subscription && $this->subscription->isActive() && !$this->subscription->isBasic()) {
            return true;
        }

        return false;
    }

    public function totalSpent($format = false)
    {
        $amount = 0;

        $this->payments->each(function ($item, $key) use (&$amount) {
            $amount += (float) $item->amount;
        });

        if ($format) {
            return number_format($amount, 2);
        }

        return $amount;
    }

    public function wasSubscribed()
    {
        return $this->wasSubscribedTo('followouts-pro-monthly') || $this->wasSubscribedTo('followouts-pro-yearly');
    }

    public function wasSubscribedTo($planId)
    {
        $payments = $this->payments()->viaChargebee()->get();

        $payments = collect($payments->toArray());

        $products = Arr::pluck($payments, 'products');

        $products = Arr::collapse($products);

        if ($planId === 'followouts-pro-monthly') {
            $filtered = collect(Arr::where($products, function ($value, $key) {
                return $value['type'] === 'subscription_monthly';
            }));

            return $filtered->count() > 0;
        }

        if ($planId === 'followouts-pro-yearly') {
            $filtered = collect(Arr::where($products, function ($value, $key) {
                return $value['type'] === 'subscription_yearly';
            }));

            return $filtered->count() > 0;
        }

        if ($planId === 'subscription_basic') {
            $filtered = collect(Arr::where($products, function ($value, $key) {
                return $value['type'] === 'subscription_basic';
            }));

            return $filtered->count() > 0;
        }

        return false;
    }

    public function wasInvitedBySalesRep()
    {
        return $this->sales_rep_code !== null;
    }

    public function wasInvitedBySalesRepWithPromo()
    {
        return $this->sales_rep_promo_code !== null;
    }

    public function follow($userId, $autosubscribed = false)
    {
        if ($userId === $this->id) {
            return false;
        }

        $user = User::find($userId);

        if (is_null($user)) {
            return false;
        }

        if ($this->following($userId)) {
            return true;
        }

        $follower = new Follower;
        $follower->follower()->associate($this);
        $follower->follows()->associate($user);
        $follower->save();

        if ($autosubscribed) {
            $this->notify(new \App\Notifications\YouWereAutosubscribed($user));
        } else {
            $user->notify(new \App\Notifications\NewSubscriber($this));
        }

        return true;
    }

    public function unfollow($userId)
    {
        if ($userId === $this->id) {
            return false;
        }

        $user = User::find($userId);

        if (is_null($user)) {
            return false;
        }

        if (!$this->following($userId)) {
            return true;
        }

        $this->follows()->where('to_id', $userId)->delete();

        return true;
    }

    public function block($userId)
    {
        if ($userId === $this->id) {
            return false;
        }

        $user = User::find($userId);

        if (is_null($user)) {
            return false;
        }

        if ($this->blocked($userId)) {
            return true;
        }

        $block = new Blacklist;
        $block->user()->associate($this);
        $block->blocked_user()->associate($user);
        $block->save();

        return true;
    }

    public function unblock($userId)
    {
        if ($userId === $this->id) {
            return false;
        }

        $user = User::find($userId);

        if (is_null($user)) {
            return false;
        }

        if (!$this->blocked($userId)) {
            return true;
        }

        $this->blocked_users()->where('blocked_user_id', $userId)->delete();

        return true;
    }

    public function blocked($userId)
    {
        return $this->blocked_users()->where('blocked_user_id', $userId)->exists();
    }

    public function following($userId)
    {
        return $this->follows()->where('to_id', $userId)->exists();
    }

    public function markedForDeletion()
    {
        return $this->requested_account_deletion_at !== null;
    }

    public function url()
    {
        return route('users.show', ['user' => $this->id]);
    }

    public function getCartTotal()
    {
        $cart = $this->getCartProducts();

        $total = number_format((float) $cart->sum('price'), 2, '.', '');

        return $total;
    }

    public function getCartProducts()
    {
        $products = collect([]);

        foreach ((array) $this->cart as $productId) {
            $products->push(Product::find($productId));
        }

        $products = $products->sort();

        return $products;
    }

    public function removeItemFromCart($productId)
    {
        $cart = collect((array) $this->cart);

        if (!$cart->contains($productId)) {
            return false;
        }

        $cart->forget($cart->search($productId));

        $cart = $cart->sort();
        $this->cart = $cart->toArray();
        $this->save();

        return true;
    }

    public function clearCart()
    {
        $this->cart = null;
        $this->save();

        return true;
    }

    /**
     * Returns active reward programs created by current user.
     */
    public function getActiveRewardPrograms()
    {
        return $this->reward_programs()->with('author', 'followout')->active()->get();
    }

    /**
     * Reward programs that are available for this user to "claim".
     */
    public function getAvailableRewardProgramsForFollowout($followoutId)
    {
        return RewardProgram::with('author', 'followout')
                    ->active()
                    ->where('followout_id', $followoutId)
                    ->whereDoesntHave('jobs', function ($query) {
                        $query->where('user_id', $this->id);
                    })->get();
    }

    // Get a collection of all followouts that are ongoing or upcoming
    public function getOngoingOrUpcomingFollowouts($authUser = null)
    {
        $followouts = collect([]);

        $followouts = $this->followouts()->ongoingOrUpcoming()->orderBy('starts_at', 'ASC')->get();

        $followouts = FollowoutHelper::filterFollowoutsForUser($followouts, $authUser);

        return $followouts;
    }

    // Get a collection of all followouts that are ongoing, upcoming or have ended in the last 90 days
    public function getOngoingOrUpcomingOrEndedRecentlyFollowouts($authUser = null)
    {
        $followouts = collect([]);

        $followouts = $this->followouts()->ongoingOrUpcomingOrEndedRecently()->orderBy('starts_at')->get();

        $followouts = FollowoutHelper::filterFollowoutsForUser($followouts, $authUser);

        return $followouts;
    }

    public function setRole($role, Carbon $expiresAt = null)
    {
        $this->role = $role;

        if ($expiresAt) {
            $this->role_expires_at = $expiresAt;
        }

        $this->save();

        return true;
    }

    public function roleHasExpired()
    {
        if (is_null($this->role_expires_at)) {
            return false;
        }

        return $this->role_expires_at < Carbon::now();
    }

    public function releaseExpiredRole()
    {
        if (!$this->roleHasExpired()) {
            return false;
        }

        if ($this->hasRole('admin') || $this->hasRole('followhost') || $this->hasRole('followee')) {
            $this->setRole('friend');
        }

        return true;
    }

    public function processInvitationsByEmail()
    {
        $invites = InvitedUnregisteredUser::where('email', $this->email)->get();

        foreach ($invites as $invite) {
            $invite->attachToUser();
        }

        return true;
    }

    public function markForAccountDeletion($reason = null)
    {
        $this->requested_account_deletion_reason = (string) $reason;
        $this->requested_account_deletion_at = Carbon::now();
        $this->save();

        return true;
    }

    public function deleteAccount()
    {
        if ($this->subscription) {
            $this->subscription->cancelAndDelete(false);
        }

        foreach ($this->followouts as $followout) {
            $followout->deleteFollowout();
        }

        $this->deleteAvatars();
        $this->deleteProfileCover();
        $this->deleteDefaultFlyer();

        $this->social_accounts()->delete();
        $this->blocked_users()->delete();
        $this->blocked_by_users()->delete();
        $this->favorites()->delete();
        $this->followees()->delete();
        $this->follows()->delete();
        $this->subscribers()->delete();
        $this->checkins()->delete();
        $this->payments()->delete();
        $this->subscription()->delete();
        $this->messages_received()->delete();
        $this->messages_sent()->delete();

        $this->delete();

        return true;
    }

    public function setEmailAttribute($value)
    {
        $this->attributes['email'] = $value ? mb_strtolower($value) : null;
    }

    public function setPhoneNumberAttribute($value)
    {
        // $this->attributes['phone_number'] = $value ? '+'.preg_replace('/\+/', '', preg_replace('/[^\d+]/', '', $value)) : null;
        $this->attributes['phone_number'] = $value ? preg_replace('/[^\d+]/', '', $value) : null;
    }

    public function setWebsiteAttribute($value)
    {
        $this->attributes['website'] = $value ? addHttpScheme(mb_strtolower(rtrim($value, '/'))) : null;
    }

    public function setAboutAttribute($value)
    {
        $this->attributes['about'] = $value ? trim($value) : null;
    }

    public function setKeywordsAttribute($value)
    {
        $this->attributes['keywords'] = $value ? get_keywords_from_string(trim($value)) : null;
    }
   
    function getDomainFromUrl($url)
    {
        $pieces = parse_url($url);
        $domain = isset($pieces['host']) ? $pieces['host'] : '';
        if (preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $regs)) {
            return $regs['domain'];
        }
        return false;
    }
}
