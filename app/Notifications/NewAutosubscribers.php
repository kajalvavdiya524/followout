<?php

namespace App\Notifications;

use Str;
use App\Channels\IOSChannel;
use App\Channels\MongoDBChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class NewAutosubscribers extends Notification
{
    use Queueable;

    public $count;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(int $count)
    {
        $this->count = $count;
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

        if ($notifiable->notificationEnabled(NewAutosubscribers::class, 'mail')) {
            $via[] = 'mail';
        }

        if ($notifiable->notificationEnabled(NewAutosubscribers::class, 'mobile_push') && $notifiable->apns_device_token) {
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
                    ->subject('You have ' . $this->count . ' new ' . Str::plural('subscriber', $this->count) . '!')
                    ->line('Congratulations! You have ' . $this->count . ' new ' . Str::plural('subscriber', $this->count) . '!')
                    ->line('Create new GEO Coupon Followouts to gain more subscribers!')
                    ->action('Manage GEO Coupons', route('coupons.index'));
    }

    public function toIOS($notifiable)
    {
        return [
            'title' => 'You have ' . $this->count . ' new ' . Str::plural('subscriber', $this->count) . '!',
            'message' => 'Create new GEO Coupon Followouts to gain more subscribers!',
            'metadata' => [
                'notification_type' => NewAutosubscribers::class,
            ],
        ];
    }
}
