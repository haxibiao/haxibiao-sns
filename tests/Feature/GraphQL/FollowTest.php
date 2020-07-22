<?php


use Haxibiao\Base\GraphQLTestCase;

class FollowTest extends GraphQLTestCase
{

    /**
     * @group like
     */

    public function testFollowToggbleMutation()
    {
        $mutation  = file_get_contents(__DIR__ . '/gql/follow/mutation/FollowToggbleMutation.gql');
        $variables = [
            "id"   => 1,
            "type" => "users",
        ];
        $this->runGQL($mutation, $variables);
    }

    public function testFollowersQuery()
    {
        $query     = file_get_contents(__DIR__ . '/gql/follow/query/FollowersQuery.gql');
        $variables = [
            "user_id" => 1,
            "filter"  => "users",
        ];
        $this->runGQL($query, $variables);
    }

    public function testFollowsQuery()
    {
        $query     = file_get_contents(__DIR__ . '/gql/follow/query/FollowsQuery.gql');
        $variables = [
            "user_id" => 1,
            "filter"  => "users",
        ];
        $this->runGQL($query, $variables);
    }
}
