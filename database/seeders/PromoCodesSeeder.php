<?php

use Illuminate\Database\Seeder;

class PromoCodesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $item = new \App\PromoCode([
            'code' => '10',
            'amount' => 10.00,
        ]);
        $item->save();

        $item = new \App\PromoCode([
            'code' => '100',
            'amount' => 100.00,
        ]);
        $item->save();
    }
}
