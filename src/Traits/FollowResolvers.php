<?php

namespace Haxibiao\Sns\Traits;

use GraphQL\Type\Definition\ResolveInfo;
use Haxibiao\Sns\Follow;
use Illuminate\Database\Eloquent\Relations\Relation;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

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

        //FIXME:前端很多地方还是用followed_id，兼容一下
        $followableId   = data_get($args, 'followed_id',data_get($args, 'followable_id'));
        $followableType = data_get($args, 'followed_type',data_get($args, 'followable_type'));
        $modelString    = Relation::getMorphedModel($followableType);
        $model          = $modelString::findOrFail($followableId);

        return $user->toggleFollow($model);
    }

    public function getByType($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        return \App\Follow::where('followable_type', data_get($args, 'followed_type', data_get($args, 'followable_type')));
    }

    public function createFollow($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        unset($args['directive']);
        $followable_type = data_get($args, 'followed_type', data_get($args, 'followable_type'));
        $followed_id     = data_get($args, 'followed_id', data_get($args, 'followable_id'));
        $args            = [
            'user_id'         => data_get($args, 'user_id'),
            'followable_type' => $followable_type,
            'followable_id'   => $followed_id,
        ];
        return \App\Follow::firstOrCreate($args);
    }

    public function resolveFollowerList($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        return Follow::query()->where('followable_type', data_get($args, 'followed_type', data_get($args, 'followable_type')))
            ->where('followable_id', data_get($args, 'followed_id', data_get($args, 'followable_id')));
    }

    public function resolveFollowList($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        return Follow::query()->where('followable_type', data_get($args, 'followed_type', data_get($args, 'followable_type')))
            ->where('user_id', data_get($args, 'user_id'));

    }
}
