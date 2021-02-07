<?php

namespace Haxibiao\Sns\Tests\Feature\GraphQL;

use App\Feedback;
use App\User;
use Haxibiao\Breeze\GraphQLTestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class FeedbackTest extends GraphQLTestCase
{
    use DatabaseTransactions;
    protected $user;
    protected $feedback;
    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->feedback = Feedback::factory()->create([
            'content' => '测试反馈...',
            'contact' => 'xxx@abc.com',
            'user_id' => $this->user->id,
        ]);
    }

    /**
     * 创建意见反馈
     * @group feedback
     * @group testCreateFeedbackMutation
     */
    public function testCreateFeedbackMutation()
    {
        $query = file_get_contents(__DIR__ . '/Feedback/createFeedbackMutation.graphql');
        $headers = $this->getRandomUserHeaders($this->user);
        $variables = [
            'content' => '测试反馈 content',
            'contact' => '测试反馈 contact',
        ];
        $this->startGraphQL($query,$variables,$headers);

        //带图片的反馈 
        // TODO:图片上传存在问题，暂时不做图片上传的ut
        // $variables = [
        //     'content' => '测试反馈 content',
        //     'contact' => '测试反馈 contact',
        //     'images'  => [$this->getBase64ImageString()],
        // ];
        // $this->startGraphQL($query,$variables,$headers);
    }

    /**
     * 反馈查询
     * @group feedback
     * @group testFeedbacksQuery
     */
    public function testFeedbacksQuery()
    {
        $query = file_get_contents(__DIR__ . '/Feedback/feedbacksQuery.graphql');
        $variables = [];
        $this->startGraphQL($query,$variables);
    }

    /**
     * 我的反馈
     * @group feedback
     * @group testMyFeedbackQuery
     */
    public function testMyFeedbackQuery()
    {
        $query = file_get_contents(__DIR__ . '/Feedback/myFeedbackQuery.graphql');
        $headers = $this->getRandomUserHeaders($this->user);
        $variables = [
            'user_id' => $this->user->id,
        ];
        $this->startGraphQL($query,$variables,$headers);
    }

}