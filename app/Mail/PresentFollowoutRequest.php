<?php

namespace App\Mail;

use App\User;
use App\Followout;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class PresentFollowoutRequest extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $followout;
    public $user;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Followout $followout, User $user)
    {
        $this->followout = $followout;
        $this->user = $user;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $userType = $this->user->isFollowee() ? 'Followee' : 'User';
        $subject = $userType.' requested to present your Followout';

        return $this->subject($subject)->markdown('mail.notifications.present-followout-request');
    }
}
