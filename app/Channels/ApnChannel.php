<?php

namespace App\Channels;

use NotificationChannels\Apn\ApnMessage;
use Illuminate\Notifications\Notification as IlluminateNotification;

class ApnChannel
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
        // TEMP
        $badge = $notifiable->unreadNotificationsCount();
        // $badge = $notifiable->unreadNotificationsCount() + $notifiable->unreadMessagesCount();

        return ApnMessage::create()
                    ->badge($badge)
                    ->title($message['title'])
                    ->body($message['message']);
    }
}
