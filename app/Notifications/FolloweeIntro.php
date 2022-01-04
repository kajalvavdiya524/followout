<?php

namespace App\Notifications;

use App\User;
use App\Mail\FolloweeIntro as Mailable;
use App\Channels\IOSChannel;
use App\Channels\MongoDBChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class FolloweeIntro extends Notification implements ShouldQueue
{
    use Queueable;

    public $user;

    public $message;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(User $user)
    {
        $this->user = $user;

        $userType = $this->user->isFollowee() ? 'Followee' : 'User';

        $this->message = $userType . ' requested to review their profile.';
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        $via = [];

        if ($notifiable->notificationEnabled(FolloweeIntro::class, 'db')) {
            $via[] = MongoDBChannel::class;
        }

        if ($notifiable->notificationEnabled(FolloweeIntro::class, 'mail')) {
            $via[] = 'mail';
        }

        if ($notifiable->notificationEnabled(FolloweeIntro::class, 'mobile_push') && $notifiable->apns_device_token) {
            $via[] = IOSChannel::class;
        }

        return $via;
    }

    public function toMail($notifiable)
    {
        return (new Mailable($this->user))->to($notifiable->email);
    }

    public function toMongoDB($notifiable)
    {
        return [
            'title' => 'Followee introduction',
            'message' => $this->message,
            'type' => FolloweeIntro::class,
            'has_action' => true,
            'action_parameters' => [
                'user' => $this->user->id,
            ],
        ];
    }

    public function toIOS($notifiable)
    {
        return [
            'title' => null,
            'message' => $this->message,
            'metadata' => [
                'notification_type' => FolloweeIntro::class,
            ],
        ];
    }
}
