<?php

namespace Haxibiao\Sns\Tests\Feature\GraphQL;

use Haxibiao\Breeze\GraphQLTestCase;

class TipTest extends GraphQLTestCase
{

    protected $mutation;


    protected function setUp(): void
    {
        parent::setUp();

        $mutation = file_get_contents(__DIR__ . '/tip/TipMutation.gql');
    }


    /**
     * Mutation Test
     */

    //@Type POST
    protected function testTipMutationWithTypePOST(): void
    {
        $variables = [

        ];
    }
    //@Type ISSUE
    protected function testTipMutationWithTypeISSUE(): void
    {
        $variables = [

        ];
    }
    //@Type COMMENT
    protected function testTipMutationWithTypeCOMMENT(): void
    {
        $variables = [

        ];
    }

    /**
     * Query Test
     */
    protected function testTipQueryWithTypePOST(): void
    {
        $variables = [

        ];
    }
    protected function testTipQueryWithTypeISSUE(): void
    {
        $variables = [

        ];
    }
    protected function testTipQueryWithTypeCOMMENT(): void
    {
        $variables = [

        ];
    }


    public function tearDown(): void
    {

        parent::tearDown();
    }
}
