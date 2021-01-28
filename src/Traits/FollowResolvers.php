<?php

namespace Haxibiao\Sns\Traits;

use GraphQL\Type\Definition\ResolveInfo;
use Haxibiao\Sns\Follow;
use Illuminate\Database\Eloquent\Relations\Relation;

trait FollowResolvers
{
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

    //关注

    public function resolveFollowToggle($root, $args, $context, ResolveInfo $info)
    {
        app_track_event('关注', $args['type'], $args['id']);
        return static::followToggle($args['type'], $args['id']);
    }

    public function toggleFollow($root, array $args, $context)
    {
        //只能简单创建
        $user           = getUser();
        $followableId   = data_get($args, 'followable_id');
        $followableType = data_get($args, 'followable_type');

        $modelString = Relation::getMorphedModel($followableType);
        $model       = $modelString::findOrFail($followableId);
        return $user->toggleFollow($model);
    }
}
