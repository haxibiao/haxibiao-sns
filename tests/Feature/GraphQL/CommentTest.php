<?php


use App\Audit;
use App\Comment;
use App\Feedback;
use App\Post;
use App\Question;
use App\User;
use App\Video;
use Haxibiao\Base\GraphQLTestCase;
use Yansongda\Supports\Str;

class CommentTest extends GraphQLTestCase
{
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = factory(User::class)->create([
            'api_token' => str_random(60),
            'account'   => rand(10000000000, 99999999999),
        ]);
    }

    public function testCommentRepliesQuery()
    {
        $query     = file_get_contents(__DIR__ . '/gql/comment/CommentRepliesQuery.gql');
        $comment   = Comment::inRandomOrder()->first();
        $variables = [
            'id' => $comment->id,
        ];

        $this->runGQL($query, $variables, $this->getHeaders($this->user));
    }

    public function testCommentsQuery()
    {
        $query     = file_get_contents(__DIR__ . '/gql/comment/CommentsQuery.gql');
        $comment   = Comment::inRandomOrder()->first();
        $variables = [
            'type' => $comment->commentable_type,
            'id'   => $comment->commentable_id,
        ];

        $this->runGQL($query, $variables, $this->getHeaders($this->user));
    }

    public function testCreateCommentMutation()
    {
        $query = file_get_contents(__DIR__ . '/gql/comment/CreateCommentMutation.gql');

        $image     = file_get_contents(__DIR__ . '/gql/comment/image1');
        $num       = \random_int(1, 6);
        $data      = Video::first();
        $data_type = "videos";
        if ($num == 1) {
            $data      = Feedback::first();
            $data_type = "feedbacks";
        } else if ($num == 2) {
            $data      = Comment::first();
            $data_type = "comments";
        } else if ($num == 3) {
            $data      = Question::first();
            $data_type = "questions";

        } else if ($num == 4) {
            $data      = Audit::first();
            $data_type = "audit";

        } else if ($num == 5) {
            $data      = Post::first();
            $data_type = "posts";
        }
        if ($data_type == "comments") {
            $variables = [
                'content'    => Str::random(5),
                // 'images' => $image,
                'comment_id' => $data->id,
                'type'       => $data_type,
                'id'         => $data->id,
            ];
        } else {
            $variables = [
                'content' => Str::random(5),
                // 'images' => $image,
                'type'    => $data_type,
                'id'      => $data->id,
            ];
        }
        $this->runGQL($query, $variables, $this->getHeaders($this->user));
    }

    public function testDeleteCommentMutation()
    {
        $query     = file_get_contents(__DIR__ . '/gql/comment/deleteCommentMutation.gql');
        $comment   = Comment::first();
        $variables = [
            "id" => $comment->id,
        ];
        $this->runGQL($query, $variables, $this->getHeaders($this->user));
    }

    public function getHeaders($user)
    {
        $token = $user->api_token;

        $headers = [
            'token'         => $token,
            'Authorization' => 'Bearer ' . $token,
            'Accept'        => 'application/json',
        ];

        return $headers;
    }
}
