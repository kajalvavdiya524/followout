<?php

namespace App\Mail;

use App\SalesRepresentative;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class NewSalesRepresentative extends Mailable implements ShouldQueue
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
        $this->subject = 'Sales Representative has accepted the agreement!';
        $this->message = 'We\'ve got a new sales representative: ' . $this->rep->full_name . ' (' . $this->rep->email . ').';
        $this->url = route('sales-reps.index', ['#'.$this->rep->id]);
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject($this->subject)
                    ->to(config('followouts.sales_rep_notification_email'))
                    ->markdown('mail.new-sales-rep');
    }
}
