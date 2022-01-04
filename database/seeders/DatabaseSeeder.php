<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(CountriesSeeder::class);
        $this->call(ExperienceCategoriesSeeder::class);
        $this->call(StaticContentSeeder::class);
        $this->call(ProductsSeeder::class);

        if (app()->environment('local')) {
            Storage::deleteDirectory('users');
            Storage::deleteDirectory('videos');
            Storage::deleteDirectory('coupons');
            Storage::deleteDirectory('followouts');

            $this->call(PromoCodesSeeder::class);
            $this->call(UsersSeeder::class);
        }
    }
}
