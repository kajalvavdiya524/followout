<?php

namespace Database\Factories;

use App\User;
use App\Country;
use Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * The name of the factory's corresponding model.
     *
     * @var \Illuminate\Database\Eloquent\Collection
     */
    protected $countries;

    public function __construct()
    {
        $this->countries = Country::all();
    }

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->firstNameMale . ' ' . $this->faker->lastName,
            'email' => $this->faker->unique()->safeEmail,
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10),
            'gender' => 'male',
            'birthday' => Carbon::now()->subYears($this->faker->numberBetween(20, 40)),
            'is_activated' => true,
            'phone_number' => $this->faker->e164PhoneNumber,
            'address' => $this->faker->streetAddress,
            'city' => $this->faker->city,
            'zip_code' => $this->faker->randomNumber(5),
            'education' => $this->faker->company,
            'about' => $this->faker->text,
            'website' => 'https://'.$this->faker->domainName,
            'last_seen' => Carbon::now(),
            'country_id' => $data['countries']->random()->id,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function unverified()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_activated' => null,
            ];
        });
    }
}
