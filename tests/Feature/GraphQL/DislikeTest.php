<?php

namespace Haxibiao\Sns\Tests\Feature\GraphQL;

use App\Post;
use App\User;
use Haxibiao\Breeze\GraphQLTestCase;

class DislikeTest extends GraphQLTestCase
{
    //汤姆
    protected $Tom;

    //鲍勃
    protected $Bob;

    protected $post;

    protected function setUp(): void
    {
        parent::setUp();

        // $this->Tom = User::role(User::EDITOR_STATUS)->first(); //小编
        // $this->Bob = User::role(User::ADMIN_STATUS)->first(); //管理

        $this->Tom = User::factory()->create();
        $this->Bob = User::factory()->create();

        $this->post = Post::factory()->create();
    }

    /**
     * @TYPE users
     * @group dislike
     */
    public function testDislikeMutation()
    {
        $token   = $this->Tom->api_token;
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Accept'        => 'application/json',
        ];
        $mutation  = file_get_contents(__DIR__ . '/dislike/DislikeMutation.gql');
        $variables = [
            'id' => $this->Bob->id,
        ];
        $this->runGuestGQL($mutation, $variables, $headers);
    }
    /**
     * @TYPE posts
     * @group dislike
     */
    public function testDislikePostMutation()
    {
        $post    = $this->post;
        $token   = $this->Tom->api_token;
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Accept'        => 'application/json',
        ];
        $mutation  = file_get_contents(__DIR__ . '/dislike/DislikePostMutation.gql');
        $variables = [
            'id' => $post->id,
        ];
        $this->runGQL($mutation, $variables, $headers);
    }

    protected function tearDown(): void
    {
        $this->Tom->forceDelete();
        $this->Bob->forceDelete();
        $this->post->forceDelete();
        parent::tearDown();
    }
}
