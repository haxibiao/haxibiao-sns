<?php

namespace Haxibiao\Sns\Tests\Feature\GraphQL;

use App\Article;
use App\Comment;
use App\Feedback;use App\Post;
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
        // $this->question = Question::factory(['user_id' => $this->user->id])->create();
        $this->feedback = Feedback::factory(['user_id' => $this->user->id])->create();
    }

    /**
     * 添加评论
     * @group  comment
     * @group  testAddCommentMutation
     */
    public function testAddCommentMutation()
    {
        $auth    = $this->user;
        $user = User::factory()->create();
        $comment = Comment::factory()->create([
            'user_id'=>$user->id
        ]);
        $post = Post::factory()->create([
            'user_id'=>$user->id
        ]);
        $headers = $this->getRandomUserHeaders($auth);

        // 回复评论
        $query     = file_get_contents(__DIR__ . '/Comment/addCommentMutation.graphql');
        $variables = [
            'body' => '回复评论',
            'commentable_id'   => $comment->id,
            'commentable_type' => $comment->getMorphClass(),
        ];
        $this->startGraphQL($query, $variables, $headers);

        // 回复文章
        $variables = [
            'body' => '回复动态',
            'commentable_id'   => $post->id,
            'commentable_type' => $post->getMorphClass(),
        ];
        $this->startGraphQL($query, $variables, $headers);
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
        $query     = file_get_contents(__DIR__ . '/Comment/commentsQuery.graphql');
        $comment   = $this->comment;
        $variables = [
            'commentable_type' => $comment->commentable_type,
            'commentable_id'   => $comment->commentable_id,
        ];

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
