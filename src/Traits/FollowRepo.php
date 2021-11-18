<?php

namespace Haxibiao\Sns\Traits;

use App\User;
use App\Visit;
use Haxibiao\Breeze\Exceptions\UserException;
use Haxibiao\Sns\Follow;

trait FollowRepo
{
    public static function followToggle($type, $id)
    {
        //排除自己关注自己
        if ($type == 'users' && $id == getUserId()) {
            return null;
        }

        $follow = Follow::firstOrNew([
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
        }

        if(currentUser()){
            Visit::saveVisit(getUser(),$follow,'follows');
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
        throw_if(!$user,UserException::class,"该用户不存在哦！！");
    
        $followsBuilder = $user->follows();

        $filter = data_get($args,'filter');
        $followed_type = data_get($args,'followed_type');

        if($filter == null){
            $followsBuilder = $followsBuilder->where('followable_type', $followed_type);
        }

        if (isset($filter)) {
            $followsBuilder = $followsBuilder->where('followable_type', $filter);
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

        $filter = data_get($args,'filter');
        $followed_type = data_get($args,'followed_type');

        if($filter == null){
            $followsBuilder = $followersBuilder->where('followable_type', $followed_type);
        }

        if (isset($args['filter'])) {
            $followersBuilder = $followersBuilder->where('followable_type', $filter);
        }
        return $followersBuilder;
    }
}
