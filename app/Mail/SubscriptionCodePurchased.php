<?php

namespace App\Mail;

use App\SubscriptionCode;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SubscriptionCodePurchased extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $subscriptionCode;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(SubscriptionCode $subscriptionCode)
    {
        $this->subscriptionCode = $subscriptionCode;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Your subscription code is here')
                    ->markdown('mail.subscription-code-receipt');
    }
}
