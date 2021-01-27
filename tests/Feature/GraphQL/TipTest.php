<?php

namespace Haxibiao\Sns\Tests\Feature\GraphQL;

use App\Article;
use App\Comment;
use App\Gold;
use App\User;
use App\Video;
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
        $this->query    = file_get_contents(__DIR__ . '/tip/TipQuery.gql');

        $this->me = User::factory()->create();

        Gold::makeIncome($this->me, 1000, "测试");

        $this->lilya = User::factory()->create();

        $this->video = Video::factory()->make([
            'user_id' => $this->lilya->id,
        ])->create();

        $this->article          = Article::factory()->create();
        $this->article->user_id = $this->lilya->id;
        $this->article->save();

        $this->comment = Comment::factory([
            'user_id'          => $this->lilya->id,
            'commentable_id'   => $this->article->id,
            'commentable_type' => "articles",
        ])->create();

    }

    // Mutation Test

    /**
     * 打赏文章
     * @group tip
     */
    public function testTipMutationWithTypePost(): void
    {
        $headers = [
            'Authorization' => 'Bearer ' . $this->me->api_token,
            'Accept'        => 'application/json',
        ];
        $variables = [
            'id'      => $this->article->id,
            'type'    => 'articles',
            'gold'    => 1,
            'message' => "good job",
        ];
        $this->startGraphQL($this->mutation, $variables, $headers);
    }

    /**
     * @Type COMMENT
     * @group tip
     */
    protected function testTipMutationWithTypeComment(): void
    {
        $headers = [
            'Authorization' => 'Bearer ' . $this->me->api_token,
            'Accept'        => 'application/json',
        ];
        \info("aaaaa" . $this->comment->id);
        $variables = [
            'id'      => $this->comment->id,
            'type'    => 'COMMENT',
            'gold'    => 1,
            'message' => "good job",
        ];
        $this->startGraphQL($this->mutation, $variables, $headers);
    }

    // Query Test

    /**
     * @Type POST
     * @group tip
     */
    public function testTipQueryWithTypePost(): void
    {
        $headers = [
            'Authorization' => 'Bearer ' . $this->me->api_token,
            'Accept'        => 'application/json',
        ];
        $variables = [
            "id"    => $this->article->id,
            "type"  => "POST",
            "count" => 5,
        ];
        $this->startGraphQL($this->query, $variables, $headers);
    }

    /**
     * @Type ISSUE
     * @group tip
     */
    public function testTipQueryWithTypeIssue(): void
    {
        $headers = [
            'Authorization' => 'Bearer ' . $this->me->api_token,
            'Accept'        => 'application/json',
        ];
        $variables = [
            "id"    => $this->article->id,
            "type"  => "ISSUE",
            "count" => 5,
        ];
        $this->startGraphQL($this->query, $variables, $headers);
    }

    /**
     * @Type COMMENT
     * @group tip
     */
    public function testTipQueryWithTypeComment(): void
    {
        $headers = [
            'Authorization' => 'Bearer ' . $this->lilya->api_token,
            'Accept'        => 'application/json',
        ];
        $variables = [
            "id"    => $this->comment->id,
            "type"  => "COMMENT",
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
