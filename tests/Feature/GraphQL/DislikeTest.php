<?php

namespace Haxibiao\Sns\Tests\Feature\GraphQL;

use Haxibiao\Breeze\GraphQLTestCase;
use Haxibiao\Breeze\User;

class DislikeTest extends GraphQLTestCase
{
    //汤姆
    protected $Tom;

    //鲍勃
    protected $Bob;

    protected function setUp(): void
    {
        parent::setUp();

        $this->Tom = User::role(User::EDITOR_STATUS)->first(); //小编
        $this->Bob = User::role(User::ADMIN_STATUS)->first(); //管理

    }

    /**
     * 屏蔽用户
     * @group dislike
     */
    public function testDislikeMutation()
    {
        $token   = $this->Tom->api_token;
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Accept'        => 'application/json',
        ];
        $mutation  = file_get_contents(__DIR__ . '/dislike/DislikeMutation.gql');
        $variables = [
            'id' => $this->Bob->id,
        ];
        $this->runGuestGQL($mutation, $variables, $headers);
    }

    protected function tearDown(): void
    {
        // $this->Tom->forceDelete();
        // $this->Bob->forceDelete();
        parent::tearDown();
    }
}
