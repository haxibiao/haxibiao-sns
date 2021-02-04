<?php

namespace Haxibiao\Sns\Tests\Feature\GraphQL;

use App\Category;
use App\Question;
use App\User;
use Haxibiao\Breeze\GraphQLTestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class LikeTest extends GraphQLTestCase
{
    use DatabaseTransactions;

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

    protected function tearDown(): void
    {
        $this->question->forceDelete();
        $this->category->forceDelete();
        $this->user->forceDelete();
        parent::tearDown();
    }

    public function testLikesQuery()
    {
        $query     = file_get_contents(__DIR__ . '/Like/likesQuery.graphql');
        $variables = [
            'user_id' => $this->user->id,
        ];

        $this->runGQL($query, $variables);
    }

    public function testToggleLikeMutation()
    {
        $query     = file_get_contents(__DIR__ . '/Like/toggleLikeMutation.graphql');
        $variables = [
            'id'   => $this->question->id,
            'type' => "questions",
            'undo' => false,
        ];
        $this->runGQL($query, $variables);
    }
}
