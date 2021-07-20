<?php

namespace Database\Factories;

use App\Message;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class MessageFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Message::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'chat_id' => rand(1,3),
            'body'    => '消息测试',
            'user_id' => rand(1,3),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
