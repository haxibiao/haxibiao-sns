<?php

namespace Database\Factories;

use App\Meetup;
use Illuminate\Database\Eloquent\Factories\Factory;

class MeetupFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Meetup::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $faker = \Faker\Factory::create('zh_CN');
        return [
            'title'         => $faker->text(50),
            'introduction'  => $faker->text(100),
            'phone'         => $faker->phoneNumber,
            'wechat'        => $faker->phoneNumber,
        ];
    }
}
