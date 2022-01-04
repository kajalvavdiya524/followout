<?php

namespace App\Mail;

use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class FolloweeIntro extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $user;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $pronoun = $this->user->isMale() ? 'his' : 'her';
        $userType = $this->user->isFollowee() ? 'Followee' : 'User';
        $subject = $userType.' requested to review '.$pronoun.' profile';

        return $this->subject($subject)->markdown('mail.notifications.followee-intro');
    }
}
