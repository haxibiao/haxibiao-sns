<?php

namespace Haxibiao\Sns\Tests\Feature\GraphQL;

use App\Category;
use App\Question;
use App\User;
use Haxibiao\Breeze\GraphQLTestCase;

class LikeTest extends GraphQLTestCase
{
    protected $category;
    protected $question;
    protected $user;
    protected function setUp(): void
    {
        parent::setUp();

        $this->user     = User::factory()->create();
        $this->category = Category::factory()->create();
        $this->question = Question::factory([
            'description' => "测试数据测试数据测试数据",
            'category_id' => $this->category->id,
            'user_id'     => $this->user->id,
        ])->create();
    }

    public function testLikesQuery()
    {
        $query     = file_get_contents(__DIR__ . '/like/query/likesQuery.gql');
        $variables = [
            'user_id' => $this->user->id,
        ];

        $this->runGQL($query, $variables);
    }

    public function testToggleLikeMutation()
    {
        $query     = file_get_contents(__DIR__ . '/like/mutation/toggleLikeMutation.gql');
        $variables = [
            'id'   => $this->question->id,
            'type' => "questions",
            'undo' => false,
        ];
        $this->runGQL($query, $variables);
    }
}
