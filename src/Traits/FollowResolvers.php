<?php

namespace Haxibiao\Sns\Traits;

use GraphQL\Type\Definition\ResolveInfo;
use Haxibiao\Sns\Follow;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

trait FollowResolvers
{
    //关注操作

    public function resolveFollowToggle($root, $args, $context, ResolveInfo $info)
    {
        $type = data_get($args, 'type');
        $id   = data_get($args, 'id');

        //兼容下目前gqls里的参数
        if (!$type) {
            $type = data_get($args, 'followed_type', data_get($args, 'followable_type'));
        }
        if (!$id) {
            $id = data_get($args, 'followed_id', data_get($args, 'followable_id'));
        }

        app_track_event("用户操作", "关注" , "关注对象为: $type, 关注类型为: $id");
        return Follow::followToggle($type, $id);
    }

    //获取粉丝列表
    public function resolveFollowers($root, $args, $context, ResolveInfo $info)
    {
        app_track_event('用户页', '获取粉丝列表');
        return Follow::followers($args);
    }

    //获取关注列表
    public function resolveFollows($root, $args, $context, ResolveInfo $info)
    {
        app_track_event('用户页', '获取关注列表');
        return Follow::follows($args);
    }

    public function getByType($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        return \App\Follow::where('followable_type', data_get($args, 'followed_type', data_get($args, 'followable_type')));
    }

    public function resolveFollowerList($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        return Follow::query()->where('followable_type', data_get($args, 'followed_type', data_get($args, 'followable_type')))
            ->where('followable_id', data_get($args, 'followed_id', data_get($args, 'followable_id')));
    }

    public function resolveFollowList($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        return Follow::query()->where('followable_type', data_get($args, 'followed_type', data_get($args, 'followable_type')))
            ->where('user_id', data_get($args, 'user_id'))
            ->orderBy('created_at', 'desc');

    }
}
