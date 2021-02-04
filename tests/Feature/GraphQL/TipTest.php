<?php

namespace Haxibiao\Sns\Tests\Feature\GraphQL;

use App\Article;
use App\Comment;
use App\Gold;
use App\User;
use App\Video;
use Haxibiao\Breeze\GraphQLTestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class TipTest extends GraphQLTestCase
{
    use DatabaseTransactions;
    protected $mutation;
    protected $query;

    protected $me;
    protected $lilya; // tipped user
    protected $video;
    protected $article;
    protected $comment;

    protected $headers;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mutation = file_get_contents(__DIR__ . '/Tip/TipMutation.graphql');
        $this->query    = file_get_contents(__DIR__ . '/Tip/TipQuery.graphql');

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

        $this->headers = [
            'Authorization' => 'Bearer ' . $this->me->api_token,
            'Accept'        => 'application/json',
        ];

    }

    /**
     * 打赏列表
     * @group tip
     * @group testTipQuery
     */
    public function testTipQuery()
    {
        $query = file_get_contents(__DIR__ . '/Tip/tipQuery.graphql');
        $headers = $this->getRandomUserHeaders($this->me);

        //articles
        $variables = [
            'tipable_id' => $this->article->id,
            'tipable_type' => 'articles',
            'count' => 1,
        ];
        $this->startGraphQL($query,$variables,$headers);

        //comments
        $variables = [
            'tipable_id' => $this->comment->id,
            'tipable_type' => 'comments',
            'count' => 1,
        ];
        $this->startGraphQL($query,$variables,$headers);
    }

    /**
     * 打赏
     * @group tip
     * @group testTipMutation
     */
    public function testTipMutation()
    {
        $query = file_get_contents(__DIR__ . '/Tip/tipMutation.graphql');
        $headers = $this->getRandomUserHeaders($this->me);

        // articles
        $variables = [
            'id' => $this->article->id,
            'type' => 'articles',
            'gold' => rand(10,30),
            'message' => '打赏articles',
        ];
        $this->startGraphQL($query,$variables,$headers);

        // TODO:打赏之后会将发送notify，评论打赏的notify不适应articles的
        // // comment
        // $variables = [
        //     'id' => $this->comment->id,
        //     'type' => 'comments',
        //     'gold' => rand(10,30),
        //     'message' => '打赏comment',
        // ];
        // $this->startGraphQL($query,$variables,$headers);
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
