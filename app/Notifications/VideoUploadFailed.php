<?php

namespace App\Notifications;

use App\Video;
use App\Followout;
use App\Channels\IOSChannel;
use App\Channels\MongoDBChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class VideoUploadFailed extends Notification
{
    use Queueable;

    public $video;

    public $action;

    public $url;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Video $video)
    {
        $this->video = $video;

        $this->action = 'View Details';
        $this->url = url('/');

        if ($video->file->type === 'followout_flyer') {
            $this->action = 'View Followout';
            $this->url = Followout::find($video->file->followout_id)->url(true);
        }
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

        if ($notifiable->notificationEnabled(VideoUploadFailed::class, 'mail')) {
            $via[] = 'mail';
        }

        return $via;
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->error()
                    ->subject('Video upload failed')
                    ->line('Your recently uploaded video was not processed successfully.')
                    ->line('You can reupload this video or try a different one.')
                    ->line('Sorry about that.')
                    ->action($this->action, $this->url);
    }
}
