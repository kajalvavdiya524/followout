<?php

namespace App\Notifications;

use App\User;
use App\Mail\NewSubscriber as Mailable;
use App\Channels\IOSChannel;
use App\Channels\MongoDBChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class NewSubscriber extends Notification implements ShouldQueue
{
    use Queueable;

    public $subscriber;

    public $message;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(User $subscriber)
    {
        $this->subscriber = $subscriber;

        $this->message = 'Congratulations! '.$subscriber->name.' has become your subscriber!';
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

        if ($notifiable->notificationEnabled(NewSubscriber::class, 'db')) {
            $via[] = MongoDBChannel::class;
        }

        if ($notifiable->notificationEnabled(NewSubscriber::class, 'mail')) {
            $via[] = 'mail';
        }

        if ($notifiable->notificationEnabled(NewSubscriber::class, 'mobile_push') && $notifiable->apns_device_token) {
            $via[] = IOSChannel::class;
        }

        return $via;
    }

    public function toMongoDB($notifiable)
    {
        return [
            'title' => 'New subscriber',
            'message' => $this->message,
            'type' => NewSubscriber::class,
            'has_action' => true,
            'action_parameters' => [
                'user' => $this->subscriber->id,
            ],
        ];
    }

    public function toMail($notifiable)
    {
        return (new Mailable($this->subscriber))->to($notifiable->email);
    }

    public function toIOS($notifiable)
    {
        return [
            'title' => 'New subscriber',
            'message' => $this->message,
            'metadata' => [
                'notification_type' => NewSubscriber::class,
                'subscriber_id' => $this->subscriber->id,
            ],
        ];
    }
}
