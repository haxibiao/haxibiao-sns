<?php

namespace Haxibiao\Sns\Tests\Feature\Api;

use App\Chat;
use App\User;
use Tests\TestCase;

class ApiChatTest extends TestCase
{

    protected $Tom;
    protected $Bob;
    protected $chat;

    protected function setUp(): void
    {
        parent::setUp();
        $this->Tom  = User::factory()->create();
        $this->Bob  = User::factory()->create();
        $this->chat = Chat::factory([
            'uids' => json_encode([$this->Tom->id, $this->Bob->id]),
        ])->create();
        $this->headers = [
            'Authorization' => 'Bearer ' . $this->Tom->token,
            'Accept'        => 'application/json',
        ];
    }

    protected function tearDown(): void
    {
        $this->Tom->forceDelete();
        $this->Bob->forceDelete();
        $this->chat->forceDelete();
        parent::tearDown();
    }

    /**
     * 测试查看聊天列表
     *
     * @group api
     * @group chat
     */
    public function testChats()
    {
        $response = $this->get("/api/notification/chats", [
            'api_token' => $this->Tom->api_token,
        ]);
        $response->assertStatus(302);
    }

    //测试聊天消息能否正常发出
    public function testChatMassage()
    {
        $chat    = \App\Chat::orderBy('id', 'desc')->take(1)->first();
        $uids    = json_decode($chat->uids);
        $user_id = $uids[0];

        $user = \App\User::find($user_id);

        $response = $this->json("POST", "/api/notification/chat/$chat->id/send", [
            'api_token' => $user->api_token,
            'message'   => 'test',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'test',
        ]);
    }

}
