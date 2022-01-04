<?php

namespace App\Notifications;

use App\Channels\IOSChannel;
use App\Channels\MongoDBChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class SubscriptionExpired extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
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

        if ($notifiable->notificationEnabled(SubscriptionExpired::class, 'mail')) {
            $via[] = 'mail';
        }

        return $via;
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject('Your Followouts Pro subscription has expired')
                    ->greeting('Hello!')
                    ->line('Your Followouts Pro subscription has expired.')
                    ->action('Subscribe', route('pricing'));
    }
}
