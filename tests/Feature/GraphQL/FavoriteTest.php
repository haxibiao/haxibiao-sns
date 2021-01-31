<?php

namespace Haxibiao\Sns\Tests\Feature\GraphQL;

use App\Article;
use App\Category;
use App\Comment;
use App\Feedback;
use App\Post;
use App\Question;
use App\User;
use Haxibiao\Breeze\GraphQLTestCase;

class FavoriteTest extends GraphQLTestCase
{
    protected $category;
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
        $this->category = Category::factory()->create();
        $this->article  = Article::factory(['user_id' => $this->user->id])->create();
        $this->comment  = Comment::factory(['user_id' => $this->user->id])->create();
        $this->post     = Post::factory(['user_id' => $this->user->id])->create();
        $this->question = Question::factory([
            'user_id'     => $this->user->id,
            'category_id' => $this->category->id,
        ])->create();
        $this->feedback = Feedback::factory(['user_id' => $this->user->id])->create();
    }

    public function testToggleFavoriteMutation()
    {
        $query     = file_get_contents(__DIR__ . '/favorite/ToggleFavoriteMutation.gql');
        $variables = [
            "id"   => 1,
            "type" => "QUESTION",
        ];
        $this->runGQL($query, $variables);
    }

    public function testFavoritesQuery()
    {
        $query     = file_get_contents(__DIR__ . '/favorite/FavoritesQuery.gql');
        $variables = [
            "type" => "QUESTION",
        ];
        $this->runGQL($query, $variables);
    }
}
