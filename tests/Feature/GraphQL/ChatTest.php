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
	protected $participants;
	protected $message;
	protected $faker;

	protected function setUp(): void
	{
		parent::setUp();
		$this->faker = \Faker\Factory::create('zh_CN');
		$this->user = User::factory()->create();
		$this->participants = User::factory(5)->create();
		$this->chat = Chat::factory()->create([
			'user_id' => $this->user->id,
			'uids'    => array_pluck($this->participants,'id'),
		]);
        $this->message  = Message::factory()->create([
        	'chat_id' => $this->chat->id,
			'user_id' => $this->user->id,
			'body'    => $this->faker->text(50)
		]);
	}

	/**
	 * 发起聊天
	 * @group chat
	 * @group testCreateChatMutation
	 */
	public function testCreateChatMutation()
	{
		$uids = array_pluck($this->participants,'id');
		$query = file_get_contents(__DIR__ . '/Chat/CreateChatMutation.graphql');;
		$variables = [
			'uids' => $uids,
		];
		$this->startGraphQL($query,$variables,[
			'Authorization'=>'Bearer '.$this->user->api_token
		]);
	}

	/**
	 * 聊天记录列表
	 * @group chat
	 * @group testChatsQuery
	 */
	public function testChatsQuery()
	{
		$query   = file_get_contents(__DIR__ . '/Chat/ChatsQuery.graphql');
		$headers = $this->getRandomUserHeaders($this->user);
		$variables = [
			'user_id' => $this->user->id,
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
		$query = file_get_contents(__DIR__ . '/Chat/MessagesQuery.graphql');
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
		$query = file_get_contents(__DIR__ . '/Chat/SendMessageMutation.graphql');
		$headers = $this->getRandomUserHeaders($this->user);
		$variables = [
			'chat_id' => $this->chat->id,
			'user_id' => $this->user->id,
			'message' => '发消息了。。。',
		];
		$this->startGraphQL($query,$variables,$headers);
	}

	/**
	 * 更新群聊
	 * @group chat
	 * @group testUpdateChatMutation
	 */
	public function testUpdateChatMutation()
	{
		$query = file_get_contents(__DIR__ . '/Chat/UpdateChatMutation.graphql');
		$headers = $this->getRandomUserHeaders($this->user);
		$variables = [
			'chat_id' => $this->chat->id,
			'subject' => $this->faker->title
		];
		$this->startGraphQL($query,$variables,$headers);
	}

	/**
	 * 邀请用户
	 * @group chat
	 * @group testAddParticipantsInGroupChatMutation
	 */
	public function testAddParticipantsInGroupChatMutation()
	{
		$newParticipants = User::factory(5)->create();
		$query = file_get_contents(__DIR__ . '/Chat/AddParticipantsInGroupChatMutation.graphql');
		$headers = $this->getRandomUserHeaders($this->user);
		$variables = [
			'chat_id' => $this->chat->id,
			'uids'    => array_pluck($newParticipants,'id')
		];
		$this->startGraphQL($query,$variables,$headers);
	}

	/**
	 * 移除用户
	 * @group chat
	 * @group testRemoveParticipantsInGroupChatMutation
	 */
	public function testRemoveParticipantsInGroupChatMutation()
	{
		$query = file_get_contents(__DIR__ . '/Chat/RemoveParticipantsInGroupChatMutation.graphql');
		$headers = $this->getRandomUserHeaders($this->user);
		$variables = [
			'chat_id' => $this->chat->id,
			'uids'    => $this->participants->slice(0, 2)->pluck('id')->toArray()
		];
		$this->startGraphQL($query,$variables,$headers);
	}
}
