<?php

namespace Haxibiao\Sns\Tests\Feature\Api;

use App\Article;
use App\User;
use Tests\TestCase;

class ApiCommentTest extends TestCase
{
    protected $headers;
    protected $user;
    protected $article;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user    = User::factory()->create();
        $this->article = Article::factory(['user_id' => $this->user->id])->create();
        $this->headers = [
            'Authorization' => 'Bearer ' . $this->user->token,
            'Accept'        => 'application/json',
        ];
    }

    protected function tearDown(): void
    {
        $this->user->forceDelete();
        $this->article->forceDelete();
        parent::tearDown();
    }

    public function testComment()
    {
        $user    = $this->user;
        $article = $this->article;

        $response = $this->json("POST", "/api/comment", [
            'api_token'         => $user->api_token,
            'body'              => 'test',
            'commentable_id'    => $article->id,
            'commentable_type'  => "articles",
            'is_new'            => true,
            'is_replay_comment' => false,
            'likes'             => 0,
            'lou'               => 1,
            'reports'           => 0,
            'time'              => time(),
        ]);

        $response->assertStatus(201);
        $content = $response->getOriginalContent();
        $response->assertJson([
            'body' => 'test',
        ]);
    }

}
