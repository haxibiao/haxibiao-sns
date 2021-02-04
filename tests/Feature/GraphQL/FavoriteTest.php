<?php

namespace Haxibiao\Sns\Tests\Feature\GraphQL;

use App\Category;
use App\Feedback;
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
    }

    protected function tearDown(): void
    {
        $this->question->forceDelete();
        $this->category->forceDelete();
        $this->user->forceDelete();
        parent::tearDown();
    }

    public function testToggleFavoriteMutation()
    {
        $query     = file_get_contents(__DIR__ . '/favorite/ToggleFavoriteMutation.gql');
        $variables = [
            "id"   => $this->question->id,
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
