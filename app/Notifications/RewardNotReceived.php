<?php

namespace App\Notifications;

use App\RewardProgramJob;
use App\Channels\IOSChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class RewardNotReceived extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @var \App\RewardProgramJob
     */
    public $rewardProgramJob;

    /**
     * @var string
     */
    public $subject;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(RewardProgramJob $rewardProgramJob)
    {
        $this->rewardProgramJob = $rewardProgramJob;
        $this->subject = 'Receive Reward Notification';
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

        if ($notifiable->notificationEnabled(RewardNotReceived::class, 'mail')) {
            $via[] = 'mail';
        }

        if ($notifiable->notificationEnabled(RewardNotReceived::class, 'mobile_push') && $notifiable->apns_device_token) {
            $via[] = IOSChannel::class;
        }

        return $via;
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject($this->subject)
                    ->line('Important! You have received a Receive Reward Notification.');
    }

    public function toIOS($notifiable)
    {
        return [
            'title' => $this->subject,
            'message' => 'Important! You have a Receive Reward Notification.',
            'metadata' => [
                'notification_type' => RewardNotReceived::class,
            ],
        ];
    }
}
