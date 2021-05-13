<?php

namespace Haxibiao\Sns\Traits;

use App\User;
use Haxibiao\Breeze\Events\NewFollow;

trait FollowRepo
{
    public static function followToggle($type, $id)
    {
        //排除自己关注自己
        if ($type == 'users' && $id == getUserId()) {
            return null;
        }

        $follow = static::firstOrNew([
            'user_id'         => getUserId(),
            'followable_id'   => $id,
            'followable_type' => $type,
        ]);

        //删除
        if (isset($follow->id)) {
            $follow->forceDelete();
            return $follow;
        } else {
            $follow->save();

            //触发广播和通知
            event(new NewFollow($follow));
        }
        return $follow;
    }

    public static function follows(array $args)
    {
        if (isset($args['user_id'])) {
            $user = User::find($args['user_id']);
        } else {
            $user = getUser();
        }
        $followsBuilder = $user->follows();

        if (isset($args['filter'])) {
            $followsBuilder = $followsBuilder->where('followable_type', $args['filter']);
        }

        return $followsBuilder;
    }

    public static function followers(array $args)
    {
        if (isset($args['user_id'])) {
            $user = User::find($args['user_id']);
        } else {
            $user = getUser();
        }
        $followersBuilder = $user->followers();

        if (isset($args['filter'])) {
            $followersBuilder = $followersBuilder->where('followable_type', $args['filter']);
        }
        return $followersBuilder;
    }
}
