<?php

namespace App\Mail;

use App\Followout;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class AttendeeInvitation extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $followout;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Followout $followout)
    {
        $this->followout = $followout;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = 'You have been invited to attend a Followout';

        return $this->subject($subject)->markdown('mail.notifications.friend-invitation');
    }
}
