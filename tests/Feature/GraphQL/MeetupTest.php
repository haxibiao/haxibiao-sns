<?php
namespace Haxibiao\Sns\Tests\Feature\GraphQL;

use App\Article;
use App\User;
use Haxibiao\Breeze\GraphQLTestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class MeetupTest extends GraphQLTestCase
{
    use DatabaseTransactions;
    protected $user;
    protected $staff;
    protected $amdin;
    protected $meetup;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory([
            'ticket' => 100,
            'role_id' => User::USER_STATUS,
        ])->create();

        $this->staff = User::factory([
            'role_id' => User::STAFF_ROLE,
            'phone'   => '17425368947',
        ])->create();

        $this->admin = User::factory([
            'role_id' => User::ADMIN_STATUS,
        ])->create();

        $this->meetup = Article::factory([
            'title' => '约单11',
            'description' => '约单11',
            'user_id' => $this->staff->id,
            'json' => [
                'expires_at' => strtotime(date("Y-m-d H:m:s",strtotime("+1 day"))),
                'address' => '湖南长沙。',
                'images' => $this->getBase64ImageString(),
            ],
        ])->create();
    }

    /**
     * 创建约单
     * @group testCreateMeetupMutation
     * @group meetup
     */
    public function testCreateMeetupMutation()
    {
        $query = file_get_contents(__DIR__ . '/Meetup/createMeetupMutation.graphql');
        $headers = $this->getRandomUserHeaders($this->staff);
        $variables = [
            'title'       => '测试发布约单。。',
            'description' => '测试发布约单。。',
            'expires_at'  =>  date('Y-m-d H:i:s',strtotime('+1 day')),
            'address'     => '湖南长沙。。',
            'images'      => [
                $this->getBase64ImageString(),
            ],
        ];
        $this->startGraphQL($query, $variables, $headers);
    }

    /**
     * 更新约单
     * @group meetup
     * @group testUpdateMeetupMutation
     */
    public function testUpdateMeetupMutation()
    {
        $query = file_get_contents(__DIR__ . '/Meetup/updateMeetupMutation.graphql');
        $headers = $this->getRandomUserHeaders($this->staff);
        $variables = [
            'id' => $this->meetup->id,
            'title' => '修改约单。。',
            'description' => '修改约单。。',
            'expires_at' => date('Y-m-d H:i:s',strtotime('+1 day')),
            'address' => '湖南长沙',
            'imgaes' => [$this->getBase64ImageString()],
        ];
        $this->startGraphQL($query, $variables, $headers);
    }

    /**
     * 删除约单
     * @group testDeleteMeetupMutation
     * @group meetup
     */
    public function testDeleteMeetupMutation()
    {
        $query = file_get_contents(__DIR__ . '/Meetup/DeleteMeetupMutation.graphql');
        $headers = $this->getRandomUserHeaders($this->staff);
        $variables = [
            'id' => $this->meetup->id,
        ];
        $this->startGraphQL($query, $variables, $headers);
    }

    /**
     * 加入约单
     * @group meetup
     * @group testJoinMeetupMutation
     */
    public function testJoinMeetupMutation()
    {
        $query = file_get_contents(__DIR__ . '/Meetup/JoinMeetupMutation.graphql');
        $headers = $this->getRandomUserHeaders($this->user);
        $variables = [
            'id' => $this->meetup->id,
        ];
        $this->startGraphQL($query, $variables, $headers);
    }

    /**
     * 我参加的约单
     * @group meetup
     * @group testJoinedMeetupsQuery
     */
    public function testJoinedMeetupsQuery()
    {
        $query = file_get_contents(__DIR__ . '/Meetup/joinedMeetupsQuery.graphql');
        $headers = $this->getRandomUserHeaders($this->user);
        $variables = [];
        $this->startGraphQL($query,$variables,$headers);
    }


    /**
     * 约单详情
     * @group meetup
     * @group testMeetupQuery
     */
    public function testMeetupQuery()
    {
        $query = file_get_contents(__DIR__ . '/Meetup/MeetupQuery.graphql');
        $headers = $this->getRandomUserHeaders($this->staff);
        $variables = [
            'id' => $this->meetup->id,
        ];
        $this->startGraphQL($query, $variables, $headers);
    }

    /**
     * 约单列表
     * @group meetup
     * @group testMeepupsQuery
     */
    public function testMeepupsQuery()
    {
        $query = file_get_contents(__DIR__ . '/Meetup/MeetupsQuery.graphql');
        $headers = $this->getRandomUserHeaders($this->staff);
        $variables = [
            'user_id' => $this->staff->id,
        ];
        $this->startGraphQL($query, $variables, $headers);
    }
}
