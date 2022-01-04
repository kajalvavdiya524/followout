<?php

namespace App\Console\Commands;

use App\Followout;
use App\Notifications\FollowoutExpiringSoon;
use Illuminate\Console\Command;

class NotifyAboutExpiringFollowouts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'followouts:notify-about-expiring';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notify users about about expiring Followouts';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->line('Notifying users about about expiring Followouts...');

        $followouts = Followout::geoCoupon()->where('ends_at', '<', now()->subHours(24))->where('expiration_notification_sent', '!=', true)->get();

        $this->info('Found ' . $followouts->count() . ' followouts.');

        foreach ($followouts as $followout) {
            $this->line('Processing ' . $followout->title . '...');

            $followout->expiration_notification_sent = true;
            $followout->save();

            $followout->author->notify(new FollowoutExpiringSoon($followout));
        }
    }
}
