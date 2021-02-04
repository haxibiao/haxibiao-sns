<?php

namespace Haxibiao\Sns\Tests\Feature\GraphQL;

use App\Article;
use App\Comment;
use App\Feedback;
use App\Post;
use App\Question;
use App\User;
use Haxibiao\Breeze\GraphQLTestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;

class CommentTest extends GraphQLTestCase
{
    use DatabaseTransactions;

    protected $user;
    protected $comment;
    protected $article;
    protected $post;
    protected $question;
    protected $feedback;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory([
            'api_token' => str_random(60),
            'account'   => rand(10000000000, 99999999999),
        ])->create();
        $this->article = Article::factory([
            'user_id' => $this->user->id,
        ])->create();
        $this->comment  = Comment::factory(['user_id' => $this->user->id])->create();
        $this->post     = Post::factory(['user_id' => $this->user->id])->create();
        $this->question = Question::factory(['user_id' => $this->user->id])->create();
        $this->feedback = Feedback::factory(['user_id' => $this->user->id])->create();
    }

    protected function tearDown(): void
    {
        $this->comment->forceDelete();
        $this->post->forceDelete();
        $this->question->forceDelete();
        $this->feedback->forceDelete();

        $this->article->forceDelete();
        $this->user->forceDelete();
        parent::tearDown();
    }

    /**
     * 评论的回复
     *
     * @group  comment
     * @group  testCommentRepliesQuery
     */
    public function testCommentRepliesQuery()
    {
        Comment::factory()->create([
            'comment_id' => $this->comment->id
        ]);

        $query     = file_get_contents(__DIR__ . '/Comment/commentRepliesQuery.graphql');
        $variables = [
            'id' => $this->comment->id,
        ];
        $this->startGraphQL($query, $variables, $this->getHeaders($this->user));
    }
    /**
     * 评论列表
     *
     * @group  comment
     * @group  testCommentsQuery
     */
    public function testCommentsQuery()
    {
        $query     = file_get_contents(__DIR__ . '/Comment/CommentsQuery.graphql');
        $comment   = $this->comment;
        $variables = [
            'commentable_type' => $comment->commentable_type,
            'commentable_id'   => $comment->commentable_id,
        ];

        $this->startGraphQL($query, $variables, $this->getHeaders($this->user));
    }
    /**
     * 创建评论
     *
     * @group  comment
     * @group  testCreateCommentMutation
     */
    public function testCreateCommentMutation()
    {
        $query = file_get_contents(__DIR__ . '/Comment/CreateCommentMutation.graphql');
        $image = $this->getBase64ImageString();
        $num   = \random_int(1, 4);

        $data_type = "articles";
        $data_id   = $this->article->id;
        if ($num == 1) {
            $data_type = "feedbacks";
            $data_id   = $this->feedback->id;
        } else if ($num == 2) {
            $data_type = "comments";
            $data_id   = $this->comment->id;
        } else if ($num == 3) {
            $data_type = "questions";
            $data_id   = $this->question->id;
        } else if ($num == 4) {
            $data_type = "posts";
            $data_id   = $this->post->id;
        }
        if ($data_type == "comments") {
            $variables = [
                'content'    => Str::random(5),
                // 'images' => $image,
                'comment_id' => $this->comment->id, //评论楼中楼
                'type'       => $data_type,
                'id'         => $data_id,
            ];
        } else {
            $variables = [
                'content' => Str::random(5),
                // 'images' => $image,
                'type'    => $data_type,
                'id'      => $data_id,
            ];
        }
        $this->startGraphQL($query, $variables, $this->getHeaders($this->user));
    }
    /**
     * 删除评论
     *
     * @group  comment
     * @group  testDeleteCommentMutation
     */
    public function testDeleteCommentMutation()
    {
        $query     = file_get_contents(__DIR__ . '/Comment/deleteCommentMutation.graphql');
        $comment   = $this->comment;
        $variables = [
            "id" => $comment->id,
        ];
        $this->startGraphQL($query, $variables, $this->getHeaders($this->user));
    }

}
