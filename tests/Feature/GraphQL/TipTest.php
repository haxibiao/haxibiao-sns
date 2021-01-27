<?php

namespace Haxibiao\Sns\Tests\Feature\GraphQL;

use App\User;
use App\Video;
use App\Article;
use App\Comment;
use App\Wallet;
use App\Gold;

use Haxibiao\Breeze\GraphQLTestCase;

class TipTest extends GraphQLTestCase
{

    protected $mutation;
    protected $query;

    protected $me;
    protected $lilya; // tipped user
    protected $video;
    protected $article;
    protected $comment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mutation = file_get_contents(__DIR__ . '/tip/TipMutation.gql');
        $this->query = file_get_contents(__DIR__ . '/tip/TipQuery.gql');

        $this->me = User::factory()->create();

        Gold::makeIncome($this->me,1000,"测试");

        $this->lilya = User::factory()->create();

        $this->video = Video::factory()->make([
            'user_id' => $this->lilya->id,
        ])->create();

        $this->article = Article::factory()->create();
        $this->article->user_id = $this->lilya->id;
        $this->article->save();


        $this->comment = Comment::factory()->create();
        $this->comment->user_id = $this->lilya->id;
        $this->comment->commentable_id = $this->article->id;
        $this->comment->commentable_type = "articles";
        $this->comment->save();
    }

    // Mutation Test

    /**
     * @Type POST
     * @group tip
     */
    public function testTipMutationWithTypePOST(): void
    {
        $headers = [
            'Authorization' => 'Bearer ' . $this->me->api_token,
            'Accept' => 'application/json',
        ];
        $variables = [
            'id' => $this->article->id,
            'type' => 'POST',
            'gold' => 1,
            'message' => "good job"
        ];
        $this->startGraphQL($this->mutation,$variables,$headers);
    }
    //@Type ISSUE
    protected function testTipMutationWithTypeISSUE(): void
    {
        $variables = [

        ];
    }
    /**
     * @Type COMMENT
     * @group tip
     */
    protected function testTipMutationWithTypeCOMMENT(): void
    {
        $headers = [
            'Authorization' => 'Bearer ' . $this->me->api_token,
            'Accept' => 'application/json',
        ];
        \info("aaaaa".$this->comment->id);
        $variables = [
            'id' => $this->comment->id,
            'type' => 'COMMENT',
            'gold' => 1,
            'message' => "good job"
        ];
        $this->startGraphQL($this->mutation,$variables,$headers);
    }

    // Query Test

    /**
     * @Type POST
     * @group tip
     */
    public function testTipQueryWithTypePOST(): void
    {
        $headers = [
            'Authorization' => 'Bearer ' . $this->me->api_token,
            'Accept' => 'application/json',
        ];
        $variables = [
            "id" => $this->article->id,
            "type" => "POST",
            "count" => 5,
        ];
        $this->startGraphQL($this->query, $variables, $headers);
    }
    /**
     * @Type ISSUE
     * @group tip
     */
    public function testTipQueryWithTypeISSUE(): void
    {
        $headers = [
            'Authorization' => 'Bearer ' . $this->me->api_token,
            'Accept' => 'application/json',
        ];
        $variables = [
            "id" => $this->article->id,
            "type" => "ISSUE",
            "count" => 5,
        ];
        $this->startGraphQL($this->query, $variables, $headers);
    }
    /**
     * @Type COMMENT
     * @group tip
     */
    public function testTipQueryWithTypeCOMMENT(): void
    {
        $headers = [
            'Authorization' => 'Bearer ' . $this->lilya->api_token,
            'Accept' => 'application/json',
        ];
        $variables = [
            "id" => $this->comment->id,
            "type" => "COMMENT",
            "count" => 5,
        ];
        $this->runGQL($this->query, $variables, $headers);
    }

    public function tearDown(): void
    {
        $this->video->forceDelete();
        $this->article->forceDelete();
        $this->lilya->forceDelete();
        $this->me->forceDelete();
        $this->comment->forceDelete();
        parent::tearDown();
    }
}
