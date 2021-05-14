<?php

namespace Haxibiao\Sns\Traits;

use App\Feedback;
use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

trait FeedbackResolvers
{
    public function resolveFeedback($root, $args, $context, $info)
    {
        app_track_event('反馈', '反馈详情ID', data_get($args, 'id'));
        return Feedback::getFeedback(data_get($args, 'id'));
    }

    public function resolveFeedbacks($root, $args, $context, $info)
    {
        app_track_event('反馈', '获取反馈列表');
        return Feedback::listFeedbacks(data_get($args, 'user_id'));
    }

    public function resolveCreateFeedback($root, $args, $context, $info)
    {
        app_track_event('反馈', '创建反馈');
        $user = getUser();
        return Feedback::store($user, $args);
    }

    public function resolveAllFeedbacks($root, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        return Feedback::orderBy('top_at', 'desc')->orderBy('rank', 'desc');
    }
}
