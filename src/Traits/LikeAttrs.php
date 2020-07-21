<?php

namespace Haxibiao\Sns\Traits;

use App\Comment;
use App\Question;
use Haxibiao\Content\Post;

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
}
