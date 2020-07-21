<?php

namespace App\Traits\Like;



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
        $likeable = $this->likable;
        return $likeable instanceof \App\Post ? $likeable : null;
    }
}
