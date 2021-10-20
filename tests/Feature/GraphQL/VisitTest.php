<?php

use App\Collection;
use App\Movie;
use App\Post;
use App\User;
use Haxibiao\Breeze\GraphQLTestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class VisitTest extends GraphQLTestCase
{
    use DatabaseTransactions;
    protected $me;
    protected $postAuthor;
    protected $post1;
    protected $post2;
    protected $collection;
    protected $headers;
    protected $user;
    protected $movie;

    protected $addVisitMutation;
    protected $addVisitWithDurationMutation;
    protected $visitHistoryQuery;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->movie = Movie::factory()->create();

        $this->me         = User::factory()->create();
        $this->postAuthor = User::factory()->create();
        $this->post1      = Post::factory()->create();
        $this->post2      = Post::factory()->create();
        $this->collection = Collection::factory()->create();

        $this->post1->user_id = $this->postAuthor->id;
        $this->post2->user_id = $this->postAuthor->id;
        $this->post1->save();
        $this->post2->save();

        $this->headers = [
            "Authorization" => "Bearer " . $this->me->api_token,
            "accept"        => "application/json",
        ];

        $this->addVisitMutation             = file_get_contents(__DIR__ . '/Visit/addVisitMutation.graphql');
        $this->addVisitWithDurationMutation = file_get_contents(__DIR__ . '/Visit/addVisitWithDurationMutation.graphql');
        $this->visitHistoryQuery            = file_get_contents(__DIR__ . '/Visit/visitsHistoryQuery.graphql');
    }

    /**
     * 浏览记录
     * @group visit
     * @group testUserVisitsQuery
     */
    public function testUserVisitsQuery()
    {
        $query = file_get_contents(__DIR__ . '/Visit/userVisitsQuery.graphql');
        $headers = $this->getRandomUserHeaders($this->user);
        $vaiables = [
            'user_id' => $this->user->id,
        ];
        $this->startGraphQL($query,$vaiables,$headers);
    }

    /**
     * 浏览时长统计接口
     * @group visit
     * @group testAddVisitWithDurationMutation
     */
    public function testAddVisitWithDurationMutation()
    {
        $query = file_get_contents(__DIR__ . '/Visit/addVisitWithDurationMutation.graphql');
        $headers = $this->getRandomUserHeaders($this->user);

        // MOVIE
        $vaiables = [
            'id'        => $this->movie->id,
            'type'      => 'MOVIE',
            'duration'  => rand(1,10),
        ];
        $this->startGraphQL($query,$vaiables,$headers);

        // COLLECTION
        $vaiables = [
            'id'        => $this->collection->id,
            'type'      => 'COLLECTION',
            'duration'  => rand(1,10),
        ];
        $this->startGraphQL($query,$vaiables,$headers);

        // POST
        $vaiables = [
            'id'        => $this->post1->id,
            'type'      => 'POST',
            'duration'  => rand(1,10),
        ];
        $this->startGraphQL($query,$vaiables,$headers);
    }

    /**
     * @group visit
     * @group testAddVisitMutation
     */
    public function testAddVisitMutation()
    {
        $query = file_get_contents(__DIR__ . '/Visit/addVisitMutation.graphql');
        $headers = $this->getRandomUserHeaders($this->user);

        // POST
        $vaiables = [
            'visited_id'   => [$this->post1->id, $this->post2->id],
            'visited_type' => 'POST',
        ];

        // COLLECTION
        $vaiables = [
            'visited_id'   => [$this->collection->id],
            'visited_type' => 'COLLECTION',
        ];
        $this->startGraphQL($query,$vaiables,$headers);
    }

    /**
     * 我的浏览记录
     * @group vists
     * @group testVisitsHistoryQuery
     */
    public function testVisitsHistoryQuery()
    {
        $query = file_get_contents(__DIR__ . '/Visit/visitsHistoryQuery.graphql');
        $headers = $this->getRandomUserHeaders($this->user);

        // type => POST
        $vaiables = [
            'user_id'      => $this->user->id,
            'visitType'    => 'POST',
        ];
        $this->startGraphQL($query,$vaiables,$headers);

        // type => COLLECTION
        $vaiables = [
            'user_id'      => $this->user->id,
            'visitType'    => 'COLLECTION',
        ];
        $this->startGraphQL($query,$vaiables,$headers);
    }

    /**
     * 清空个人访问记录
     * @group visit
     * @group testCleanMyVisitsMutation
     */
    public function testCleanMyVisitsMutation()
    {
        $query = file_get_contents(__DIR__ . '/Visit/cleanMyVisitsMutation.graphql');
        $headers = $this->getRandomUserHeaders($this->user);
        $vaiables = [];
        $this->startGraphQL($query,$vaiables,$headers);
    }

    public function tearDown(): void
    {
        $this->me->forceDelete();
        $this->postAuthor->forceDelete();
        $this->post1->forceDelete();
        $this->post2->forceDelete();
        $this->collection->forceDelete();
        parent::tearDown();
    }
}
