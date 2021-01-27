<?php

namespace Database\Factories;

use App\Comment;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CommentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Comment::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id'          => random_int(1, 3),
            'commentable_id'   => 1, //better override
            'commentable_type' => 'articles', //better override
            'body'             => $this->faker->text(20),
        ];
    }
}
