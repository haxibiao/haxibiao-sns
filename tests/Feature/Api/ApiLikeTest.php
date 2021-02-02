<?php

namespace Haxibiao\Sns\Tests\Feature\Api;

use App\Article;
use App\User;
use Tests\TestCase;

class ApiLikeTest extends TestCase
{
    protected $user;
    protected $article;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user    = User::factory()->create();
        $this->article = Article::factory(['user_id' => $this->user->id])->create();
    }

    protected function tearDown(): void
    {
        $this->article->forceDelete();
        $this->user->forceDelete();
        parent::tearDown();
    }

    public function testLikeApi()
    {
        $user    = $this->user;
        $article = $this->article;

        $response = $this->post("/api/like/{$article->id}/article", [
            'api_token' => $user->api_token,
        ]);

        $response->assertStatus(200);
        $response->assertSeeText('liked');
    }

}
