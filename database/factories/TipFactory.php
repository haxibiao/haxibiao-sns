<?php

namespace Database\Factories;

use App\Tip;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TipFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Tip::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id'       => rand(1,3),
            'amount'        => rand(1,3),
            'gold'          => rand(10,30),
            'message'       => '测试tip',
            'tipable_id'    => rand(1,3),
            'tipable_type'  => 'articles',
        ];
    }
}
