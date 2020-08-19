<?php

namespace Haxibiao\Sns\Traits;



use App\User;
use Haxibiao\Sns\Like;

trait LikeResolvers
{
    //resolvers
    public function resolveLikes($root, $args, $context, $info)
    {
        $user_id = $args['user_id'];
        if ($user = User::find($user_id)) {
            if (isset($args['type'])){
                return $user->likes()->where('likable_type',$args['type'])->latest('id')->with('likable');
            }
            return $user->likes()->latest('id')->with('likable');
        }
    }

    public function resolveToggleLike($root, $args, $context, $info)
    {
        $user = getUser();
        return static::toggle($user, $args['type'], $args['id']);
    }
}
