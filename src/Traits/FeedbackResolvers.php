<?php

namespace Haxibiao\Sns\Traits;

use App\Feedback;
use App\User;
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
        return Feedback::listFeedbacks(data_get($args, 'user_id'), data_get($args, 'type', null));
    }

    public function resolveDeleteFeedback($root, $args, $context, $info)
    {
        app_track_event("用户操作", "反馈", '删除反馈');
        return Feedback::find($args['id'])->delete();
    }
    public function resolveCreateFeedback($root, $args, $context, $info)
    {
        app_track_event("用户操作", "反馈", '创建反馈');
        //剧好看网页反馈需要一个公用的user来作匿名反馈
        if (config('app.name') == 'juhaokan') {
            $user = getUser(false);
            if (is_null($user)) {
                $user = User::find(3);
            }
        } else {
            $user = getUser();
        }

        // FIXME：反馈类型需要重构为简单的enum
        return Feedback::store($user, $args);
    }

    public function resolveAllFeedbacks($root, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        $qb = Feedback::orderBy('top_at', 'desc')->orderBy('rank', 'desc');
        //用户的反馈（如：我的反馈记录）
        if ($user_id = data_get($args, 'user_id')) {
            $qb->where('user_id', $user_id);
        }
        return $qb;
    }
}