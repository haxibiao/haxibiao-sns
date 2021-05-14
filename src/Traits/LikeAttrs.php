<?php

namespace Haxibiao\Sns\Traits;

trait LikeAttrs
{
    public function getQuestionAttribute()
    {
        return $this->likable;
    }

    public function getCommentAttribute()
    {
        return $this->likable;
    }

    public function getPostAttribute()
    {
        return $this->likable;
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
