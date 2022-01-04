<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBasicSubscription extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $product = Product::subscriptionBasic()->first();

        if (is_null($product)) {
            $product = new \App\Product([
                'name' => 'Followouts Basic',
                'action_name' => 'Subscribe',
                'description' => 'Basic account subscription.',
                'price' => 99.00,
                'type' => 'subscription_basic',
            ]);
            $product->save();
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
