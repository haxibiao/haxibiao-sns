<?php

namespace Haxibiao\Sns\Tests\Feature\GraphQL;

use App\Chat;
use App\Message;
use App\User;
use Haxibiao\Breeze\GraphQLTestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ChatTest extends GraphQLTestCase
{
    use DatabaseTransactions;
    protected $chat;
    protected $user;
    protected $withUser;
    protected $message;

    protected function setUp(): void
    {
        parent::setUp();
        $this->chat = Chat::factory()->create();
        $this->user = User::factory()->create();
        $this->withUser = User::factory()->create();
        $this->message  = Message::factory()->create();
    }

    /**
     * 聊天记录
     * @group chat
     * @group testChasTest
     */
    public function testChasTest()
    {
        $query = file_get_contents(__DIR__ . '/Chat/chatsQuery.graphql');
        $headers = $this->getRandomUserHeaders($this->user);
        $variables = [
            'user_id' => $this->user->id,
        ];
        $this->startGraphQL($query,$variables,$headers);
    }

    /**
     * 创建聊天
     * @group chat
     * @group testCreateChatMutation
     */
    public function testCreateChatMutation()
    {
        $query = file_get_contents(__DIR__ . '/Chat/createChatMutation.graphql');
        $headers = $this->getRandomUserHeaders($this->user);
        $variables = [
            'with_user_id' => $this->withUser->id,
        ];
        $this->startGraphQL($query,$variables,$headers);
    }

    /**
     * 消息列表
     * @group chat
     * @group testMessagesQuery
     */
    public function testMessagesQuery()
    {
        $query = file_get_contents(__DIR__ . '/Chat/messagesQuery.graphql');
        $headers = $this->getRandomUserHeaders($this->user);
        $variables = [
            'chat_id' => $this->chat->id,
        ];
        $this->startGraphQL($query,$variables,$headers);
    }

    /**
     * 发消息
     * @group chat
     * @group testSendMessageMutation
     */
    public function testSendMessageMutation()
    {
        $query = file_get_contents(__DIR__ . '/Chat/sendMessageMutation.graphql');
        $headers = $this->getRandomUserHeaders($this->user);
        $variables = [
            'chat_id' => $this->chat->id,
            'user_id' => $this->user->id,
            'message' => '发消息了。。。',
        ];
        $this->startGraphQL($query,$variables,$headers);
    }
}