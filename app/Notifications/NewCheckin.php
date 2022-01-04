<?php

namespace App\Notifications;

use App\Followout;
use App\Channels\IOSChannel;
use App\Channels\MongoDBChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class NewCheckin extends Notification implements ShouldQueue
{
    use Queueable;

    public $followout;

    public $message;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Followout $followout)
    {
        $this->followout = $followout;

        $this->message = 'Congratulations! Someone has entered your Followout.';
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

        if ($notifiable->notificationEnabled(NewCheckin::class, 'db')) {
            $via[] = MongoDBChannel::class;
        }

        if ($notifiable->notificationEnabled(NewCheckin::class, 'mobile_push') && $notifiable->apns_device_token) {
            $via[] = IOSChannel::class;
        }

        return $via;
    }

    public function toMongoDB($notifiable)
    {
        return [
            'title' => 'New checkin',
            'message' => $this->message,
            'type' => NewCheckin::class,
            'has_action' => true,
            'action_parameters' => [
                'followout' => $this->followout->id,
            ],
        ];
    }

    public function toIOS($notifiable)
    {
        return [
            'title' => 'New checkin',
            'message' => $this->message,
            'metadata' => [
                'notification_type' => NewCheckin::class,
                'followout' => $this->followout->id,
            ],
        ];
    }
}
