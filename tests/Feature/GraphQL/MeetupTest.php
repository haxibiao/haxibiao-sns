<?php

namespace Haxibiao\Sns\Tests\Feature\GraphQL;

use App\Meetup;
use App\User;
use App\Video;
use Haxibiao\Breeze\GraphQLTestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class MeetupTest extends GraphQLTestCase
{
    use DatabaseTransactions;

    private $video;
    private $user;
    private $meetups;
    private $faker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->faker = \Faker\Factory::create('zh_CN');
        $this->user  = User::factory()->create();
        $this->video = Video::factory()->create();
        $this->meetups = Meetup::factory(5)->create([
            'user_id' => $this->user->id
        ]);
    }
    /**
     *
     * @group meetup
     * @group testLinkMeetupWhenCreatingPostMutation
     * @test
     */
    public function testLinkMeetupWhenCreatingPostMutation()
    {
        $query   = file_get_contents(__DIR__ . '/Post/createPostContentMutation.graphql');
        $video = $this->video;

        $variables = [
            'video_id' => $video->id,
            'body'     => '测试创建创建视频动态',
            'meet_id'  => data_get($this->meetups,'1.id'),
        ];
        $this->startGraphQL($query, $variables, [
            'Authorization' => 'Bearer ' . $this->user->api_token,
        ]);
    }

    /**
     *
     * @group meetup
     * @group testCreateMeetupMutation
     * @test
     */
    public function testCreateMeetupMutation()
    {
        $query = file_get_contents(__DIR__ . '/Meetup/CreateMeetupMutation.graphql');
        $variables = [
            'title'         => $this->faker->text(50),
            'introduction'  => $this->faker->text(100),
            'phone'         => $this->faker->phoneNumber,
            'wechat'        => $this->faker->phoneNumber,
            'images'        => [
                $this->getBase64ImageString()
            ]
        ];
        $this->startGraphQL($query,$variables,[
            'Authorization' => 'Bearer '.$this->user->api_token
        ]);
    }

    /**
     *
     * @group meetup
     * @group testUpdateMeetupMutation
     * @test
     */
    public function testUpdateMeetupMutation(){

        $query = file_get_contents(__DIR__ . '/Meetup/UpdateMeetupMutation.graphql');
        $variables = [
            'id'    => data_get($this->meetups,'0.id'),
            'title' => $this->faker->text(50),
        ];
        $this->startGraphQL($query,$variables,[
            'Authorization' => 'Bearer '.$this->user->api_token
        ]);
    }

    /**
     *
     * @group meetup
     * @group testDeleteMeetupMutation
     * @test
     */
    public function testDeleteMeetupMutation(){
        $query = file_get_contents(__DIR__ . '/Meetup/DeleteMeetupMutation.graphql');
        $variables = [
            'id' => data_get($this->meetups,'0.id'),
        ];
        $this->startGraphQL($query,$variables,[
            'Authorization' => 'Bearer '.$this->user->api_token
        ]);
    }

    /**
     *
     * @group meetup
     * @group testMeetupsQuery
     * @test
     */
    public function testMeetupsMutation(){
        $query = file_get_contents(__DIR__ . '/Meetup/MeetupsQuery.graphql');
        $variables = [
            'user_id' => $this->user->id,
        ];
        $this->startGraphQL($query,$variables,[]);
    }

}
