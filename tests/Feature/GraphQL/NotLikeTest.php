<?php

namespace Haxibiao\Sns\Tests\Feature\GraphQL;

use Haxibiao\Base\GraphQLTestCase;
use Haxibiao\Base\User;

class NotLikeTest extends GraphQLTestCase
{
    //汤姆
    protected $Tom;

    //鲍勃
    protected $Bob;

    protected function setUp(): void
    {
        parent::setUp();
        $this->Tom = factory(User::class)->create([
            'api_token' => str_random(60),
        ]);

        $this->Bob = factory(User::class)->create([
            'api_token' => str_random(60),
        ]);

    }
    /* --------------------------------------------------------------------- */
    /* ------------------------------- Mutation ---------------------------- */
    /* --------------------------------------------------------------------- */

    //屏蔽用户
    public function testNotLikeMutation()
    {
        $token   = $this->Tom->api_token;
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Accept'        => 'application/json',
        ];
        $mutation  = file_get_contents(__DIR__ . '/gql/notLike/NotLikeMutation.gql');
        $variables = [
            'notlike_id' => $this->Bob->id,
        ];
        $this->runGuestGQL($mutation, $variables, $headers);
    }

    /* --------------------------------------------------------------------- */
    /* ------------------------------- Query ------------------------------- */
    /* --------------------------------------------------------------------- */

    protected function tearDown(): void
    {
        $this->Tom->forceDelete();
        $this->Bob->forceDelete();
        parent::tearDown();
    }
}
