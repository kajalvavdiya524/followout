<?php

namespace App\Notifications;

use App\RewardProgramJob;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class RewardProgramJobBecameRedeemable extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @var \App\RewardProgramJob
     */
    public $rewardProgramJob;

    /**
     * @var string
     */
    public $notifiableType;

    /**
     * @var string
     */
    public $subject;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(RewardProgramJob $rewardProgramJob, $notifiableType)
    {
        $this->rewardProgramJob = $rewardProgramJob;
        $this->notifiableType = $notifiableType;
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

        if ($notifiable->notificationEnabled(RewardProgramJobBecameRedeemable::class, 'mail')) {
            $via[] = 'mail';
        }

        return $via;
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject($this->subject)
                    ->line('Important! You have received a Receive Reward Notification.');
        /*
        $followhost = $this->rewardProgramJob->reward_program->author;
        $followee = $this->rewardProgramJob->user;
        $rewardProgram = $this->rewardProgramJob->reward_program;

        if ($this->notifiableType === 'followee') {
            return (new MailMessage)
                        ->subject($this->subject)
                        ->line('Congratulations! ' . $rewardProgram->title . ' is complete! ' . $followhost->name . ' will correspond accordingly.');
        } else { // reward program author
            return (new MailMessage)
                        ->subject($this->subject)
                        ->line('Congratulations! ' . $followee->name . ' has completed ' . $rewardProgram->title . ' reward program!')
                        ->line('Please send redeem code including details on how to receive ' . $rewardProgram->description . ' to: ' . $followee->email);
        }
        */
    }
}
