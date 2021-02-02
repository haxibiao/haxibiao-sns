<?php

use App\Collection;
use App\Post;
use App\User;
use Haxibiao\Breeze\GraphQLTestCase;

class VisitTest extends GraphQLTestCase
{

    protected $me;
    protected $postAuthor;
    protected $post1;
    protected $post2;
    protected $collection;
    protected $headers;

    protected $addVisitMutation;
    protected $addVisitWithDurationMutation;
    protected $visitHistoryQuery;

    public function setUp(): void
    {
        parent::setUp();

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

        $this->addVisitMutation             = file_get_contents(__DIR__ . '/visit/addVisitMutation.graphql');
        $this->addVisitWithDurationMutation = file_get_contents(__DIR__ . '/visit/addVisitWithDurationMutation.graphql');
        $this->visitHistoryQuery            = file_get_contents(__DIR__ . '/visit/visitsHistoryQuery.graphql');
    }

    //MUTATION TEST

    /**
     * @type POST
     * @group visit
     */
    public function testAddVisitMutationWithTypePost()
    {
        $vaiables = [
            "ids"  => [$this->post1->id, $this->post2->id],
            "type" => "POST",
        ];
        $this->startGraphQL($this->addVisitMutation, $vaiables, $this->headers);
    }
    /**
     * @type COLLECTION
     * @group visit
     */
    protected function testAddVisitMutationWithTypeCollection()
    {
        $vaiables = [
            "ids"  => $this->collection->id,
            "type" => "COLLECTION",
        ];
        $this->startGraphQL($this->addVisitMutation, $vaiables, $this->headers);
    }
    /**
     * @type POST
     * @group visit
     */
    public function testAddVisitMutationWithDurationUnderTypePost()
    {
        $vaiables = [
            "ids"      => $this->post1->id,
            "type"     => "POST",
            "duration" => random_int(5, 20),
        ];
        $this->startGraphQL($this->addVisitMutation, $vaiables, $this->headers);
    }
    /**
     * @type COLLECTION
     * @group visit
     */
    protected function testAddVisitMutationWithDurationUnderTypeCollection()
    {
        $vaiables = [
            "ids"      => $this->collection->id,
            "type"     => "COLLECTION",
            "duration" => random_int(5, 20),
        ];
        $this->startGraphQL($this->addVisitMutation, $vaiables, $this->headers);
    }

    //QUERY TEST

    /**
     * @type POST
     * @group visit
     */
    public function testVisitHistoriesQueryWithTypePost()
    {
        $vaiables = [
            "id"   => $this->postAuthor->id,
            "type" => "POST",
        ];
        $this->startGraphQL($this->visitHistoryQuery, $vaiables, $this->headers);
    }
    /**
     * @type COLLECTION
     * @group visit
     */
    public function testVisitHistoriesQueryWithTypeCollection()
    {
        $vaiables = [
            "id"   => $this->postAuthor->id,
            "type" => "COLLECTION",
        ];
        $this->startGraphQL($this->visitHistoryQuery, $vaiables, $this->headers);
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
