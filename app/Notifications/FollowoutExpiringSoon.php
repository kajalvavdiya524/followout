<?php

namespace App\Notifications;

use App\Followout;
use App\Channels\MongoDBChannel;
use App\Channels\IOSChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class FollowoutExpiringSoon extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @var \App\Followout
     */
    public $followout;

    /**
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $message;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Followout $followout)
    {
        $this->followout = $followout;
        $this->title = $followout->title . ' is about to expire';
        $this->message = 'Your Followout will expire in less than 24 hours.';
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        if ($notifiable->notificationEnabled(FollowoutExpiringSoon::class, 'db')) {
            $via[] = MongoDBChannel::class;
        }

        if ($notifiable->notificationEnabled(FollowoutExpiringSoon::class, 'mail')) {
            $via[] = 'mail';
        }

        if ($notifiable->notificationEnabled(FollowoutExpiringSoon::class, 'mobile_push') && $notifiable->apns_device_token) {
            $via[] = IOSChannel::class;
        }

        return $via;
    }

    public function toMongoDB($notifiable)
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'type' => FollowoutExpiringSoon::class,
            'has_action' => true,
            'action_parameters' => [
                'followout' => $this->followout->id,
            ],
        ];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject('Friendly Reminder: ' . $this->title)
                    ->greeting('Hello!')
                    ->line($this->message)
                    ->line('Feel free to create a new Followout!')
                    ->action('View Followout', $this->followout->url(true));
    }

    /**
     * Get the representation of the notification for APNs.
     *
     * @param  mixed $notifiable
     * @return array
     */
    public function toIOS($notifiable)
    {
        return [
            'title' => null,
            'message' => $this->title,
            'metadata' => [
                'notification_type' => FollowoutExpiringSoon::class,
            ],
        ];
    }
}
