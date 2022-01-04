<?php

namespace App\Notifications;

use App\User;
use App\Channels\IOSChannel;
use App\Channels\MongoDBChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class YouWereAutosubscribed extends Notification
{
    use Queueable;

    public $followhost;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(User $followhost)
    {
        $this->followhost = $followhost;
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

        if ($notifiable->notificationEnabled(YouWereAutosubscribed::class, 'mail')) {
            $via[] = 'mail';
        }

        if ($notifiable->notificationEnabled(YouWereAutosubscribed::class, 'mobile_push') && $notifiable->apns_device_token) {
            $via[] = IOSChannel::class;
        }

        return $via;
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
                    ->subject('You have been automatically subscribed to ' . $this->followhost->name . '!')
                    ->line('Good news! You have been automatically subscribed to ' . $this->followhost->name . ' which now enables you to view their GEO Coupons, Offers, and Deals Followouts!')
                    ->action('View and share discounts', $this->followhost->url());
    }

    public function toIOS($notifiable)
    {
        return [
            'title' => 'You have been automatically subscribed to ' . $this->followhost->name,
            'message' => 'Good news! You have been automatically subscribed to ' . $this->followhost->name . ' which now enables you to view their GEO Coupons, Offers, and Deals Followouts!',
            'metadata' => [
                'notification_type' => YouWereAutosubscribed::class,
                'user_id' => $this->followhost->id,
            ],
        ];
    }
}
