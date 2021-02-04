<?php

namespace Haxibiao\Sns\Tests\Feature\GraphQL;

use Haxibiao\Breeze\GraphQLTestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class FollowTest extends GraphQLTestCase
{
    use DatabaseTransactions;

    /**
     * @group like
     */
    public function testFollowToggbleMutation()
    {
        $mutation  = file_get_contents(__DIR__ . '/follow/mutation/FollowToggleMutation.gql');
        $variables = [
            "id"   => 1,
            "type" => "users",
        ];
        $this->runGQL($mutation, $variables);
    }

    /**
     * @group like
     */
    public function testFollowersQuery()
    {
        $query     = file_get_contents(__DIR__ . '/follow/query/FollowersQuery.gql');
        $variables = [
            "user_id" => 1,
            "filter"  => "users",
        ];
        $this->runGQL($query, $variables);
    }

    /**
     * @group like
     */
    public function testFollowsQuery()
    {
        $query     = file_get_contents(__DIR__ . '/follow/query/FollowsQuery.gql');
        $variables = [
            "user_id" => 1,
            "filter"  => "users",
        ];
        $this->runGQL($query, $variables);
    }
}
