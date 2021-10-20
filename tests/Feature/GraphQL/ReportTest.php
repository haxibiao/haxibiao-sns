<?php

use App\Comment;
use App\Post;
use App\Question;
use App\User;
use Haxibiao\Breeze\GraphQLTestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ReportTest extends GraphQLTestCase
{
    use DatabaseTransactions;

    protected $mutation;
    protected $me;
    protected $bad_guy;
    protected $post;
    protected $comment;
    protected $question;

    protected $headers;

    protected function setUp(): void
    {
        parent::setUp();

        $this->me                        = User::factory()->create();
        $this->bad_guy                   = User::factory()->create();
        $this->post                      = Post::factory()->create();
        $this->comment                   = Comment::factory()->create();
        $this->comment->user_id          = $this->bad_guy->id;
        $this->comment->commentable_type = 'posts';
        $this->comment->save();

        $this->headers = [
            "Authorization" => "Bearer " . $this->me->api_token,
            "Accept"        => "application/json",
        ];

        $this->mutation = file_get_contents(__DIR__ . '/Report/reportMutation.graphql');
    }

    /**
     * @type USER
     * @group report
     * @group testReportMutationWithTypeUser
     */
    public function testReportMutationWithTypeUser()
    {
        $variables = [
            "id"     => $this->bad_guy->id,
            "type"   => "USER",
            "reason" => "you're a bad guy",
        ];
        $this->startGraphQL($this->mutation, $variables, $this->headers);
    }
    /**
     * @type COMMENT
     * @group report
     * @group testReportMutationWithTypeComment
     */
    public function testReportMutationWithTypeComment()
    {
        $variables = [
            "id"     => $this->comment->id,
            "type"   => "COMMENT",
            "reason" => "defraud",
        ];
        $this->startGraphQL($this->mutation, $variables, $this->headers);
    }
    /**
     * @type POST
     * @group report
     * @group testReportMutationWithTypePost
     */
    public function testReportMutationWithTypePost()
    {
        $variables = [
            "id"     => $this->post->id,
            "type"   => "POST",
            "reason" => "tendency of violence",
        ];
        $this->startGraphQL($this->mutation, $variables, $this->headers);
    }

    /**
     * 不感兴趣
     * @group report
     * @group testAddArticleBlockMutation
     */
    public function testAddArticleBlockMutation()
    {
        $query = file_get_contents(__DIR__ . '/Report/addArticleBlockMutation.graphql');
        $headers = $this->getRandomUserHeaders($this->me);
        $variables = [
            'id' => $this->post->id,
        ];
        $this->startGraphQL($query,$variables,$headers);
    }

     /**
     * 举报
     * @group report
     * @group testCreateReportMutation
     */
    public function testCreateReportMutation()
    {
        $query = file_get_contents(__DIR__ . '/Report/createReportMutation.graphql');
        $headers = $this->getRandomUserHeaders($this->me);
        $variables = [
            'id' => $this->post->id,
            'reason' => '举报动态。。',
            'type'   => 'posts',
        ];
        $this->startGraphQL($query,$variables,$headers);
    }
}
