<?php

namespace Haxibiao\Sns\Tests\Feature\GraphQL;

use App\Post;
use App\User;
use Haxibiao\Breeze\GraphQLTestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class DislikeTest extends GraphQLTestCase
{
    use DatabaseTransactions;

    protected $Tom;
    protected $Bob;
    protected $post;

    protected $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->Tom  = User::factory()->create();
        $this->Bob  = User::factory()->create();
        $this->post = Post::factory()->create();
    }

    protected function tearDown(): void
    {
        $this->Tom->forceDelete();
        $this->Bob->forceDelete();
        $this->post->forceDelete();
        parent::tearDown();
    }

    /**
     * 屏蔽用户
     *
     * @group dislike
     * @group testDislikeMutation
     */
    public function testDislikeMutation()
    {
        $headers   = $this->getRandomUserHeaders($this->Tom);
        $mutation  = file_get_contents(__DIR__ . '/Dislike/DislikeMutation.graphql');
        $variables = [
            'id' => $this->Bob->id,
        ];
        $this->startGraphQL($mutation, $variables, $headers);
    }
    /**
     * 视频动态不感兴趣
     *
     * @group dislike
     * @group testDislikePostMutation
     */
    public function testDislikePostMutation()
    {
        $headers   = $this->getRandomUserHeaders($this->Tom);
        $mutation  = file_get_contents(__DIR__ . '/Dislike/DislikePostMutation.graphql');
        $variables = [
            'id' => $this->post->id,
        ];
        $this->startGraphQL($mutation, $variables, $headers);
    }
}
