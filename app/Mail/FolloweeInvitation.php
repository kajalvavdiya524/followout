<?php

namespace App\Mail;

use App\Followee;
use App\Followout;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class FolloweeInvitation extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $followout;

    public $followee;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Followout $followout, Followee $followee = null)
    {
        $this->followout = $followout;
        $this->followee = $followee;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = 'You have been invited to present Followout';

        return $this->subject($subject)->markdown('mail.notifications.followee-invitation');
    }
}
