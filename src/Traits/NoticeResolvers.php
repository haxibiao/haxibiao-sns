<?php

namespace Haxibiao\Sns\Traits;

use App\Visit;
use GraphQL\Type\Definition\ResolveInfo;
use Haxibiao\Sns\Notice;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

trait NoticeResolvers
{
    /**
     * 系统消息详情
     */
    public function resolveNotice($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        if (config('app.name') == 'datizhuanqian') {
            return Notice::getNotice($args['id']);
        }
        $noticbuild = Notice::where('expires_at', '>', now());
        return $noticbuild;
    }

    /**
     * 未过期系统消息
     */
    public function resolveNotices($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        if (config('app.name') == 'datizhuanqian') {
            $qb      = Notice::getNoticesQuery();
            $notices = $qb->get();
            $user    = getUser();
            Notice::markAsRead($user, $notices);
            return $qb;
        }
        //公共消息
        $publicNotice = \App\Notice::active()
            ->whereNull('to_user_id');
        //标记已读
        if ($user = getUser(false)) {
            Visit::saveVisits($user, $publicNotice->get(), 'notices');
        }

        return $publicNotice->orderBy('created_at', 'desc');
    }

    public function resolvePersonalNotices($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        //个人消息
        $user_id    = data_get($args, 'user_id');
        $noticbuild = Notice::active()
            ->where('to_user_id', $user_id);
        //标记已读
        if ($user = getUser(false)) {
            Visit::saveVisits($user, $noticbuild->get(), 'notices');
        }
        return $noticbuild->orderBy('created_at', 'desc');
    }

}
