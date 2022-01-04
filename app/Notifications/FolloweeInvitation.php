<?php

namespace App\Notifications;

use App\Followee;
use App\Followout;
use App\Mail\FolloweeInvitation as Mailable;
use App\Channels\IOSChannel;
use App\Channels\MongoDBChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class FolloweeInvitation extends Notification implements ShouldQueue
{
    use Queueable;

    public $followout;

    public $followee;

    public $message;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Followout $followout, Followee $followee)
    {
        $this->followout = $followout;
        $this->followee = $followee;

        $this->message = 'You\'ve been invited by '.$followout->author->name.' to present a '.$followout->title.'.';
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

        if ($notifiable->notificationEnabled(FolloweeInvitation::class, 'db')) {
            $via[] = MongoDBChannel::class;
        }

        if ($notifiable->notificationEnabled(FolloweeInvitation::class, 'mail')) {
            $via[] = 'mail';
        }

        if ($notifiable->notificationEnabled(FolloweeInvitation::class, 'mobile_push') && $notifiable->apns_device_token) {
            $via[] = IOSChannel::class;
        }

        return $via;
    }

    public function toMail($notifiable)
    {
        return (new Mailable($this->followout, $this->followee))->to($notifiable->email);
    }

    public function toMongoDB($notifiable)
    {
        return [
            'title' => 'Present a Followout',
            'message' => $this->message,
            'type' => FolloweeInvitation::class,
            'has_action' => true,
            'action_parameters' => [
                'followout' => $this->followout->id,
                'hash' => $this->followout->hash,
            ],
        ];
    }

    public function toIOS($notifiable)
    {
        return [
            'title' => null,
            'message' => $this->message,
            'metadata' => [
                'notification_type' => FolloweeInvitation::class,
            ],
        ];
    }
}
