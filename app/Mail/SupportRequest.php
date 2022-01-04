<?php

namespace App\Mail;

use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SupportRequest extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $subject;
    public $user;
    public $message;
    public $messageSubject;
    public $fromName;
    public $fromEmail;
    public $url;
    public $userUrl;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($message, $messageSubject = null, User $user = null, $fromEmail = null, $fromName = null)
    {
        $this->messageSubject = $messageSubject;
        $this->message = trim($message);
        $this->user = $user;

        if ($user) {
            $this->userUrl = route('users.show', ['user' => $user]);
            $this->fromName = $user->name;
            $this->fromEmail = $user->email;
        } else {
            $this->fromName = $fromName;
            $this->fromEmail = mb_strtolower($fromEmail);
        }

        $replySubject = $messageSubject ? 'Re: ' . $messageSubject : 'Re: Your support request';

        $this->url = 'mailto:' . $this->fromEmail . '?subject=' . rawurlencode($replySubject);
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('New support request')
                    ->to(config('followouts.support_email'))
                    ->markdown('mail.support-request');
    }
}
