<?php

namespace Haxibiao\Sns\Tests\Feature\Api;

use App\Article;
use App\Comment;
use App\User;
use Tests\TestCase;

class ApiCommentTest extends TestCase
{
    protected $headers;
    protected $user;
    protected $article;
    protected $bob;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user    = User::factory()->create();
        $this->bob     = User::factory()->create();

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

    /**
     * @group testCommentApi
     */
    public function testCommentApi()
    {
        $user    = $this->user;
        $article = $this->article;
        $comment = New Comment();
        $comment->body = 'test';
        $comment->commentable_id = $article->id;
        $comment->commentable_type = 'articles';
        $data = $comment->toArray();
        $response = $this->actingAs($user,'api')->json("POST", "/api/comment", $data,['Authorization' => 'Bearer' . $user->token]);
        $response->assertStatus(201);
    }
}
