<?php

namespace App\Console\Commands;

use App\Subscription;
use App\SubscriptionCode;
use Illuminate\Console\Command;

class UpdateSubscriptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update user subscriptions';

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
        $toBeRenewed = Subscription::pendingRenewal()->get();

        foreach ($toBeRenewed as $subscription) {
            $this->line('Renewing subscription for user #'.$subscription->user->id.' ('.$subscription->user->email.')');

            $subscription->renew();
        }

        $toBeDeleted = Subscription::pendingDeletion()->get();

        foreach ($toBeDeleted as $subscription) {
            $this->line('Removing subscription from user #'.$subscription->user->id.' ('.$subscription->user->email.')');

            $subscription->cancelAndDelete();
        }

        // Remove expired subscription codes
        $this->line('Removing expired subscription codes...');

        SubscriptionCode::whereNotNull('expires_at')->where('expires_at', '<', now())->delete();
    }
}
