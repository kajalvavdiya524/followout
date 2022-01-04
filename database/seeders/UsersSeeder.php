<?php

use App\Country;
use App\FollowoutCategory;
use App\User;
use Illuminate\Database\Seeder;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $experienceCategories = FollowoutCategory::all();
        $countries = Country::all();

        // Admin: a@a.com|123123
        $user = new User([
            'email' => 'a@a.com',
            'name' => 'Admin',
            'password' => bcrypt('123123'),
            'is_unregistered' => false,
            'is_activated' => true,
            'role' => 'admin',
            'api_token' => 'H2Yd1EmIEEMFQrDf2K3rk4kgFSxjvDEONkNzlVTOTuebtYOZQlirY3CswLbOkY04961NGA9EaSJvrhlUi689jJDK9XS0J0xAaSCm',
            'last_seen' => now(),
            'phone_number' => '+1234567890',
            'gender' => null,
            'birthday' => null,
            'address' => null,
            'city' => null,
            'state' => null,
            'privacy_type' => 'public',
            'zip_code' => '12345',
            'about' => 'Lorem ipsum dolor sit amet.',
            'coupon_uses' => 0,
        ]);
        $user->save();

        $user->country()->associate($countries->pluck('_id')->random());
        $user->account_categories()->attach([$experienceCategories->pluck('_id')->random()]);
        $user->save();
    }
}
