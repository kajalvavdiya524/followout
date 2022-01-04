<?php

namespace App\Notifications;

use App\Channels\IOSChannel;
use App\Channels\MongoDBChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class TestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $message;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->message = 'Hello, world! This is a test notification.';
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

        if ($notifiable->notificationEnabled(TestNotification::class, 'db')) {
            $via[] = MongoDBChannel::class;
        }

        if ($notifiable->notificationEnabled(TestNotification::class, 'mail')) {
            $via[] = 'mail';
        }

        if ($notifiable->notificationEnabled(TestNotification::class, 'mobile_push') && $notifiable->apns_device_token) {
            $via[] = IOSChannel::class;
        }

        return $via;
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject('Test notification')
                    ->line($this->message)
                    ->action('Go to website', url('/'));
    }

    public function toMongoDB($notifiable)
    {
        return [
            'title' => 'Test notification',
            'message' => $this->message,
            'type' => TestNotification::class,
            'has_action' => true,
            'action_parameters' => [
                //
            ],
        ];
    }

    public function toIOS($notifiable)
    {
            return [
                'title' => 'Test notification',
                'message' => $this->message,
                'metadata' => [
                    'notification_type' => TestNotification::class,
                ],
            ];
    }
}
