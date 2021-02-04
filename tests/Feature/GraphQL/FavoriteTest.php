<?php

namespace Haxibiao\Sns\Tests\Feature\GraphQL;

use App\Article;
use App\Category;
use App\Feedback;
use App\Post;
use App\Question;
use App\User;
use Haxibiao\Breeze\GraphQLTestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class FavoriteTest extends GraphQLTestCase
{
    use DatabaseTransactions;

    protected $category;
    protected $user;
    protected $question;
    protected $article;
    protected $post;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory([
            'api_token' => str_random(60),
            'account'   => rand(10000000000, 99999999999),
        ])->create();
        $this->category = Category::factory()->create();
        $this->question = Question::factory([
            'user_id'     => $this->user->id,
            'category_id' => $this->category->id,
        ])->create();
        $this->feedback = Feedback::factory(['user_id' => $this->user->id])->create();
        $this->article = Article::factory()->create();
        $this->post = Post::factory()->create();
    }

    /**
     * 收藏文章
     * @group favorite
     * @group testFavoriteArticleMutation
     */
    public function testFavoriteArticleMutation()
    {
        $query = file_get_contents(__DIR__ . '/Favorite/favoriteArticleMutation.graphql');
        $headers = $this->getRandomUserHeaders($this->user);
        $variables = [
            'article_id' => $this->article->id,
        ];
        $this->startGraphQL($query,$variables,$headers);
    }

    /**
     * 收藏
     * @group favorite
     * @group testToggleFavoriteMutation
     */
    public function testToggleFavoriteMutation()
    {
        $query     = file_get_contents(__DIR__ . '/Favorite/ToggleFavoriteMutation.graphql');
        $headers = $this->getRandomUserHeaders($this->user);

        // QUESTION
        $variables = [
            "id"   => $this->question->id,
            "type" => "QUESTION",
        ];
        $this->startGraphQL($query, $variables,$headers);

        // POSTS
        $variables = [
            "id"   => $this->post->id,
            "type" => "POSTS",
        ];
        $this->startGraphQL($query, $variables,$headers);

        // ARTICLE
        $variables = [
            "id"   => $this->article->id,
            "type" => "ARTICLE",
        ];
        $this->startGraphQL($query, $variables,$headers);
    }

    /**
     * 收藏的影视
     * @group favorite
     * @group testFavoritedMoviesQuery
     */
    public function testFavoritedMoviesQuery()
    {
        $query = file_get_contents(__DIR__ . '/Favorite/favoritedMoviesQuery.graphql');
        $headers = $this->getRandomUserHeaders($this->user);
        $variables = [
            'user_id' => $this->user->id,
            'type'    => 'movies'
        ];
        $this->startGraphQL($query,$variables,$headers);
    }

    /**
     * 收藏记录
     * @group favorite
     * @group testFavoritesQuery
     */
    public function testFavoritesQuery()
    {
        $query     = file_get_contents(__DIR__ . '/Favorite/favoritesQuery.graphql');
        $headers = $this->getRandomUserHeaders($this->user);

        // QUESTION
        $variables = [
            "type" => "QUESTION",
        ];
        $this->startGraphQL($query, $variables,$headers);

        // POSTS
        $variables = [
            "type" => "POSTS",
        ];
        $this->startGraphQL($query, $variables,$headers);

        // ARTICLE
        $variables = [
            "type" => "ARTICLE",
        ];
        $this->startGraphQL($query, $variables,$headers);
    }

    protected function tearDown(): void
    {
        $this->question->forceDelete();
        $this->category->forceDelete();
        $this->user->forceDelete();
        parent::tearDown();
    }
}
