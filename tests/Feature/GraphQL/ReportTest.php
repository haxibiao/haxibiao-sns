<?php

namespace Haxibiao\Sns\Tests\Feature\GraphQL;

use App\User;
use Haxibiao\Breeze\GraphQLTestCase;

class ReportTest extends GraphQLTestCase
{
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = factory(User::class)->create([
            'api_token' => str_random(60),
        ]);

    }
    /* --------------------------------------------------------------------- */
    /* ------------------------------- Mutation ---------------------------- */
    /* --------------------------------------------------------------------- */

    //举报
    public function testReportMutation()
    {
        $token   = $this->user->api_token;
        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Accept'        => 'application/json',
        ];
        $mutation = file_get_contents(__DIR__ . '/gql/report/ReportMutation.gql');
        //举报用户
        $variables = [
            'reason'      => '长得太帅',
            'report_id'   => $this->user->id,
            //type 实则为 Enum，有users，posts等类型，这里写死为users不太好，但这并不影响测试功能是否可用
            'report_type' => 'USER',
        ];
        $this->runGuestGQL($mutation, $variables, $headers);
    }

    /* --------------------------------------------------------------------- */
    /* ------------------------------- Query ------------------------------- */
    /* --------------------------------------------------------------------- */

    protected function tearDown(): void
    {
        $this->user->forceDelete();
        parent::tearDown();
    }
}
