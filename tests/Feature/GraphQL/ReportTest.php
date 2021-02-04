<?php

use App\Comment;
use App\Post;
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

        $this->mutation = file_get_contents(__DIR__ . '/report/ReportMutation.gql');
    }

    protected function tearDown(): void
    {
        $this->me->forceDelete();
        $this->bad_guy->forceDelete();
        $this->post->forceDelete();
        parent::tearDown();
    }

    /**
     * @type USER
     * @group report
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
}
