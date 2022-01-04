<?php

namespace App\Mail;

use App\SalesRepresentative;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SalesRepresentativeInvite extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $rep;

    public $subject;

    public $message;

    public $url;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(SalesRepresentative $rep)
    {
        $this->rep = $rep;
        $this->subject = 'Become a Sales Representative of FollowOut!';
        $this->message = 'You are invited to become a sales representative of FollowOut! Click below to learn more.';
        $this->url = route('sales-rep-agreement', ['hash' => $this->rep->hash]);
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->to($this->rep->email)->subject($this->subject)->markdown('mail.sales-rep-invite');
    }
}
