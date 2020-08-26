<?php

namespace Haxibiao\Sns\Traits;


use App\Comment;

trait CanComment
{

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

}