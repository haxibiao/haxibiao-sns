<?php

namespace Haxibiao\Sns\Tests\Feature\GraphQL;

use App\Category;
use App\Post;
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
    }

    /**
     * 用户的粉丝列表
     * @group like
     * @group testLikesQuery
     */
    public function testLikesQuery()
    {
        $query     = file_get_contents(__DIR__ . '/Like/likesQuery.graphql');
        $variables = [
            'user_id' => $this->user->id,
        ];

        $this->startGraphQL($query, $variables);
    }

    /**
     * 用户的粉丝列表
     * @group like
     * @group testToggleLikeMutation
     */
    public function testToggleLikeMutation()
    {
        $post = Post::factory()->create([
            'user_id'=>$this->user->id
        ]);
        $user = User::factory()->create();
        $headers = $this->getRandomUserHeaders($user);
        $query     = file_get_contents(__DIR__ . '/Like/toggleLikeMutation.graphql');
        $variables = [
            'id'   => $post->id,
            'type' => 'POST'
        ];
        $this->startGraphQL($query, $variables, $headers);
    }
}
