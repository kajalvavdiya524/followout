<?php

namespace App\Channels;

use App\Notification;
use Illuminate\Notifications\Notification as IlluminateNotification;

class MongoDBChannel
{
    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return void
     */
    public function send($notifiable, IlluminateNotification $notification)
    {
        $message = $notification->toMongoDB($notifiable);

        Notification::create([
            'user_id' => $notifiable->id,
            'data' => $message,
            'read_at' => null,
        ]);
    }
}
