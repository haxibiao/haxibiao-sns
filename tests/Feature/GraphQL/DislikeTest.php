<?php

namespace Haxibiao\Sns\Tests\Feature\GraphQL;

use App\Post;
use App\User;
use Haxibiao\Breeze\GraphQLTestCase;

class DislikeTest extends GraphQLTestCase
{
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

        $this->headers = [
            'Authorization' => 'Bearer ' . $this->Tom->token,
            'Accept'        => 'application/json',
        ];
    }

    protected function tearDown(): void
    {
        $this->Tom->forceDelete();
        $this->Bob->forceDelete();
        $this->post->forceDelete();
        parent::tearDown();
    }

    /**
     * @type users
     * @group dislike
     */
    public function testDislikeMutation()
    {

        $mutation  = file_get_contents(__DIR__ . '/dislike/DislikeMutation.gql');
        $variables = [
            'id' => $this->Bob->id,
        ];
        $this->runGuestGQL($mutation, $variables, $this->headers);
    }
    /**
     * @type posts
     * @group dislike
     */
    public function testDislikePostMutation()
    {
        $mutation  = file_get_contents(__DIR__ . '/dislike/DislikePostMutation.gql');
        $variables = [
            'id' => $this->post->id,
        ];
        $this->runGQL($mutation, $variables, $this->headers);
    }
}
