<?php

namespace App\Notifications;

use App\User;
use App\Followout;
use App\Mail\PresentFollowoutRequest as Mailable;
use App\Channels\IOSChannel;
use App\Channels\MongoDBChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class PresentFollowoutRequest extends Notification implements ShouldQueue
{
    use Queueable;

    public $followout;
    public $user;
    public $message;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Followout $followout, User $user)
    {
        $this->followout = $followout;
        $this->user = $user;
        $this->message = $user->name . ' requested to present your Followout.';
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

        if ($notifiable->notificationEnabled(PresentFollowoutRequest::class, 'db')) {
            $via[] = MongoDBChannel::class;
        }

        if ($notifiable->notificationEnabled(PresentFollowoutRequest::class, 'mail')) {
            $via[] = 'mail';
        }

        if ($notifiable->notificationEnabled(PresentFollowoutRequest::class, 'mobile_push') && $notifiable->apns_device_token) {
            $via[] = IOSChannel::class;
        }

        return $via;
    }

    public function toMail($notifiable)
    {
        return (new Mailable($this->followout, $this->user))->to($notifiable->email);
    }

    public function toMongoDB($notifiable)
    {
        return [
            'title' => 'Present Followout request',
            'message' => $this->message,
            'type' => PresentFollowoutRequest::class,
            'has_action' => true,
            'action_parameters' => [
                'user' => $this->user->id,
                'followout' => $this->followout->id,
            ],
        ];
    }

    public function toIOS($notifiable)
    {
        return [
            'title' => null,
            'message' => $this->message,
            'metadata' => [
                'notification_type' => PresentFollowoutRequest::class,
            ],
        ];
    }
}
