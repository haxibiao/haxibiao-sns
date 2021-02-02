<?php

namespace Haxibiao\Sns\Tests\Feature\Api;

use App\Category;
use App\User;
use Tests\TestCase;

class ApiFollowTest extends TestCase
{
    protected $headers;
    protected $user;
    protected $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user     = User::factory()->create();
        $this->category = Category::factory(['user_id' => $this->user->id])->create();
        $this->headers  = [
            'Authorization' => 'Bearer ' . $this->user->token,
            'Accept'        => 'application/json',
        ];
    }

    protected function tearDown(): void
    {
        $this->category->forceDelete();
        $this->user->forceDelete();
        parent::tearDown();
    }

    public function testFollowApi()
    {
        //简单测试用户关注一个专题
        $response = $this->post("/api/follow/{$this->category->id}/categories", [
            "api_token" => $this->user->api_token,
        ]);
        $response->assertStatus(200);
    }
}
