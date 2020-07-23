<?php

namespace Haxibiao\Sns\Tests\Feature\GraphQL;


use Haxibiao\Base\GraphQLTestCase;

class FavoriteTest extends GraphQLTestCase
{
    public function testToggleFavoriteMutation()
    {
        $query     = file_get_contents(__DIR__ . '/gql/favorite/ToggleFavoriteMutation.gql');
        $variables = [
            "id"   => "1",
            "type" => "QUESTION",
        ];
        $this->runGQL($query, $variables);
    }

    public function testFavoritesQuery()
    {
        $query     = file_get_contents(__DIR__ . '/gql/favorite/FavoritesQuery.gql');
        $variables = [
            "type" => "QUESTION",
        ];
        $this->runGQL($query, $variables);
    }
}
