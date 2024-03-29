<?php

namespace Haxibiao\Sns\Tests\Feature\GraphQL;

use App\Post;
use App\User;
use App\Collection;
use Haxibiao\Breeze\GraphQLTestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class FollowTest extends GraphQLTestCase
{
    use DatabaseTransactions;

    protected $star;
    protected $follower;
    protected $collection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->star      = User::factory()->create();
        $this->follower  = User::factory()->create();
        $this->collection  = Collection::factory()->create();
    }

    /**
     * 我关注的合集
     *
     * @group follow
     * @group testFollowedCollectionsQuery
     */
    public function testFollowedCollectionsQuery(){
        $headers = $this->getRandomUserHeaders($this->follower);
        $mutation  = file_get_contents(__DIR__ . '/Follow/followedCollectionsQuery.graphql');

        $variables = [
            "user_id"       => $this->star->id,
            "followed_type" => 'collections',
        ];
        $this->startGraphQL($mutation, $variables, $headers);
    }

    /**
     * 我的关注列表
     *
     * @group follow
     * @group testFollowedUsersQuery
     */
    public function testFollowedUsersQuery(){
        $headers = $this->getRandomUserHeaders($this->star);
        $mutation  = file_get_contents(__DIR__ . '/Follow/followedUsersQuery.graphql');

        $variables = [
            "user_id"       => $this->star->id,
        ];
        $this->startGraphQL($mutation, $variables, $headers);
    }

    /**
     * 我关注对象下的动态
     *
     * @group follow
     * @group testFollowPostsQuery
     */
    public function testFollowPostsQuery(){
        $headers = $this->getRandomUserHeaders($this->follower);
        $mutation  = file_get_contents(__DIR__ . '/Follow/followPostsQuery.graphql');

        $variables = [
            "user_id"       => $this->follower->id,
            "filter"        => 'normal',
        ];

        $this->startGraphQL($mutation, $variables, $headers);
    }

    /**
     * 切换关注状态（旧）
     *
     * @group follow
     * @group testFollowToggbleMutation
     */
    public function testFollowToggbleMutation()
    {
        $headers = $this->getRandomUserHeaders($this->follower);
        $mutation  = file_get_contents(__DIR__ . '/Follow/FollowToggleMutation.graphql');

        // 关注
        $variables = [
            "id"   => $this->star->id,
            "type" => $this->star->getMorphClass(),
        ];
        $this->startGraphQL($mutation, $variables, $headers);

        // 取消关注
        $variables = [
            "id"   => $this->star->id,
            "type" => $this->star->getMorphClass(),
        ];
        $this->startGraphQL($mutation, $variables, $headers);

        // 关注合集
        $variables = [
            "id"   => $this->collection->id,
            "type" => "collections",
        ];
        $this->startGraphQL($mutation, $variables, $headers);


    }

    /**
     * 切换关注状态（新,印象视频用）
     * @group follow
     * @group testToggleMutation
     */
    public function testToggleMutation()
    {
        $follower = $this->follower;
        $star     = $this->star;

        $headers = $this->getRandomUserHeaders($follower);
        $query     = file_get_contents(__DIR__ . '/Follow/toggleMutation.graphql');

        // 关注
        $variables = [
            "id"    => $star->id,
            "type"  => 'users',
        ];
        $this->startGraphQL($query, $variables,$headers);

        // 取消关注
        $variables = [
            "id"    => $star->id,
            "type"  => 'users',
        ];
        $this->startGraphQL($query, $variables,$headers);
    }

    /**
     * 我的粉丝列表
     * @group follow
     * @group testUserFollowersQuery
     */
    public function testUserFollowersQuery()
    {
        $headers = $this->getRandomUserHeaders($this->star);
        $query     = file_get_contents(__DIR__ . '/Follow/userFollowersQuery.graphql');
        $variables = [
            "user_id" => $this->star->id
        ];
        $this->startGraphQL($query, $variables,$headers);
    }

}
