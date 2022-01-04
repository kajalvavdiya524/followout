<?php

namespace App\Notifications;

use App\Subscription;
use App\Channels\IOSChannel;
use App\Channels\MongoDBChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class SubscriptionExpiringSoon extends Notification implements ShouldQueue
{
    use Queueable;

    public $subscription;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Subscription $subscription)
    {
        $this->$subscription = $subscription;
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

        if ($notifiable->notificationEnabled(SubscriptionExpiringSoon::class, 'mail')) {
            $via[] = 'mail';
        }

        return $via;
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject('Renew your Followouts Pro subscription')
                    ->greeting('Hello!')
                    ->line('Your Followouts Pro subscription will expire soon.')
                    ->action('Renew subscription', route('settings.account'));
    }
}
