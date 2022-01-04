<?php

namespace App\Channels;

use Illuminate\Notifications\Notification as IlluminateNotification;

class IOSChannel
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
        /*
        $message = $notification->toIOS($notifiable);

        $badge = $notifiable->unreadNotificationsCount();
        // $badge = $notifiable->unreadNotificationsCount() + $notifiable->unreadMessagesCount();

        $device = Device::apns($notifiable->apns_device_token)->badge($badge);

        $pushNotification = new PushNotification($message['title'], $message['message']);

        foreach ($message['metadata'] as $key => $value) {
            $pushNotification->metadata($key, $value);
        }

        $pushNotification->push($device);

        $results = $pushNotification->send();

        foreach ($results['errors'] as $data) {
            User::where('apns_device_token', $data->token)->update(['apns_device_token' => null]);
        }
        */
    }
}
