<?php

namespace App\Notifications;

use App\Message;
use App\Channels\IOSChannel;
use App\Channels\MongoDBChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class NewMessage extends Notification implements ShouldQueue
{
    use Queueable;

    public $message;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Message $message)
    {
        $this->message = $message;
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

        if ($notifiable->notificationEnabled(NewMessage::class, 'db')) {
            $via[] = MongoDBChannel::class;
        }

        if ($notifiable->notificationEnabled(NewMessage::class, 'mobile_push') && $notifiable->apns_device_token) {
            $via[] = IOSChannel::class;
        }

        return $via;
    }

    public function toMongoDB($notifiable)
    {
        return [
            'title' => 'New message from '.$this->message->from->name,
            'message' => $this->message->message,
            'type' => NewMessage::class,
            'has_action' => true,
            'action_parameters' => [
                'chat_id' => $this->message->from->id,
            ],
        ];
    }

    public function toIOS($notifiable)
    {
        return [
            'title' => 'New message from '.$this->message->from->name,
            'message' => $this->message->message,
            'metadata' => [
                'notification_type' => NewMessage::class,
                'chat_id' => $this->message->from->id,
            ],
        ];
    }
}
