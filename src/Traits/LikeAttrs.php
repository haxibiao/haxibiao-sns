<?php

namespace Haxibiao\Sns\Traits;

use App\Comment;
use App\Question;
use App\Post;

trait LikeAttrs
{
    public function getQuestionAttribute()
    {
        $likeable = $this->likable;
        return $likeable instanceof Question ? $likeable : null;
    }

    public function getCommentAttribute()
    {
        $likeable = $this->likable;
        return $likeable instanceof Comment ? $likeable : null;
    }

    public function getPostAttribute()
    {
        $likeable = $this->likable;
        return $likeable instanceof Post ? $likeable : null;
    }

    // 兼容旧接口用
    public function getLikedAttribute()
    {
        if ($user = getUser(false)) {
            return $user->likes()
                    ->byLikableType($this->likable_type)
                    ->byLikableId($this->likable_id)->count() > 0;
        }
        return false;
    }
}
