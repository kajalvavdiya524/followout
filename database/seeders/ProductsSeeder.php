<?php

use App\Product;
use Illuminate\Database\Seeder;

class ProductsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $item = new Product([
            'name' => 'Followee services',
            'action_name' => 'Add Followee Services',
            'description' => 'Service fee for one Followee for one Followout.',
            'price' => 99.99,
            'type' => 'followee_services',
        ]);
        $item->save();

        $item = new Product([
            'name' => 'Followouts Basic',
            'action_name' => 'Subscribe',
            'description' => 'Basic account subscription.',
            'price' => 99.00,
            'type' => 'subscription_basic',
        ]);
        $item->save();

        $item = new Product([
            'name' => 'Followouts Pro (monthly)',
            'action_name' => 'Subscribe (monthly)',
            'description' => 'Pro account subscription.',
            'price' => 29.95,
            'type' => 'subscription_monthly',
        ]);
        $item->save();

        $item = new Product([
            'name' => 'Followouts Pro (yearly)',
            'action_name' => 'Subscribe (yearly)',
            'description' => 'Pro account subscription.',
            'price' => 150.00,
            'type' => 'subscription_yearly',
        ]);
        $item->save();

        if (!Product::where('name', 'Followouts Pro Setup Fee')->where('type', 'subscription_setup_fee')->exists()) {
            $item = new Product([
                'name' => 'Followouts Pro Setup Fee',
                'action_name' => null,
                'description' => 'Pro account subscription setup fee.',
                'price' => 49.98,
                'type' => 'subscription_setup_fee',
            ]);
            $item->save();
        }

        $item = new Product([
            'name' => 'GEO coupon',
            'action_name' => 'Use GEO coupon',
            'description' => 'Service fee for attaching one GEO coupon to one Followout.',
            'price' => 49.99,
            'type' => 'geo_coupon',
        ]);
        $item->save();
    }
}
