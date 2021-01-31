<?php

namespace Database\Factories;

use App\Feedback;
use Illuminate\Database\Eloquent\Factories\Factory;

class FeedbackFactory extends Factory
{
    protected $model = Feedback::class;
    public function definition()
    {
        return [
            'content' => '测试反馈...',
            'contact' => 'xxx@abc.com',
            'user_id' => rand(1, 3),
        ];
    }
}
