<?php

namespace Haxibiao\Sns\Tests\Feature\GraphQL;

use App\Article;
use App\Comment;
use App\Feedback;
use App\Post;
use App\Question;
use App\User;
use Haxibiao\Breeze\GraphQLTestCase;
use Illuminate\Support\Str;

class CommentTest extends GraphQLTestCase
{
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

    public function testCommentRepliesQuery()
    {
        $query     = file_get_contents(__DIR__ . '/comment/CommentRepliesQuery.gql');
        $comment   = $this->comment;
        $variables = [
            'id' => $comment->id,
        ];

        $this->runGQL($query, $variables, $this->getHeaders($this->user));
    }

    public function testCommentsQuery()
    {
        $query     = file_get_contents(__DIR__ . '/comment/CommentsQuery.gql');
        $comment   = $this->comment;
        $variables = [
            'type' => $comment->commentable_type,
            'id'   => $comment->commentable_id,
        ];

        $this->runGQL($query, $variables, $this->getHeaders($this->user));
    }

    public function testCreateCommentMutation()
    {
        $query = file_get_contents(__DIR__ . '/comment/CreateCommentMutation.gql');
        $image = file_get_contents(__DIR__ . '/comment/image1'); //TODO: 还未正式测试评论带图片
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
        $this->runGQL($query, $variables, $this->getHeaders($this->user));
    }

    public function testDeleteCommentMutation()
    {
        $query     = file_get_contents(__DIR__ . '/comment/deleteCommentMutation.gql');
        $comment   = $this->comment;
        $variables = [
            "id" => $comment->id,
        ];
        $this->runGQL($query, $variables, $this->getHeaders($this->user));
    }

}
