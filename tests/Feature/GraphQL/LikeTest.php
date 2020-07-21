<?php

namespace Tests\Feature\GraphQL;

use App\Question;
use App\User;
use Illuminate\Support\Facades\Auth;

class LikeTest extends GraphQLTestCase
{

    protected $question;
    protected function setUp(): void
    {
        parent::setUp();
        Auth::login(User::find(1));
        $this->question = factory(Question::class)->create([
            'description' => "测试数据测试数据测试数据",
            'category_id' => 1,
        ]);
    }

    // public function testLikesQuery()
    // {
    //     $query     = file_get_contents(__DIR__ . '/gql/like/query/likesQuery.gql');
    //     $variables = [
    //         'user_id' => $this->getRandomUser()->id,
    //     ];

    //     $this->runGQL($query, $variables);
    // }

    public function testToggleLikeMutation()
    {
        $query     = file_get_contents(__DIR__ . '/gql/like/mutation/toggleLikeMutation.gql');
        $variables = [
            'id'   => $this->question->id,
            'type' => "questions",
            'undo' => false,
        ];
        $this->runGQL($query, $variables);
    }
}
