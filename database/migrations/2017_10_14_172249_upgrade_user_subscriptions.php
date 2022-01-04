<?php

use Illuminate\Support\Facades\Schema;
use Jenssegers\Mongodb\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpgradeUserSubscriptions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Update products
        $products = \App\Product::all();

        foreach ($products as $product) {
            if ($product->isFolloweeServices()) {
                $product->action_name = 'Add Followee Services';
            }

            if ($product->isSubscriptionMonthly()) {
                $product->action_name = 'Subscribe (monthly)';
            }

            if ($product->isSubscriptionYearly()) {
                $product->action_name = 'Subscribe (yearly)';
            }

            $product->save();
        }

        // Update subscriptions
        $subscriptions = \App\Subscription::all();

        foreach ($subscriptions as $subscription) {
            $subscription->type = 'subscription_monthly';

            if ($subscription->isActive()) {
                $subscription->renewal_request_sent = false;
            } else {
                $subscription->renewal_request_sent = true;
            }

            $subscription->save();
        }

        // Update users
        $users = \App\User::all();

        foreach ($users as $user) {
            $user->clearCart();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
