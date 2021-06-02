<?php

namespace Haxibiao\Sns\Traits;

use App\Post;

trait LikeAttrs
{
    public function getQuestionAttribute()
    {
        $likeable = $this->likable;
        return $likeable instanceof \App\Question ? $likeable : null;
    }

    public function getCommentAttribute()
    {
        $likeable = $this->likable;
        return $likeable instanceof \App\Comment ? $likeable : null;
    }

    public function getPostAttribute()
    {
//        $likeable = $this->likable;
//        return $likeable instanceof \App\Post ? $likeable : null;
        return Post::query()->whereId($this->likable_id)->first();
    }

    // 兼容旧接口用
    // public function getLikedAttribute()
    // {
    //     if ($user = getUser(false)) {
    //         return $user->likes()
    //             ->byLikableType($this->likable_type)
    //             ->byLikableId($this->likable_id)->count() > 0;
    //     }
    //     return false;
    // }
}
